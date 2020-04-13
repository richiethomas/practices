<?php
$sc = "admin_calendar.php";
$heading = "practices: admin";
include 'lib-master.php';
include 'libs/validate.php';


$view->data['workshops'] = Workshops\get_sessions_to_come(); 
$view->renderPage('admin_calendar');
