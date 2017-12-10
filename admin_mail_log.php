<?php
$sc = "admin_mail_log.php";
$heading = "practices: admin";
include 'lib-master.php';
include 'libs/validate.php';

Wbhkit\set_vars(array('ac'));


if ($ac == 'condel') {
	file_put_contents(MAIL_LOG, '');
}

$log = file_get_contents(MAIL_LOG);
if (!$log) {
	$log = 'No email activity!';
}
$view->data['log'] = $log;
$view->data['ac'] = $ac;
$view->renderPage('admin_mail_log');



