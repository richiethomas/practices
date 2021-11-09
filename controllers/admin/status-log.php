<?php
$heading = "status changes";


$wid =  (int) ($params[2] ?? 0);
if ($wid) { $wk = \Workshops\get_workshop_info($wid); }

$eh = new EnrollmentsHelper();

$view->data['log'] = $eh->get_status_change_log($wk);
$view->renderPage('admin/status');



