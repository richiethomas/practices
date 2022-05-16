<?php
$view->data['heading'] = "revenue by class";

$u->reject_user_below(3); // group 3 or higher


$vars = array('searchstart', 'searchend', 'nextstart', 'nextend', 'laststart', 'lastend', 'mode');
Wbhkit\set_vars($vars);

// search defaults to last week
if (!$searchstart && !$searchend) {
	$searchstart = date('Y-m-1');
	$searchend = date('Y-m-t');
}

$day_one = date('Y-m-1', strtotime($searchstart));
$day_end = date('Y-m-t', strtotime($searchstart));
$laststart = date('Y-m-1', change_date_string($day_one, "-1 day"));
$lastend = date('Y-m-t', change_date_string($day_one, "-1 day"));
$nextstart = date('Y-m-1', change_date_string($day_end, "+1 day"));
$nextend = date('Y-m-t', change_date_string($day_end, "+1 day"));


$view->add_globals($vars);	
$view->data['workshops_list'] = Workshops\get_workshops_list_bydate($searchstart, $searchend, $mode);

$view->renderPage('admin/revenue_byclass');


function change_date_string(string $timestring, string $change) {
	$new = new DateTime($timestring);
	$new->modify($change);
	return strtotime($new->format('Y-m-d'));
}

