<?php
$view->data['heading'] = "teachers";

$vars_to_set = array();
$vars_to_set = \Wbhkit\add_empty_fields($vars_to_set, Teachers\empty_teacher());
Wbhkit\set_vars($vars_to_set);

$tid =  (int) ($params[2] ?? 0);
//$vars_to_set = array('guest_id');
$t = array(); // array for teacher info
$t_classes = array();
if ($tid) {
	$t = Teachers\get_teacher_by_id($tid);
	$t_classes = Teachers\get_teacher_all_classes($tid);
}

switch ($ac) {
	
	case 'make':
		$guest_id = (int) ($params[2] ?? 0);
		if (!$t = Teachers\is_teacher($guest_id)) {
			$tid = Teachers\make_teacher($guest_id);
			$t = Teachers\get_teacher_by_id($tid);
		}
		break;
		
	case 'up':
		$id = $tid;
		$user_id = $t['user_id'];
		foreach (Teachers\empty_teacher() as $k => $v) {
			$t[$k] = $$k;
		}
		$tid = Teachers\update_teacher_info($t);
		break;
		
	case 'photo':
		\Teachers\upload_teacher_photo($t, $message, $error);
		break;
	
}

$view->data['t'] = $t;
$view->data['t_classes'] = $t_classes;
$view->data['teachers'] = Teachers\get_all_teachers();

$view->renderPage('admin/teachers');



