<?php
$view->data['heading'] = "upcoming classes";

//\XtraSessions\update_all_ranks();

$wh = new WorkshopsHelper();

$your_teacher_id = 0;
if ($t = Teachers\is_teacher($u->fields['id'])) {
	$your_teacher_id = $t['id'];
}


if ($ac=='del') {
	$wid = 	(int) ($params[2] ?? 0);
	if ($wid) {
		$wk->set_by_id($wid);
		$wk->delete_workshop();
		$message = "Deleted '{$wk->fields['title']}'";
		$logger->debug($message);
	}
}

if ($ac == 'view') {
	$filter_by = 	(int) ($params[2] ?? 0);
} else {
	$filter_by = 0;
}

$view->data['faculty'] = Teachers\get_all_teachers(true); // active teachers
$view->data['workshops'] = $wh->get_sessions_to_come(true); // get enrollments
$view->data['filter_by'] = $filter_by; 
$view->data['your_teacher_id'] = $your_teacher_id; 
$view->data['unpaid'] = $wh->get_unpaid_students();
$view->data['bitnesses'] = $wh->get_recent_bitness();

$view->renderPage('admin/dashboard');

