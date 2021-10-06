<?php
$heading = 'workshop';
include 'lib-master.php';

$show_other_action = true;
$e = new Enrollment();
if ($u->logged_in() && isset($wk['id']) && $wk['id'] > 0) {
	$e->set_by_u_wk($u, $wk);
}

if (Workshops\is_public($wk)) {

	switch ($ac) {

		case 'enroll':
			if (!$u->logged_in()) {
				$error = "You must be logged in to enroll.";
				$logger->debug("attempted enroll with no one logged in.");
				break;
			}
			if ($wk['upcoming'] == 0) {
				$error = 'This workshop is past';
				$logger->debug("{$u->fields['nice_name']} cannot enroll since {$wk['title']} is past.");
				break;
			}	
			
			if ($e->change_status(SMARTENROLL)) {
				// finicky confirmation message
				if ($e->fields['status_id'] == ENROLLED) {
					$message = "'{$u->fields['nice_name']}' is now enrolled in '{$wk['title']}'!<ul><li>The zoom link, and other class info, was just emailed to <b>{$u->fields['email']}</b></li>\n";
					
					$message .= "<li>Please be ON TIME for class! Classes are short - being even a few minutes late really disupts things!</li>\n";
					
					$message .= "<li>".\Emails\payment_text($wk)."</li>\n";
										
					$message .= "</ul>\n";
					
				} elseif ($e->fields['status_id'] == WAITING) {
					$message = "This practice is full. '{$u->fields['nice_name']}' is now on the waiting list.";
				} 
			
			} else {
				$error = $e->error;
			}
			$wk = Workshops\set_enrollment_stats($wk);
			break;
		
		// request a drop (still must be confirmed)
		case 'drop':
				
			if (!$u->logged_in()) {
				$error = 'You are not logged in! You have to be logged in to drop a workshop.';
				$logger->debug("attempted drop with no one logged in.");
			
				break;
			}
			if ($wk['cancelled']) {
				$error = 'This workshop has been cancelled.';
				$logger->debug("{$u->fields['nice_name']} tried to drop from {$wk['title']} but it's cancelled.");
			
				break;
			}
									
			$message = "Do you really want to drop '{$wk['title']}'? Then click <a class='btn btn-warning' href=\"$sc?ac=condrop&wid={$wid}\">confirm drop</a>";
			$show_other_action = false;
		
			if ($e->fields['while_soldout']) { 
				$message .= '<br><br>'.Emails\get_dropping_late_warning();
			}
			break;
		
		// confirm drop
		case 'condrop':
			if (!$u->logged_in()) {
				$error = 'You are not logged in! You have to be logged in to drop a workshop.';
				break;
			}
			if ($wk['cancelled']) {
				$error = 'This workshop has been cancelled.';
				break;
			}
				
			$message = $e->change_status(DROPPED, 1);
			$wk = Workshops\set_enrollment_stats($wk);
			$e->notify_waiting($wk);
			$message = "Dropped user ({$u->fields['email']}) from '{$wk['title']}.'";
			break;	

	}
}

if (isset($wk) && isset($wk['id']) && $wk['id']) {
		
	$view->data['e'] = $e;
	$view->data['show_other_action'] = $show_other_action;
	$view->data['admin'] = 0;
	
	$heading = $view->data['fb_title'] = $wk['title'];
	$view->data['fb_image'] = "http://{$_SERVER['HTTP_HOST']}".Teachers\get_teacher_photo_src($wk['teacher_info']['user_id']);
	$view->data['fb_description'] = $wk['notes'];
	$view->renderPage('winfo');
} else {
	$view->data['error_message'] = "<h1>Whoops!</h1><p>You are asking to look at info about a workshop, but I (the computer) cannot tell which workshop you mean. Sorry!</p>\n";
	$view->renderPage('error');
}
