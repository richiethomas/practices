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
				Workshops\update_workshop_col($id, 'revenue', $value);
			}
			if (substr($key, 0, 9) == 'expenses_') {
				$id = substr($key, 9);
				Workshops\update_workshop_col($id, 'expenses', $value);
			}
		}
		break;
		
						
}

$vars = array('searchstart', 'searchend');
Wbhkit\set_vars($vars);
if ($searchstart) { $searchstart = date('Y-m-d H:i:s', strtotime($searchstart)); }
if ($searchend) { $searchend = date('Y-m-d H:i:s', strtotime($searchend)); }
$view->add_globals($vars);	

$view->data['workshops_list'] = Workshops\get_workshops_list_bydate($searchstart, $searchend);
$view->renderPage('admin_rev');

}




