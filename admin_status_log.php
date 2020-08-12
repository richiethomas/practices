<?php
$heading = "status changes";
include 'lib-master.php';

$view->data['log'] = Enrollments\get_status_change_log();
$view->renderPage('admin/status');



