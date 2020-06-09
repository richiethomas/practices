<?php
$sc = "admin_users.php";
$heading = "practices: admin";
include 'lib-master.php';

Wbhkit\set_vars(array('guest_id', 'carrier_id', 'phone', 'send_text', 'newemail', 'display_name', 'needle', 'group_id'));

$guest = array(); // the user we're going to change
if ($guest_id > 0) {
	$guest = Users\get_user_by_id($guest_id); // second parameter means "don't save this in the cookie"
}

switch ($ac) {

	case 'updategroup':
		if (Users\check_user_level(3)) {	
			$guest['group_id'] = $group_id;
			Users\update_group_level($guest);
		}
		break;
	

	case 'adduser':
		if (Users\validate_email($needle)) {
			$guest = Users\get_user_by_email($needle);
		}
		break;
	
 	case 'delstudent':
		$message = "Really delete '{$guest['email']}'? <a class='btn btn-danger' href='admin.php?ac=delstudentconfirm&guest_id={$guest['id']}'>yes delete</a> or <a class='btn btn-primary' href='$sc?guest_id={$guest['id']}'>cancel</a>";
		break;
			
	case 'updateu':
		$guest['carrier_id'] = $carrier_id;
		$guest['phone'] = $phone;
		$guest['send_text'] = $send_text;
		Users\update_text_preferences($guest, $message, $error); // function will update all of those arguments
		break;		
		
		// update display name
	case 'updatedn':
		$guest['display_name'] = $display_name;
		Users\update_display_name($guest, $message, $error); // function will update all of those arguments
		break;	
		
 	case 'cemail':
		if ($guest['id']) {
			$result = Users\change_email($guest['id'], $newemail);
			if ($result !== true) {
				$error = $result;
			} else {
				$message = "Email changed from '{$guest['email']}' to '$newemail'";
				$guest = Users\get_user_by_email($newemail);
				if (!$guest) {
					$error = "'$newemail' is maybe not a valid email?";
				}
			}
		} else {
			$error = "Can't change email because there's no value for the user.";
		}
		break;		
		
		
		
		
	case 'at':
	
		$paids = (isset($_REQUEST['paids']) && is_array($_REQUEST['paids'])) ? $_REQUEST['paids'] : array();
		$all_enrollments = Enrollments\get_enrollment_ids_for_user($guest['id']);
		foreach ($all_enrollments as $eid) {
			if (in_array($eid, $paids)) {
				Enrollments\update_paid_by_enrollment_id($eid, 1);
			} else {
				Enrollments\update_paid_by_enrollment_id($eid, 0);
			}
		}		
		
}
if (!$guest['id']) {
	$view->renderPage('admin_error');	
} else {
	$view->data['key'] = Users\get_key($guest['id'], 0); // don't save it in a cookie
	$view->data['guest'] = $guest;
	$view->data['needle'] = trim($needle);
	$view->data['transcripts'] = Enrollments\get_transcript_tabled($guest, true, $page);	
	$view->data['change_email_form'] = Users\edit_change_email($guest);
	$view->data['text_preferences'] =  Users\edit_text_preferences($guest);
	$view->data['display_name_form'] = Users\edit_display_name($guest);
	$view->data['groups_form'] = Users\edit_group_level($guest);
	$view->renderPage('admin_users');
}

	




