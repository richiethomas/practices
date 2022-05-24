<?php

$link_email_sent_flag = false;

switch ($ac) {

	case 'k':
	
		if ($u->set_by_key($params[2])) {
			$message = "Welcome, {$u->fields['nice_name']}!";
			$logger->debug("{$u->fields['email']} logged in via URL.");
		} else {
			$error = "Tried to log someone in, but the key was, as we say in the computer business, malformed.";
		}
		break;

	case 'link':
	
		Wbhkit\set_vars(array('email', 'display_name', 'fax_only', 'when_login'));
	
		if (!$fax_only) {
			
			
			if (!$email) {
				$logger->error("Login link requested but no email submitted.");
				break;
			}
			if (!$when_login) {
				$logger->error("Login link requested but time of submission (when_login) missing.");
				break;
			}
			
			// already logged in with this email? do nothing
			if ($u->logged_in() && $u->fields['email'] == $email) {
				$logger->error("LOGIN PROTECT: {$u->fields['email']} already logged in!");
				break;
			}
			
			// if it's 30 minutes after the login form was generated, do nothing
			$passed = strtotime('now') - strtotime($when_login);
			if (($passed / 60) > 30) {
				$logger->error("LOGIN PROTECT: {$email} requested a login ".($passed / 60)." mins later");
				break;
			}
			
			// if a user exists for that email, get it
			$u->set_by_email($email);

			// if failed, that was a bad email
			if (!$u->logged_in()) {
				$error = $u->error;
				break;
			}

			// send log in link to that user
			if ($u->email_link()) {
				$message = "Thanks! I've sent a link to your {$u->fields['email']}. If you don't see it <a href='index.php'>click here to refresh the page</a> and try again.";
				
				if (LOCAL) {
					$trans = URL."home/k/".$u->get_key();
					$message .= "<br><br>This is the link: <a href='$trans'>{$trans}</a>";
				}
				
				$link_email_sent_flag = true;
				$u->soft_logout();

			} else {
				$error = "I was unable to email a link to {$u->fields['email']}! Sorry.";
			}
		
		} else {
			$message = "Everything went great.";
			$logger->error("spambot '{$email}' - IP: {$_SERVER['SERVER_ADDR']}");
		}
		break;		
	
	
	// log out	
	case 'lo':
		$logger->debug("{$u->fields['nice_name']} logging out.");
		if (LOCAL) {
			$message = $u->soft_logout();
		} else {
			$message = $u->hard_logout();
			header("Location: ".URL);
			die();
		}
		break;	

	case 'cemail':
		Wbhkit\set_vars(array('newemail'));

		if (!$u->logged_in()) {
			$error = 'You are not logged in! You have to be logged in to change your email.';
			break;
		}
		if (!$u->validate_email($newemail)) {
			$error = 'You asked to change your email but the new email \'$newemail\' is not a valid email';
			break;
		}

		if ($u->change_email_phase_one($newemail)) {
			$message = "Okay, a link has been sent to the new email address ({$newemail}). Check your spam folder if you don't see it.";

		} else {
			// if error, email already being used
			$error = $u->error;
		}
		break;


	// update display name
	case 'updatedn':
		Wbhkit\set_vars(array('display_name'));
	
		if (!$u->logged_in()) {
			$error = 'You are not logged in! You have to be logged in to update your display name.';
			break;
		}
		$message = "Changing display name to '$display_name' from '{$u->fields['display_name']}'";
		$u->update_display_name($display_name);		
		break;		

	case 'updatetz':
		Wbhkit\set_vars(array('time_zone'));
	
		if (!$u->logged_in()) {
			$error = 'You are not logged in! You have to be logged in to update your display name.';
			break;
		}
		$message = "Changing time zone to '$time_zone' for '{$u->fields['email']}'";
		$u->update_time_zone($time_zone);		
		break;		
			


	case 'concemail':
		if (!$u->logged_in()) {
			$error = 'You are not logged in! You have to be logged in to change your email.';
			break;
		}
		
		$oldemail = $u->fields['email'];
		$newemail = $u->fields['new_email'];
		if ($u->user_finish_change_email()) {
			$message = "Email changed from '{$oldemail}' to '{$newemail}'";
		} else {
			// if error, email being used
			$error = $u->error;
		}
		break;

}	
if ($error) { $logger->debug($error); }
if ($message) { $logger->debug($message); }


