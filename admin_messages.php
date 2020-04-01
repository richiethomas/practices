<?php
$sc = "admin_messages.php";
$heading = "practices: admin";
include 'lib-master.php';
include 'libs/validate.php';

$mess_vars = array('st', 'note', 'subject', 'sms', 'cancellation');
Wbhkit\set_vars($mess_vars);

$short_where = $wk['place'];
$long_where = "{$wk['place']} {$wk['lwhere']}";

if ($wk['location_id'] == ONLINE_LOCATION_ID) {
	$long_where .= "<br>If possible, wear headphones for the session.<br>\n";
}

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

		$base_msg =	$note."
<p><b>Practice details:</b><br>
Title: {$wk['showtitle']}<br>
$long_where<br>
When: {$wk['when']}<br>
Pay via Venmo @willhines or PayPal whines@gmail.com<br>
<b>LATE DROP POLICY:</b> If you drop within ".LATE_HOURS." hours of the start, you must still pay for your spot.</p>\n";


		foreach ($stds as $std) {
			$key = Users\get_key($std['id']);
			$trans = URL."index.php?key=$key";
			$msg = $base_msg."<p>Log in or drop out here:<br>$trans</p>\n";

			if ($cancellation) {
				$e = \Enrollments\get_an_enrollment($wk, $std); 
				$msg .= \Emails\set_email_markup($e, $wk, $std, true);
			}

			\Emails\centralized_email($std['email'], $subject, $msg);
			$sent .= "{$std['email']}, ";
		
			Emails\send_text($std, $sms); // routine will check if they want texts and have proper info
		
		}
		$message = "Email '$subject' sent to $sent";
		$logger->info($message);
		break;

	case 'remind':
		$subject = "REMINDER: {$wk['title']} {$wk['friendly_when']} at $short_where";
		$note = "Hey! You're enrolled in this workshop. ";
		if ($wk['type'] == 'past') {
			$note .= "Actually, it looks like this workshop is in the past, which means this reminder was probably sent in error. But since I'm just a computer, then maybe there's something going on that I don't quite grasp. At any rate, this is a reminder. ";
		} else {
			$note .= "It starts ".nicetime($wk['start']).".";
			if ($wk['location_id'] == ONLINE_LOCATION_ID && $wk['online_url']) {
				$note .= "Here's the link: {$wk['online_url']}\n"; 
			}
		}
		$note .="If you think you're not going to make it, that's fine but use the link below to drop out. ";
		if ($wk['waiting'] > 0) {
			$note .= "There are currently people on the waiting list who might want to go. ";
		}
		$note .= " Okay, see you soon!";
		
		$sms = "Reminder: {$wk['title']} workshop, {$wk['friendly_when']}, ".URL;
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


	case 'cancel':
		$subject = "{$wk['showtitle']}";
		$note = "<p>I had to cancel this workshop! I'm so sorry.<br>-Will</p>";
		$st = ENROLLED; // pre-populating the status drop in 'send message' form
		$sms = "Workshop cancelled: {$wk['showtitle']}";
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






