<?php
$sc = "admin.php";
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

$vars = array('searchstart', 'searchend');
Wbhkit\set_vars($vars);
if ($searchstart) { $searchstart = date('Y-m-d H:i:s', strtotime($searchstart)); }
if ($searchend) { $searchend = date('Y-m-d H:i:s', strtotime($searchend)); }
$view->add_globals($vars);	

$view->data['workshops_list'] = Workshops\get_workshops_list_bydate($searchstart, $searchend, true);

// count attended - doing this here, and not in "get_workshops_list_bydate" since this is the only place I need this info
foreach ($view->data['workshops_list'] as $workshop) {
	$total_attended= Workshops\how_many_attended($workshop);
	$view->data['workshops_list'][$workshop['id']]['attended'] = $total_attended;
}

$view->renderPage('admin_revenue');





