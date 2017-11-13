<?php

switch ($ac) {
 
 	case 'cemail':
		if ($uid) {
			$result = Users\change_email($uid, $newemail);
			if ($result !== true) {
				$error = $result;
			} else {
				$message = "Email changed from '{$u['email']}' to '$newemail'";
				$u = Users\get_user_by_email($newemail);
			}
		} else {
			$error = "Can't change email because there's no value for the user.";
		}
		$v = 'astd';
		break;
 
 	case 'zero':
		$message = "Really remove students with zero workshops? <a class='btn btn-danger' href='$sc?ac=zeroconfirm'>yes remove</a> or <a class='btn btn-primary' href='$sc?v=search&needle=everyone'>cancel</a>";
		$v = 'search';
		break;
		
	case 'zeroconfirm':
	
		$stds = Users\find_students('everyone');
		$message = '';
		foreach ($stds as $s) {
			if ($s['classes'] == 0) {
				$message .= "deleting {$s['email']} {$s['id']} - ({$s['classes']})<br>\n";
				Users\delete_student($s['id']);
			}
		}
		if (!$message) {
			$message = "No zero registation students to delete.";
		}
		$v = 'search';
		$needle = 'everyone';
		break;	
		
 	case 'delstudent':
		$message = "Really delete '{$u['email']}'? <a class='btn btn-danger' href='$sc?ac=delstudentconfirm&uid={$u['id']}'>yes delete</a> or <a class='btn btn-primary' href='$sc?v=search&needle=everyone'>cancel</a>";
		break;
		
	case 'delstudentconfirm':
		Users\delete_student($uid);
		$v = 'search';
		$needle = 'everyone';
		break;

	case 'cr':
		$sql = 'update registrations set last_modified = \''.Database\mres(date('Y-m-d H:i:s', strtotime($lmod))).'\' where workshop_id = '.Database\mres($wk['id']).' and user_id = '.Database\mres($u['id']);
		Database\mysqli($sql) or Database\db_error();
		$v = 'cs';
		break; 
		
	case 'cw':
		$message = Enrollments\check_waiting($wk);
		$v = 'ed';
		break;

	case 'at':
		$v = 'at';
		$users = $_REQUEST['users'];
		foreach ($statuses as $sid => $sts) {
			$stds = Enrollments\get_students($wid, $sid);
			foreach ($stds as $as) {
				if (is_array($users) && in_array($as['id'], $users)) {
					Enrollments\update_attendance($wid, $as['id'], 1);
				} else {
					Enrollments\update_attendance($wid, $as['id'], 0);
				}
			}
		}		
		break;
	
	
	case 'sendmsg':
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
		$v = 'em';
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
		break;
		
	
	case 'conrem':
		Enrollments\drop_session($wk, $u);
		$message = "Removed user ({$u['email']}) from practice '{$wk['showtitle']}'";
		$v = 'ed';
		break;
	
	case 'enroll':
		$message = Enrollments\handle_enroll($wk, $u, $email, $con);
		$v = 'ed';
		break;
		
	case 'cs':
		$message = Enrollments\change_status($wk, $u, $st, $con);
		$v = 'cs';
		break;

	case 'up':
	
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
		break;
		
	case 'ad':
	
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
		break;
		
		
	case 'remind':
	//{$wk['friendly_when']}
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
		$v = 'rev';
		break;
		
	case 'updateu':
		$u['carrier_id'] = $carrier_id;
		$u['phone'] = $phone;
		$u['send_text'] = $send_text;
		Users\update_text_preferences($u, $message, $error); // function will update all of those arguments
		$phone = $u['phone']; // sometimes gets updated
		$v = 'astd';
		break;		
		
		// update display name
	case 'updatedn':
		$u['display_name'] = $display_name;
		Users\update_display_name($u, $message, $error); // function will update all of those arguments
		$v = 'astd'; 
		break;		
		
	
}

?>