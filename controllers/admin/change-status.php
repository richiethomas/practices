<?php
$view->data['heading'] = "change status";


$wk_vars = array('st', 'con', 'lmod', 'wid_new');
Wbhkit\set_vars($wk_vars);


$wid =  (int) ($params[2] ?? 0);
if (!$wid) {
	$view->data['error_message'] = "<h1>Whoops!</h1><p>You are asking to look at info about a workshop, but I (the computer) cannot tell which workshop you mean. Sorry!</p>\n";
	$view->renderPage('admin/error');
	exit();
}
$wk->set_by_id($wid);

$guest_id =  (int) ($params[3] ?? 0);
if (!$guest_id) {
	$view->data['error_message'] = "<h1>Whoops!</h1><p>You are asking to look at info about a user, but I (the computer) cannot tell which user you mean. Sorry!</p>\n";
	$view->renderPage('admin/error');
	exit();
}
$guest = new User();
$guest->set_by_id($guest_id);

$e = new Enrollment();
$e->set_by_u_wk($guest, $wk);

if (!$e->fields['id'])  {
	$view->data['error_message'] = "<h1>Whoops!</h1><p>You are asking to look at info about an enrollment, but I (the computer) cannot tell which enrollment you mean. Sorry!</p>\n";
	$view->renderPage('admin/error');
	exit();
}

switch ($action) {
	case 'cr':
		$stmt = \DB\pdo_query("update registrations set last_modified = :lmod where workshop_id = :wid and user_id = :gid", array(':lmod' => date(MYSQL_FORMAT, strtotime($lmod)), ':wid' => $wid, ':gid' => $guest_id));
		$e = new Enrollment();
		$e->set_by_u_wk($guest, $wk);
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
		$wk = new Workshop();
		$wk->set_by_id($wid_new);
		$e = new Enrollment();
		$e->set_by_u_wk($guest, $wk);
		$message .= "<br>\n".$e->change_status(ENROLLED, true);
		break;
	
}

$view->data['e'] = $e;
$view->data['statuses'] = $lookups->statuses;
$view->data['guest'] = $guest;
$view->renderPage('admin/change-status');


