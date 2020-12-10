<?php
$heading = "payroll";
include 'lib-master.php';

$u->reject_user_below(3); // group 3 or higher

$vars = array('searchstart', 'searchend', 'lastweekstart', 'lastweekend', 'nextweekstart', 'nextweekend');
Wbhkit\set_vars($vars);

switch ($ac) {
	
	case 'up':
	
		foreach ($_REQUEST as $key => $value) {
			$exp = null;
			$rev = null;
			if (substr($key, 0, 9) == 'whenpaid_') {
				$ps = explode('_', $key);
				//echo "key: $key, value $value<br>\n";
				$query = "update ".($ps[2] ? 'xtra_sessions' : 'workshops')." 
					set when_teacher_paid = :when_paid where id = :id";
					$params = array(
						':when_paid' => $value ?  date("Y-m-d H:i:s", strtotime($value)) : NULL, 
						':id' => $ps[2] ? $ps[2] : $ps[1]);
						
						$db = \DB\get_connection();
						$stmt = $db->prepare($query);
						$id = $ps[2] ? $ps[2] : $ps[1];
						$stmt->bindParam(':id', $id);
						if ($value) {
							$datetoinsert = date("Y-m-d H:i:s", strtotime($value));
							$stmt->bindParam(':when_paid', $datetoinsert);
						} else {
							$stmt->bindValue(':when_paid', null, PDO::PARAM_INT);
						}
				//echo \DB\interpolateQuery($query, $params)."<br>\n";
				$stmt = \DB\pdo_query($query, $params);
			}
			if (substr($key, 0, 7) == 'actual_') {
				$ps = explode('_', $key);
				//echo "key: $key, value $value<br>\n";
				$query = "update ".($ps[2] ? 'xtra_sessions' : 'workshops')." 
					set actual_pay = :override where id = :id";
				$params = array(':override' => $value, 
					':id' => ($ps[2] ? $ps[2] : $ps[1]));
				$stmt = \DB\pdo_query($query, $params);
				//echo \DB\interpolateQuery($query, $params)."<br>\n";
			}
		}
		break;
		
						
}


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
$view->data['searchstart'] = $searchstart;
$view->data['searchend'] = $searchend;


$view->renderPage('admin/payroll');


function change_date_string($timestring, $change) {
	$lastweek = date_create($timestring);
	date_modify($lastweek, $change);
	return date_format($lastweek, 'Y-m-d');
}


