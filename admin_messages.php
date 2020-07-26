<?php
$sc = "admin_messages.php";
$heading = "practices: admin";
include 'lib-master.php';

$mess_vars = array('st', 'note', 'subject', 'sms', 'cancellation');
Wbhkit\set_vars($mess_vars);

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

	case 'remind':
		$reminder = Reminders\get_reminder_message_data($wk);
		$subject = $reminder['subject'];
		$note = $reminder['note'];
		$sms = $reminder['sms'];
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
	$view->renderPage('admin_error');	
} else {
	$view->add_globals($mess_vars);		
	$students = array();
	foreach ($statuses as $stid => $status_name) {
		$students[$stid] = Enrollments\get_students($wid, $stid);
	}	
	$view->data['students'] = $students;
	$view->data['statuses'] = $statuses;
	$view->renderPage('admin_messages');
}






