<?php
$view->data['heading'] = "edit workshop";

if ($action != 'ad') {
	$wid =  (int) ($params[2] ?? 0);
	if (!$wid) {
		$view->data['error_message'] = "<h1>Whoops!</h1><p>You are asking to look at info about a workshop, but I (the computer) cannot tell which workshop you mean. Sorry!</p>\n";
		$view->renderPage('error');
		exit();
	}
	$wk->set_by_id($wid);
}

$wk_vars = array('title', 'notes', 'start', 'end', 'lid', 'online_url', 'cost', 'capacity', 'notes', 'when_public', 'email', 'con', 'guest_id', 'reminder_sent', 'teacher_id', 'co_teacher_id', 'application',  'hidden', 'start_xtra', 'end_xtra', 'online_url_xtra', 'hideconpay', 'class_show', 'tags', 'location_id');
Wbhkit\set_vars($wk_vars);


$e = new Enrollment();
$eh = new EnrollmentsHelper();

switch ($action) {
	
	case 'delallxtra':
		$wk->delete_xtra_sessions();
		$message = "Deleted all xtra sessions.";
		break;

	case 'sar':
		Reminders\remind_enrolled(array($wk->fields['id'], 0, $wk->fields['title']));
		$message = "Reminders sent to enrolled.";
		break;

	case 'up':
	case 'ad':
	
		if ($action == 'ad' && !$title) {
			$error = 'Must include a title for new workshop.';
			$view->renderPage('admin/error');
			exit();
		}

		// build a workshop array from data we have
		$id = $wid ?? null; // so the next bit can find the id
		$location_id = $lid;
		$wk_fields = $wk->cols; // the db columns
		foreach ($wk_fields as $field => $fieldvalue) {
			$wk->fields[$field] = $$field; // set the field array with each db col
		}
		
		$wid = $wk->add_update_workshop($action);
		$wk->set_by_id($wid); // re-fetch workshop info from database - inefficient, but only done by admins;
		
		if ($action == 'up') {
			$message = "Updated practice ({$wk->fields['id']}) - {$wk->fields['title']}";
		} elseif ($action == 'ad') {
			$message = "Added practice ({$wk->fields['id']}) - ({$wk->fields['title']}) ";
		}
		break;
		
	case 'nw':
		if (!$wk->fields['application']) {
			$message = $e->notify_waiting($wk);
		} else {
			$message = "Cannot notify waiting list - this class takes applications.";
		}
		break;

	case 'conrem':
	
		$guest = new User(); // the user we're going to change
		$guest_id = (int) ($params[3] ?? 0);
		if ($guest_id > 0) {
			$guest->set_by_id($guest_id); 
			$e = new Enrollment();
			$e->set_by_u_wk($guest, $wk);
			if ($e->drop_session()) {
				$message = "Removed user ({$guest->fields['email']}) from practice '{$wk->fields['title']}'";
			} else {
				$error = $e->error;
			}
		} else {
			$error = "Could not remove student because I could not tell which student.";
		}
		break;

	case 'enroll':
		$guest = new User();
		Wbhkit\set_vars(array('email', 'con'));
		$guest->set_by_email($email);
		if ($guest->error) {
			$error = $guest->error;
			break;
		}
		$e = new Enrollment();
		$e->set_by_u_wk($guest, $wk);
		$message = $e->change_status(ENROLLED, $con); 
		break;
	
	case 'cdel':
		$error = "Are you sure you want to delete '{$wk->fields['title']}'? <a class='btn btn-danger' href='/admin/del/{$wk->fields['id']}'>delete</a>";
		break;

		
	case 'adxtra':	
	
		$class_show = (int)$class_show;
	
		if (!$location_id) { $location_id = 0; }
		XtraSessions\add_xtra_session($wid, $start_xtra, $end_xtra, $online_url_xtra, $class_show, $location_id);
		$wk->finish_setup();
		$message = "Added xtra session.";
		break;
		
	case 'delxtra':
		$xtraid = (int) ($params[3] ?? 0);
		if ($xtraid) {
			XtraSessions\delete_xtra_session($xtraid);
			XtraSessions\update_ranks($wk->fields['id']);
			$wk->finish_setup();
			$message = "Deleted xtra session";
		} else {
			$error = "You wanted to delete an extra session but I could not determine which one.";
		}
		break;
		
		
	case 'at':
		
		$amounts = array();
		$whens = array();
		$channels = array();
		foreach ($_REQUEST as $k => $v) {
			
			if (substr($k, 0, 7) == 'amount_') {
				$pa = explode('_', $k);
				$amounts[$pa[1]] = $v;
			}
			if (substr($k, 0, 5) == 'when_') {
				$pw = explode('_', $k);
				$whens[$pw[1]] = $v;
			}
			if (substr($k, 0, 8) == 'channel_') {
				$pc = explode('_', $k);
				$channels[$pc[1]] = $v;
			}
		}
	
		$paids = (isset($_REQUEST['paids']) && is_array($_REQUEST['paids'])) ? $_REQUEST['paids'] : array();
		$msg = null;
		if ($wid) {
			foreach ($lookups->statuses as $sid => $sts) {
				$stds = $eh->get_students($wid, $sid);
				foreach ($stds as $as) {
					
					$eid = $as['enrollment_id'];
					$uid = $as['user_id'];
					
					$pa = isset($amounts[$uid]) ? $amounts[$uid] : 0;
					$pw = isset($whens[$uid]) ? $whens[$uid] : null;
					$pc = isset($channels[$uid]) ? $channels[$uid] : null;

					
					if (in_array($as['id'], $paids)) {
						$msg = $e->update_paid_by_enrollment_id($eid, 1, $pa, $pw, $pc, $hideconpay);
					} else {
						$msg = $e->update_paid_by_enrollment_id($eid,  0, $pa, $pw, $pc, $hideconpay);
					}
					if ($msg) {
						$message .= $msg."<br>\n";
						$msg = null;
					}
				}
			}
			$wk->fields['actual_revenue'] = $wk->get_actual_revenue();
						
		}
		break;
		
	case 'week':
		$wk = \XtraSessions\add_a_week($wk);
		$message = "Added xtra session a week after the most recent one.";
		break;
							
}


if ($error && !preg_match('/you sure/', $error)) { $logger->error($error); }
if ($message) { $logger->debug($message); }


$stats = array();
$lists = array();
foreach ($lookups->statuses as $stid => $status_name) {
	$lists[$stid] = $eh->get_students($wid, $stid);
	$stats[$stid] = count($lists[$stid]);
}

$view->add_globals(array('stats', 'lists', 'status_log', 'hideconpay'));	
$view->data['statuses'] = $lookups->statuses;
$view->data['wid'] = $wk->fields['id'];

$ph = new PaymentsHelper();
$view->data['all_costs'] = $ph->get_class_costs_simple($wk->fields['id']);

$view->renderPage('admin/workshop');




