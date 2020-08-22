<?php

$link_email_sent_flag = false;

switch ($ac) {


	case 'link':
	
		Wbhkit\set_vars(array('email', 'display_name'));
	
		// if a user exists for that email, get it
		$u = Users\get_user_by_email($email);

		// if not, make that user
		if (!$u) {
			$error = "'$email' is not a valid email, I think?";
			$logger->debug($error);
			
			break;
		}

		// send log in link to that user
		if (Users\email_link($u)) {
			$message = "Thanks! I've sent a link to your {$u['email']}. If you don't see it <a href='index.php'>click here to refresh the page</a> and try again.";
			$link_email_sent_flag = true;
			$logger->debug($message);
			
		} else {
			$error = "I was unable to email a link to {$u['email']}! Sorry.";
			$logger->debug($error);
		}
		
		break;		
	
	
	// log out	
	case 'lo':
		$logger->info("{$u['nice_name']} logging out.");
		Users\logout($key, $u, $message);
		header("Location: ".URL);
		die();
		break;	

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


	case 'concemail':
		if (!Users\logged_in()) {
			$error = 'You are not logged in! You have to be logged in to change your email.';
			$logger->debug($error);
			break;
		}
		//actually change the email
		$result = Users\change_email($u['id'], $u['new_email']);
		if ($result !== true) {
			$error = $result;
		} else {
			$message = "Email changed from '{$u['email']}' to '{$u['new_email']}'";
			$logger->info($message);
	
			$u = Users\get_user_by_email($u['new_email']);
	
			// make session key equal to database key
			$_SESSION['s_key'] = $key = $u['ukey'];
		}
		break;

}	


