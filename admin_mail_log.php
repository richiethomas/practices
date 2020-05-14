<?php
$sc = "admin_mail_log.php";
$heading = "practices: admin";
include 'lib-master.php';

Wbhkit\set_vars(array('ac'));


if ($ac == 'condel') {
	file_put_contents(ERROR_LOG, '');
}

$log = file_get_contents(ERROR_LOG);
if (!$log) {
	$log = 'No log activity!';
}
$view->data['log'] = $log;
$view->data['ac'] = $ac;
$view->renderPage('admin_mail_log');



