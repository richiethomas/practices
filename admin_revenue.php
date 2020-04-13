<?php
$sc = "admin_revenue.php";
$heading = "practices: admin";
include 'lib-master.php';
include 'libs/validate.php';

$v = null;
switch ($ac) {
	
	case 'rev':

		foreach ($_REQUEST as $key => $value) {
			$exp = null;
			$rev = null;
			if (substr($key, 0, 8) == 'revenue_') {
				$id = substr($key, 8);
				$stmt = \DB\pdo_query("update workshops set revenue = :revenue where id = :wid", array(':revenue' => $value, ':wid' => $id));
			}
			if (substr($key, 0, 9) == 'expenses_') {
				$id = substr($key, 9);
				$stmt = \DB\pdo_query("update workshops set expenses = :expenses where id = :wid", array(':expenses' => $value, ':wid' => $id));
			}
		}
		break;
		
						
}

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

$view->data['workshops_list'] = Workshops\get_workshops_list_bydate($searchstart, $searchend);

$view->renderPage('admin_revenue');


function change_date_string($timestring, $change) {
	$lastweek = date_create($timestring);
	date_modify($lastweek, $change);
	return date_format($lastweek, 'Y-m-d');
}


