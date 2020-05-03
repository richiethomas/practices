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
	
	
	// accept an invite to a workshop
	case 'accept':
		if (!Users\logged_in()) {
			$error = 'You are not logged in! You have to be logged in to accept an invite.';
			$logger->debug($error);
			
			break;
		}
		if ($wk['cancelled']) {
			$error = 'Cannot accept invite. This workshop has been cancelled.';
			$logger->debug("Rejected invite for {$u['nice_name']} since {$wk['title']} is cancelled.");
			
			break;
		}
		$e = Enrollments\get_an_enrollment($wk, $u);
		if ($e['status_id'] == INVITED) {
			Enrollments\change_status($wk, $u, ENROLLED, 1);
			Enrollments\check_waiting($wk);
			$message = "You are now enrolled in '{$wk['title']}'! Info emailed to <b>{$u['email']}</b>.";
			
		} else {
			$error = "You tried to accept an invitation to '{$wk['title']}', but I don't see that there is an open spot.";
			$logger->debug("Rejected invite for {$u['nice_name']} since {$wk['title']} is full.");
		}
		break;

	case 'decline':
		if (!Users\logged_in()) {
			$error = 'You are not logged in! You have to be logged in to decline an invite.';
			$logger->debug($error);
			break;
		}
		if ($wk['cancelled']) {
			$error = 'This workshop has been cancelled.';
			$logger->debug("Rejected decline for {$u['nice_name']} since {$wk['title']} is cancelled.");
			break;
		}
	
		$e = Enrollments\get_an_enrollment($wk, $u);
		if ($e['status_id'] == INVITED) {
			Enrollments\change_status($wk, $u, DROPPED, 1);
			Enrollments\check_waiting($wk);
			$message = "You have dropped out of the waiting list for '{$wk['title']}'.";			
		} else {
			$error = "You tried to decline an invitation to '{$wk['title']}', but I don't see that there was an open spot.";
			$logger->debug("Rejected decline for {$u['nice_name']} since {$wk['title']} is full.");
			
		}
		break;
		
	case 'faq':
	 
		$view->data['faq'] = Emails\get_faq();
		$view->renderPage('faq');
		die;
	
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





