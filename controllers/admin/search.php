<?php
$view->data['heading'] = "search";

$userhelper = new UserHelper("/admin-search");

$vars = array('sort', 'needle');
Wbhkit\set_vars($vars);
if ($sort != 'n' && $sort != 't' && $sort != 'd') {
	$sort = 'n';
}

$needle = (string) ($params[2] ?? $needle); // URL $needle takes precedence over $_POST $needle
$needle = trim($needle);



switch ($action) {
	
	case 'zeroconfirm':

		$stds = $userhelper->find_students('everyone');		
		$message = '';
		$total_deleted = 0;
		foreach ($stds as $s) {
			
			if ($s['classes'] == 0) {
				if (\Teachers\is_teacher($s['id'])) {
					//$message .= "<b>{$s['email']} - TEACHER</b><br>";
				} else {
					$message .= "{$s['email']}<br>\n";
					$userhelper->delete_user($s['id']);
					$total_deleted++;
				}
			}
		}
		if (!$message) {
			$message = "No zero registation students to delete.";
		} else {
			$message .= "'{$total_deleted}' zero registration students removed.";
		}
		$needle = 'everyone';
		break;
		
	 case 'zero':
		if ($action == 'zero') {
			$message = "Really remove students with zero workshops? <a class='btn btn-danger' href='/admin-search/zeroconfirm'>yes remove</a> or <a class='btn btn-primary' href='/admin-serach/search/everyone'>cancel</a>";			
		}
		
		break;
		
	case 'adduser':
		$guest = new User();
		if ($guest->validate_email($needle)) {
			if ($guest->set_by_email($needle)) {
				$message = "Added '$needle' as user. <a href='/admin-users/view/{$guest->fields['id']}'>Go to that user page</a>.";
			} else {
				$view->data['error_message'] = "<h1>Whoops!</h1><p>Tried to make a user out of '$needle' but got this error: '{$guest->error}'</p>\n";
				$view->renderPage('admin/error');
				exit();
			};
		} else {
			$view->data['error_message'] = "<h1>Whoops!</h1><p>Tried to make a user out of '$needle' but that is not an email address.</p>\n";
			$view->renderPage('admin/error');
			exit();
		}
	
	
		break;
	
				
}

$all = ($needle ? $userhelper->find_students($needle, $sort) : array());
$view->add_globals(array('needle', 'sort', 'all'));
$view->renderPage('admin/search');



