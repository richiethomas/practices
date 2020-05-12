<?php
$heading = 'improv practices';
$sc = "index.php";
include 'lib-master.php';
	
switch ($ac) {
	
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
			$message = "Thanks! I've sent a link to your {$u['email']}. Click it! (check your spam folder, too)";
			$logger->debug($message);
			
		} else {
			$error = "I was unable to email a link to {$u['email']}! Sorry.";
			$logger->debug($error);
		}
		
		if ($display_name) {
			$u['display_name'] = $display_name;
			Users\update_display_name($u, $message, $error);
		}
		
		break;		
	
	
	// log out	
	case 'lo':
		$logger->info("{$u['nice_name']} logging out.");
		Users\logout($key, $u, $message);
		break;	

}	


// if nothing else happens, we'll render 'home'
$view->data['upcoming_workshops'] = Workshops\get_workshops_list(0, $page);
$view->data['transcript'] = Enrollments\get_transcript_tabled($u, 0, $page); 
$view->data['unavailable_workshops'] = Workshops\get_unavailable_workshops(); 
$view->renderPage('home');





