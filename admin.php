<?php
$sc = "admin.php";
$heading = "practices: admin";
include 'lib-master.php';
include 'libs/validate.php';

$wk_vars = array('wid', 'title', 'notes', 'start', 'end', 'lid', 'cost', 'capacity', 'notes', 'revenue', 'expenses', 'when_public', 'email', 'con');

Wbhkit\set_vars(array('st', 'con', 'lmod'));

$v = null;

switch ($ac) {
	
	case 'delstudentconfirm':
		Users\delete_student($u['id']);
		break;
	
	case 'cr':
		$sql = 'update registrations set last_modified = \''.Database\mres(date('Y-m-d H:i:s', strtotime($lmod))).'\' where workshop_id = '.Database\mres($wk['id']).' and user_id = '.Database\mres($u['id']);
		Database\mysqli($sql) or Database\db_error();
		$v = 'cs';
		break; 
		
	case 'lo':
		Validate\invalidate();
		header("Location: $sc");
		break;
		 
	case 'cdel':
		$error = "Are you sure you want to delete '{$wk['title']}'? <a class='btn btn-danger' href='$sc?ac=del&wid={$wid}'>delete</a>";
		break;
		
	case 'del':
		$sql = "delete from registrations where workshop_id = ".Database\mres($wid);
		Database\mysqli($sql) or Database\db_error();
		$sql = "delete from workshops where id = ".Database\mres($wid);
		Database\mysqli($sql) or Database\db_error();
		$message = "Deleted '{$wk['title']}'";
		$v= 'home';
		break;
		
	case 'cs':
		\Wbhkit\set_vars('st', 'con', 'lmod');
		if ($st) {
			$message = Enrollments\change_status($wk, $u, $st, $con);
		}
		break;

	case 'up':
	
		Wbhkit\set_vars($wk_vars);
		$sql = sprintf("update workshops
		set title = '%s', start = '%s', end = '%s', cost = %u, capacity = %u, location_id = %u, notes = '%s', revenue = %u, expenses = %u, when_public = '%s'
		where id = %u",
			Database\mres($title),
			Database\mres(date('Y-m-d H:i:s', strtotime($start))),
			Database\mres(date('Y-m-d H:i:s', strtotime($end))),
			Database\mres($cost),
			Database\mres($capacity),
			Database\mres($lid),
			Database\mres($notes),
			Database\mres($revenue),
			Database\mres($expenses),
			Database\mres(date('Y-m-d H:i:s', strtotime($when_public))),
			Database\mres($wid));
		Database\mysqli($sql) or Database\db_error();
		$wk = Workshops\get_workshop_info($wid);
		$message = "Updated practice ({$wid}) - {$wk['title']}";
		$v = 'ed';
		break;
		
	case 'ad':
		Wbhkit\set_vars($wk_vars);
		if (!$title) {
			$error = 'Must include a title for new workshop.';
			break;
		}

		$sql = sprintf("insert into workshops (title, start, end, cost, capacity, location_id, notes, revenue, expenses, when_public)
		VALUES ('%s', '%s', '%s', '%u', '%u', '%u', '%s', %u, %u, '%s')",
			Database\mres($title),
			Database\mres(date('Y-m-d H:i:s', strtotime($start))),
			Database\mres(date('Y-m-d H:i:s', strtotime($end))),
			Database\mres($cost),
			Database\mres($capacity),
			Database\mres($lid),
			Database\mres($notes),
			Database\mres($revenue),
			Database\mres($expenses),
			Database\mres(date('Y-m-d H:i:s', strtotime($when_public))));
		Database\mysqli($sql) or Database\db_error();
		$wid = $db->insert_id;
		$wk = Workshops\get_workshop_info($wid);
		$message = "Added practice ({$title})";
		$v = 'home';
		break;

	case 'cw':
		$message = Enrollments\check_waiting($wk);
		$v = 'ed';
		break;

	case 'conrem':
		Enrollments\drop_session($wk, $u);
		$message = "Removed user ({$u['email']}) from practice '{$wk['showtitle']}'";
		$v = 'ed';
		break;

	case 'enroll':
		Wbhkit\set_vars(array('email', 'con'));
		$message = Enrollments\handle_enroll($wk, $u, $email, $con);
		$v = 'ed';
		break;
		
	case 'gemail':
	
		Wbhkit\set_vars(array('workshops'));
		$all_workshops = Workshops\get_workshops_dropdown();
		$results = null;
		if (is_array($workshops)) {
			$statuses[0] = 'all'; // modifying global $statuses
			foreach ($statuses as $stid => $status_name) { 
				foreach ($workshops as $workshop_id) {
					if ($workshop_id) {
						$stds = Enrollments\get_students($workshop_id, $stid);
						$students = array();
						foreach ($stds as $as) {
							$students[] = $as['email'];
						}
					}
				}
				$students = array_unique($students);
				natcasesort($students);
				$results[$stid] = $students; // attach list of students
			}
		}
		break;

	case 'at':
		if (isset($_REQUEST['users']) && is_array($_REQUEST['users']) && $wid) {
			$users = $_REQUEST['users'];
			foreach ($statuses as $sid => $sts) {
				$stds = Enrollments\get_students($wid, $sid);
				foreach ($stds as $as) {
					if (in_array($as['id'], $users)) {
						Enrollments\update_attendance($wid, $as['id'], 1);
					} else {
						Enrollments\update_attendance($wid, $as['id'], 0);
					}
				}
			}		
		}
		break;
						
}

if (!$v && $ac) { 
	$v = $ac; 
}

switch ($v) {
	
	case 'cs':
		$view->data['e'] = Enrollments\get_an_enrollment($wk, $u);
		$view->renderPage('admin_change_status');
		break;
	
	case 'ed':
		$stats = array();
		$lists = array();
		foreach ($statuses as $stid => $status_name) {
			$stats[$stid] = count(Enrollments\get_students($wid, $stid));
			$lists[$stid] = Enrollments\list_students($wid, $stid);
		}
				
		$log = Enrollments\get_status_change_log($wk);
		$view->add_globals(array('stats', 'statuses', 'lists', 'log'));		
		$view->renderPage('admin_edit');
		break;
	
	case 'at':
		$students = array();
		if ($wid) {
			foreach ($statuses as $stid => $status_name) {
				$students[$stid] = Enrollments\get_students($wid, $stid);
			}
		}
		$view->data['wk'] = $wk;
		$view->data['statuses'] = $statuses; // global
		$view->data['students'] = $students;
		$view->renderPage('admin_attendance');
		break;
	
	case 'allchange':
	
		$view->data['log'] = Enrollments\get_status_change_log();
		$view->renderPage('admin_allchange');
		break;
	
	case 'gemail':
		$view->add_globals(array('all_workshops', 'workshops', 'statuses', 'results'));
		$view->renderPage('admin_gemail');
		break;

	case 'home':
	default:
		$view->data['workshops_list'] = Workshops\get_workshops_list(1, $page);
		$view->data['add_workshop_form'] = Workshops\add_workshop_form($wk);
		$view->renderPage('admin_home');
	
}




