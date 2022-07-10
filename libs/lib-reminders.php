<?php
namespace Reminders;

function get_reminders(int $how_many = 100) {
	
	// check reminder database -- has it been 6 hours?
	$stmt = \DB\pdo_query("select * from reminder_checks order by id desc limit :howmany", array(':howmany' => $how_many)); 
	$reminders = array();
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		$reminders[] = $row;
	}
	return $reminders;
	
}

function check_tasks(bool $force = false) {
	

	$th = new \TasksHelper();
	$th->get_upcoming_tasks();
	
	foreach ($th->tasks as $t) {
		
		if ($t->reminder_email->fields['id'] == 1) { continue; }  // reminder email 1 = do not send any
		
		\Emails\centralized_email(
			$t->user->fields['email'], 
			$t->reminder_email->fields['subject'],
			$t->reminder_email->fields['body'],
			$t->user->fields['display_name'] );
			
		// test	
		\Emails\centralized_email(
			WEBMASTER, 
			$t->reminder_email->fields['subject'],
			$t->reminder_email->fields['body'],
			$t->user->fields['display_name'] );
			
		$t->update_reminder_sent(true);
		
	}
	
}


function check_reminders(bool $force = false) {
	
	// check reminder database -- has it been 4 hours?
	$stmt = \DB\pdo_query("select * from reminder_checks order by id desc limit 1"); // most recent check
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		if ((time() - strtotime($row['time_checked'])) / 3600 <= 4 && ($force !== true)) { return false; } // checked less than four hours ago
		 
	}
	
	check_tasks($force); 
	
	// if yes, get a list of all workshops that have yet to start within REMINDER_HOURS
	$classes_to_remind = array();
	$mysqlnow = date(MYSQL_FORMAT);
	
	
	// set up $workshops_to_remind
	// first number id, second number xtra_session row id (0 for first session)
	
	// first do workshops table - these are session 1s
	$stmt = \DB\pdo_query("select id as workshop_id, start, w.title from workshops w where start > :now and reminder_sent = 0", array(':now' => $mysqlnow)); // workshops in the future
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		if ((strtotime($row['start']) - time()) / 3600 < REMINDER_HOURS) {
			$classes_to_remind[] = array($row['workshop_id'], 0, $row['title']);
		}
	}
	
	// xtra sessions - session 2 and higher, including shows
	$stmt = \DB\pdo_query("select x.id, x.workshop_id, x.start, x.class_show, w.title from xtra_sessions x, workshops w where x.workshop_id = w.id and x.start > :now and x.reminder_sent = 0", array(':now' => $mysqlnow)); // workshops in the future
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		if ((strtotime($row['start']) - time()) / 3600 < REMINDER_HOURS) {
			$classes_to_remind[] = array($row['workshop_id'], $row['id'], $row['title']); 
		}
	}
	
	// go through each workshop that start in that window, and send reminders
	$wk = array();
	foreach ($classes_to_remind as $class) {
		remind_enrolled($class);
		if ($class[1] > 0) {
			$stmt = \DB\pdo_query("update xtra_sessions set reminder_sent = 1 where id = :id", array(':id' => $class[1])); // most recent check
		} else {
			$stmt = \DB\pdo_query("update workshops set reminder_sent = 1 where id = :id", array(':id' => $class[0])); // most recent check
		}
	}
	
	// add a row to reminder check
	$stmt = \DB\pdo_query("insert into reminder_checks (time_checked, reminders_sent) VALUES (:now, :rsent)", array(':now' => $mysqlnow, ':rsent' => count($classes_to_remind))); // most recent check

}

