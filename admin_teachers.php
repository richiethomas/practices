<?php
$heading = "teachers";
include 'lib-master.php';

$vars_to_set = array('guest_id', 'tid');
$vars_to_set = \Wbhkit\add_empty_fields($vars_to_set, Teachers\empty_teacher());
Wbhkit\set_vars($vars_to_set);

$t = array(); // array for teacher info
$t_classes = array();
if ($tid) {
	$t = Teachers\get_teacher_by_id($tid);
	$t_classes = Teachers\get_teacher_all_classes($tid);
}

switch ($ac) {
	
	case 'make':
		if (!$t = Teachers\is_teacher($guest_id)) {
			$tid = Teachers\make_teacher($guest_id);
			$t = Teachers\get_teacher_by_id($tid);
		}
		break;
		
	case 'up':
		list($t['id'], $t['bio'], $t['active'], $t['default_rate']) = array($tid, $bio, $active, $default_rate);
			$tid = Teachers\update_teacher_info($t);
		break;
		
	case 'photo':
		\Teachers\upload_teacher_photo($t, $message, $error);
		break;
	
}

//$all = ($needle ? $userhelper->find_students($needle, $sort) : array());

//$view->add_globals(array('needle', 'sort', 'all'));
//$view->data['search_opts'] = array('n' => 'by name', 't' => 'by total classes', 'd' => 'by date registered');
$view->data['t'] = $t;
$view->data['t_classes'] = $t_classes;
$view->data['teachers'] = Teachers\get_all_teachers();

$view->renderPage('admin/teachers');



