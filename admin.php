<?php
$heading = "upcoming";
include 'lib-master.php';

Wbhkit\set_vars(array('filter_by'));

$your_teacher_id = 0;
if ($t = Teachers\is_teacher($u->fields['id'])) {
	$your_teacher_id = $t['id'];
}

if (!$filter_by) {
	$filter_by = 'all';
}

if ($ac && $ac=='del' && isset($wk) && isset($wk['id'])) {
	
	Workshops\delete_workshop($wk['id']);
	$message = "Deleted '{$wk['title']}'";
	$logger->info($message);
}

$view->data['faculty'] = Teachers\get_all_teachers(true); // active teachers
$view->data['workshops'] = Workshops\get_sessions_to_come();
$view->data['filter_by'] = $filter_by; 
$view->data['your_teacher_id'] = $your_teacher_id; 
$view->data['unpaid'] = Workshops\get_unpaid_students();

$view->renderPage('admin/dashboard');

