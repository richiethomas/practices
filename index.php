<?php
$sc = "index.php";
include 'db.php';
include 'common.php';

wbh_set_vars(array('ac', 'wid', 'uid', 'email', 'v', 'key', 'message', 'phone', 'carrier_id', 'send_text'));
$key = wbh_current_key(); // checks for key in REQUEST and SESSION, not logged in otherwise
$error = '';
$message = '';

if ($wid) {
	$wk = wbh_get_workshop_info($wid);
	wbh_check_waiting($wk);
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
		if ($e['status'] == INVITED) {
			wbh_change_status($wk, $u, 'enrolled', 1);
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
		if ($e['status'] == INVITED) {
			wbh_change_status($wk, $u, 'dropped', 1);
			wbh_check_waiting($wk);
			$message = "You have dropped out of the waiting list for '{$wk['showtitle']}'.";
		} else {
			$error = "You tried to decline an invitation to '{$wk['showtitle']}', but I don't see that there was an open spot.";
		}
		break;
	
	case 'enroll':
		if (wbh_logged_in()) {
			$message = wbh_handle_enroll($wk, $u, $email);
		} else {
			$error = "You must be logged in to enroll.";
		}
		break;
		
	case 'drop':
		if (!wbh_logged_in()) {
			$error = 'You are not logged in! You have to be logged in to drop a workshop.';
			break;
		}
	
		if ($u) {
			if (wbh_verify_key($key, $u['ukey'], $error)) {
								
				$message = "Do you really want to drop '{$wk['title']}'? Then click <a class='btn btn-warning' href=\"$sc?ac=condrop&uid={$u['id']}&wid={$wid}\">drop</a>";
				
				$e = wbh_get_an_enrollment($wk, $u);
				if ($e['while_soldout']) { 
					$message .= '<br><br>'.wbh_get_dropping_late_warning();
				}
			}
		}
		break;
		
	case 'condrop':
		if (!wbh_logged_in()) {
			$error = 'You are not logged in! You have to be logged in to drop a workshop.';
			break;
		}
	
		$message = wbh_change_status($wk, $u, 'dropped', 1);
		$wk =  wbh_get_workshop_info($wk['id']);
		wbh_check_waiting($wk);
		$message = "Dropped user ({$u['email']}) from practice '{$wk['title']}.'";
		break;
		
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
			if (strlen($phone) < 10) {
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

		$v = 'default';
		break;		
}	

switch ($v) {
	
	case 'edit':
		$body .= "<div class='row'><div class='col-md-12'>\n";
		$body .= "<h2>Your settings</h2>\n";
		if (wbh_logged_in()) {
			$body .= "<h3>Text Notifications</h3>\n";
			$body .= "<p>If you want notifications via text, check the box and set your phone info.</p>\n";			
			$body .= wbh_edit_user_form($u);

			$body .= "<h3>New Email</h3>\n";
			$body .= "<p>If you have a new email, enter it below. We will send a link to that new email. Click that link and we'll reset your account to have that email.</p>\n";

			$body .= "<h3>Reset Your Link</h3>\n";
			$body .= "<p>For the paranoid: This will log you out, generate a new key, and a send a link to your email. Click that link to log back in. Want to do that? If you don't even understand this then don't worry about it. <a class='btn btn-default' href='$sc?ac=reset'>Reset My Login Link</a></p>";
			
			
		} else {
			$body .= "<p>You are not logged in! Go back to the <a href='$sc'>front page</a> and enter your email. We'll email you a link so you can log in.</p>\n";
		}
		$body .= "</div></div>\n";
		break;
	
	case 'confirm':
		$body .= "<div class='row'><div class='col-md-12'>\n";
		$body .= "<p>Registered for:</p>\n";
		$body .= wbh_get_workshop_info_tabled($wid);
		$body .= "<p>Return to a <a href='$sc'>list of all upcoming practices</a>.</p>\n";
		$body .= "</div></div> <!-- end of col and row -->\n";
		break;

	case 'view':
		$body .= "<div class='row'><div class='col-md-12'>\n";
		if (wbh_logged_in()) {
			$e = wbh_get_an_enrollment($wk, $u);
			switch ($e['status']) {
				case ENROLLED:
					$point = "You are ENROLLED in this practice. Would you like to <a class='btn btn-info' href='$sc?ac=drop&wid={$wk['id']}&uid={$u['id']}&key={$key}&v=view'>drop</a> it?";
					break;
				case WAITING:
					$point = "You are spot number {$e['rank']} on the WAIT LIST for this practice.";
					break;
				case INVITED:
					$point = "A spot opened up in this practice. Would you like to <a class='btn btn-info' href='$sc?ac=accept&wid={$wk['id']}&uid={$u['id']}&key={$key}&v=view'>accept</a> it, or <a class='btn btn-info' href='$sc?ac=decline&wid={$wk['id']}&uid={$u['id']}&key={$key}&v=view'>decline</a> it?";
					break;
				case DROPPED:
					$point = "You have dropped out of this practice.";
					break;
				default:
					$point = "You are a status of '$st' for this practice:";
					break;
			}
			if (!$ac) {
				if ($wk['type'] == 'past') {
					$point = "This workshop is IN THE PAST.";
				}
				$body .= "<p class='alert alert-info'>$point</p>\n";
			}
			$body .= wbh_get_workshop_info_tabled($wid);
			$body .= "<p>Return to a <a href='$sc?v=trans'>list of your practices</a>.</p>\n";
		
		} else {
			$body .= wbh_login_prompt();
		}
		$body .= "</div></div> <!-- end of col and row -->\n";
		break;
			
	default:
	
		$body .= "<div class='row'><div class='col-md-12'>\n";
		if (wbh_logged_in()) {
			$body .= "<h2>Welcome</h2>\n";
			$body .= "<p>You are logged in as {$u['email']}! (You can <a href='$sc?v=edit'>edit your preferences</a> or <a href='$sc?ac=lo'>log out</a>)</p>";			
		} else {
			$body .= "<h2>Log In</h2>\n";
			$body .= "<p>You are not logged in. To log in, you don't need a password or a Facebook account but you do need an email account.</p>";
			$body .= wbh_login_prompt();
		}
		$body .= "</div></div> <!-- end of col and row -->\n";

		$body .= "<div class='row'><div class='col-md-12'>\n";
		$body .= "<h2>All Workshops</h2>\n"; 
		$body .= wbh_get_workshops_list(0);
		$body .= "</div></div> <!-- end of col and row -->\n";
		
		$body .= "<div class='row'><div class='col-md-12'>";
		$body .= "<h2>Your Workshops</h2>";
		if (wbh_logged_in()) {
			$body .= wbh_get_transcript_tabled($u);  
		} else {
			$body .= "<p>You're not logged in, so I can't list your workshops. Log in further up this page.</p>";
		}
		$body .= "</div></div> <!-- end of col and row -->\n";		
		
		break;
}


$body .= wbh_get_faq();

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


