<?php
$heading = "get emails";
include 'lib-master.php';


Wbhkit\set_vars(array('workshops'));
$results = null;
$all_workshops = Workshops\get_workshops_dropdown();

$eh = new EnrollmentsHelper();

$unpaid = array();
$students = array();
$statuses = $lookups->statuses;

if (is_array($workshops)) {
	$statuses[0] = 'all'; // modifying global $statuses
	foreach ($statuses as $stid => $status_name) { 
		foreach ($workshops as $workshop_id) {
			if ($workshop_id) {
				$stds = $eh->get_students($workshop_id, $stid);
				foreach ($stds as $as) {		
					
					// track students by status		
					$student_emails[] = $as['email'];
					$student_names[] = $as['nice_name'];
					
					// also tally who paid
					if ($as['status_id'] == ENROLLED and $as['paid'] == 0) {
						$unpaid[] = $as['email'];
					}

				}
			}
		}
				
		$student_emails = array_unique($student_emails);
		natcasesort($student_emails);
		$results[$stid]['emails'] = $student_emails; // attach list of students

		$student_names = array_unique($student_names);
		natcasesort($student_names);
		$results[$stid]['nice_names'] = $student_names; // attach list of students
		
		// also unpaid
		$unpaid = array_unique($unpaid);
		natcasesort($unpaid);
		
	}
}

$view->add_globals(array('all_workshops', 'workshops', 'statuses', 'results', 'unpaid'));
$view->renderPage('admin/gemail');


