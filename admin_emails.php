<?php
$sc = "admin_emails.php";
$heading = "practices: admin";
include 'lib-master.php';


Wbhkit\set_vars(array('workshops'));
$results = null;
$all_workshops = Workshops\get_workshops_dropdown();


$unpaid = array();
$students = array();
if (is_array($workshops)) {
	$statuses[0] = 'all'; // modifying global $statuses
	foreach ($statuses as $stid => $status_name) { 
		foreach ($workshops as $workshop_id) {
			if ($workshop_id) {
				$stds = Enrollments\get_students($workshop_id, $stid);
				foreach ($stds as $as) {		
					
					// track students by status		
					$students[] = $as['email'];
					
					// also tally who paid
					if ($as['status_id'] == ENROLLED and $as['paid'] == 0) {
						$unpaid[] = $as['email'];
					}

				}
			}
		}
				
		$students = array_unique($students);
		natcasesort($students);
		$results[$stid] = $students; // attach list of students
		
		// also unpaid
		$unpaid = array_unique($unpaid);
		natcasesort($unpaid);
		
	}
}

$view->add_globals(array('all_workshops', 'workshops', 'statuses', 'results', 'unpaid'));
$view->renderPage('admin_gemail');


