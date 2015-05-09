<?php
$sc = "index.php";
include 'db.php';
include 'common.php';

wbh_set_vars(array('ac', 'wid', 'uid', 'email', 'v', 'key', 'message', 'phone', 'carrier_id', 'send_text'));
$key = wbh_current_key();
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
	
	case 'updateu':
		$phone = preg_replace('/\D/', '', $phone); // just numbers for phone
		
		$u['carrier_id'] = $carrier_id;
		$u['phone'] = $phone;
		$u['send_text'] = $send_text;
		
		if ($send_text == 1) {
			if (strlen($phone) < 10) {
				$error = 'Phone number must be ten digits.';
			} 
			if ($carrier_id == 0) {
				$error = 'You must pick a carrier if you want text updates.';
			}
		}
		
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
	
		break;
	case 'lo':
		unset($_SESSION['s_key']);
		$key = '';
		$u = null;
		$message = 'You are logged out!';
		break;
	
	case 'accept':
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
		$e = wbh_get_an_enrollment($wk, $u);
		if ($e['status'] == INVITED) {
			wbh_change_status($wk, $u, 'dropped', 1);
			wbh_check_waiting($wk);
			$message = "You have dropped out of the waiting list for '{$wk['showtitle']}'.";
		} else {
			$error = "You tried to decline an invitation to '{$wk['showtitle']}', but I don't see that there was an open spot.";
		}
		break;

	
	case 'link':
		if ($email && !$u) {
			$u = wbh_make_user($email);
			if ($u === false) {
				$error = "'$email' is not a valid email, I think?";
				break;
			}
		}
		if (wbh_email_link($u)) {
			$message = "Thanks! I've sent a link to your {$u['email']}. Click it! (check your spam folder, too)";
		} else {
			$error = "I was unable to email a link to {$u['email']}! Sorry.";
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
		if (wbh_logged_in()) {
			$message = wbh_change_status($wk, $u, 'dropped', 1);
			$wk =  wbh_get_workshop_info($wk['id']);
			wbh_check_waiting($wk);
			$message = "Dropped user ({$u['email']}) from practice '{$wk['title']}.'";
		} else {
			$error = "You must be logged in to drop out.";
		}
		break;
}	

switch ($v) {
	
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
			$body .= "<p>You are logged in as {$u['email']}! (<a href='$sc?ac=lo'>log out</a>)</p>";			
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
		
		
		if (wbh_logged_in()) {
			$body .= "<div class='row'><div class='col-md-4'>\n";
			$body .= "<h2>Your settings</h2>\n";
			$body .= wbh_edit_user_form($u);
			$body .= "</div></div> <!-- end of col and row -->\n";
		}
		
		
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
