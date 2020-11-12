<?php
$heading = "payroll";
include 'lib-master.php';

Users\reject_user_below(3); // group 3 or higher

$vars = array('searchstart', 'searchend', 'lastweekstart', 'lastweekend', 'nextweekstart', 'nextweekend');
Wbhkit\set_vars($vars);

// search defaults to current week
if (!$searchstart && !$searchend) {
	$searchstart = (date("l") == 'Sunday' ? 'today' : 'last Sunday');
	$searchend = (date("l") == 'Saturday' ? 'today' : 'next Saturday');
}

if ($searchstart) { $searchstart = date('Y-m-d 00:00:00', strtotime($searchstart)); }
if ($searchend) { $searchend = date('Y-m-d 23:59:59', strtotime($searchend)); }



$lastweekstart = change_date_string($searchstart, '-7 days');
$lastweekend = change_date_string($searchend, '-7 days');
$nextweekstart = change_date_string($searchstart, '+7 days');
$nextweekend = change_date_string($searchend, '+7 days');


$view->add_globals($vars);	

$view->data['workshops_list'] = Workshops\get_sessions_bydate($searchstart, $searchend);

$view->renderPage('admin/payroll');


function change_date_string($timestring, $change) {
	$lastweek = date_create($timestring);
	date_modify($lastweek, $change);
	return date_format($lastweek, 'Y-m-d');
}


