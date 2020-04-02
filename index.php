<?php
$heading = 'improv practices';
$sc = "index.php";

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

	
	// log out	
	case 'lo':
		$logger->info("{$u['nice_name']} logging out.");
		Users\logout($key, $u, $message);
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
			$logger->debug("Rejected invite for {$u['nice_name']} since {$wk['showtitle']} is cancelled.");
			
			break;
		}
		$e = Enrollments\get_an_enrollment($wk, $u);
		if ($e['status_id'] == INVITED) {
			Enrollments\change_status($wk, $u, ENROLLED, 1);
			Enrollments\check_waiting($wk);
			$message = "You are now enrolled in '{$wk['showtitle']}'! Info emailed to <b>{$u['email']}</b>.";
			
		} else {
			$error = "You tried to accept an invitation to '{$wk['showtitle']}', but I don't see that there is an open spot.";
			$logger->debug("Rejected invite for {$u['nice_name']} since {$wk['showtitle']} is full.");
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
			$logger->debug("Rejected decline for {$u['nice_name']} since {$wk['showtitle']} is cancelled.");
			break;
		}
	
		$e = Enrollments\get_an_enrollment($wk, $u);
		if ($e['status_id'] == INVITED) {
			Enrollments\change_status($wk, $u, DROPPED, 1);
			Enrollments\check_waiting($wk);
			$message = "You have dropped out of the waiting list for '{$wk['showtitle']}'.";			
		} else {
			$error = "You tried to decline an invitation to '{$wk['showtitle']}', but I don't see that there was an open spot.";
			$logger->debug("Rejected decline for {$u['nice_name']} since {$wk['showtitle']} is full.");
			
		}
		break;
	
	case 'enroll':
		if ($wk['cancelled']) {
			$error = 'This workshop has been cancelled.';
			$logger->debug("{$u['nice_name']} cannot enroll since {$wk['showtitle']} is cancelled.");
			
			break;
		}	
		if (Users\logged_in()) {
			$message = Enrollments\handle_enroll($wk, $u);
			if (!$u['send_text']) {
				$message .= " Want notifications by text? <a  class='btn btn-primary' href='$sc?v=text'>Set your text preferences</a>.";	
			}
		} else {
			$error = "You must be logged in to enroll.";
			$logger->debug("attempted enroll with no one logged in.");
			
		}
		break;
		
	// request a drop (still must be confirmed)
	case 'drop':
		if (!Users\logged_in()) {
			$error = 'You are not logged in! You have to be logged in to drop a workshop.';
			$logger->debug("attempted drop with no one logged in.");
			
			break;
		}
		if ($wk['cancelled']) {
			$error = 'This workshop has been cancelled.';
			$logger->debug("{$u['nice_name']} tried to drop from {$wk['showtitle']} but it's cancelled.");
			
			break;
		}
	
		if ($u) {
			if (Users\verify_key($key, $u['ukey'], $error)) {
								
				$message = "Do you really want to drop '{$wk['title']}'? Then click <a class='btn btn-warning' href=\"$sc?ac=condrop&uid={$u['id']}&wid={$wid}\">confirm drop</a>";
				
				$e = Enrollments\get_an_enrollment($wk, $u);
				if ($e['while_soldout']) { 
					$message .= '<br><br>'.Emails\get_dropping_late_warning();
				}
			}
		}
		break;
		
	// confirm drop
	case 'condrop':
		if (!Users\logged_in()) {
			$error = 'You are not logged in! You have to be logged in to drop a workshop.';
			break;
		}
		if ($wk['cancelled']) {
			$error = 'This workshop has been cancelled.';
			break;
		}
	
		$message = Enrollments\change_status($wk, $u, DROPPED, 1);
		$wk =  Workshops\get_workshop_info($wk['id']);
		Enrollments\check_waiting($wk);
		$message = "Dropped user ({$u['email']}) from practice '{$wk['title']}.'";
		break;
		
	// update text preferences
	case 'updateu':
	
	
		Wbhkit\set_vars(array('carrier_id', 'phone', 'send_text'));
		
	
		if (!Users\logged_in()) {
			$error = 'You are not logged in! You have to be logged in to update your preferences.';
			break;
		}
		$u['carrier_id'] = $carrier_id;
		$u['phone'] = $phone;
		$u['send_text'] = $send_text;
		Users\update_text_preferences($u, $message, $error); // function will update all of those arguments
		break;		
		
		
	case 'faq':
	 
		$view->data['faq'] = Emails\get_faq();
		$view->renderPage('faq');
		die;
		

}	


// if a $wid was passed in, we'll show that page
if ($wid) {
	$wk = Workshops\fill_out_workshop_row($wk, true);
	$view->data['e'] = Enrollments\get_an_enrollment($wk, $u);
	$view->data['workshop_tabled'] = Workshops\get_workshop_info_tabled($wk);
	$view->renderPage('winfo');
} else {
	// if nothing else happens, we'll render 'home'
	$view->data['upcoming_workshops'] = Workshops\get_workshops_list(0);
	$view->data['transcript'] = Enrollments\get_transcript_tabled($u, 0, $page); 
	$view->renderPage('home');
	
}





