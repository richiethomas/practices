<?php
$view->data['heading'] = "registrations";

$vars = array('searchstart', 'searchend', 'sortby');
Wbhkit\set_vars($vars);

// search defaults to last week
if (!$searchstart && !$searchend) {
	$searchend = 'last Friday';
	$searchstart = '-6 days '.date('Y-m-d', strtotime($searchend));
}

if ($searchstart) { $searchstart = date('Y-m-d 00:00:00', strtotime($searchstart)); }
if ($searchend) { $searchend = date('Y-m-d 23:59:59', strtotime($searchend)); }

switch ($ac) {

	case 'update':
		
		foreach ($_REQUEST as $k => $v) {
			if (substr($k, 0, 10) == 'payamount_') {
				$ps = explode('_', $k);
				if ($v != $_REQUEST["hiddenamount_{$ps[1]}"]) {
					$message .= "update reg {$ps[1]} amount to $v<br>\n";
					\DB\pdo_query("update registrations set pay_amount = :pa where id = :id",
					array(':pa' => $v, ':id' => $ps[1]));
				}
			}

			if (substr($k, 0, 8) == 'paywhen_') {
				$ps = explode('_', $k);
				if ($v && $v != '0000-00-00' && strtotime($v) != $_REQUEST["hiddenwhen_{$ps[1]}"]) {
					$message .= "update reg {$ps[1]} when to $v<br>\n";
					\DB\pdo_query("update registrations set pay_when = :pw where id = :id",
					array(':pw' => date(MYSQL_FORMAT, strtotime($v)), ':id' => $ps[1]));
				}
			}

			if (substr($k, 0, 11) == 'paychannel_') {
				$ps = explode('_', $k);
				if ($v != $_REQUEST["hiddenchannel_{$ps[1]}"]) {
					$message .= "update reg {$ps[1]} channel to $v<br>\n";
					\DB\pdo_query("update registrations set pay_channel = :pc where id = :id",
					array(':pc' => $v, ':id' => $ps[1]));
				}
			}
		}
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




