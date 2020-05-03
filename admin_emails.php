<?php
$sc = "admin_emails.php";
$heading = "practices: admin";
include 'lib-master.php';
include 'libs/validate.php';


Wbhkit\set_vars(array('workshops'));
$results = null;
$all_workshops = Workshops\get_workshops_dropdown();


$unattended = array();
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
					
					// also tally who attended
					if ($as['status_id'] == ENROLLED and $as['attended'] == 0) {
						$unattended[] = $as['email'];
					}

				}
			}
		}
				
		$students = array_unique($students);
		natcasesort($students);
		$results[$stid] = $students; // attach list of students
		
		// also attended
		$unattended = array_unique($unattended);
		natcasesort($unattended);
		
	}
}

$view->add_globals(array('all_workshops', 'workshops', 'statuses', 'results', 'unattended'));
$view->renderPage('admin_gemail');


