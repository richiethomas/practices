<?php
$heading = 'Your Info';
$sc = "you.php";
include 'lib-master.php';


switch ($ac) {
	case 'cemail':
		Wbhkit\set_vars(array('newemail'));
	
		if (!Users\logged_in()) {
			$error = 'You are not logged in! You have to be logged in to change your email.';
			$logger->debug($error);
			break;
		}
		if (!Users\validate_email($newemail)) {
			$error = 'You asked to change your email but the new email \'$newemail\' is not a valid email';
			$logger->debug($error);
			break;
		}
	
		Users\change_email_phase_one($u, $newemail);
		$message = "Okay, a link has been sent to the new email address ({$newemail}). Check your spam folder if you don't see it.";
		$logger->debug($message);
		break;



	// update display name
	case 'updatedn':
		Wbhkit\set_vars(array('display_name'));
		
		if (!Users\logged_in()) {
			$error = 'You are not logged in! You have to be logged in to update your display name.';
			$logger->debug($error);
			break;
		}
		$logger->info("Changing display name to '$display_name' from '{$u['display_name']}' for user {$u['id']}");
		$u['display_name'] = $display_name;
		Users\update_display_name($u, $message, $error); // function will update all of those arguments
		break;		


	// update text preferences
	case 'updateu':


		Wbhkit\set_vars(array('carrier_id', 'phone', 'send_text'));
	

		if (!Users\logged_in()) {
			$error = 'You are not logged in! You have to be logged in to update your text preferences.';
			break;
		}
		$u['carrier_id'] = $carrier_id;
		$u['phone'] = $phone;
		$u['send_text'] = $send_text;
		Users\update_text_preferences($u, $message, $error); // function will update all of those arguments
		break;		
		
		
}

$view->data['transcript'] = Enrollments\get_transcript_tabled($u, 0, $page); 
$view->data['admin'] = false;
$view->renderPage('you');

