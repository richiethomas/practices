<?php
$view->data['heading'] = "revenue by date";

$u->reject_user_below(3); // group 3 or higher


$vars = array('searchstart', 'searchend', 'nextstart', 'nextend', 'laststart', 'lastend');
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

$classes = array();
$tasks = array();
$params = array(':start' => $searchstart, ':end' => $searchend, ':start2' => $searchstart, ':end2' => $searchend);

$stmt = \DB\pdo_query("
	select p.id, p.task, p.table_id, p.when_paid, p.amount, w.title, w.start, w.id as workshop_id
	from payrolls p, workshops w
	where 
	p.task = 'workshop' and p.table_id = w.id
	and p.when_paid >= :start
	and p.when_paid <= :end
	UNION
	select p.id, p.task, p.table_id, p.when_paid, p.amount, w.title, w.start, w.id as workshop_id
	from payrolls p, workshops w, xtra_sessions x
	where 
	p.task = 'class' 
	and p.table_id = x.id
	and x.workshop_id = w.id
	and p.when_paid >= :start2
	and p.when_paid <= :end2", 
		$params);

while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
	$key = "{$row['start']}-{$row['workshop_id']}";
	$classes = add_money($classes, $key, 'teacher_pay', $row['amount']);
	$classes[$key]['title'] = $row['title'];
	$classes[$key]['start'] = $row['start'];
	$classes[$key]['workshop_id'] = $row['workshop_id'];
}

$stmt = \DB\pdo_query("
	select r.pay_when, r.pay_amount, w.title, w.start, w.id as workshop_id, 'dated' as revenue_type
	from registrations r, workshops w
	where r.pay_amount > 0 
	and r.workshop_id = w.id
	and r.pay_when >= :start 
	and r.pay_when <= :end
	UNION
	select r.pay_when, r.pay_amount, w.title, w.start, w.id as workshop_id, 'undated' as revenue_type
	from registrations r, workshops w
	where r.pay_amount > 0 
	and r.workshop_id = w.id
	and (r.pay_when = '0000-00-00' or r.pay_when is null)
	and w.start >= :start2 
	and w.start <= :end2", 
		$params);

while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
	$key = "{$row['start']}-{$row['workshop_id']}";
	$classes = add_money($classes, $key, $row['revenue_type'], $row['pay_amount']);
	$classes[$key]['title'] = $row['title'];
	$classes[$key]['start'] = $row['start'];
	$classes[$key]['workshop_id'] = $row['workshop_id'];
	
}

unset($params[':start2']);
unset($params[':end2']);

$stmt = \DB\pdo_query("
	select p.id, p.task, p.table_id, p.when_paid, p.amount, t.title, t.event_when as start
	from payrolls p, tasks t
	where 
	p.task = 'task' 
	and p.table_id = t.id
	and p.when_paid >= :start
	and p.when_paid <= :end", 
		$params);

while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
	$key = "{$row['when_paid']}-{$row['id']}";
	$tasks = add_money($tasks, $key, 'cost', $row['amount']);
	$tasks[$key]['title'] = $row['title'];
	$tasks[$key]['when_paid'] = $row['when_paid'];
	$tasks[$key]['task_id'] = $row['id'];
}


ksort($classes);
ksort($tasks);

$view->add_globals($vars);
$view->data['classes'] = $classes;
$view->data['tasks'] = $tasks;	
$view->renderPage('admin/revenue_bydate');


function change_date_string(string $timestring, string $change) {
	$new = new DateTime($timestring);
	$new->modify($change);
	return strtotime($new->format('Y-m-d'));
}

function add_money(array $ledger, string $key, string $type_of_revenue, int $amount) {
	
	if (!isset($ledger[$key][$type_of_revenue])) {
		$ledger[$key][$type_of_revenue] = 0;
	}
	$ledger[$key][$type_of_revenue] += $amount;
	return $ledger;
}


