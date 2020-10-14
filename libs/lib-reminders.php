<?php
namespace Reminders;


function get_reminders($how_many = 100) {
	
	// check reminder database -- has it been 6 hours?
	$stmt = \DB\pdo_query("select * from reminder_checks order by id desc limit :howmany", array(':howmany' => $how_many)); 
	$reminders = array();
	while ($row = $stmt->fetch()) {
		$reminders[] = $row;
	}
	return $reminders;
	
}

function check_reminder($force = false) {
	
	/*
delete from reminder_checks;
update workshops set reminder_sent = 0;
update xtra_sessions set reminder_sent = 0;
	*/

	// check reminder database -- has it been 6 hours?
	$stmt = \DB\pdo_query("select * from reminder_checks order by id desc limit 1"); // most recent check
	while ($row = $stmt->fetch()) {
		if ((time() - strtotime($row['time_checked'])) / 3600 <= 4 && ($force !== true)) { return false; } // checked less than six hours ago
		 
	}
	
	// if yes, get a list of all workshops that have yet to start within REMINDER_HOURS
	$classes_to_remind = array();
	$mysqlnow = date("Y-m-d H:i:s");
	
	
	// set up $workshops_to_remind
	// first number id, second number xtra_session row id (0 if not an xtra session)
	
	// first do workshops table - these are session 1s
	$stmt = \DB\pdo_query("select id as workshop_id, start from workshops w where start > :now and reminder_sent = 0", array(':now' => $mysqlnow)); // workshops in the future
	while ($row = $stmt->fetch()) {
		if ((strtotime($row['start']) - time()) / 3600 < REMINDER_HOURS) {
			$classes_to_remind[] = array($row['workshop_id'], 0);
		}
	}
	
	$stmt = \DB\pdo_query("select id, workshop_id, start from xtra_sessions where start > :now and reminder_sent = 0", array(':now' => $mysqlnow)); // workshops in the future
	while ($row = $stmt->fetch()) {
		if ((strtotime($row['start']) - time()) / 3600 < REMINDER_HOURS) {
			$classes_to_remind[] = array($row['workshop_id'], $row['id']); 
		}
	}
	
	// go through each workshop that start in that window, and send remidners
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

function remind_enrolled($class) {
	$wk = \Workshops\get_workshop_info($class[0]);
	$xtra = \XtraSessions\get_xtra_session($class[1]);
	
	$reminder = get_reminder_message_data($wk, $xtra);
	
	$subject = $reminder['subject'];
	$note = $reminder['note'];
	$sms = $reminder['sms'];
	
	$stds = \Enrollments\get_students($class[0], ENROLLED);

	$base_msg =	$note.\Emails\get_workshop_summary($wk);

	foreach ($stds as $std) {
		
		// add note if student has to pay
		$note = $reminder['note'];
		if (!$std['paid'] && $wk['cost']) {
			$note .= "<p>Our records show you have not yet paid. If that's true, please pay by the beginning of class. Send {$wk['cost']} USD via venmo @willhines or paypal whines@gmail.com.<br>
If you've got  questions/concerns about this, send them to ".WEBMASTER."</p>";
		}
		$base_msg =	$note.\Emails\get_workshop_summary($wk);
		
		
		$key = \Users\get_key($std['id']);
		$trans = URL."workshop.php?key=$key&wid={$wk['id']}";
		$msg = $base_msg."<p>Class info online:<br>$trans</p>\n";
		
		//\Emails\centralized_email('whines@gmail.com', $subject, $msg); // for testing, i get everything

		if (!LOCAL) {
			\Emails\centralized_email($std['email'], $subject, $msg);
			\Emails\send_text($std, $sms); // routine will check if they want texts and have proper info
		}
	}
	//remind teacher
	if (!LOCAL) {
				
		$trans = URL."workshop.php?key={$wk['teacher_key']}&wid={$wk['id']}";
		$teacher_reminder = get_reminder_message_data($wk, $xtra, true);
		$msg = $teacher_reminder['note']."<p>Class info online:<br>$trans</p>\n";
		
		if (!$xtra['id']) { // is it first session? send teacher the roster
			$msg .= "<h3>List of students in your class</h3>\n".
				preg_replace('/\n/', "<br>\n", \Workshops\get_cut_and_paste_roster($wk));
		}
		
		\Emails\centralized_email($wk['teacher_email'], $teacher_reminder['subject'], $msg);
	}
	
	// if not full -- point it out to Will
	if ($wk['enrolled'] < $wk['capacity'] && !LOCAL) {
		
		$alert_msg = "'{$wk['title']}' is not full. {$wk['enrolled']} of {$wk['capacity']} signed up<br>\n".
			URL."admin_edit.php?wid={$wk['id']}<br>\n".
				\Emails\get_workshop_summary($wk);
		
		\Emails\centralized_email(WEBMASTER, "'{$wk['title']}' is not full.", $alert_msg);
	}
	
	//\Emails\centralized_email('whines@gmail.com', $subject, $msg);
	
	
}

function get_reminder_message_data($wk, $xtra, $teacher = false) {
	
	
	if ($xtra['id']) {
		$subject = "REMINDER: {$wk['title']} ".($xtra['class_show'] ? 'CLASS SHOW - ' : '')."{$xtra['friendly_when']}";
	} else {
		$subject = "REMINDER: {$wk['title']} {$wk['when']}";
	}
	
	
	if ($wk['location_id'] != ONLINE_LOCATION_ID) {
		$subject .= " at {$wk['place']}";	
	}
	
	if ($xtra['class_show'] == 1) {
		$note = "<p>Greetings. ".($teacher ? "You are teaching" :  "You have")." a class show soonish, ";
	} elseif ($xtra['id']) {
		$note = "<p>Greetings. ".($teacher ? "You are teaching" :  "You have")." another session of this class soonish, ";
	} else {
		$note = "<p>Greetings. ".($teacher ? "You are teaching" :  "You are enrolled in")." a class that starts soonish, ";
	}

	$note .= "specifically ".\TimeDifference\nicetime($xtra['id'] ? $xtra['start'] : $wk['start']).".</p>\n";
	
	if ($wk['location_id'] == ONLINE_LOCATION_ID) {
		
		$note .= "<p>Here's the zoom link: ".($xtra['online_url'] ? $xtra['online_url'] : $wk['online_url'])."</p>\n";  
		// should be workshop url or xtra_session url, set in lib_workshops.php fill_out_workshop_row
		if ($xtra['online_url']) {
			$note .= "<p>Please note: this is a DIFFERENT LINK than you usually use for this class!</p>\n";
		}
		
		if ($xtra['class_show'] == 1) {
			$note .= "<p>Invite your friends and family to watch the show at the YouTube Channel:<br>https://www.youtube.com/channel/UCxM09rN2BoddZYtqnlPBucQ<br>(Every show is streamed to that channel - same YouTube link for all class shows)</p>\n";
		}

		
	}			
	
	$sms = "Reminder: {$wk['title']} ".($xtra['class_show'] ? 'class show' : 'class').", {$wk['nextstart']}, ".URL;
	
	return array(
	 'subject' => $subject,
	 'note' => $note,
	 'sms' => $sms	
	);
	
}