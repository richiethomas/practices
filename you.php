<?php
$heading = 'you';
include 'lib-master.php';


include 'login_actions.php';


if (!$u->logged_in()) {
	$view->data['error_message'] = "<h1>Whoops!</h1><p>You are asking to look at info about a student, probably you, but I (the computer) cannot tell which student you mean. Sorry!</p>\n";
	$view->renderPage('error');
	exit();
} else {


	$eh = new EnrollmentsHelper();
	$view->data['transcript'] = $eh->get_transcript_tabled($u, 0); 
	$view->data['admin'] = false;
	$view->data['userhelper'] = new UserHelper($sc);
	$view->data['lookups'] = $lookups;
	$view->renderPage('you');
	
}

