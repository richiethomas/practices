<?php
$heading = "send messages";
include 'lib-master.php';

$mess_vars = array('st', 'note', 'subject', 'sms', 'cancellation');
Wbhkit\set_vars($mess_vars);
if (!$st) { $st = ENROLLED; }

$long_where = "{$wk['place']} {$wk['lwhere']}";

// multiple sessions?
$wk['when'] = \XtraSessions\add_sessions_to_when($wk['when'], $wk['sessions']);

switch ($ac) {
			
	case 'sendmsg':

		if (!$st) {
			$error = 'No status chosen';
			break;
		}
		if (!$wk['id']) {
			$error = 'No workshop chosen';
			break;
		}
		$stds = Enrollments\get_students($wk['id'], $st);
		$sent = '';
		$subject = preg_replace('/TITLE/', $wk['title'], $subject);
		$note = preg_replace('/TITLE/', $wk['title'], $note);
		$sms = preg_replace('/TITLE/', $wk['title'], $sms);


		$note = preg_replace('/\R/', "<br>", $note);
		

		$base_msg = $note.Emails\get_workshop_summary($wk);


		foreach ($stds as $std) {
			$key = Users\get_key($std['id']);
			$trans = URL."workshop.php?key=$key&wid={$wk['id']}";
			$msg = $base_msg."<p>Drop/re-enroll/see more info here:<br>$trans</p>\n";
			
			Emails\centralized_email($std['email'], $subject, $msg);
			$sent .= "{$std['email']}, ";
		
			Emails\send_text($std, $sms); // routine will check if they want texts and have proper info
		
		}
		$message = "Email '$subject' sent to $sent";
		$logger->info($message);
		break;

	case 'emails':

		$students = Enrollments\get_students($wk['id'], $st);

		$names = array();
		$just_emails = array();
		foreach ($students as $s) {
			$names[] = "{$s['nice_name']} - {$s['email']}";
			$just_emails[] = "{$s['email']}";
		}
		sort($names);
		sort($just_emails);

		$subject = "emails for '{$wk['title']}'";
		$note = "Here is the list of emails for your class. First is just the email addresses, ready to be cut-and-pasted into a blank email. Then it's a list of the emails with their actual human names. Unless I (the computer) do not know their name, in which case it's just their email again.\n\n".implode("\n", $just_emails)."\n\n".implode(",\n", $names);
		$sms = null;
		$st = ENROLLED; // pre-populating the status drop in 'send message' form
		break;

	case 'feedback':
		$subject = "Feedback for '{$wk['title']}'";
		$note = "Thank you for taking this workshop!

If you want: I'd love to know feedback on the workshop. Any suggestions of what you liked or what you'd change.

No worries if you'd rather not answer! Thank you all again for taking it!

-Will";
		$st = ENROLLED; // pre-populating the status drop in 'send message' form
		break;


	case 'cancel':
		$subject = "{$wk['title']}";
		$note = "<p>I had to cancel this workshop! I'm so sorry.<br>-Will</p>";
		$st = ENROLLED; // pre-populating the status drop in 'send message' form
		$sms = "Workshop cancelled: {$wk['title']}";
		$cancellation = 1;
		break;
						
}
if (!$wk['id']) {
	$view->renderPage('admin/error');	
} else {
	$view->add_globals($mess_vars);		
	$students = array();
	foreach ($statuses as $stid => $status_name) {
		$students[$stid] = Enrollments\get_students($wid, $stid);
	}	
	$view->data['students'] = $students;
	$view->data['statuses'] = $statuses;
	$view->renderPage('admin/messages');
}






