<?php
$sc = "admin.php";
$heading = "practices: admin";
include 'lib-master.php';
include 'libs/validate.php';

$wk_vars = array('wid', 'title', 'notes', 'start', 'end', 'lid', 'cost', 'capacity', 'notes', 'revenue', 'expenses', 'when_public', 'email', 'con', 'cancelled');
Wbhkit\set_vars($wk_vars);

$change_status_vars = array('st', 'con', 'lmod');
Wbhkit\set_vars($change_status_vars);

$v = null;

switch ($ac) {

	case 'up':
	
		$sql = sprintf("update workshops
		set title = '%s', start = '%s', end = '%s', cost = %u, capacity = %u, location_id = %u, notes = '%s', revenue = %u, expenses = %u, when_public = '%s', cancelled = %u
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
			Database\mres($cancelled),
			Database\mres($wid));
		Database\mysqli($sql) or Database\db_error();
		$wk = Workshops\get_workshop_info($wid);
		$message = "Updated practice ({$wid}) - {$wk['title']}";
		$v = 'ed';
		break;
		
	case 'ad':
		if (!$title) {
			$error = 'Must include a title for new workshop.';
			$v = 'home';
			break;
		}

		$sql = sprintf("insert into workshops (title, start, end, cost, capacity, location_id, cancelled, notes, revenue, expenses, when_public)
		VALUES ('%s', '%s', '%s', '%u', '%u', '%u', '%u', '%s', %u, %u, '%s')",
			Database\mres($title),
			Database\mres(date('Y-m-d H:i:s', strtotime($start))),
			Database\mres(date('Y-m-d H:i:s', strtotime($end))),
			Database\mres($cost),
			Database\mres($capacity),
			Database\mres($lid),
			Database\mres($cancelled),
			Database\mres($notes),
			Database\mres($revenue),
			Database\mres($expenses),
			Database\mres(date('Y-m-d H:i:s', strtotime($when_public))));
		Database\mysqli($sql) or Database\db_error();
		$wid = $db->insert_id;
		$wk = Workshops\get_workshop_info($wid);
		$message = "Added practice ({$title})";
		$v = 'ed';
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
		$message = Enrollments\handle_enroll($wk, null, $email, $con); 
		// second argument is null, because i don't want to enroll the user who is logged in,
		// want to enroll the email which is the third argument
		$v = 'ed';
		break;

	// initially called in admin_student.php
	// but it comes here to finish the job
	case 'delstudentconfirm':
		Users\delete_student($u['id']);
		break;

	// change last modified (moves people in waiting list order)
	case 'cr':
		$sql = 'update registrations set last_modified = \''.Database\mres(date('Y-m-d H:i:s', strtotime($lmod))).'\' where workshop_id = '.Database\mres($wk['id']).' and user_id = '.Database\mres($u['id']);
		Database\mysqli($sql) or Database\db_error();
		$v = 'cs';
		break; 
	
	case 'cdel':
		$error = "Are you sure you want to delete '{$wk['title']}'? <a class='btn btn-danger' href='$sc?ac=del&wid={$wk['id']}'>delete</a>";
		$v = 'ed';
		break;
	
	case 'del':
		$sql = "delete from registrations where workshop_id = ".Database\mres($wk['id']);
		Database\mysqli($sql) or Database\db_error();
		$sql = "delete from workshops where id = ".Database\mres($wk['id']);
		Database\mysqli($sql) or Database\db_error();
		$message = "Deleted '{$wk['title']}'";
		$v= 'home';
		break;
	
	case 'cs':
		if ($st) {
			$message = Enrollments\change_status($wk, $u, $st, $con);
		}
		break;						
}

if (!$v && $ac) { 
	$v = $ac; 
}

switch ($v) {
	
	case 'cs':
		$view->data['e'] = Enrollments\get_an_enrollment($wk, $u);
		$view->data['statuses'] = $statuses;
		$view->renderPage('admin_change_status');
		break;
	
	case 'ed':
		$stats = array();
		$lists = array();
		foreach ($statuses as $stid => $status_name) {
			$stats[$stid] = count(Enrollments\get_students($wid, $stid));
			$lists[$stid] = Enrollments\list_students($wid, $stid);
		}
				
		$data['log'] = Enrollments\get_status_change_log($wk);
		$status_log = $view->renderSnippet('admin_status', $data);
		
		$view->add_globals(array('stats', 'statuses', 'lists', 'status_log'));		
		$view->renderPage('admin_edit');
		break;

	case 'home':
	default:
		$view->data['workshops_list'] = Workshops\get_workshops_list(1, $page);
		$view->data['add_workshop_form'] = Workshops\add_workshop_form($wk);
		$view->renderPage('admin_home');
	
}




