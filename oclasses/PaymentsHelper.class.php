<?php

class PaymentsHelper extends WBHObject {


	public array $payments; // past payrolls
	public array $claims; // proposed payrolls
	
	function __construct() {		
		parent::__construct(); // load logger, lookups
		$this->payments = array();
		$this->claims = array();
	}

	function get_payments($start, $end) {
		
		if (!$start) { $start = "Jan 1 1000"; }
		if (!$end) { $end = "Dec 31 3000"; }

		// get IDs of workshops Y-m-d
		$mysqlstart = date("Y-m-d", strtotime($start));
		$mysqlend = date("Y-m-d", strtotime($end));

		$stmt = \DB\pdo_query("select p.* from payments p, users u
			where (
				(p.when_paid >= :start and p.when_paid <= :end) or (p.when_happened >= :start2 and p.when_happened <= :end2)
		)
		and p.user_id = u.id
		order by p.when_paid, u.display_name, u.email", 
		array(':start' => $mysqlstart, ':end' => $mysqlend, ':start2' => $mysqlstart, ':end2' => $mysqlend));

		$this->payments = array();
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$p = new Payment();
			$p->set_into_fields($row);
			$p->finish_setup();
			$this->payments[] = $p;
		}
		return $this->payments;
	}
	
	// proposed payroll objects
	function get_claims(string $start = "Jan 1 1000", string $end = "Dec 31 3000") {

		$mysqlstart = date("Y-m-d", strtotime($start));
		$mysqlend = date("Y-m-d", strtotime($end));

		$this->claims = array();
	
		// workshops are potential claims
		$stmt = \DB\pdo_query("
	select '".TEACHERPAY."' as title, w.id as workshop_id, t.user_id, t.default_rate as amount, w.start as when_happened, null as when_paid
	from workshops w, teachers t, users u
	where date(w.start) >= :start1 and date(w.start) <= :end1
	and (w.teacher_id = t.id or w.co_teacher_id = t.id)
	and t.user_id = u.id
	order by u.display_name, u.email, start asc",
	array(':start1' => $mysqlstart,
	':end1' => $mysqlend)); 	
	
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$np = new Payment();
			$np->set_into_fields($row);
			$np->finish_setup();
			$this->claims[] = $np;
		}
		return $this->claims;
	}
	
	function add_payment(int $user_id, string $amount, string $when_paid, string $when_happened, string $title, int $workshop_id = 0 ) {
		
		$p = new Payment();
		
		if (!$amount) { 
			$this->error = 'Amount must be greater than 0 for payments.';
			return false;
		}
		
		$amount = (int) $amount;
		
		// update this very instance with data that was passed in
		$p->set_into_fields(array(
			'title' => $title,
			'workshop_id' => $workshop_id,
			'user_id' => $user_id,
			'amount' => $amount,
			'when_paid' => $when_paid,
			'when_happened' => $when_happened
		));

		return $p->save_data();
	}	

	// no associated workshop or user data
	function get_class_costs_simple(int $wid) {
		
		$stmt = \DB\pdo_query("
			select p.*, u.display_name, u.email
			from payments p, users u
			where p.workshop_id = :wid
			and p.user_id = u.id
			order by title",
			array(':wid' => $wid)); 	
	
		$costs = array();
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$row['nice_name'] = $row['display_name'] ? $row['display_name'] : $row['email'];
			$costs[] = $row;
		}
		return $costs;		
	}

	function get_class_costs_total(int $wid, ?string $title = null) {
		
		$costs = $this->get_class_costs_simple($wid);
		
		$total = 0;
		foreach ($costs as $c) {
			if (!$title || $c['title'] == $title) {
				$total += $c['amount'];
			}
		}
		return $total;
	}

	function get_most_recent_paydate() {
		$stmt = \DB\pdo_query("select when_paid from payments order by when_paid desc limit 1"); 	
	
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			return $row['when_paid'];
		}
		return false;
	}

	
}
	
?>