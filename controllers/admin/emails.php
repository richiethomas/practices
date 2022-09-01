<?php
$view->data['heading'] = "get emails";

Wbhkit\set_vars(array('workshops'));
$results = null;

$wh = new WorkshopsHelper();
$all_workshops = $wh->get_workshops_dropdown();

$eh = new EnrollmentsHelper();

$unpaid = array();
$student_emails = array();
$student_names = array();
$statuses = $lookups->statuses;

if (is_array($workshops)) {
	$statuses[0] = 'all'; // modifying global $statuses
	foreach ($statuses as $stid => $status_name) { 
		$student_emails = array();
		$student_names = array();
		foreach ($workshops as $workshop_id) {
			if ($workshop_id) {
				$stds = $eh->get_students($workshop_id, $stid);
				foreach ($stds as $as) {		
					
					// track students by status		
					$student_emails[] = $as['email'];
					$student_names[] = "{$as['nice_name']}, {$as['email']}";
					
				}
			}
		}
				
		$student_emails = array_unique($student_emails);
		natcasesort($student_emails);
		$results[$stid]['emails'] = $student_emails; // attach list of students

		$student_names = array_unique($student_names);
		natcasesort($student_names);
		$results[$stid]['nice_names'] = $student_names; // attach list of students
				
	}
}

$view->add_globals(array('all_workshops', 'workshops', 'statuses', 'results'));
$view->renderPage('admin/gemail');


