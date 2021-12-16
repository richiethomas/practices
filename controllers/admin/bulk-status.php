<?php
$view->data['heading'] = "bulk status edit";

$wk_vars = array('st', 'confirm');
Wbhkit\set_vars($wk_vars);

$wid =  (int) ($params[2] ?? 0);
if (!$wid) {
		$view->data['error_message'] = "<h1>Whoops!</h1><p>You are asking to look at info about a workshop, but I (the computer) cannot tell which workshop you mean. Sorry!</p>\n";
		$view->renderPage('error');
		exit();
}
$wk = \Workshops\get_workshop_info($wid);
$e = new Enrollment();
$eh = new EnrollmentsHelper();

switch ($ac) {

	case 'change':
		
		$users = (isset($_REQUEST['users']) && is_array($_REQUEST['users'])) ? $_REQUEST['users'] : array();

		if ($wid) {
			foreach ($users as $uid) {
				$e->set_by_uid_wid($uid, $wid);
				$e->reset_user_and_workshop();
				if ($st) {
					$message .= $e->change_status($st, $confirm);
					if ($confirm) { $message .= " - emailed"; }
					$message .= "<br>\n";
				}
			}		
		}
		break;
		
}


$stats = array();
$lists = array();
foreach ($lookups->statuses as $stid => $status_name) {
	$lists[$stid] = $eh->get_students($wid, $stid);
	$stats[$stid] = count($lists[$stid]);
}

$view->add_globals(array('stats', 'lists',  'confirm'));	
$view->data['statuses'] = $lookups->statuses;
$view->renderPage('admin/bulk-status');