function remind_enrolled(array $class) {
	
	global $logger;
	
	$wk = new \Workshop();
	$wk->set_by_id($class[0]);
	$xtra = \XtraSessions\get_xtra_session($class[1]);	
	$eh = new \EnrollmentsHelper();
	$stds = $eh->get_students($class[0], ENROLLED);

	//$base_msg =	$note.\Emails\get_workshop_summary($wk);

	foreach ($stds as $std) {
		
		if (!$std['time_zone']) { $std['time_zone'] = DEFAULT_TIME_ZONE; }
		
		$wk->format_times($std['time_zone']);
		$xtra = $wk->format_times_one_level($xtra, $std['time_zone']);
		
		$subject = get_subject($wk, $xtra);
		$note = get_note($wk, $xtra, $std['nice_name']);
		$trans = URL."workshop/view/{$wk->fields['id']}";

		// add note if student has to pay
		if (!$std['paid']) {
			$note .= \Emails\payment_text($wk, 1);
		}

		$note .= $wk->email_teacher_info();

		if (!$class[1]) { // if this not an xtra session, then it's class 1
			$note .= "<p>DROPPING OUT<br>\n
	---------------------------------<br>\n
	If you need to drop the class, you can do so on this web site at this link. That way if someone is on the waiting list, we can notify them right away so they have a chance to join.<br>
	{$trans}</p>\n";
		}

		$note .= \Emails\email_boilerplate();

		$note =	$note.\Emails\get_workshop_summary($wk);
				
		//\Emails\centralized_email('whines@gmail.com', $subject, $note); // for testing, i get everything

		\Emails\centralized_email($std['email'], $subject, $note);
	}
	
	//remind teacher
	remind_teacher($wk, $xtra, $wk->teacher);
	if ($wk->fields['co_teacher_id']) {
		remind_teacher($wk, $xtra, $wk->coteacher);
	}
	
	// if not full -- point it out to Will
	if ($wk->fields['enrolled'] < $wk->fields['capacity'] && !$xtra['id']) { // no $xtra['id'] means first session
		$guest = new \User();
		$guest->set_by_id(1); // that's right, hard-coded
		
		$wk->format_times($guest->fields['time_zone']);
		
		$alert_msg = "'{$wk->fields['title']}' is not full. {$wk->fields['enrolled']} of {$wk->fields['capacity']} signed up<br>\n".
			URL."admin-workshop/view/{$wk->fields['id']}<br>\n".
				\Emails\get_workshop_summary($wk);
		
		\Emails\centralized_email($guest->fields['email'], "'{$wk->fields['title']}' is not full.", $alert_msg);
	}


	// done!
	$logger->info("Reminders sent for: {$class[2]}");
	
	
}

function get_subject(\Workshop $wk, array $xtra) {

	$subject = "WGIS class reminder: {$wk->fields['title']} ".($xtra['id'] ? $xtra['when'] : $wk->fields['when']);

	if ($wk->fields['location_id'] != ONLINE_LOCATION_ID) {
		$subject .= " at {$wk->location['place']}";	
	}
	
	return $subject;
	
}


function get_note(\Workshop $wk, array $xtra, $name = 'dear human', bool $teacher = false) {
	
	$note = null;
	if ($xtra['class_show']) {
		$note = "<p>Greetings, $name! ".($teacher ? "You are teaching" :  "You have")." a class show soonish, ";
	} elseif ($xtra['id']) {
		$note = "<p>Greetings, $name! ".($teacher ? "You are teaching" :  "You have")." another session of this class soonish, ";
	} else {
		$note = "<p>Greetings, $name! ".($teacher ? "You are teaching" :  "You are enrolled in")." a class that starts soonish, ";
	}
	
	$note .= "specifically at ".($xtra['id'] ? $xtra['when'] : $wk->fields['when']).".</p>\n";
	
	if ($wk->fields['location_id'] == ONLINE_LOCATION_ID) {
		
		//echo "{$wk['online_url']}, {$wk['online_url_display']}<br>";
		//echo "{$xtra['online_url']}, {$xtra['online_url_display']}<br>";
		
		
		if (isset($xtra['id']) && $xtra['id']) {
			$link = $xtra['url']['online_url_display'] ? $xtra['url']['online_url_display'] : $wk->url['online_url_display'];
		} else {
			$link = $wk->url['online_url_display'];
		}
		
		$note .= "<p>ZOOM LINK:<br>
Here's the zoom link. Try to sign in a few minutes early if you can.<br>
$link</p>\n"; 

		// should be workshop url or xtra_session url, set in lib_workshops.php fill_out_workshop_row
		if ($link != $wk->url['online_url_display']) {
			$note .= "<p>Please note: this is a DIFFERENT LINK than you usually use for this class!</p>\n";
		}		
		
		if ($xtra['class_show']) {
			$note .= "<p>Invite your friends and family to watch the show at the Twitch channel:<br>
https://www.twitch.tv/wgimprovschool</p>\n";
		}
	} else {
		$note .= "<p>LOCATION:<br>\n---------<br>\n{$wk->location['place']}<br>\n{$wk->location['address']}<br>\n{$wk->location['city']}, {$wk->location['state']} {$wk->location['zip']}</p>\n";
		
	}	

	return $note;
}

function remind_teacher(\Workshop $wk, $xtra, $teacher_info) {
	
	$wk->format_times($teacher_info['time_zone']);
	$xtra = $wk->format_times_one_level($xtra, $teacher_info['time_zone']);
	
	$subject = get_subject($wk, $xtra);
	$note = get_note($wk, $xtra, $teacher_info['nice_name'], true);
	
	if (!$xtra['id']) { // is it first session? send teacher the roster
		$note .= "<h3>Full info for class</h3>\n".
			preg_replace('/\n/', "<br>\n", $wk->get_cut_and_paste_roster());
	} else {
		$note .= \Emails\get_workshop_summary($wk);
	}
	\Emails\centralized_email($teacher_info['email'], $subject, $note);
	
}