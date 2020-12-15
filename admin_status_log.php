<?php
$heading = "status changes";
include 'lib-master.php';


$eh = new EnrollmentsHelper();

$view->data['log'] = $eh->get_status_change_log($wk);
$view->renderPage('admin/status');



