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

function check_reminders(bool $force = false) {
	
	/*
delete from reminder_checks;
update workshops set reminder_sent = 0;
update xtra_sessions set reminder_sent = 0;
	*/

	// check reminder database -- has it been 6 hours?
	$stmt = \DB\pdo_query("select * from reminder_checks order by id desc limit 1"); // most recent check
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		if ((time() - strtotime($row['time_checked'])) / 3600 <= 4 && ($force !== true)) { return false; } // checked less than four hours ago
		 
	}
	
	// if yes, get a list of all workshops that have yet to start within REMINDER_HOURS
	$classes_to_remind = array();
	$mysqlnow = date(MYSQL_FORMAT);
	
	
	// set up $workshops_to_remind
	// first number id, second number xtra_session row id (0 if not an xtra session)
	
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
	
	$guest = new \User();
	
	$wk = \Workshops\get_workshop_info($class[0]);
	$xtra = \XtraSessions\get_xtra_session($class[1]);
	
	$reminder = get_reminder_message_data($wk, $xtra);
	
	$subject = $reminder['subject'];
	$note = $reminder['note'];
	
	$eh = new \EnrollmentsHelper();
	$stds = $eh->get_students($class[0], ENROLLED);

	//$base_msg =	$note.\Emails\get_workshop_summary($wk);

	foreach ($stds as $std) {
		
		$note = $reminder['note'];

		// add note if student has to pay
		if (!$std['paid']) {
			$note .= \Emails\payment_text($wk, 1);
		}
		
		$trans = URL."workshop/view/{$wk['id']}";

		$note .= \Workshops\email_teacher_info($wk);

		if (!$class[1]) { // if this not an xtra session or a show
			$note .= "<p>DROPPING OUT<br>\n
	---------------------------------<br>\n
	If you need to drop the class (and it's before the first week), you can do so on this web site at this link. That way if someone is on the waiting list, we can notify them right away they have a chance to join.<br>
	{$trans}</p>\n";
		}

		$note .= \Emails\email_boilerplate();

		$base_msg =	$note.\Emails\get_workshop_summary($wk)."<br>
Class info on web site: $trans";
				
		//\Emails\centralized_email('whines@gmail.com', $subject, $base_msg); // for testing, i get everything

		\Emails\centralized_email($std['email'], $subject, $base_msg);
		$guest->set_by_id($std['id']);
	}
	//remind teacher
	$trans = URL."workshop/view/{$wk['id']}";
	$teacher_reminder = get_reminder_message_data($wk, $xtra, true);
	$msg = $teacher_reminder['note']."<p>Class info online:<br>$trans</p>\n";
	
	if (!$xtra['id']) { // is it first session? send teacher the roster
		$msg .= "<h3>Full info for class</h3>\n".
			preg_replace('/\n/', "<br>\n", \Workshops\get_cut_and_paste_roster($wk));
	}
	
	\Emails\centralized_email($wk['teacher_info']['email'], $teacher_reminder['subject'], $msg);
	
	// if not full -- point it out to Will
	if ($wk['enrolled'] < $wk['capacity']) {
		
		$alert_msg = "'{$wk['title']}' is not full. {$wk['enrolled']} of {$wk['capacity']} signed up<br>\n".
			URL."admin-workshop/view/{$wk['id']}<br>\n".
				\Emails\get_workshop_summary($wk);
		
		\Emails\centralized_email(WEBMASTER, "'{$wk['title']}' is not full.", $alert_msg);
	}
	
	//\Emails\centralized_email('whines@gmail.com', $subject, $msg);
	$logger->info("Reminders sent for: {$class[2]}");
	
	
}

function get_reminder_message_data(array $wk, array $xtra, bool $teacher = false) {
	
	if ($xtra['id']) {
		$start = $xtra['friendly_when'];
		if ($xtra['online_url_display']) {
			$link = $xtra['online_url_display'];
		} else {
			$link = $wk['online_url_display'];
		}
		$subject = "WGIS class reminder: {$wk['title']} {$start}";
	} else {
		$start = $wk['when'];
		$link = $wk['online_url_display'];
		$subject = "WGIS class reminder: {$wk['title']} {$start}";
	}
	
	
	if ($wk['location_id'] != ONLINE_LOCATION_ID) {
		$subject .= " at {$wk['place']}";	
	}
	
	if ($xtra['class_show']) {
		$note = "<p>Greetings. ".($teacher ? "You are teaching" :  "You have")." a class show soonish, ";
	} elseif ($xtra['id']) {
		$note = "<p>Greetings. ".($teacher ? "You are teaching" :  "You have")." another session of this class soonish, ";
	} else {
		$note = "<p>Greetings. ".($teacher ? "You are teaching" :  "You are enrolled in")." a class that starts soonish, ";
	}

	$note .= "specifically at $start (".TIMEZONE.").</p>\n";
	
	if ($wk['location_id'] == ONLINE_LOCATION_ID) {
		
		$note .= "<p>ZOOM LINK:<br>
Here's the zoom link. Try to sign in a few minutes early if you can.<br>
$link</p>\n"; 

		// should be workshop url or xtra_session url, set in lib_workshops.php fill_out_workshop_row
		if ($link != $wk['online_url_display']) {
			$note .= "<p>Please note: this is a DIFFERENT LINK than you usually use for this class!</p>\n";
		}		
		
		if ($xtra['class_show']) {
			$note .= "<p>Invite your friends and family to watch the show at the Twitch channel:<br>
https://www.twitch.tv/wgimprovschool</p>\n";
		}
	} else {
		$note .= "<p>LOCATION:<br>\n---------<br>\n{$wk['place']}<br>\n{$wk['address']}<br>\n{$wk['city']}, {$wk['state']} {$wk['zip']}</p>\n";
		
	}	


	
	return array(
	 'subject' => $subject,
	 'note' => $note
	);

	
}