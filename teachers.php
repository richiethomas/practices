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
		}
	}
}
$view->renderPage('teachers');


