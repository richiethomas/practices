<?php
$heading = 'teachers';
include 'lib-master.php';

Wbhkit\set_vars(array('tid'));

$view->data['tid'] = $tid;
$view->data['faculty'] = Teachers\get_faculty();
if ($tid) {
	foreach ($view->data['faculty'] as $f) {
		if ($f['id'] == $tid) {
			$heading = $f['nice_name'];
			$view->data['fb_image'] = "http://{$_SERVER['HTTP_HOST']}".Teachers\get_teacher_photo_src($f['user_id']);
		}
	}
}
$view->renderPage('teachers');


