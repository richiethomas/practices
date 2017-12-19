<?php
$sc = "admin_student.php";
$heading = "practices: admin";
include 'lib-master.php';
include 'libs/validate.php';


Wbhkit\set_vars(array('carrier_id', 'phone', 'send_text', 'newemail', 'display_name', 'needle'));

switch ($ac) {
	
 	case 'delstudent':
		$message = "Really delete '{$u['email']}'? <a class='btn btn-danger' href='admin.php?ac=delstudentconfirm&uid={$u['id']}'>yes delete</a> or <a class='btn btn-primary' href='$sc?uid={$u['id']}'>cancel</a>";
		break;
			
	case 'updateu':
		$u['carrier_id'] = $carrier_id;
		$u['phone'] = $phone;
		$u['send_text'] = $send_text;
		Users\update_text_preferences($u, $message, $error); // function will update all of those arguments
		break;		
		
		// update display name
	case 'updatedn':
		$u['display_name'] = $display_name;
		Users\update_display_name($u, $message, $error); // function will update all of those arguments
		break;	
		
 	case 'cemail':
		if ($u['id']) {
			$result = Users\change_email($u['id'], $newemail);
			if ($result !== true) {
				$error = $result;
			} else {
				$message = "Email changed from '{$u['email']}' to '$newemail'";
				$u = Users\get_user_by_email($newemail);
				if (!$u) {
					$error = "'$newemail' is maybe not a valid email?";
				}
			}
		} else {
			$error = "Can't change email because there's no value for the user.";
		}
		break;		
		
}
if (!$u['id']) {
	$view->renderPage('admin_error');	
} else {
	$view->data['key'] = Users\get_key($u['id']);
	$view->data['needle'] = $needle;
	$view->data['transcripts'] = Enrollments\get_transcript_tabled($u, true, $page);	
	$view->data['change_email_form'] = Users\edit_change_email($u);
	$view->data['text_preferences'] =  Users\edit_text_preferences($u);
	$view->data['display_name_form'] = Users\edit_display_name($u);
	$view->renderPage('admin_student');
}

	




