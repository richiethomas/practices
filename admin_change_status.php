<?php
$sc = "admin_change_status.php";
$heading = "practices: admin edit";
include 'lib-master.php';
include 'libs/validate.php';

$wk_vars = array('wid', 'uid', 'st', 'con', 'lmod');
Wbhkit\set_vars($wk_vars);


if (!isset($wk) || !isset($wk['id']) || !isset($u) || !isset($u['id'])) {
	$view->data['error_message'] = "<h1>Whoops!</h1><p>You are asking to look at info about an enrollment, but I (the computer) cannot tell which enrollment you mean. Sorry!</p>\n";
	$view->renderPage('admin_error');
	exit();
}

switch ($ac) {
	case 'cr':
		$stmt = \DB\pdo_query("update registrations set last_modified = :lmod where workshop_id = :wid and user_id = :uid", array(':lmod' => date('Y-m-d H:i:s', strtotime($lmod)), ':wid' => $wid, ':uid' => $uid));
	break;
	
	case 'cs':
	if ($st) {
		$message = Enrollments\change_status($wk, $u, $st, $con);
	}
	break;	
	
	
}

$view->data['e'] = Enrollments\get_an_enrollment($wk, $u);
$view->data['statuses'] = $statuses;
$view->renderPage('admin_change_status');



