<?php
$view->data['heading'] = "calendar";

$wh = new WorkshopsHelper();

$view->data['faculty'] = Teachers\get_all_teachers(true); // active teachers
$view->data['workshops'] = $wh->get_sessions_to_come(false);
$view->data['fb_description'] = "A calendar of upcoming classes at WGIS.";

$view->renderPage('calendar');

