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
	
		$stmt = \DB\pdo_query("
	(select 'workshop' as task, w.id as table_id, w.title, w.start, w.teacher_id, 1 as rank, w.id as workshop_id
	from workshops w
	where w.start >= :start1 and w.start <= :end1)
	union
	(select 'class' as task, x.id as table_id, w.title, x.start, w.teacher_id, x.rank as rank, w.id as workshop_id
	from xtra_sessions x, workshops w
	where w.id = x.workshop_id and x.start >= :start2 and x.start <= :end2)
	order by teacher_id, task, start asc",
	array(':start1' => $mysqlstart,
	':end1' => $mysqlend,
	':start2' => $mysqlstart,
	':end2' => $mysqlend)); 	
	
	//	$stmt = \DB\pdo_query("select w.* from workshops w WHERE w.start >= :start and w.end <= :end order by teacher_id, start desc", array(':start' => date(MYSQL_FORMAT, strtotime($start)), ':end' => date(MYSQL_FORMAT, strtotime($end))));
	
		$this->claims = array();
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
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
}
	
?>