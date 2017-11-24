<?php
$sc = "admin_attendance.php";
$heading = "practices: admin";
include 'lib-master.php';
include 'libs/validate.php';

switch ($ac) {
	
	case 'at':
		if (isset($_REQUEST['users']) && is_array($_REQUEST['users']) && $wid) {
			$users = $_REQUEST['users'];
			foreach ($statuses as $sid => $sts) {
				$stds = Enrollments\get_students($wid, $sid);
				foreach ($stds as $as) {
					if (in_array($as['id'], $users)) {
						Enrollments\update_attendance($wid, $as['id'], 1);
					} else {
						Enrollments\update_attendance($wid, $as['id'], 0);
					}
				}
			}		
		}
		break;
						
}

$students = array();
if (!$wk['id']) {
	$view->renderPage('admin_error');	
} else {
	foreach ($statuses as $stid => $status_name) {
		$students[$stid] = Enrollments\get_students($wid, $stid);
	}
	$view->data['wk'] = $wk;
	$view->data['statuses'] = $statuses; // global
	$view->data['students'] = $students;
	$view->renderPage('admin_attendance');
}





