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
$upayments = array();
$params = array(':start' => $searchstart, ':end' => $searchend);

//pay for workshops
$stmt = \DB\pdo_query("
	select p.id, p.when_paid, p.amount, w.title, w.start, w.id as workshop_id, 'cost' as money_type
	from payments p, workshops w
	where 
	p.workshop_id = w.id
	and p.when_paid >= :start
	and p.when_paid <= :end", 
		$params);

while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
	process_class_money($classes, $row);
}


// revenue for classes, dated
$stmt = \DB\pdo_query("
	select r.pay_when, r.pay_amount as amount, w.title, w.start, w.id as workshop_id, 'dated_revenue' as money_type
	from registrations r, workshops w
	where r.pay_amount > 0 
	and r.workshop_id = w.id
	and r.pay_when >= :start 
	and r.pay_when <= :end", $params);

while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
	process_class_money($classes, $row);
}	

//revenue for classes, undated
$stmt = \DB\pdo_query("select r.pay_when, r.pay_amount as amount, w.title, w.start, w.id as workshop_id, 'undated_revenue' as money_type
from registrations r, workshops w
where r.pay_amount > 0 
and r.workshop_id = w.id
and (r.pay_when is null or r.pay_when = 0)
and w.start >= :start 
and w.start <= :end", 
	$params);

while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
	process_class_money($classes, $row);
}

// pay given to payments without classes associated
$stmt = \DB\pdo_query("
	select p.id, p.title, p.when_paid, p.amount as cost
	from payments p
	where 
	(p.workshop_id = 0 or p.workshop_id IS NULL)
	and p.when_paid >= :start
	and p.when_paid <= :end
	order by p.when_paid", 
		$params);

while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
	$upayments[] = $row;
}


ksort($classes);

$view->add_globals($vars);
$view->data['classes'] = $classes;
$view->data['upayments'] = $upayments;	
$view->renderPage('admin/revenue_bydate');


function change_date_string(string $timestring, string $change) {
	$new = new DateTime($timestring);
	$new->modify($change);
	return strtotime($new->format('Y-m-d'));
}


function process_class_money(array &$classes, array $row) {
	
	$key = "{$row['start']}-{$row['workshop_id']}";
	if (!isset($classes[$key])) {
		$classes[$key]['start'] = $row['start'];
		$classes[$key]['id'] = $row['workshop_id'];
		$classes[$key]['title'] = $row['title'];
	}
	if (!isset($classes[$key][$row['money_type']])) { // dated or undated?
		$classes[$key][$row['money_type']] = 0;
	}
	$classes[$key][$row['money_type']] += $row['amount'];

	return true;
}
	





