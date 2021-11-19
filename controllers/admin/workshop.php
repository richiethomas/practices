<?php
$view->data['heading'] = "edit workshop";

if ($ac != 'ad') {
	$wid =  (int) ($params[2] ?? 0);
	if (!$wid) {
		$view->data['error_message'] = "<h1>Whoops!</h1><p>You are asking to look at info about a workshop, but I (the computer) cannot tell which workshop you mean. Sorry!</p>\n";
		$view->renderPage('error');
		exit();
	}
	$wk = \Workshops\get_workshop_info($wid);
}

$wk_vars = array('title', 'notes', 'start', 'end', 'lid', 'online_url', 'cost', 'capacity', 'notes', 'when_public', 'email', 'con', 'guest_id', 'reminder_sent', 'sold_out_late', 'teacher_id', 'co_teacher_id', 'application',  'start_xtra', 'end_xtra', 'online_url_xtra', 'hideconpay');
Wbhkit\set_vars($wk_vars);

$e = new Enrollment();
$eh = new EnrollmentsHelper();

switch ($ac) {

	case 'sar':
		Reminders\remind_enrolled(array($wk['id'], 0, 0));
		$message = "Reminders sent to enrolled.";
		break;

	case 'up':
	case 'ad':
	
		if ($ac == 'ad' && !$title) {
			$error = 'Must include a title for new workshop.';
			$view->renderPage('admin/error');
			exit();
		}

		// build a workshop array from data we have
		$id = $wid ?? null; // so the next bit can find the id
		$location_id = $lid;
		$wk_fields = \Workshops\get_empty_workshop();
		foreach ($wk_fields as $field => $fieldvalue) {
			$wk[$field] = $$field;
		}
	
		$wid = Workshops\add_update_workshop($wk, $ac);
		$wk = Workshops\get_workshop_info($wid); // re-fetch workshop info from database - inefficient, but only done by admins;
				
		if ($ac == 'up') {
			$message = "Updated practice ({$wid}) - {$wk['title']}";
			$logger->info($message);
		} elseif ($ac == 'ad') {
			$message = "Added practice ({$wk['id']}) - ({$title}) ";
			$logger->info($message);
		}
		break;
		
	case 'nw':
		if (!$wk['application']) {
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
				$message = "Removed user ({$guest->fields['email']}) from practice '{$wk['title']}'";
				$logger->info($message);
			} else {
				$error = $e->error;
				$logger->error($error);
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
		$error = "Are you sure you want to delete '{$wk['title']}'? <a class='btn btn-danger' href='admin.php?ac=del&wid={$wk['id']}'>delete</a>";
		break;

		
	case 'adxtra':	
	
		XtraSessions\add_xtra_session($wid, $start_xtra, $end_xtra, $online_url_xtra);
		$wk = Workshops\fill_out_workshop_row($wk);
		$message = "Added xtra session.";
		break;
		
	case 'delxtra':
		$xtraid = (int) ($params[3] ?? 0);
		if ($xtraid) {
			XtraSessions\delete_xtra_session($xtraid);
			XtraSessions\update_ranks($wk['id']);
			$wk = Workshops\fill_out_workshop_row($wk);
			$message = "Deleted xtra session";
		} else {
			$error = "You wanted to delete an extra session but I could not determine which one.";
		}
		break;
		
		
	case 'at':
		
		$pay_overrides = array();
		foreach ($_REQUEST as $k => $v) {
			if (substr($k, 0, 12) == 'payoverride_') {
				$po = explode('_', $k);
				$pay_overrides[$po[1]][$po[2]] = $v;
			}
		}
	
		$users = (isset($_REQUEST['users']) && is_array($_REQUEST['users'])) ? $_REQUEST['users'] : array();
		$msg = null;
		if ($wid) {
			foreach ($lookups->statuses as $sid => $sts) {
				$stds = $eh->get_students($wid, $sid);
				foreach ($stds as $as) {
					$po = 0;
					if (isset($pay_overrides[$as['id']][$wid])) {
						$po = $pay_overrides[$as['id']][$wid];
					}
						
					if (in_array($as['id'], $users)) {
						$msg = $e->update_paid_by_uid_wid($as['id'], $wid, 1, $po, $hideconpay);
					} else {
						$msg = $e->update_paid_by_uid_wid($as['id'], $wid,  0, $po, $hideconpay);
					}
					if ($msg) {
						$message .= $msg."<br>\n";
						$msg = null;
					}
				}
			}		
		}
		break;
		
	case 'week':
		$wk = \XtraSessions\add_a_week($wk);
		$message = "Added xtra session a week after the most recent one.";
		break;
							
}


$stats = array();
$lists = array();
foreach ($lookups->statuses as $stid => $status_name) {
	$lists[$stid] = $eh->get_students($wid, $stid);
	$stats[$stid] = count($lists[$stid]);
}

$view->add_globals(array('stats', 'lists', 'status_log', 'hideconpay'));	
$view->data['statuses'] = $lookups->statuses;
$view->renderPage('admin/workshop');




