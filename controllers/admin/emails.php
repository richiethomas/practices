<?php
$view->data['heading'] = "get emails";

Wbhkit\set_vars(array('workshops', 'opt_outs', 'teams'));
$results = null;

$all_workshops = get_workshops_dropdown();
$all_teams = get_teams_dropdown();

$eh = new EnrollmentsHelper();

$student_emails = array();
$student_names = array();

if (is_array($workshops)) {
	foreach ($workshops as $workshop_id) {
		if (!$workshop_id) { continue; }
		$stds = $eh->get_students($workshop_id); // gets enrolled by default
		foreach ($stds as $as) {		
			if ($as['opt_out'] && !$opt_outs) { continue; } // skip opt-outs (unless being evil)
			$student_emails[] = $as['email'];
			$student_names[] = "{$as['nice_name']}, {$as['email']}";
		}
	}
}

if (is_array($teams)) {
	foreach ($teams as $tid) {
		if (!$tid) { continue; }
		$team = new Team();
		$team->set_by_id($tid);
		foreach ($team->users as $as) {		
			if ($as->fields['opt_out'] && !$opt_outs) { continue; } // skip opt-outs (unless being evil)
			$student_emails[] = $as->fields['email'];
			$student_names[] = "{$as->fields['nice_name']}, {$as->fields['email']}";
		}
	}
}


$student_emails = array_unique($student_emails);
natcasesort($student_emails);

$student_names = array_unique($student_names);
natcasesort($student_names);

$view->add_globals(array('all_workshops', 'workshops', 'all_teams', 'teams', 'student_emails', 'student_names'));
$view->renderPage('admin/gemail');


function get_workshops_dropdown() {

	$stmt = \DB\pdo_query("select w.* from workshops w where w.start >= '2020-03-01' order by start desc");
	$workshops = array('0' => '');
	$wk = new Workshop();
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		$row = $wk->format_times_one_level($row);
		$start = '';
		if (strpos($row['tags'], 'inperson') !== false) { $start = '---'; }
		$workshops[$row['id']] = $start.$row['title'].' ('.date('Y-M-d', strtotime($row['start'])).')';
	}
	return $workshops;
}

function get_teams_dropdown() {

	$stmt = \DB\pdo_query("select * from teams t where t.active = 1 order by formed desc");
	$teams = array('0' => '');
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		$start = '';
		if (!$row['online']) { $start = '---'; }
		$teams[$row['id']] = $start.$row['title'].' ('.date('Y-M-d', strtotime($row['formed'])).')';
	}
	return $teams;
}

