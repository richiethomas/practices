<?php
$view->data['heading'] = "payments";

$u->reject_user_below(3); // group 3 or higher

$ph = new PaymentsHelper();

$vars = array('searchstart', 'searchend', 'lastweekstart', 'lastweekend', 'nextweekstart', 'nextweekend', 'pid',  'title', 'workshop_id', 'user_id', 'amount', 'when_paid', 'when_happened', 'wid', 'uid', 'amt', 'wh', 'wp', 'email');
Wbhkit\set_vars($vars);

switch ($ac) {
	
	case 'del':
		$p = new Payment();
		$p->fields['id'] = $pid;
		$p->delete_row();
		break;
	case 'singlecourse':	
		$workshop_id = (int) $wid;
		$user_id = (int) $uid;
		$amount = (int) $amt;
		
		$ph->add_payment($user_id, $amount, $wp, $wh, TEACHERPAY, $workshop_id);
		break;
	case 'allcourses':

		$pd = array(); // payment data
		foreach ($_REQUEST as $k => $v) {
			if (substr($k, 0, 2) == 'pd') {
				$ps = explode('_', $k);
				$pd[$ps[0]][$ps[1]] = $v;
			}
		}
		foreach ($pd as $id => $item) {

			$ph->add_payment($item['uid'], $item['amount'], $item['whenpaid'], $item['whenhappened'], TEACHERPAY, $item['wid']);
		}
		break;
		
	case 'addsingle':
		$u = new User();
		$u->set_by_email($email);
		if ($u->fields['id']) {
			if (!$wid) { $wid = 0; }
			$ph->add_payment($u->fields['id'], $amt, $wp, $wh, $title, $wid);
			$message = "added '$title' - ({$u->fields['id']}, $amt, $wp, $wh, $title, $wid)";
		} else {
			$error = "Cannot find user '$email'";
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

$wh = new WorkshopsHelper();

$view->data['recent_workshops'] = $wh->get_recent_workshops_dropdown(50);
$view->data['payments'] = $ph->get_payments($searchstart, $searchend);
$view->data['claims'] = $ph->get_claims($searchstart, $searchend);
$view->data['searchstart'] = $searchstart;
$view->data['searchend'] = $searchend;

$view->renderPage('admin/payments');


function change_date_string($timestring, $change) {
	$lastweek = date_create($timestring);
	date_modify($lastweek, $change);
	return date_format($lastweek, 'Y-m-d');
}


