<?php
$view->data['heading'] = "payroll";

$u->reject_user_below(3); // group 3 or higher

$ph = new PayrollsHelper();

$vars = array('searchstart', 'searchend', 'lastweekstart', 'lastweekend', 'nextweekstart', 'nextweekend', 'pid', 'singleadd', 'task', 'table_id', 'user_id', 'amount', 'when_paid', 'when_happened');
Wbhkit\set_vars($vars);

switch ($ac) {
	
	case 'del':
		$p = new Payroll();
		$p->fields['id'] = $pid;
		$p->delete_row();
		break;
	case 'singleadd':
		$table_id = (int) $table_id;
		$user_id = (int) $user_id;
		$amount = (int) $amount;
		$ph->add_payroll($task, $table_id, $user_id, $amount, $when_paid, $when_happened);
		break;
	case 'add':


		$payroll_data = array();
		foreach ($_REQUEST as $k => $v) {
			if (substr($k, 0, 3) == 'pd_') {
				$ps = explode('_', $k);
				$payroll_data["{$ps[1]}-{$ps[2]}"]['task'] = $ps[1];
				$payroll_data["{$ps[1]}-{$ps[2]}"]['tableid'] = $ps[2];
				$payroll_data["{$ps[1]}-{$ps[2]}"][$ps[3]] = $v;
			}
		}
		//print_r($payroll_data);
		foreach ($payroll_data as $id => $item) {
			$ph->add_payroll($item['task'], $item['tableid'], $item['userid'], $item['amount'], $item['whenpaid'], $item['whenhappened']);
		}
		break;
		
						
}


// search defaults to last week
if (!$searchstart && !$searchend) {
	$searchend = 'last Friday';
	$searchstart = '-6 days '.date('Y-m-d', strtotime($searchend));
}

if ($searchstart) { $searchstart = date('Y-m-d', strtotime($searchstart)); }
if ($searchend) { $searchend = date('Y-m-d', strtotime($searchend)); }

$lastweekstart = change_date_string($searchstart, '-7 days');
$lastweekend = change_date_string($searchend, '-7 days');
$nextweekstart = change_date_string($searchstart, '+7 days');
$nextweekend = change_date_string($searchend, '+7 days');


$view->add_globals($vars);	

$view->data['payrolls'] = $ph->get_payrolls($searchstart, $searchend);
$view->data['claims'] = $ph->get_claims($searchstart, $searchend);
$view->data['searchstart'] = $searchstart;
$view->data['searchend'] = $searchend;

$view->renderPage('admin/payroll');


function change_date_string($timestring, $change) {
	$lastweek = date_create($timestring);
	date_modify($lastweek, $change);
	return date_format($lastweek, 'Y-m-d');
}


