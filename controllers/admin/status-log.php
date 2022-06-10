<?php
$view->data['heading'] = "status changes";

$wid =  (int) ($params[2] ?? 0);
if ($wid) {
	$wk->set_by_id($wid);
}

$eh = new EnrollmentsHelper();

$view->data['log'] = $eh->get_status_change_log($wid);
$view->renderPage('admin/status');



