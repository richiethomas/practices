<?php
$heading = 'improv practices';
$sc = "teachers.php";
include 'lib-master.php';

Wbhkit\set_vars(array('tid'));

$view->data['tid'] = $tid;
$view->data['faculty'] = Teachers\get_faculty();
$view->renderPage('teachers');


