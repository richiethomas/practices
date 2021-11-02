<?php
$heading = 'teachers';


$tid = $params[2] ?? null;

$view->data['tid'] = $tid;
$view->data['faculty'] = Teachers\get_faculty();
if ($tid) {
	foreach ($view->data['faculty'] as $f) {
		if ($f['id'] == $tid) {
			$heading = $f['nice_name'];
			$view->data['fb_image'] = "http://{$_SERVER['HTTP_HOST']}".Teachers\get_teacher_photo_src($f['user_id']);
			$view->data['fb_title'] = $f['nice_name'];
			$view->data['fb_description'] = $f['bio'];
		}
	}
} else {
	$view->data['fb_title'] = "WGIS Teachers";
	$view->data['fb_description'] = "The faculty at WGIS.";
}
$view->renderPage('teachers');


