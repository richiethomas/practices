<?php
$sc = "admin.php";
$heading = "practices: admin";
include 'lib-master.php';
include 'validate.php';

$wk_vars = array('wid', 'title', 'notes', 'start', 'end', 'lid', 'cost', 'capacity', 'notes', 'revenue', 'expenses', 'when_public', 'email', 'con');
$mess_vars = array('st', 'note', 'subject', 'sms');

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

	case 'sendmsg':

		Wbhkit\set_vars($mess_vars);

		if (!$st) {
			$error = 'No status chosen';
			break;
		}
		if (!$wk['id']) {
			$error = 'No workshop chosen';
			break;
		}
		$stds = Enrollments\get_students($wk['id'], $st);
		$sent = '';
		$subject = preg_replace('/TITLE/', $wk['showtitle'], $subject);
		$note = preg_replace('/TITLE/', $wk['showtitle'], $note);
		$sms = preg_replace('/TITLE/', $wk['showtitle'], $sms);

		foreach ($stds as $std) {
			$key = Users\get_key($std['id']);
			$trans = URL."index.php?key=$key";
			$msg = $note;
			$msg .= "\n\nLog in or drop out here:\n$trans\n";
			$msg .= "
Regarding this practice:
Title: {$wk['showtitle']}
Where: {$wk['place']}
When: {$wk['when']}";
			mail($std['email'], $subject, $msg, 'From: '.WEBMASTER);
			$sent .= "{$std['email']}, ";
		
			Emails\send_text($std, $sms); // routine will check if they want texts and have proper info
		
		}
		$message = "Email '$subject' sent to $sent";
		$v = 'mess';
		break;

	case 'remind':
		$subject = "REMINDER: workshop {$wk['friendly_when']} at {$wk['place']}";
		$note = "Hey! You're enrolled in this workshop. ";
		if ($wk['type'] == 'past') {
			$note .= "Actually, it looks like this workshop is in the past, which means this reminder was probably sent in error. But since I'm just a computer, then maybe there's something going on that I don't quite grasp. At any rate, this is a reminder. ";
		} else {
			$note .= "It starts ".nicetime($wk['start']).".";
		}
		$note .=" If you think you're not going to make it, that's fine but use the link below to drop out. ";
		if ($wk['waiting'] > 0) {
			$note .= "There are currently people on the waiting list who might want to go. ";
		}
		$note .= " Okay, see you soon!";
		$sms = "Reminder: workshop {$wk['friendly_when']} at {$wk['place']}";
		$st = ENROLLED; // pre-populating the status drop in 'send message' form
		$v = 'mess';
		break;

	case 'feedback':
		$subject = "Feedback for '{$wk['showtitle']}'";
		$note = "Thank you for taking this workshop!

If you want: I'd love to know feedback on the workshop. Any suggestions of what you liked or what you'd change.

No worries if you'd rather not answer! Thank you all again for taking it!

-Will";
		$st = ENROLLED; // pre-populating the status drop in 'send message' form
		$v = 'mess';
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

	case 'rev':

		foreach ($_REQUEST as $key => $value) {
			$exp = null;
			$rev = null;
			if (substr($key, 0, 8) == 'revenue_') {
				$id = substr($key, 8);
				Workshops\update_workshop_col($id, 'revenue', $value);
			}
			if (substr($key, 0, 9) == 'expenses_') {
				$id = substr($key, 9);
				Workshops\update_workshop_col($id, 'expenses', $value);
			}
		}
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
	
	case 'mess':
		
		$view->add_globals($mess_vars);		
		$students = array();
		foreach ($statuses as $stid => $status_name) {
			$students[$stid] = Enrollments\get_students($wid, $stid);
		}	
		$view->data['students'] = $students;
		$view->data['statuses'] = $statuses;
		$view->renderPage('admin_send_messages');
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
	

	case 'rev':
		$vars = array('searchstart', 'searchend');
		Wbhkit\set_vars($vars);
		if ($searchstart) { $searchstart = date('Y-m-d H:i:s', strtotime($searchstart)); }
		if ($searchend) { $searchend = date('Y-m-d H:i:s', strtotime($searchend)); }

		$view->add_globals($vars);	
		$view->data['workshops_list'] = Workshops\get_workshops_list_bydate($searchstart, $searchend);
		$view->renderPage('admin_rev');
		break;

	case 'home':
	default:
		$view->data['workshops_list'] = Workshops\get_workshops_list(1, $page);
		$view->data['add_workshop_form'] = Workshops\add_workshop_form($wk);
		$view->renderPage('admin_home');
	
}




