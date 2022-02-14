<?php

class PayrollsHelper extends WBHObject {


	public array $payrolls;
	public array $claims;
	
	function __construct() {		
		parent::__construct(); // load logger, lookups
		$this->payrolls = array();
		$this->claims = array();
	}

	function get_payrolls($start, $end) {
		if (!$start) { $start = "Jan 1 1000"; }
		if (!$end) { $end = "Dec 31 3000"; }

		// get IDs of workshops
		$mysqlstart = date(MYSQL_FORMAT, strtotime($start));
		$mysqlend = date(MYSQL_FORMAT, strtotime($end));

		$stmt = \DB\pdo_query("select * from payrolls
			where (when_paid > :start and when_paid < :end) or
		(when_happened > :start2 and when_happened < :end2)
		order by when_paid, teacher_id, task, table_id", 
		array(':start' => $mysqlstart, ':end' => $mysqlend, ':start2' => $mysqlstart, ':end2' => $mysqlend));

		$this->payrolls = array();
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$p = new Payroll();
			$p->set_into_fields($row);
			$p->format_row();
			$this->payrolls[] = $p;
		}
		return $this->payrolls;
	}
	
	function get_claims(string $start = "Jan 1 1000", string $end = "Dec 31 3000") {
		
		//echo "select w.* from workshops w WHERE w.start >= '".date(MYSQL_FORMAT, strtotime($start))."' and w.end <= '".date(MYSQL_FORMAT, strtotime($end))."' order by start desc";
	
		// get IDs of workshops
		$mysqlstart = date(MYSQL_FORMAT, strtotime($start));
		$mysqlend = date(MYSQL_FORMAT, strtotime($end));
	
		// only need workshops table since we only need first sesssion
		$stmt = \DB\pdo_query("
	(select 'workshop' as task, w.id as table_id, w.title, w.start, w.teacher_id, 1 as rank, w.id as workshop_id, w.id, w.cost
	from workshops w
	where w.start >= :start1 and w.start <= :end1)
	order by teacher_id, task, start asc",
	array(':start1' => $mysqlstart,
	':end1' => $mysqlend)); 	
	
		$this->claims = array();
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$row = \Workshops\fill_out_xtra_sessions($row); 
			$row = \Workshops\set_enrollment_stats($row);
			$row = \Workshops\set_actual_revenue($row);
			$this->claims[] = $row;
		}
		return $this->claims;
	}
	
	// this should set this instance to have values in $this->fields but I didn't do it
	function add_claim(string $task, int $table_id, int $teacher_id, int $amount, string $when_paid, string $when_happened) {
		
		$params = array(
		':task' => $task,
		':table_id' => $table_id,
		':teacher_id' => $teacher_id,
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
				set teacher_id = :teacher_id, 
			amount = :amount, 
			when_paid = :when_paid, 
			when_happened = :when_happened 
			where task = :task and table_id = :table_id", $params);
		} else {
			$stmt = \DB\pdo_query("insert into payrolls 
				(task, table_id, teacher_id, amount, when_paid, when_happened)
				VALUES (:task, :table_id, :teacher_id, :amount, :when_paid, :when_happened)", $params);
			
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