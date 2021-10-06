<?php
$heading = "change status";
include 'lib-master.php';

$wk_vars = array('wid', 'uid', 'st', 'con', 'lmod', 'guest_id', 'wid_new');
Wbhkit\set_vars($wk_vars);

$guest = new User();
if ($guest_id > 0) {
	$guest->set_by_id($guest_id); 
}


$e = new Enrollment();
$e->set_by_u_wk($guest, $wk);

if (!isset($wk) || !isset($wk['id']) || !$guest->logged_in()) {
	$view->data['error_message'] = "<h1>Whoops!</h1><p>You are asking to look at info about an enrollment, but I (the computer) cannot tell which enrollment you mean. Sorry!</p>\n";
	$view->renderPage('admin/error');
	exit();
}

switch ($ac) {
	case 'cr':
		$stmt = \DB\pdo_query("update registrations set last_modified = :lmod where workshop_id = :wid and user_id = :gid", array(':lmod' => date('Y-m-d H:i:s', strtotime($lmod)), ':wid' => $wid, ':gid' => $guest_id));
		break;
	
	case 'cs':
		if ($st) {
			$message = $e->change_status($st, $con);
		}
		break;	
		
	case 'xfer':
		// drop from old
		$message = $e->change_status(DROPPED, false);
		
		//set new info
		$wk = array();
		$wk = \Workshops\get_workshop_info($wid_new);
		$e = new Enrollment();
		$e->set_by_u_wk($guest, $wk);
		$message .= "<br>\n".$e->change_status(ENROLLED, true);
		break;
	
}

$view->data['e'] = $e;
$view->data['statuses'] = $lookups->statuses;
$view->data['guest'] = $guest;
$view->renderPage('admin/change_status');



