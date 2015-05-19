<?php
$sc = "index.php";
include 'db.php';
include 'common.php';

wbh_set_vars(array('ac', 'wid', 'uid', 'email', 'v', 'key', 'message', 'phone', 'carrier_id', 'send_text', 'newemail'));
$key = wbh_current_key(); // checks for key in REQUEST and SESSION, not logged in otherwise
$error = '';
$message = '';

if ($wid) {
	$wk = wbh_get_workshop_info($wid);
	wbh_check_waiting($wk);
	if (!$v) { $v = 'view'; } // if we've passed in a workshop id, let's show it
}

if ($uid) {
	$u = wbh_get_user_by_id($uid);
} elseif ($email) {
	$u = wbh_get_user_by_email($email);
} elseif ($key) {
	$u = wbh_key_to_user($key);
}

$body = '';
$body = '';
switch ($ac) {
	
	
	case 'cemail':
		if (!wbh_logged_in()) {
			$error = 'You are not logged in! You have to be logged in to change your email.';
			break;
		}
		if (!wbh_validate_email($newemail)) {
			$error = 'You asked to change your email but the new email \'$newemail\' is not a valid email';
			break;
		}
		$sql = "update users set new_email = '".mres($newemail)."' where id = ".mres($u['id']);
		wbh_mysqli($sql) or wbh_db_error();
		
		$sub = 'email update at will hines practices';
		$link = URL."index.php?key=$key&ac=concemail";
		$ebody = "You requested to change what email you use at the Will Hines practices web site. Use the link below to do that:\n\n$link";
		mail($newemail, $sub, $ebody, "From: ".WEBMASTER);
		$message = "Okay, a link has been sent to the new email address ({$newemail}). Check your spam folder if you don't see it.";
		
		
		break;	
		
	case 'concemail':
		if (!wbh_logged_in()) {
			$error = 'You are not logged in! You have to be logged in to change your email.';
			break;
		}
		//actually change the email
		$result = wbh_change_email($u['id'], $u['new_email']);
		if ($result !== true) {
			$error = $result;
		} else {
			$message = "Email changed from '{$u['email']}' to '{$u['new_email']}'";
			$u = wbh_get_user_by_email($u['new_email']);
			
			// make session key equal to database key
			$_SESSION['s_key'] = $key = $u['ukey'];
		}
		break;

	case 'link':
		// if a user exists for that email, get it
		$u = wbh_get_user_by_email($email);

		// if not, make that user
		if ($email && !$u) {
			$u = wbh_make_user($email);
			if ($u === false) {
				$error = "'$email' is not a valid email, I think?";
				break;
			}
		}

		// send log in link to that user
		if (wbh_email_link($u)) {
			$message = "Thanks! I've sent a link to your {$u['email']}. Click it! (check your spam folder, too)";
		} else {
			$error = "I was unable to email a link to {$u['email']}! Sorry.";
		}
		break;		
		
	// log out	
	case 'lo':
		wbh_logout($key, $u, $message);
		break;
	
	// accept an invite to a workshop
	case 'accept':
		if (!wbh_logged_in()) {
			$error = 'You are not logged in! You have to be logged in to accept an invite.';
			break;
		}
		$e = wbh_get_an_enrollment($wk, $u);
		if ($e['status_id'] == INVITED) {
			wbh_change_status($wk, $u, ENROLLED, 1);
			wbh_check_waiting($wk);
			$message = "You are now enrolled in '{$wk['showtitle']}'!";
		} else {
			$error = "You tried to accept an invitation to '{$wk['showtitle']}', but I don't see that there is an open spot.";
		}
		break;

	case 'decline':
		if (!wbh_logged_in()) {
			$error = 'You are not logged in! You have to be logged in to decline an invite.';
			break;
		}
	
		$e = wbh_get_an_enrollment($wk, $u);
		if ($e['status_id'] == INVITED) {
			wbh_change_status($wk, $u, DROPPED, 1);
			wbh_check_waiting($wk);
			$message = "You have dropped out of the waiting list for '{$wk['showtitle']}'.";
		} else {
			$error = "You tried to decline an invitation to '{$wk['showtitle']}', but I don't see that there was an open spot.";
		}
		break;
	
	case 'enroll':
		if (wbh_logged_in()) {
			$message = wbh_handle_enroll($wk, $u, $email);
			if (!$u['send_text']) {
				$message .= " Want notifications by text? <a  class='btn btn-default' href='$sc?v=text'>Set your text preferences</a>.";	
			}
			$v = 'view';
		} else {
			$error = "You must be logged in to enroll.";
		}
		break;
		
	// request a drop (still must be confirmed)
	case 'drop':
		if (!wbh_logged_in()) {
			$error = 'You are not logged in! You have to be logged in to drop a workshop.';
			break;
		}
	
		if ($u) {
			if (wbh_verify_key($key, $u['ukey'], $error)) {
								
				$message = "Do you really want to drop '{$wk['title']}'? Then click <a class='btn btn-warning' href=\"$sc?ac=condrop&uid={$u['id']}&wid={$wid}\">confirm drop</a>";
				
				$e = wbh_get_an_enrollment($wk, $u);
				if ($e['while_soldout']) { 
					$message .= '<br><br>'.wbh_get_dropping_late_warning();
				}
			}
		}
		break;
		
	// confirm drop
	case 'condrop':
		if (!wbh_logged_in()) {
			$error = 'You are not logged in! You have to be logged in to drop a workshop.';
			break;
		}
	
		$message = wbh_change_status($wk, $u, DROPPED, 1);
		$wk =  wbh_get_workshop_info($wk['id']);
		wbh_check_waiting($wk);
		$message = "Dropped user ({$u['email']}) from practice '{$wk['title']}.'";
		break;
		
	// reset the 'key'
	case 'reset':
		if (!wbh_logged_in()) {
			$error = 'You are not logged in! You have to be logged in to get a new log in link.';
			break;
		}
		$key = wbh_gen_key($u['id']); // change the key
		$u = wbh_get_user_by_id($u['id']); // update user variable to include new key
		wbh_email_link($u); // send new log in link to email
		wbh_logout($key, $u, $message);
		break;

	// update text preferences
	case 'updateu':
		if (!wbh_logged_in()) {
			$error = 'You are not logged in! You have to be logged in to update your preferences.';
			break;
		}

		$phone = preg_replace('/\D/', '', $phone); // just numbers for phone
	
		$u['carrier_id'] = $carrier_id;
		$u['phone'] = $phone;
		$u['send_text'] = $send_text;
	
		// only validate data if they want texts, else who cares?
		if ($send_text == 1) {
			if (strlen($phone) != 10) {
				$error = 'Phone number must be ten digits.';
			} 
			if ($carrier_id == 0) {
				$error = 'You must pick a carrier if you want text updates.';
			}
		}
	
		// update user info
		if (!$error) {
			$sql = sprintf("update users set send_text = %u, phone = '%s', carrier_id = %u where id = %u",
			mres($send_text),
			mres($phone),
			mres($carrier_id),
			mres($u['id']));
			wbh_mysqli($sql) or wbh_db_error();
			$u = wbh_get_user_by_id($u['id']);
			$message = 'Preferences updated!';
		}

		$v = 'text';
		break;		
}	

