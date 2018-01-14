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
	case 'ad':
	
		if ($ac == 'ad' && !$title) {
			$error = 'Must include a title for new workshop.';
			$v = 'home';
			break;
		}

		// build a workshop array from data we have
		$id = $wid ? $wid : null; // so the next bit can find the id
		$location_id = $lid;
		$wk_fields = \Workshops\empty_workshop();
		foreach ($wk_fields as $field => $fieldvalue) {
			$wk[$field] = $$field;
		}
	
		$wid = Workshops\add_update_workshop($wk, $ac);
		
		// fill out $wk array
		$wk = Workshops\fill_out_workshop_row($wk);
		
		if ($ac == 'up') {
			$message = "Updated practice ({$wid}) - {$wk['title']}";
			$logger->info($message);
		} elseif ($ac == 'ad') {
			$message = "Added practice ({$title})";
			$logger->info($message);
		}
		$v = 'ed';
		break;
		
	case 'cw':
		$message = Enrollments\check_waiting($wk);
		$v = 'ed';
		break;

	case 'conrem':
		Enrollments\drop_session($wk, $u);
		$message = "Removed user ({$u['email']}) from practice '{$wk['showtitle']}'";
		$logger->info($message);
		$v = 'ed';
		break;

	case 'enroll':
		Wbhkit\set_vars(array('email', 'con'));
		$u = Users\get_user_by_email($email);
		$message = Enrollments\handle_enroll($wk, $u, $con); 
		$v = 'ed';
		break;

	// initially called in admin_student.php
	// but it comes here to finish the job
	case 'delstudentconfirm':
		Users\delete_student($u['id']);
		break;


	// change last modified (moves people in waiting list order)
	case 'cr':
		$stmt = \DB\pdo_query("update registrations set last_modified = :mod where workshop_id = :wid and user_id = :uid", array(':lmod' => date('Y-m-d H:i:s', strtotime($lmod)), ':wid' => $wk['id'], ':uid' => $u['id']));
		$v = 'cs';
		break; 
	
	case 'cdel':
		$error = "Are you sure you want to delete '{$wk['title']}'? <a class='btn btn-danger' href='$sc?ac=del&wid={$wk['id']}'>delete</a>";
		$v = 'ed';
		break;
	
	case 'del':
		$stmt = \DB\pdo_query("delete from registrations where workshop_id = :wid", array(':wid' => $wk['id']));
		$stmt = \DB\pdo_query("delete from workshops where id = :wid", array(':wid' => $wk['id']));
		$message = "Deleted '{$wk['title']}'";
		$logger->info($message);
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
			$lists[$stid] = Enrollments\get_students($wid, $stid);
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




