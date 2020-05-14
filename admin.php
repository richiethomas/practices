<?php
$sc = "admin_calendar.php";
$heading = "practices: admin";
include 'lib-master.php';


if ($ac && $ac=='del' && isset($wk) && isset($wk['id'])) {
	
	Workshops\delete_workshop($wk['id']);
	$message = "Deleted '{$wk['title']}'";
	$logger->info($message);
}

$view->data['workshops'] = Workshops\get_sessions_to_come(); 
$view->renderPage('admin_calendar');