switch ($v) {
	
	
	case 'faq':
	
		$body .= "<div class='row'><div class='col-md-12'>\n";
		$body .= wbh_get_faq();
		$body .= "<p>Just <a href='$sc'>go back to the main page</a>.</p>";
		$body .= "</div></div>\n";
		break;	

	case 'text':
		$body .= "<div class='row'><div class='col-md-12'>\n";
		$body .= "<h2>Your settings</h2>\n";
		if (wbh_logged_in()) {
			$body .= "<h3>Text Notifications</h3>\n";
			$body .= "<p>If you want notifications via text, check the box and set your phone info.</p>\n";			
			$body .= wbh_edit_text_preferences($u);
		} else {
			$body .= "<p>You are not logged in! Go back to the <a href='$sc'>front page</a> and enter your email. We'll email you a link so you can log in.</p>\n";
		}
		$body .= "<p>Just <a href='$sc'>go back to the main page</a>.</p>";
		$body .= "</div></div>\n";

		break;
		
		
	case 'edit':
		$body .= "<div class='row'><div class='col-md-12'>\n";
		$body .= "<h2>Your settings</h2>\n";
		if (wbh_logged_in()) {
			$body .= "<h3>New Email</h3>\n";
			$body .= "<p>If you have a new email, enter it below. We will send a link to your new email. Click that link and we'll reset your account to use that email.</p>\n";
			$body .= "<div class='row'><div class='col-md-4'>\n";
			$body .= "<form action='$sc' method='post'>\n";
			$body .= wbh_hidden('ac', 'cemail');
			$body .= wbh_texty('newemail', null, 0, 'new email address');
			$body .= wbh_submit('Change Email');
			$body .= "</div></div> <!-- end of col and row -->\n";
			

			$body .= "<h3>Reset Your Link</h3>\n";
			$body .= "<p>For the paranoid: This will log you out, generate a new key, and a send a link to your email. If you don't even understand this then don't worry about it. <a class='btn btn-primary' href='$sc?ac=reset'>Reset My Login Link</a></p>";
			
			$body .= "<h3>Never Mind</h3>\n";
			$body .= "<p>Just <a href='$sc'>go back to the main page</a>.</p>";
			
			
		} else {
			$body .= "<p>You are not logged in! Go back to the <a href='$sc'>front page</a> and enter your email. We'll email you a link so you can log in.</p>\n";
		}
		$body .= "</div></div>\n";
		break;
	
	case 'view':
		$body .= "<div class='row'><div class='col-md-12'>\n";
		if (wbh_logged_in()) {
			$e = wbh_get_an_enrollment($wk, $u);
			switch ($e['status_id']) {
				case ENROLLED:
					$point = "You are ENROLLED in this practice. Would you like to <a class='btn btn-default' href='$sc?ac=drop&wid={$wk['id']}&uid={$u['id']}&key={$key}&v=view'>drop</a> it?";
					break;
				case WAITING:
					$point = "You are spot number {$e['rank']} on the WAIT LIST for this practice. Would you like to <a class='btn btn-default' href='$sc?ac=drop&wid={$wk['id']}&uid={$u['id']}&key={$key}&v=view'>drop</a> it?";
					break;
				case INVITED:
					$point = "A spot opened up in this practice. Would you like to <a class='btn btn-default' href='$sc?ac=accept&wid={$wk['id']}&uid={$u['id']}&key={$key}&v=view'>accept</a> it, or <a class='btn btn-default' href='$sc?ac=decline&wid={$wk['id']}&uid={$u['id']}&key={$key}&v=view'>decline</a> it?";
					break;
				case DROPPED:
					$point = "You have dropped out of this practice. Would you like to <a class='btn btn-default'  href='$sc?ac=enroll&wk={$wk['id']}'>re-enroll</a>?";
					break;
				default:
					$point = "You are a status id of '{$e['status_id']}' (I don't know what that is) for this practice:";
					break;
			}
			if ($wk['type'] == 'past') {
				$point = "This workshop is IN THE PAST.";
			}
			$body .= wbh_get_workshop_info_tabled($wid);
			$body .= "<p class='alert alert-info'>$point</p>\n";
			$body .= "<p>Return to a <a href='$sc'>the main page</a>.</p>\n";
		
		} else {
			$body .= wbh_login_prompt();
		}
		$body .= "</div></div> <!-- end of col and row -->\n";
		break;
			
	default:
	
		$body .= "<div class='row'><div class='col-md-12'>\n";
		if (wbh_logged_in()) {
			$body .= "<h2>Welcome</h2>\n";
			$body .= "<p>You are logged in as {$u['email']}! (You can <a href='$sc?v=edit'>change your email</a> or <a href='$sc?ac=lo'>log out</a>)</p>";			

			$body .= "<p>".($u['send_text'] ? "You have signed up for text notfications. " : "Would you like text notifications?")." <a  class='btn btn-primary' href='$sc?v=text'>Get text notifications</a>.</p>\n";

		} else {
			$body .= "<h2>Log In</h2>\n";
			$body .= "<p>You are not logged in. To log in, you don't need a password or a Facebook account but you do need an email account.</p>";
			$body .= wbh_login_prompt();
		}
		$body .= "</div></div> <!-- end of col and row -->\n";

		$body .= "<div class='row'><div class='col-md-12'>\n";
		$body .= "<h2>All Upcoming Workshops</h2>\n"; 
		$body .= wbh_get_workshops_list(0);
		$body .= "</div></div> <!-- end of col and row -->\n";
		
		$body .= "<div class='row'><div class='col-md-12'>";
		$body .= "<h2>Your Current/Past Workshops</h2>";
		if (wbh_logged_in()) {
			$body .= wbh_get_transcript_tabled($u);  
		} else {
			$body .= "<p>You're not logged in, so I can't list your workshops. Log in further up this page.</p>";
		}
		$body .= "<h2>Questions</h2>\n";
		$body .= "<p>Paying? Lateness? Levels? See <a href='$sc?v=faq'>questions</a>.</p>\n";		
		$body .= "</div></div> <!-- end of col and row -->\n";	
		
		include 'mailchimp.php';
			
		$body .= "<br><br>\n";
		break;
}



$heading = 'improv practices';
include 'header.php';
echo $body;


include 'footer.php';

function wbh_login_prompt() {
	return "<p>Submit your email with this form and we will email you a link to log in: " .wbh_get_trans_form()."</p>\n";
}

function wbh_logout(&$key, &$u, &$message) {
	unset($_SESSION['s_key']);
	$key = '';
	$u = null;
	$message = 'You are logged out!';
}


