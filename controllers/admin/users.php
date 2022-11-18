<?php
$view->data['heading'] = "users";

Wbhkit\set_vars(array('newemail', 'display_name', 'group_id', 'hideconpay'));

$guest_id =  (int) ($params[2] ?? 0);
if (!$guest_id) {
	$view->data['error_message'] = "<h1>Whoops!</h1><p>You are asking to look at info about a user, but I (the computer) cannot tell which user you mean. Sorry!</p>\n";
	$view->renderPage('admin/error');
	exit();
}
$guest = new User();
$guest->set_by_id($guest_id);


// to link back to search page
$needle = (string) ($params[3] ?? null); // URL $needle takes precedence over $_POST $needle
$needle = trim($needle);

$e = new Enrollment();
$eh = new EnrollmentsHelper();


$close_transcript = false;

switch ($ac) {
	
	
	case 'updateuser':
	
		Wbhkit\set_vars(array('display_name', 'time_zone', 'opt_out'));
		if (!$opt_out) { $opt_out = 0; }
		$guest->fields['display_name'] = $display_name;
		$guest->fields['time_zone'] = $time_zone;
		$guest->fields['opt_out'] = $opt_out;

		if ($guest->save_data()) {
			$message = "Updated that user profile! Thank you!";
		} else {
			$error = "Could not update user profile. Who knows why? Maybe this: ".$guest->error;
		}
		$close_transcript = true;
		break;
	
	case 'updategroup':
		if ($u->check_user_level(3)) {	
			$guest->update_group_level($group_id);
			$message = "Updated group level to '$group_id' for '{$guest->fields['email']}'";
			$close_transcript = true;
		}
		break;
	

 	case 'delstudent':
		$message = "Really delete '{$guest->fields['email']}'? <a class='btn btn-danger' href='/admin-users/delstudentconfirm/{$guest->fields['id']}'>yes delete</a> or <a class='btn btn-primary' href='/admin-users/view/{$guest->fields['id']}'>cancel</a>";
		$close_transcript = true;
		break;

	case 'delstudentconfirm':
		$guest = new User();
		if ($guest->set_by_id($guest_id)) {
			$guest->delete_user();
			$view->data['error_message'] = "<h1>Deleted</h1><p>Student '{$guest->fields['nice_name']}' deleted! Go to the <a href='/admin-search'>search page</a> to find other students</p>\n";
			$view->renderPage('admin/error');
			exit();
		} else {
			$error = $guest->error;
		}
		break;
		
 	case 'cemail':
		if ($guest->logged_in()) {
			if (!$guest->validate_email($newemail)) {
				$error = "'$newemail' is not a valid email?";
			} else {
				$guest->admin_change_email($guest->fields['email'], $newemail);
				$message = "Email changed from '{$guest->fields['email']}' to '$newemail'";
				$guest->set_by_email($newemail);
			}
			$close_transcript = true;
			
		} else {
			$error = "Can't change email because I, the computer, have lost track of the user we are trying to change. Meaning the variable where I stored the info is now empty.";
		}
		break;		
		
		
		
		
	case 'at':
	
		if (!isset($_REQUEST['page'])) { 


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
			$all_enrollments = $eh->get_enrollment_ids_for_user($guest->fields['id']);
			$msg = null;
			foreach ($all_enrollments as $eid) {

				$pa = isset($amounts[$eid]) ? $amounts[$eid] : 0;
				$pw = isset($whens[$eid]) ? $whens[$eid] : null;
				$pc = isset($channels[$eid]) ? $channels[$eid] : null;
							
				if (in_array($eid, $paids)) {
					$msg = $e->update_paid_by_enrollment_id($eid, 1,  $pa, $pw, $pc, $hideconpay);
				} else {
					$msg = $e->update_paid_by_enrollment_id($eid, 0,  $pa, $pw, $pc, $hideconpay);
				}
				if ($msg) {
					$message .= $msg."<br>\n";
					$msg = null;
				}
			}		
		}
		
}
if (!$guest->logged_in()) {
	$view->data['error_message'] = "<p>I don't know what user we are trying to examine. Perhaps try going to the <a href='/admin-search'>page where you can search for students</a></p>";
	$view->renderPage('admin/error');	
} else {
	$view->data['key'] = $guest->get_key(); 
	$view->data['guest'] = $guest; // user profile of user we are modifying
	$view->data['needle'] = trim($needle);
	$view->data['transcripts'] = $eh->get_transcript_tabled($guest, true, $hideconpay);
	$view->data['userhelper'] = new UserHelper('/admin-users');
	$view->data['lookups'] = $lookups;
	$view->data['close_transcript'] = $close_transcript;
	$view->renderPage('admin/users');
}

	




