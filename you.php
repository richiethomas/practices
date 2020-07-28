<?php
$heading = 'you';
include 'lib-master.php';


include 'login_actions.php';


if (!Users\logged_in()) {
	$view->data['error_message'] = "<h1>Whoops!</h1><p>You are asking to look at info about a student, probably you, but I (the computer) cannot tell which student you mean. Sorry!</p>\n";
	$view->renderPage('admin_error');
	exit();
} else {


	$view->data['transcript'] = Enrollments\get_transcript_tabled($u, 0, $page); 
	$view->data['admin'] = false;
	$view->renderPage('you');
	
}

