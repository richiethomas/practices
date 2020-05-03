<?php
$heading = 'improv practices';
$sc = "workshop.php";
include 'lib-master.php';


switch ($ac) {

	case 'enroll':
		if ($wk['cancelled']) {
			$error = 'This workshop has been cancelled.';
			$logger->debug("{$u['nice_name']} cannot enroll since {$wk['title']} is cancelled.");
			
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
			$logger->debug("{$u['nice_name']} tried to drop from {$wk['title']} but it's cancelled.");
			
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
		
}


// maybe check the $wk or $wk['id'] here?

if (isset($wk) && isset($wk['id']) && $wk['id']) {
	$wk = Workshops\fill_out_workshop_row($wk);
	$view->data['e'] = Enrollments\get_an_enrollment($wk, $u);
	$view->data['workshop_tabled'] = Workshops\get_workshop_info_tabled($wk);
	$view->data['admin'] = 0;
	$view->renderPage('winfo');
} else {
	$view->data['error_message'] = "<h1>Whoops!</h1><p>You are asking to look at info about a workshop, but I (the computer) cannot tell which workshop you mean. Sorry!</p>\n";
	$view->renderPage('error');
}
