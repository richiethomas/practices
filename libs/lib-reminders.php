<?php
namespace Reminders;

function check_reminder() {
	
	/*
delete from reminder_checks;
update workshops set reminder_sent = 0;
update xtra_sessions set reminder_sent = 0;
	*/

	// check reminder database -- has it been 6 hours?
	$stmt = \DB\pdo_query("select * from reminder_checks order by id desc limit 1"); // most recent check
	while ($row = $stmt->fetch()) {
		if ((time() - strtotime($row['time_checked'])) / 3600 <= 6) { return false; } // checked less than six hours ago
		 
	}
	
	// if yes, get a list of all workshops that have yet to start within REMINDER_HOURS
	$workshops_to_remind = array();
	$mysqlnow = date("Y-m-d H:i:s");
	
	
	// set up $workshops_to_remind
	// first number id, second number xtra_session row id (0 if not an xtra session)
	
	
	$stmt = \DB\pdo_query("select id, start from workshops where start > :now and reminder_sent = 0", array(':now' => $mysqlnow)); // workshops in the future
	while ($row = $stmt->fetch()) {
		if ((strtotime($row['start']) - time()) / 3600 < REMINDER_HOURS) {
			$workshops_to_remind[] = array($row['id'], 0);
		}
	}
	
	$stmt = \DB\pdo_query("select id, workshop_id, start, online_url from xtra_sessions where start > :now and reminder_sent = 0", array(':now' => $mysqlnow)); // workshops in the future
	while ($row = $stmt->fetch()) {
		if ((strtotime($row['start']) - time()) / 3600 < REMINDER_HOURS) {
			$workshops_to_remind[] = array($row['workshop_id'], $row['id']); 
		}
	}
	
	// go through each workshop that start in that window, and send remidners
	$wk = array();
	foreach ($workshops_to_remind as $wk_id_info) {
		$wk = \Workshops\get_workshop_info($wk_id_info[0]);
		
		remind_enrolled($wk);
		if ($wk_id_info[1] > 0) {
			$stmt = \DB\pdo_query("update xtra_sessions set reminder_sent = 1 where id = :id", array(':id' => $wk_id_info[1])); // most recent check
		} else {
			$stmt = \DB\pdo_query("update workshops set reminder_sent = 1 where id = :id", array(':id' => $wk_id_info[0])); // most recent check
		}
	}
	
	// add a row to reminder check
	$stmt = \DB\pdo_query("insert into reminder_checks (time_checked, reminders_sent) VALUES (:now, :rsent)", array(':now' => $mysqlnow, ':rsent' => count($workshops_to_remind))); // most recent check

}

function remind_enrolled($wk) {
	$reminder = get_reminder_message_data($wk);
	$subject = $reminder['subject'];
	$note = $reminder['note'];
	$sms = $reminder['sms'];
	
	$stds = \Enrollments\get_students($wk['id'], ENROLLED);

	$base_msg =	$note.\Emails\get_workshop_summary($wk);

	foreach ($stds as $std) {
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
		$msg = $base_msg."<p>Class info online:<br>$trans</p>\n";
		\Emails\centralized_email($wk['teacher_email'], $subject, $msg);
	}
	
	// if not full -- point it out to Will
	if ($wk['enrolled'] < $wk['capacity']) {
		
		$alert_msg = "'{$wk['title']}' is not full. {$wk['enrolled']} of {$wk['capacity']} signed up<br>\n".
			URL."admin_edit.php?wid={$wk['id']}<br>\n".
				\Emails\get_workshop_summary($wk);
		
		\Emails\centralized_email(WEBMASTER, "'{$wk['title']}' is not full.", $alert_msg);
	}
	
	//\Emails\centralized_email('whines@gmail.com', $subject, $msg);
	
	
}

function get_reminder_message_data($wk) {
	
	$subject = "REMINDER: {$wk['title']} ".($wk['nextsession_show'] ? 'CLASS SHOW - ' : '')."{$wk['nextstart']}";
	if ($wk['location_id'] != ONLINE_LOCATION_ID) {
		$subject .= " at {$wk['place']}";	
	}
	
	if ($wk['nextsession_show'] == 1) {
		$note = "<p>Greetings. You have a class show tomorrow! ";
	} elseif ($wk['nextsession_extra']) {
		$note = "<p>Greetings. You have another session of this class tomorrow. ";
	} else {
		$note = "<p>Greetings. You're enrolled in a class that starts tomorrow. ";
	}

	$note .= "It starts ".\TimeDifference\nicetime($wk['nextstart_raw']).".</p>\n";
	
	if ($wk['location_id'] == ONLINE_LOCATION_ID) {
		
		$note .= "<p>Here's the link: {$wk['nextstart_url']}</p>\n";  
		// should be workshop url or xtra_session url, set in lib_workshops.php fill_out_workshop_row
		if ($wk['nextstart_url'] != $wk['online_url']) {
			$note .= "<p>Please note: this is a DIFFERENT LINK than you usually use for this class!</p>\n";
		}
		
		if ($wk['nextsession_show'] == 1) {
			$note .= "<p>Invite your friends and family to watch the show at the YouTube Channel:<br>https://www.youtube.com/channel/UCxM09rN2BoddZYtqnlPBucQ<br>(Every show is streamed to that channel - same YouTube link for all class shows)</p>\n";
		}

		

	}			
	$sms = "Reminder: {$wk['title']} ".($wk['nextsession_show'] ? 'class show' : 'class').", {$wk['nextstart']}, ".URL;
	
	return array(
	 'subject' => $subject,
	 'note' => $note,
	 'sms' => $sms	
	);
	
}