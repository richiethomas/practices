<?php
$sc = "admin_messages.php";
$heading = "practices: admin";
include 'lib-master.php';
include 'libs/validate.php';

$mess_vars = array('st', 'note', 'subject', 'sms');
Wbhkit\set_vars($mess_vars);

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
		$subject = preg_replace('/TITLE/', $wk['showtitle'], $subject);
		$note = preg_replace('/TITLE/', $wk['showtitle'], $note);
		$sms = preg_replace('/TITLE/', $wk['showtitle'], $sms);

		foreach ($stds as $std) {
			$key = Users\get_key($std['id']);
			$trans = URL."index.php?key=$key";
			$msg = $note;
			$msg .= "\n\nLog in or drop out here:\n$trans\n";
			$msg .= "
Regarding this practice:
Title: {$wk['showtitle']}
Where: {$wk['place']}
When: {$wk['when']}";
			\Emails\centralized_email($std['email'], $subject, $msg);
			$sent .= "{$std['email']}, ";
		
			Emails\send_text($std, $sms); // routine will check if they want texts and have proper info
		
		}
		$message = "Email '$subject' sent to $sent";
		break;

	case 'remind':
		$subject = "REMINDER: workshop {$wk['friendly_when']} at {$wk['place']}";
		$note = "Hey! You're enrolled in this workshop. ";
		if ($wk['type'] == 'past') {
			$note .= "Actually, it looks like this workshop is in the past, which means this reminder was probably sent in error. But since I'm just a computer, then maybe there's something going on that I don't quite grasp. At any rate, this is a reminder. ";
		} else {
			$note .= "It starts ".nicetime($wk['start']).".";
		}
		$note .=" If you think you're not going to make it, that's fine but use the link below to drop out. ";
		if ($wk['waiting'] > 0) {
			$note .= "There are currently people on the waiting list who might want to go. ";
		}
		$note .= " Okay, see you soon!";
		$sms = "Reminder: workshop {$wk['friendly_when']} at {$wk['place']}";
		$st = ENROLLED; // pre-populating the status drop in 'send message' form
		break;

	case 'feedback':
		$subject = "Feedback for '{$wk['showtitle']}'";
		$note = "Thank you for taking this workshop!

If you want: I'd love to know feedback on the workshop. Any suggestions of what you liked or what you'd change.

No worries if you'd rather not answer! Thank you all again for taking it!

-Will";
		$st = ENROLLED; // pre-populating the status drop in 'send message' form
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






