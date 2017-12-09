<?php
$sc = "admin_mail_log.php";
$heading = "practices: admin";
include 'lib-master.php';
include 'libs/validate.php';


$log = file_get_contents(MAIL_LOG);
if (!$log) {
	$log = 'No email activity!';
}
$view->data['log'] = $log;
$view->renderPage('admin_mail_log');



