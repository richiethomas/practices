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


switch ($ac) {

	case 'updategroup':
		if ($u->check_user_level(3)) {	
			$guest->update_group_level($group_id);
		}
		break;
	

 	case 'delstudent':
		$message = "Really delete '{$guest->fields['email']}'? <a class='btn btn-danger' href='/admin-users/delstudentconfirm/{$guest->fields['id']}'>yes delete</a> or <a class='btn btn-primary' href='/admin-users/view/{$guest->fields['id']}'>cancel</a>";
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


		// update display name
	case 'updatedn':
		$guest->update_display_name($display_name);
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
		} else {
			$error = "Can't change email because I, the computer, have lost track of the user we are trying to change. Meaning the variable where I stored the info is now empty.";
		}
		break;		
		
		
		
		
	case 'at':
	
		if (!isset($_REQUEST['page'])) { 

			$pay_overrides = array();
			foreach ($_REQUEST as $k => $v) {
				if (substr($k, 0, 12) == 'payoverride_') {
					$po = explode('_', $k);
					$pay_overrides[$po[1]] = $v;
				}
			}

			$paids = (isset($_REQUEST['paids']) && is_array($_REQUEST['paids'])) ? $_REQUEST['paids'] : array();
			$all_enrollments = $eh->get_enrollment_ids_for_user($guest->fields['id']);
			$msg = null;
			foreach ($all_enrollments as $eid) {
			
				$po = 0;
				if (isset($pay_overrides[$eid])) {
					$po = $pay_overrides[$eid];
				}
			
				if (in_array($eid, $paids)) {
					$msg = $e->update_paid_by_enrollment_id($eid, 1, $po, $hideconpay);
				} else {
					$msg = $e->update_paid_by_enrollment_id($eid, 0, $po, $hideconpay);
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
	$view->data['transcripts'] = $eh->get_transcript_tabled($guest, true, 1, $hideconpay);
	$view->data['userhelper'] = new UserHelper('/admin-users');
	$view->data['lookups'] = $lookups;
	$view->renderPage('admin/users');
}

	




