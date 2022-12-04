<?php
$view->data['heading'] = 'workshop';


$wid =  (int) ($params[2] ?? 0);
if (!$wid) {
	$view->data['error_message'] = "<h1>Whoops!</h1><p>You are asking to look at info about a workshop, but I (the computer) cannot tell which workshop you mean. Sorry!</p>\n";
	$view->renderPage('error');
	exit();
}
$wk->set_by_id($wid);

$show_other_action = true;
$e = new Enrollment();
if ($u->logged_in() && isset($wk->fields['id']) && $wk->fields['id'] > 0) {
	$e->set_by_u_wk($u, $wk);
}


if ($wk->is_public()) {

	switch ($action) {

		case 'enroll':
			if (!$u->logged_in()) {
				$error = "You must be logged in to enroll.";
				break;
			}
			if (strtotime($wk->fields['start']) < strtotime("now")) {
				$error = "That workshop already started.";
				break;
			}	
			
			if ($e->change_status(SMARTENROLL)) {
				// finicky confirmation message
				if ($e->fields['status_id'] == ENROLLED) {
					$message = "'{$u->fields['nice_name']}' is now enrolled in '{$wk->fields['title']}'!<ul><li>The zoom link, and other class info, was just emailed to <b>{$u->fields['email']}</b></li>\n";
					
					$message .= "<li>Please be ON TIME for class! Classes are short - being even a few minutes late really disrupts things!</li>\n";
					
					$message .= "<li>".\Emails\payment_text($wk)."</li>\n";
										
					$message .= "</ul>\n";
				} elseif ($e->fields['status_id'] == APPLIED) {
					$message = "'{$u->fields['nice_name']}' has applied for '{$wk->fields['title']}'! You'll be notified soon if you got in or not.\n";

				} elseif ($e->fields['status_id'] == WAITING) {
					$message = "This practice is full. '{$u->fields['nice_name']}' is now on the waiting list.";
				} 
				$logger->debug("'{$u->fields['nice_name']}' is status '".$lookups->find_status_by_value($e->fields['status_id'])."' for'{$wk->fields['title']}'");
			
			} else {
				$error = $e->error;
				$logger->info($error);
			}
			$wk->set_enrollment_stats();
			break;
		
		// request a drop (still must be confirmed)
		case 'drop':
				
			if (!$u->logged_in()) {
				$error = 'You are not logged in! You have to be logged in to drop a workshop.';
				break;
			}

			if (isset($wk->fields['title'])) {
				$message = "Do you really want to drop '".$wk->fields['title']."'? Then click <a class='btn btn-warning' href='/workshop/condrop/{$wid}'>confirm drop</a>";
			}
			
			$show_other_action = false;
		
			$hours_left = (strtotime($wk->fields['start']) - strtotime('now')) / 3600;
			if ($hours_left > 0 && $hours_left < LATE_HOURS) {
				$message .= '<br><br>'.Emails\get_dropping_late_warning();
			}
			break;
		
		// confirm drop
		case 'condrop':
			if (!$u->logged_in()) {
				$error = 'You are not logged in! You have to be logged in to drop a workshop.';
				break;
			}
				
			$message = $e->change_status(DROPPED, 1);
			$wk->set_enrollment_stats();
			if (!$wk->fields['application']) {
				$e->notify_waiting($wk);
			}
			$message = "Dropped user ({$u->fields['email']}) from '{$wk->fields['title']}.'";
			$logger->debug($message);
			break;	
			
			
			case 'view':
				break;

	}
}

if (isset($wk->fields['id']) && $wk->fields['id']) {
	
	if ($wk->fields['hidden'] && !$u->check_user_level(2)) {
		$view->data['error_message'] = "<h1>Hidden Class</h1><p>This class exists but is currently HIDDEN. Probably means the schedule is still being confirmed with the teacher.</p>\n";
		$view->renderPage('error');
		
	} else {
		
		$view->data['e'] = $e;
		$view->data['show_other_action'] = $show_other_action;
		$view->data['admin'] = 0;
	
		$view->data['heading'] = $view->data['fb_title'] = $wk->fields['title'];
		$view->data['fb_image'] = "http://{$_SERVER['HTTP_HOST']}".Teachers\get_teacher_photo_src($wk->teacher['user_id']);
		$view->data['fb_description'] = $wk->fields['notes'];
		$view->renderPage('workshop');		
	}

} else {
	$view->data['error_message'] = "<h1>Whoops!</h1><p>You are asking to look at info about a workshop, but I (the computer) cannot tell which workshop you mean. Sorry!</p>\n";
	$view->renderPage('error');
}

