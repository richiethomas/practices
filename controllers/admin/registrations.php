<?php
$view->data['heading'] = "registrations";

$vars = array('searchstart', 'searchend', 'sortby', 'payamount', 'paywhen', 'paychannel', 'nextstart', 'nextend', 'laststart', 'lastend');
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

if ($searchstart) { $searchstart = date('Y-m-d', strtotime($searchstart)); }
if ($searchend) { $searchend = date('Y-m-d', strtotime($searchend)); }

switch ($ac) {

	case 'update':

		$rid =  (int) ($params[2] ?? 0);
		//echo "update: $rid, $payamount, $paywhen, $paychannel<br>";
		
		if (is_numeric($rid)) {
			$stmt = \DB\pdo_query("update registrations set pay_amount = :pa, pay_when = :pw, pay_channel = :pc where id = :id", array(
				':pa' => $payamount, 
				':pw' => date(MYSQL_FORMAT, strtotime($paywhen)),
				':pc' => $paychannel, 
				':id' => $rid));
				if ($stmt) {
					$message = "Update reg '$rid' to: $payamount, $paywhen, $paychannel";
					break;
				}
		}
		$error = "Failed to update reg '$rid' to: $payamount, $paywhen, $paychannel";
		
		break;
}


$view->add_globals($vars);	

$registrations = array();

if (!$sortby) { $sortby = 'reg'; }
$orderby = array(
'reg' => "r.id",
'student' => "u.email",
'class' => "w.id",
'when' => "r.pay_when desc"
);


$stmt = \DB\pdo_query("select r.*, w.title, w.start, w.cost, u.email, u.display_name, tu.email as teacher_email, tu.display_name as teacher_display_name, w.teacher_id
from registrations r, workshops w, users u, teachers t, users tu
where r.workshop_id = w.id
and r.user_id = u.id
and w.teacher_id = t.id
and t.user_id = tu.id
and (r.status_id = 1 or r.pay_amount > 0)
and r.registered >= :start and r.registered <= :end
order by {$orderby[$sortby]}",
array(':start' => date(MYSQL_FORMAT, strtotime($searchstart)), ':end' => date(MYSQL_FORMAT, strtotime($searchend))));


while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
	$row['nice_name'] = $row['display_name'] ?? $row['email'];
	$row['teacher_nice_name'] = $row['teacher_display_name'] ?? $row['teacher_email'];
	$registrations[] = $row;
}


$view->data['registrations'] = $registrations;
$view->renderPage('admin/registrations');

function change_date_string($timestring, $change) {
	$new = new DateTime($timestring);
	$new->modify($change);
	return strtotime($new->format('Y-m-d'));
}




