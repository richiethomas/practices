<?php
$sc = "index.php";
include 'db.php';
include 'lib-master.php';

Wbhkit\set_vars(array('ac', 'wid', 'uid', 'email', 'v', 'key', 'message', 'phone', 'carrier_id', 'send_text', 'newemail'));


$key = Users\current_key(); // checks for key in REQUEST and SESSION and COOKIE, not logged in otherwise
$error = '';
$message = '';

if ($wid) {
	$wk = Workshops\get_workshop_info($wid);
	Enrollments\check_waiting($wk);
	if (!$v) { $v = 'view'; } // if we've passed in a workshop id, let's show it
}

if ($uid) {
	$u = Users\get_user_by_id($uid);
} elseif ($email) {
	$u = Users\get_user_by_email($email);
} elseif ($key) {
	$u = Users\key_to_user($key);
}

$body = '';
$body = '';
switch ($ac) {
	
	
	case 'cemail':
		if (!Users\logged_in()) {
			$error = 'You are not logged in! You have to be logged in to change your email.';
			break;
		}
		if (!Users\validate_email($newemail)) {
			$error = 'You asked to change your email but the new email \'$newemail\' is not a valid email';
			break;
		}
		$sql = "update users set new_email = '".Database\mres($newemail)."' where id = ".Database\mres($u['id']);
		Database\mysqli($sql) or Database\db_error();
		
		$sub = 'email update at will hines practices';
		$link = URL."index.php?key=$key&ac=concemail";
		$ebody = "You requested to change what email you use at the Will Hines practices web site. Use the link below to do that:\n\n$link";
		mail($newemail, $sub, $ebody, "From: ".WEBMASTER);
		$message = "Okay, a link has been sent to the new email address ({$newemail}). Check your spam folder if you don't see it.";
		
		
		break;	
		
	case 'concemail':
		if (!Users\logged_in()) {
			$error = 'You are not logged in! You have to be logged in to change your email.';
			break;
		}
		//actually change the email
		$result = Users\change_email($u['id'], $u['new_email']);
		if ($result !== true) {
			$error = $result;
		} else {
			$message = "Email changed from '{$u['email']}' to '{$u['new_email']}'";
			$u = Users\get_user_by_email($u['new_email']);
			
			// make session key equal to database key
			$_SESSION['s_key'] = $key = $u['ukey'];
		}
		break;

	case 'link':
		// if a user exists for that email, get it
		$u = Users\get_user_by_email($email);

		// if not, make that user
		if ($email && !$u) {
			$u = Users\make_user($email);
			if ($u === false) {
				$error = "'$email' is not a valid email, I think?";
				break;
			}
		}

		// send log in link to that user
		if (Users\email_link($u)) {
			$message = "Thanks! I've sent a link to your {$u['email']}. Click it! (check your spam folder, too)";
		} else {
			$error = "I was unable to email a link to {$u['email']}! Sorry.";
		}
		break;		
		
	// log out	
	case 'lo':
		Users\logout($key, $u, $message);
		break;
	
	// accept an invite to a workshop
	case 'accept':
		if (!Users\logged_in()) {
			$error = 'You are not logged in! You have to be logged in to accept an invite.';
			break;
		}
		$e = Enrollments\get_an_enrollment($wk, $u);
		if ($e['status_id'] == INVITED) {
			Enrollments\change_status($wk, $u, ENROLLED, 1);
			Enrollments\check_waiting($wk);
			$message = "You are now enrolled in '{$wk['showtitle']}'!";
		} else {
			$error = "You tried to accept an invitation to '{$wk['showtitle']}', but I don't see that there is an open spot.";
		}
		break;

	case 'decline':
		if (!Users\logged_in()) {
			$error = 'You are not logged in! You have to be logged in to decline an invite.';
			break;
		}
	
		$e = Enrollments\get_an_enrollment($wk, $u);
		if ($e['status_id'] == INVITED) {
			Enrollments\change_status($wk, $u, DROPPED, 1);
			Enrollments\check_waiting($wk);
			$message = "You have dropped out of the waiting list for '{$wk['showtitle']}'.";
		} else {
			$error = "You tried to decline an invitation to '{$wk['showtitle']}', but I don't see that there was an open spot.";
		}
		break;
	
	case 'enroll':
		if (Users\logged_in()) {
			$message = Enrollments\handle_enroll($wk, $u, $email);
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
		if (!Users\logged_in()) {
			$error = 'You are not logged in! You have to be logged in to drop a workshop.';
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
	
		$message = Enrollments\change_status($wk, $u, DROPPED, 1);
		$wk =  Workshops\get_workshop_info($wk['id']);
		Enrollments\check_waiting($wk);
		$message = "Dropped user ({$u['email']}) from practice '{$wk['title']}.'";
		break;
		
	// reset the 'key'
	case 'reset':
		if (!Users\logged_in()) {
			$error = 'You are not logged in! You have to be logged in to get a new log in link.';
			break;
		}
		$key = Users\gen_key($u['id']); // change the key
		$u = Users\get_user_by_id($u['id']); // update user variable to include new key
		Users\email_link($u); // send new log in link to email
		Users\logout($key, $u, $message);
		break;

	// update text preferences
	case 'updateu':
		if (!Users\logged_in()) {
			$error = 'You are not logged in! You have to be logged in to update your preferences.';
			break;
		}
		$u['carrier_id'] = $carrier_id;
		$u['phone'] = $phone;
		$u['send_text'] = $send_text;
		Users\update_text_preferences($u, $message, $error); // function will update all of those arguments
		$v = 'text';
		break;		
}	

switch ($v) {
	
	
	case 'faq':
	
		$body .= "<div class='row'><div class='col-md-12'>\n";
		$body .= Emails\get_faq();
		$body .= "<p>Just <a href='$sc'>go back to the main page</a>.</p>";
		$body .= "</div></div>\n";
		break;	

	case 'text':
		$body .= "<div class='row'><div class='col-md-12'>\n";
		$body .= "<h2>Your settings</h2>\n";
		if (Users\logged_in()) {
			$body .= "<h3>Text Notifications</h3>\n";
			$body .= "<p>If you want notifications via text, check the box and set your phone info.</p>\n";			
			$body .= Users\edit_text_preferences($u);
		} else {
			$body .= "<p>You are not logged in! Go back to the <a href='$sc'>front page</a> and enter your email. We'll email you a link so you can log in.</p>\n";
		}
		$body .= "<p>Just <a href='$sc'>go back to the main page</a>.</p>";
		$body .= "</div></div>\n";

		break;
		
		
	case 'edit':
		$body .= "<div class='row'><div class='col-md-12'>\n";
		$body .= "<h2>Your settings</h2>\n";
		if (Users\logged_in()) {
			$body .= "<h3>New Email</h3>\n";
			$body .= "<p>If you have a new email, enter it below. We will send a link to your new email. Click that link and we'll reset your account to use that email.</p>\n";
			$body .= "<div class='row'><div class='col-md-4'>\n";
			$body .= "<form action='$sc' method='post'>\n";
			$body .= Wbhkit\hidden('ac', 'cemail');
			$body .= Wbhkit\texty('newemail', null, 0, 'new email address');
			$body .= Wbhkit\submit('Change Email');
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
		if (Users\logged_in()) {
			$e = Enrollments\get_an_enrollment($wk, $u);
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
			$body .= Workshops\get_workshop_info_tabled($wid);
			$body .= "<p class='alert alert-info'>$point</p>\n";
			$body .= "<p>Return to a <a href='$sc'>the main page</a>.</p>\n";
		
		} else {
			$body .= login_prompt();
		}
		$body .= "</div></div> <!-- end of col and row -->\n";
		break;
			
	default:
	
		$body .= "<div class='row'><div class='col-md-12'><div id='login_prompt' class='well'>\n";
	
		
		
		
		if (Users\logged_in()) {
			$body .= "<h2>Welcome</h2>\n";
			$body .= "<p>You are logged in as {$u['email']}! (You can <a href='$sc?v=edit'>change your email</a> or <a href='$sc?ac=lo'>log out</a>)</p>";			

			$body .= "<p>".($u['send_text'] ? "You have signed up for text notfications. " : "Would you like text notifications?")." <a  class='btn btn-primary' href='$sc?v=text'>Set your text preferences</a>.</p>\n";

		} else {
			$body .= "<h2>Log In To This Site</h2>\n";
			$body .= "<p>First you must log in. We do that via email.</p>";
			$body .= login_prompt();
		}
		//include 'mailchimp.php';
		$body .= "</div></div></div>\n"; // end of log in prompt div, and its column and row

		$body .= "<div class='row'><div class='col-md-12'>\n";
		$body .= "<h2>Paying</h2>\n"; 
		$body .= "<p>Pay in person or with <a href=\"http://venmo.com/willhines?txn=pay&share=friends&amount=25&note=improv%20workshop\">venmo</a> (click that link or pay to <a href=\"http://venmo.com/willhines?txn=pay&share=friends&amount=25&note=improv%20workshop\">whines@gmail.com</a>). On the day of the workshop is fine.</p>\n";
		$body .= "</div></div> <!-- end of col and row -->\n";

		$body .= "<div class='row'><div class='col-md-12'>\n";
		$body .= "<h2>No late drops!</h2>\n"; 
		$body .= "<p>Dropping out the night before or the morning of is very not cool! You were holding a spot and then you didn't use it! Please don't do it! If you do, I might ask you to pay which is gonna be weird for both of us.</p>\n";
		$body .= "</div></div> <!-- end of col and row -->\n";


		$body .= "<div class='row'><div class='col-md-12'>\n";
		$body .= "<h2>Mailing list</h2>\n"; 
		$body .= "<p>You are NOT automatically put on my mailing list for these workshops. If you WANT to be on that mailing list, <a href='http://eepurl.com/c6-T-H'>sign yourself up here</a>.</p>\n";
		$body .= "</div></div> <!-- end of col and row -->\n";


		$body .= "<div class='row'><div class='col-md-12'>\n";
		$body .= "<h2>Questions</h2>\n";
		$body .= "<p>You can be late. You can leave early. Pre-reqs are not enforced. For more, see <a href='$sc?v=faq'>common questions</a>.</p>\n";		
		$body .= "</div></div> <!-- end of col and row -->\n";	


		$body .= "<div class='row'><div class='col-md-12'>\n";
		$body .= "<h2>All Upcoming Workshops</h2>\n"; 
		$body .= Workshops\get_workshops_list(0);
		$body .= "</div></div> <!-- end of col and row -->\n";
		
		$body .= "<div class='row'><div class='col-md-12'>";
		$body .= "<h2>Your Current/Past Workshops</h2>";
		if (Users\logged_in()) {
			$body .= Enrollments\get_transcript_tabled($u);  
		} else {
			$body .= "<p>You're not logged in, so I can't list your workshops. Log in further up this page.</p>";
		}
		$body .= "</div></div> <!-- end of col and row -->\n";	
		
			
		$body .= "<br><br>\n";
		break;
}



$heading = 'improv practices';
include 'header.php';
echo $body;


include 'footer.php';

function login_prompt() {
	return "<p>Submit your email with this form and the site will email you a link to log in: " .Users\get_trans_form()."</p>\n";
}




