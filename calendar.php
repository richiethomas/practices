<?php
$heading = "calendar";
include 'lib-master.php';

$view->data['faculty'] = Teachers\get_all_teachers(true); // active teachers
$view->data['workshops'] = Workshops\get_sessions_to_come();

$view->renderPage('calendar');

