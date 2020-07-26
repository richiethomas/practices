<?php
$sc = "admin_teachers.php";
$heading = "practices: admin";
include 'lib-master.php';

Wbhkit\set_vars(array('guest_id', 'tid', 'bio', 'active'));

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
		list($t['id'], $t['bio'], $t['active']) = array($tid, $bio, $active);
		$tid = Teachers\update_teacher_info($t);
		break;
		
	case 'photo':
		\Teachers\upload_teacher_photo($t, $message, $error);
		break;
	
}

//$all = ($needle ? Users\find_students($needle, $sort) : array());

//$view->add_globals(array('needle', 'sort', 'all'));
//$view->data['search_opts'] = array('n' => 'by name', 't' => 'by total classes', 'd' => 'by date registered');
$view->data['t'] = $t;
$view->data['t_classes'] = $t_classes;
$view->data['teachers'] = Teachers\get_all_teachers();

$view->renderPage('admin_teachers');



