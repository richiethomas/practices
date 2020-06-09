<?php
$sc = "admin_change_status.php";
$heading = "practices: admin edit";
include 'lib-master.php';

$wk_vars = array('wid', 'uid', 'st', 'con', 'lmod', 'guest_id');
Wbhkit\set_vars($wk_vars);

$guest = array(); // the user we're going to change
if ($guest_id > 0) {
	$guest = Users\get_user_by_id($guest_id); // second parameter means "don't save this in the cookie"
}

if (!isset($wk) || !isset($wk['id']) || !isset($guest) || !isset($guest['id'])) {
	$view->data['error_message'] = "<h1>Whoops!</h1><p>You are asking to look at info about an enrollment, but I (the computer) cannot tell which enrollment you mean. Sorry!</p>\n";
	$view->renderPage('admin_error');
	exit();
}

switch ($ac) {
	case 'cr':
		$stmt = \DB\pdo_query("update registrations set last_modified = :lmod where workshop_id = :wid and user_id = :gid", array(':lmod' => date('Y-m-d H:i:s', strtotime($lmod)), ':wid' => $wid, ':gid' => $guest_id));
	break;
	
	case 'cs':
		if ($st) {
			$message = Enrollments\change_status($wk, $guest, $st, $con);
		}
		break;	
	
	
}

$view->data['e'] = Enrollments\get_an_enrollment($wk, $guest);
$view->data['statuses'] = $statuses;
$view->data['guest'] = $guest;
$view->renderPage('admin_change_status');



