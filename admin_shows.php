<?php
$heading = "class shows";
include 'lib-master.php';

Wbhkit\set_vars(array('ac', 'show_id', 'start', 'end', 'online_url', 'actual_pay', 'teacher_id', 'wid', 'when_teacher_paid', 'reminder_sent'));

$cs = new ClassShow();

if ($show_id) {
	$cs->set_by_id($show_id);
	$cs->set_workshops();
}

if ($ac == 'ad' || $ac == 'up') {
	$cs->set_into_fields(
		array(
			'start' => $start,
			'end' => $end,
			'online_url' => $online_url,
			'actual_pay' => $actual_pay,
			'teacher_id' => $teacher_id,
			'when_teacher_paid' => $when_teacher_paid,
			'reminder_sent' => $reminder_sent));
}


switch ($ac) {
	case 'ad':
		$cs->save_data();
		break;

	case 'up':
		$cs->fields['id'] = $show_id;
		$cs->save_data();
		break;
		
	case 'asc':
		if ($cs->associate_workshop($wid)) {
			$message = $cs->message;
		} else {
			$error = $cs->error;
		}
		$cs->set_workshops();
		break;

	case 'rem':
		if ($cs->remove_workshop($wid)) {
			$message = $cs->message;
		} else {
			$error = $cs->error;
		}
		$cs->set_workshops();
		break;
		
	case 'del':
		if ($cs->delete_show()) {
			$message = $cs->message;
		} else {
			$error = $cs->error;
		}
		break;
		
}

$view->data['shows'] = get_class_shows();
$view->data['cs'] = $cs;
$view->renderPage('admin/shows');


function get_class_shows(int $limit = 25) {
	
	$stmt = \DB\pdo_query("select * from shows order by start desc limit $limit");
	$all = array();
	while ($row = $stmt->fetch()) {
		$cs = new ClassShow();
		$cs->set_by_id($row['id']);
		$cs->set_workshops();
		$cs->set_teacher();
		$all[] = $cs;
	}
	return $all;
}


