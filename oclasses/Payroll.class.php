<?php

class Payroll extends WBHObject {
		
	public array $wk;
	public User $u;
	public Task $task;
	
	
	function __construct() {		
		parent::__construct(); // load logger, lookups
		
		$this->tablename = 'payrolls';

		$this->fields = array(
				'id' => null,
				'user_id' => null,
				'amount' => null,
				'when_happened' => null,
				'when_paid' => null,
				'task' => null,
				'table_id' => null);	
				
		$this->cols= $this->fields;
		$this->u = new User();
		$this->wk = array();
		$this->task = new Task();
			
	}

	function finish_setup() {
		$this->set_mysql_datetime_field('when_paid', $this->fields['when_paid']);
		if (!$this->fields['amount']) { $this->fields['amount'] = 0; }
		if (!$this->fields['table_id']) { $this->fields['table_id'] = 0; }
		
		
		if ($this->fields['user_id']) {
			$this->u->set_by_id($this->fields['user_id']);
			$this->fields['user_name'] = $this->u->fields['nice_name'];
		}
		
		
		if ($this->fields['task'] && $this->fields['table_id']) {
			
			
			if ($this->fields['task'] == 'workshop') {
				
				$this->wk = \Workshops\get_workshop_info($this->fields['table_id']);
				$this->wk['rank'] = 1;
				
			}

			if ($this->fields['task'] == 'class') {
				$stmt = \DB\pdo_query("select w.title, x.start, x.rank as rank, w.id as workshop_id
					from workshops w, xtra_sessions x
				where x.id = :id
				and w.id = x.workshop_id ",
				array(':id' => $this->fields['table_id']));
				while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
					$this->wk = \Workshops\fill_out_workshop_row($row);
				}
			}
			
			if ($this->fields['task'] == 'task') {
				$this->task->set_by_id($this->fields['table_id']);
			}

		}
	}	
	
	
}
	
?>