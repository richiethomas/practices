<?php
switch ($v) {
	
	
	case 'faq':
	
		$body .= "<div class='row'><div class='col'>\n";
		$body .= Emails\get_faq();
		$body .= "<p>Just <a href='$sc'>go back to the main page</a>.</p>";
		$body .= "</div></div>\n";
		break;	

	case 'text':
		$body .= "<div class='row'><div class='col'>\n";
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
	
	case 'editdn':
		$body .= "<div class='row'><div class='col'>\n";
		$body .= "<h2>Set Your Display Name</h2>\n";
		if (Users\logged_in()) {
			$body .= "<div class='row'><div class='col'>\n";
			$body .= "<h3>Update Display Name</h3>\n";
			$body .= "<p>This is the name we will show when we list who is in the practice.</p>\n";
			$body .= Users\edit_display_name();
			$body .= "</div></div> <!-- end of col and row -->\n";
			$body .= "<div class='row'><div class='col'>\n";
			$body .= "<p>Just <a href='$sc'>go back to the main page</a>.</p>";
			$body .= "</div></div> <!-- end of col and row -->\n";
		} else {
			$body .= "<p>You are not logged in! Go back to the <a href='$sc'>front page</a> and enter your email. We'll email you a link so you can log in.</p>\n";
		}
		$body .= "</div></div>\n";
				
	case 'edit':
		$body .= "<div class='row'><div class='col'>\n";
		$body .= "<h2>Your settings</h2>\n";
		if (Users\logged_in()) {


			$body .= "<div class='row'><div class='col'>\n";
			$body .= "<h3>Update Display Name</h3>\n";
			$body .= "<p>This is the name we will show when we list who is in the practice.</p>\n";
			$body .= Users\edit_display_name($u);
			$body .= "</div></div> <!-- end of col and row -->\n";


			$body .= "<div class='row'><div class='col'>\n";
			$body .= "<h3>New Email</h3>\n";
			$body .= "<p>If you have a new email, enter it below. We will send a link to your new email. Click that link and we'll reset your account to use that email.</p>\n";
			$body .= "<form action='$sc' method='post'>\n";
			$body .= Wbhkit\hidden('ac', 'cemail');
			$body .= Wbhkit\texty('newemail', null, 0, 'new email address');
			$body .= Wbhkit\submit('Change Email');
			$body .= "</form>";
			$body .= "</div></div> <!-- end of col and row -->\n";
			

			$body .= "<div class='row'><div class='col'>\n";
			$body .= "<h3>Reset Your Link</h3>\n";
			$body .= "<p>For the paranoid: This will log you out, generate a new key, and a send a link to your email. If you don't even understand this then don't worry about it. <a class='btn btn-primary' href='$sc?ac=reset'>Reset My Login Link</a></p>";
			$body .= "</div></div> <!-- end of col and row -->\n";
			
			$body .= "<div class='row'><div class='col'>\n";
			$body .= "<h3>Never Mind</h3>\n";
			$body .= "<p>Just <a href='$sc'>go back to the main page</a>.</p>";
			$body .= "</div></div> <!-- end of col and row -->\n";
			
			
		} else {
			$body .= "<p>You are not logged in! Go back to the <a href='$sc'>front page</a> and enter your email. We'll email you a link so you can log in.</p>\n";
		}
		$body .= "</div></div>\n";
		break;
	
	case 'winfo':
		$body .= "<div class='row'><div class='col'>\n";
		if (Users\logged_in()) {
			$e = Enrollments\get_an_enrollment($wk, $u);
			
			$enroll_link = "$sc?ac=enroll&wid={$wk['id']}";
			
			switch ($e['status_id']) {
				case ENROLLED:
					$point = "You are ENROLLED in the practice listed below. Would you like to <a class='btn btn-primary' href='$sc?ac=drop&wid={$wk['id']}&uid={$u['id']}&key={$key}&v=winfo'>drop</a> it?";
					break;
				case WAITING:
					$point = "You are spot number {$e['rank']} on the WAIT LIST for the practice listed below. Would you like to <a class='btn btn-primary' href='$sc?ac=drop&wid={$wk['id']}&uid={$u['id']}&key={$key}&v=winfo'>drop</a> it?";
					break;
				case INVITED:
					$point = "A spot opened up in the practice listed below. Would you like to <a class='btn btn-primary' href='$sc?ac=accept&wid={$wk['id']}&uid={$u['id']}&key={$key}&v=winfo'>accept</a> it, or <a class='btn btn-primary' href='$sc?ac=decline&wid={$wk['id']}&uid={$u['id']}&key={$key}&v=winfo'>decline</a> it?";
					break;
				case DROPPED:
					$point = "You have dropped out of the practice listed below. Would you like to <a class='btn btn-primary'  href='$enroll_link'>re-enroll</a>?";
					break;
				default:
				
					$point = "You are not currenty signed up for the practice listed below. ".
						($wk['type'] == 'soldout' 
						? "It is full. Want to <a class='btn btn-primary' href='$enroll_link'>join the wait list</a>?"
						: "Want to <a class='btn btn-primary' href='$enroll_link'>enroll</a>?");
				
					break;
			}
			if ($wk['type'] == 'past') {
				$point = "This workshop is IN THE PAST.";
			}
			$body .= "<p class='alert alert-info'>$point</p>\n";
			$body .= "<p>Click here to <a href='$sc'>return to the main page</a>.</p>\n";
			$body .= "<hr>";
			$body .= Workshops\get_workshop_info_tabled($wk);
		
		} else {
			$body .= Users\login_prompt();
		}
		$body .= "</div></div> <!-- end of col and row -->\n";
		break;
			
	default:
	
		$body .= "<div class='row'><div class='col-md-12'><div id='login_prompt' class='card'>
			<div class='card-body'>\n";
	
		
		
		
		if (Users\logged_in()) {
			$body .= "<h2 class='card-title'>Welcome";
			if ($u['display_name']) {
				$body .= ", {$u['display_name']}";
			}
			$body .="</h2>\n";
			$body .= "<p>You are logged in as ".
				($u['display_name'] 
				? "{$u['display_name']} ({$u['email']})"
				: "{$u['email']}").
			"! (You can <a href='$sc?v=edit'>change your name and email</a> or <a href='$sc?ac=lo'>log out</a>)</p>";		
						
			$body .= "<p>".($u['send_text'] ? "You have signed up for text notfications. " : "Would you like text notifications?")." <a  class='btn btn-primary' href='$sc?v=text'>Set your text preferences</a>.</p>\n";
			
			if (!$u['display_name']) {
				$body .= "<p>&nbsp;</p>\n";
				$body .= "<p>Would you mind entering a real human name? It's helpful to see who is signed up. Your email isn't shown, just this name.</p>\n";
				$body .= Users\edit_display_name($u);
			}
			

		} else {
			$body .= "<h2 class='card-title'>Log In To This Site</h2>\n";
			$body .= "<p>First you must log in. We do that via email.</p>";
			$body .= Users\login_prompt();
		}
		//include 'mailchimp.php';
		$body .= "</div></div></div></div>\n"; // end two card divs, then column, then row

		$body .= "<div class='row'><div class='col'>\n";
		$body .= "<h2>Paying</h2>\n"; 
		$body .= "<p>Pay in person or with <a href=\"http://venmo.com/willhines?txn=pay&share=friends&amount=25&note=improv%20workshop\">venmo</a> (click that link or pay to <a href=\"http://venmo.com/willhines?txn=pay&share=friends&amount=25&note=improv%20workshop\">whines@gmail.com</a>). On the day of the workshop is fine.</p>\n";
		$body .= "</div></div> <!-- end of col and row -->\n";

		$body .= "<div class='row'><div class='col'>\n";
		$body .= "<h2>No late drops!</h2>\n"; 
		$body .= "<p>Dropping out the night before or the morning of is not cool! You were holding a spot and then you didn't use it! Please don't do it! If you do, can you try to get a replacement? If not, I might ask you to pay which is gonna be weird for both of us.</p>\n";
		$body .= "</div></div> <!-- end of col and row -->\n";


		$body .= "<div class='row'><div class='col'>\n";
		$body .= "<h2>Mailing list</h2>\n"; 
		$body .= "<p>You are NOT automatically put on my mailing list for these workshops. If you WANT to be on that mailing list, <a href='http://eepurl.com/c6-T-H'>sign yourself up here</a>.</p>\n";
		$body .= "</div></div> <!-- end of col and row -->\n";


		$body .= "<div class='row'><div class='col'>\n";
		$body .= "<h2>Questions</h2>\n";
		$body .= "<p>You can be late. You can leave early. Pre-reqs are not enforced. For more, see <a href='$sc?v=faq'>common questions</a>.</p>\n";		
		$body .= "</div></div> <!-- end of col and row -->\n";	


		$body .= "<div class='row'><div class='col'>\n";
		$body .= "<h2>All Upcoming Workshops</h2>\n"; 
		$body .= Workshops\get_workshops_list(0);
		$body .= "</div></div> <!-- end of col and row -->\n";
		
		$body .= "<div class='row'><div class='col'>";
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
	
?>