<?php
$view->data['heading'] = 'you';

include 'login_actions.php';

if (isset($params[1]) && $params[1] == 'text') {
	$table_mode = false;
} else {
	$table_mode = true;
}

if (!$u->logged_in()) {
	$view->data['error_message'] = "<h1>Not Logged In</h1><p class='my-3'>You need to log in! Click 'Login' in the upper-right hand corner of the screen.<br><br>If you're on a phone, you'll see a square with three lines at the top of the page. Click that, then click 'Login'.</p>\n";
	$view->renderPage('error');
	exit();
} else {

	$eh = new EnrollmentsHelper();
	$view->data['transcript'] = $eh->get_transcript_html($u, false, true, $table_mode); 
	$view->data['admin'] = false;
	$view->data['userhelper'] = new UserHelper('/you'); // used to set up forms
	$view->data['lookups'] = $lookups;
	$view->renderPage('you');
	
}

