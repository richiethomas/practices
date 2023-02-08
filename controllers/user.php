<?php
$view->data['heading'] = 'user';


$gid =  (int) ($params[2] ?? 0);
if (!$gid) {
	$view->data['error_message'] = "<h1>Whoops!</h1><p>You are asking to look at info about a user, but I (the computer) cannot tell which user you mean. Sorry!</p>\n";
	$view->renderPage('error');
	exit();
}
$guest = new User();
$guest->set_by_id($gid);


if (!$guest->fields['id']) {
	$view->data['error_message'] = "<h1>Whoops!</h1><p>You wanted user with an ID of '{$gid}'. But I (the computer) cannot find a user with that ID. Sorry!</p>\n";
	$view->renderPage('error');
	exit();
}

$eh = new EnrollmentsHelper();

$view->data['admin'] = 0;
$view->data['guest'] = $guest;
$view->data['transcript'] = $eh->get_transcript_html($guest, false, 0, true); 
$view->data['heading'] = $view->data['fb_title'] = $guest->fields['nice_name'];
$view->data['fb_description'] = "Transcript and profile info for ".$guest->fields['nice_name'];
$view->renderPage('user');

