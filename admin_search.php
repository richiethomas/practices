<?php
$sc = "admin_search.php";
$heading = "practices: admin";
include 'lib-master.php';
include 'libs/validate.php';

$vars = array('needle', 'sort');
Wbhkit\set_vars($vars);
if ($sort != 'n' && $sort != 't' && $sort != 'd') {
	$sort = 'n';
}

switch ($ac) {

	
	case 'zeroconfirm':

		$stds = Users\find_students('everyone');
		$message = '';
		foreach ($stds as $s) {
			if ($s['classes'] == 0) {
				$message .= "deleting {$s['email']} {$s['id']} - ({$s['classes']})<br>\n";
				Users\delete_student($s['id']);
			}
		}
		if (!$message) {
			$message = "No zero registation students to delete.";
			$logger->info($message);
		} else {
			$message = "Zero registration students removed.";
			$logger->info($message);
		}
		$needle = 'everyone';
		
	 case 'zero':
		if ($ac == 'zero') {
			$message = "Really remove students with zero workshops? <a class='btn btn-danger' href='$sc?ac=zeroconfirm'>yes remove</a> or <a class='btn btn-primary' href='$sc?ac=search&needle=everyone'>cancel</a>";			
		}
				
}

$all = ($needle ? Users\find_students($needle, $sort) : array());

$view->add_globals(array('needle', 'sort', 'all'));
$view->data['search_opts'] = array('n' => 'by name', 't' => 'by total classes', 'd' => 'by date registered');
$view->renderPage('admin_search');



