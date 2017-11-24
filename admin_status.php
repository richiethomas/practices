<?php
$sc = "admin_status.php";
$heading = "practices: admin";
include 'lib-master.php';
include 'libs/validate.php';

$view->data['log'] = Enrollments\get_status_change_log();
$view->renderPage('admin_status');



