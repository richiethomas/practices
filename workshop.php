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
					$message = "'{$u->fields['nice_name']}' is now enrolled in '{$wk['title']}'!<ul><li>The zoom link, and other class info, was just emailed to <b>{$u->fields['email']}</b></li>";
					
					if ($wk['cost'] > 1) {
						$message .= "<li>Payment ({$wk['cost']} USD) is due by start of class. Either venmo @wgimprovschool (it's a business, not a person) or paypal https://paypal.me/WGImprovSchool.</li>";
					}
					if ($wk['cost'] == 1) {
						$message .= "<li>This is a PAY WHAT YOU CAN class. Full price is usually $40USD, but pay anything from zero to $40USD, whatever you like. If you are going to pay something, venmo @wgimprovschool (it's a business, not a person) or paypal https://paypal.me/WGImprovSchool</li>";
					}
					
					$message .= "<li>Any questions? Ask will@wgimprovschool.com. Thanks!</li></ul>\n";
					
				} elseif ($e->fields['status_id'] == WAITING) {
					$message = "This practice is full. '{$u->fields['nice_name']}' is now on the waiting list.";
				} 
			
				if (!$u->fields['send_text']) {
					$message .= " Want notifications by text? <a  class='btn btn-primary' href='$sc?v=text'>Set your text preferences</a>.";	
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
			$e->check_waiting($wk);
			$wk = Workshops\set_enrollment_stats($wk);
			$message = "Dropped user ({$u->fields['email']}) from practice '{$wk['title']}.'";
			break;	


		// accept an invite to a workshop
		case 'accept':
			if (!$u->logged_in()) {
				$error = 'You are not logged in! You have to be logged in to accept an invite.';
				$logger->debug($error);
		
				break;
			}
			if ($wk['cancelled']) {
				$error = 'Cannot accept invite. This workshop has been cancelled.';
				$logger->debug("Rejected invite for {$u->fields['nice_name']} since {$wk['title']} is cancelled.");
		
				break;
			}
			
			
			if ($e->fields['status_id'] == INVITED) {
				$e->change_status(ENROLLED, 1);
				$message = "You are now enrolled in '{$wk['title']}'! Info emailed to <b>{$u->fields['email']}</b>.";
				$wk = Workshops\set_enrollment_stats($wk);
		
			} else {
				$error = "You tried to accept an invitation to '{$wk['title']}', but I don't see that there is an open spot.";
				$logger->debug("Rejected invite for {$u->fields['nice_name']} since {$wk['title']} is full.");
			}
			break;

		case 'decline':
			if (!$u->logged_in()) {
				$error = 'You are not logged in! You have to be logged in to decline an invite.';
				$logger->debug($error);
				break;
			}
			if ($wk['upcoming'] == 0) {
				$error = 'This workshop has past or already started.';
				$logger->debug("Rejected decline for {$u->fields['nice_name']} since {$wk['title']} is past/started.");
				break;
			}

			if ($e->fields['status_id'] == INVITED) {
				$e->change_status(DROPPED, 1);
				$e->check_waiting($wk);
				$wk = Workshops\set_enrollment_stats($wk);
				$message = "You have dropped out of the waiting list for '{$wk['title']}'.";			
			} else {
				$error = "You tried to decline an invitation to '{$wk['title']}', but I don't see that there was an open spot.";
				$logger->debug("Rejected decline for {$u->fields['nice_name']} since {$wk['title']} is full.");
		
			}
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
