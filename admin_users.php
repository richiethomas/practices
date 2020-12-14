<?php
$heading = "users";
include 'lib-master.php';

Wbhkit\set_vars(array('guest_id', 'carrier_id', 'phone', 'send_text', 'newemail', 'display_name', 'needle', 'group_id'));

$e = new Enrollment();
$eh = new EnrollmentsHelper();
$guest = new User();

if ($guest_id > 0) {
	$guest->set_by_id($guest_id); // second parameter means "don't save this in the cookie"
}

switch ($ac) {

	case 'updategroup':
		if ($u->check_user_level(3)) {	
			$guest->update_group_level($group_id);
		}
		break;
	

	case 'adduser':
		if ($u->validate_email($needle)) {
			$guest->set_by_email($needle);
		}
		break;
	
 	case 'delstudent':
		$message = "Really delete '{$guest->fields['email']}'? <a class='btn btn-danger' href='admin_search.php?ac=delstudentconfirm&guest_id={$guest->fields['id']}'>yes delete</a> or <a class='btn btn-primary' href='$sc?guest_id={$guest->fields['id']}'>cancel</a>";
		break;
		
			
	case 'updateu':
		$guest->update_text_preferences($phone, $send_text, $carrier_id); 
		$error = $guest->error ? $guest->error : null; // if there was an error, show it
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
				$guest->change_email($guest->fields['id'], $newemail);
				$message = "Email changed from '{$guest->fields['email']}' to '$newemail'";
				$guest->set_by_email($newemail);
			}
		} else {
			$error = "Can't change email because I, the computer, have lost track of the user we are trying to change. Meaning the variable where I stored the info is now empty.";
		}
		break;		
		
		
		
		
	case 'at':
	
		$paids = (isset($_REQUEST['paids']) && is_array($_REQUEST['paids'])) ? $_REQUEST['paids'] : array();
		$all_enrollments = $eh->get_enrollment_ids_for_user($guest->fields['id']);
		$msg = null;
		foreach ($all_enrollments as $eid) {
			if (in_array($eid, $paids)) {
				$msg = $e->update_paid_by_enrollment_id($eid, 1);
			} else {
				$msg = $e->update_paid_by_enrollment_id($eid, 0);
			}
			if ($msg) {
				$message .= $msg."<br>\n";
				$msg = null;
			}
		}		
		
}
if (!$guest->logged_in()) {
	$view->renderPage('admin/error');	
} else {
	$view->data['key'] = $guest->get_key(); 
	$view->data['guest'] = $guest; // user profile of user we are modifying
	$view->data['needle'] = trim($needle);
	$view->data['transcripts'] = $eh->get_transcript_tabled($guest, true);
	$view->data['userhelper'] = new UserHelper($sc);
	$view->data['lookups'] = $lookups;
	$view->renderPage('admin/users');
}

	




