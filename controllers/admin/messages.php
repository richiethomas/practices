<?php
$view->data['heading'] = "send messages";


$wid =  (int) ($params[2] ?? 0);
if (!$wid) {
	$view->data['error_message'] = "<h1>Whoops!</h1><p>You are asking to look at info about a workshop, but I (the computer) cannot tell which workshop you mean. Sorry!</p>\n";
	$view->renderPage('error');
	exit();
}
$wk->set_by_id($wid);



$mess_vars = array('st', 'note', 'subject');
Wbhkit\set_vars($mess_vars);
if (!$st) { $st = ENROLLED; }

$eh = new EnrollmentsHelper();
$guest = new User();

switch ($action) {
			
	case 'sendmsg':

		if (!$st) {
			$error = 'No status chosen';
			break;
		}
		if (!$wk->fields['id']) {
			$error = 'No workshop chosen';
			break;
		}
		$stds = $eh->get_students($wk->fields['id'], $st);
		$sent = '';
		
		foreach ($stds as $std) {

			list($wk, $this_subject, $this_note) = set_message_properties($wk, $std['time_zone'], $subject, $note);
						
			Emails\centralized_email($std['email'], $this_subject, $this_note);
			$sent .= "{$std['email']}, ";
			$guest->set_by_id($std['id']);
		
		}
		
		// send a copy to the webmaster
		$guest = new User();
		$guest->set_by_id(1);
		list($wk, $this_subject, $this_note) = set_message_properties($wk, $guest->fields['time_zone'], $subject, $note);
		$message = "Email '$this_subject' sent to $sent";
		$this_subject = "WGIS message: $this_subject";
		Emails\centralized_email(WEBMASTER, $this_subject, "<p>Hi admin -- the below message got sent to this class:</p>".$this_note);
		
		//send a copy to the teacher(s)
		$note = "<p>Hello teacher or co-teacher! The below email was sent by the WGIS admin to the students of this class:<br>
---------------</p>".$note;
		list($wk, $this_subject, $this_note) = set_message_properties($wk, $wk->teacher['time_zone'], $subject, $note);
		Emails\centralized_email($wk->teacher['email'], $this_subject, $this_note);
		if ($wk->fields['co_teacher_id']) {
			list($wk, $this_subject, $this_note) = set_message_properties($wk, $wk->coteacher['time_zone'], $subject, $note);
			Emails\centralized_email($wk->coteacher['email'], $this_subject, $this_note);
		}

		$subject = $_REQUEST['subject'];
		$note = $_REQUEST['note'];
		break;



	case 'roster':

		$subject = "full class info for '{$wk->fields['title']}'";
		$note = "Hi! You are a student in the class '{$wk->fields['title']}' from WGIS. Below is some info you might need. You might want to save this one. It's got your zoom link, list of class dates (some of which might have different zoom links) and a list of your classmates.\n\n".$wk->get_cut_and_paste_roster();

		$sms = null;
		$st = ENROLLED; // pre-populating the status drop in 'send message' form
		break;

	case 'feedback':
		$subject = "Feedback for '{$wk->fields['title']}'";
		$note = "Thank you for taking this workshop!

If you want: I'd love to know feedback on the workshop. Any suggestions of what you liked or what you'd change.

No worries if you'd rather not answer! Thank you all again for taking it!

-Will";
		$st = ENROLLED; // pre-populating the status drop in 'send message' form
		break;
		
}
if (!$wk->fields['id']) {
	$view->renderPage('admin/error');	
} else {
	$view->add_globals($mess_vars);		
	$students = array();
	foreach ($lookups->statuses as $stid => $status_name) {
		$students[$stid] = $eh->get_students($wid, $stid);
	}	
	$view->data['students'] = $students;
	$view->data['statuses'] = $lookups->statuses;
	$view->renderPage('admin/messages');
}



function set_message_properties(\Workshop $wk, $tz, $subject, $note) {
	$wk->format_times(($tz ?? DEFAULT_TIME_ZONE));
	$this_subject = preg_replace('/TITLE/', $wk->fields['title'], $subject);
	$this_note = preg_replace('/TITLE/', $wk->fields['title'], $note);
	$this_note = preg_replace('/\R/', "<br>", $this_note);
	$this_note = $this_note.Emails\get_workshop_summary($wk);
	return array($wk, $this_subject, $this_note);
}



