<?php
$view->data['heading'] = "upcoming classes";

//\XtraSessions\update_all_ranks();

$your_teacher_id = 0;
if ($t = Teachers\is_teacher($u->fields['id'])) {
	$your_teacher_id = $t['id'];
}

if (!$filter_by) {
	$filter_by = 'all';
}

if ($ac=='del') {
	$wid = 	(int) ($params[2] ?? 0);
	if ($wid) {
		$wk = \Workshops\get_workshop_info($wid);
	
		Workshops\delete_workshop($wk['id']);
		$message = "Deleted '{$wk['title']}'";
		$logger->info($message);
	}
}

if ($ac == 'view') {
	$filter_by = 	(int) ($params[2] ?? 0);
} else {
	$filter_by = 0;
}

$view->data['faculty'] = Teachers\get_all_teachers(true); // active teachers
$view->data['workshops'] = Workshops\get_sessions_to_come(true, true); // get enrollments, show hidden
$view->data['filter_by'] = $filter_by; 
$view->data['your_teacher_id'] = $your_teacher_id; 
$view->data['unpaid'] = Workshops\get_unpaid_students();

$view->renderPage('admin/dashboard');

