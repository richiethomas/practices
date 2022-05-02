<?php

class PayrollsHelper extends WBHObject {


	public array $payrolls; // past payrolls
	public array $claims; // proposed payrolls
	
	function __construct() {		
		parent::__construct(); // load logger, lookups
		$this->payrolls = array();
		$this->claims = array();
	}

	function get_payrolls($start, $end) {
		
		
		if (!$start) { $start = "Jan 1 1000"; }
		if (!$end) { $end = "Dec 31 3000"; }

		// get IDs of workshops Y-m-d H:i:s
		$mysqlstart = date("Y-m-d 00:00:00", strtotime($start));
		$mysqlend = date("Y-m-d 23:59:59", strtotime($end));

		$stmt = \DB\pdo_query("select p.* from payrolls p, users u
			where (
				(p.when_paid > :start and p.when_paid < :end) or (p.when_happened >= :start2 and p.when_happened <= :end2)
		)
		and p.user_id = u.id
		order by p.when_paid, u.display_name, u.email, p.task, p.table_id", 
		array(':start' => $mysqlstart, ':end' => $mysqlend, ':start2' => $mysqlstart, ':end2' => $mysqlend));

		$this->payrolls = array();
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$p = new Payroll();
			$p->set_into_fields($row);
			$p->finish_setup();
			$this->payrolls[] = $p;
		}
		return $this->payrolls;
	}
	
	// proposed payroll objects
	function get_claims(string $start = "Jan 1 1000", string $end = "Dec 31 3000") {

		$mysqlstart = date("Y-m-d 00:00:00", strtotime($start));
		$mysqlend = date("Y-m-d 23:59:59", strtotime($end));

		$this->claims = array();
	
		// workshops are potential claims
		$stmt = \DB\pdo_query("
	select 'workshop' as task, w.id as table_id, t.user_id, t.default_rate as amount, w.start as when_happened, null as when_paid
	from workshops w, teachers t, users u
	where w.start >= :start1 and w.start <= :end1
	and w.teacher_id = t.id
	and t.user_id = u.id
	order by u.display_name, u.email, task, start asc",
	array(':start1' => $mysqlstart,
	':end1' => $mysqlend)); 	
	
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$new_payroll = new Payroll();
			$new_payroll->set_into_fields($row);
			$new_payroll->finish_setup();
			$this->claims[] = $new_payroll;
		}

		// tasks are potential claims
		$stmt = \DB\pdo_query("
	select 'task' as task, t.id as table_id, t.user_id, t.payment_amount as amount, t.event_when as when_happened, null as when_paid
	from tasks t, users u
	where t.event_when >= :start1 and t.event_when <= :end1
	and t.user_id = u.id
	order by u.display_name, u.email, t.event_when asc",
	array(':start1' => $mysqlstart,
	':end1' => $mysqlend)); 	
	
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$new_payroll = new Payroll();
			$new_payroll->set_into_fields($row);
			$new_payroll->finish_setup();
			$this->claims[] = $new_payroll;
		}
		
		return $this->claims;
	}
	
	function add_payroll(string $task, int $table_id, int $user_id, int $amount, string $when_paid, string $when_happened) {
		
		// update this very instance with data that was passed in
		$this->set_into_fields(array(
			'task' => $task,
			'table_id' => $table_id,
			'user_id' => $user_id,
			'amount' => $amount,
			'when_paid' => $when_paid,
			'when_happened' => $when_happened
		));
		
		$params = array(
		':task' => $task,
		':table_id' => $table_id,
		':user_id' => $user_id,
		':amount' => $amount,
		':when_paid' => date(MYSQL_FORMAT, strtotime($when_paid)),
		':when_happened' => date(MYSQL_FORMAT, strtotime($when_happened)));
		
		$exists = false;
		$stmt = \DB\pdo_query("select * from payrolls where task = :task and table_id = :table_id", array(':task' => $task, ':table_id' => $table_id));
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$exists = true;
		}
		
		if ($exists) {
			$stmt = \DB\pdo_query("update payrolls 
				set user_id = :user_id, 
			amount = :amount, 
			when_paid = :when_paid, 
			when_happened = :when_happened 
			where task = :task and table_id = :table_id", $params);
		} else {
			$stmt = \DB\pdo_query("insert into payrolls 
				(task, table_id, user_id, amount, when_paid, when_happened)
				VALUES (:task, :table_id, :user_id, :amount, :when_paid, :when_happened)", $params);
			
		}
		
		
		return true;
	}	
	
	
	function get_recorded_teacher_pay(int $wid) {
		
		$sql = "select p.* 
			from payrolls p
			where p.task = 'workshop' and p.table_id = :id
			UNION
			select p.*
			from payrolls p, xtra_sessions x
			where p.task = 'class'
			and p.table_id = x.id
			and x.workshop_id = :id2";

		$stmt = \DB\pdo_query($sql, array(':id' => $wid, ':id2' => $wid));
		
		$pay = 0;
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$pay += $row['amount'];
		}
		
		return $pay;
		
	}
	
}
	
?>