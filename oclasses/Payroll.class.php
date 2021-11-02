<?php

class Payroll extends WBHObject {
	
	function __construct() {		
		parent::__construct(); // load logger, lookups
		
		$this->tablename = 'payrolls';

		$this->fields = array(
				'id' => null,
				'teacher_id' => null,
				'amount' => null,
				'when_happened' => null,
				'when_paid' => null,
				'task' => null,
				'table_id' => null);	
				
		$this->cols= $this->fields;
		
		$this->fields['title'] = null;		
	}

	function format_row() {
		$this->set_mysql_datetime_field('when_paid', $this->fields['when_paid']);
		if (!$this->fields['amount']) { $this->fields['amount'] = 0; }
		if (!$this->fields['table_id']) { $this->fields['table_id'] = 0; }
		
		
		if ($this->fields['teacher_id']) {
			$t = \Teachers\get_teacher_by_id($this->fields['teacher_id']);
			$this->fields['teacher_name'] = $t['nice_name'];
		}
		
		if ($this->fields['task'] && $this->fields['table_id']) {
			if ($this->fields['task'] == 'workshop') {
				$stmt = \DB\pdo_query("select w.title, w.start, 1 as rank, w.id as workshop_id
					from workshops w
				where w.id = :id",
				 array(':id' => $this->fields['table_id']));
				while ($row = $stmt->fetch()) {
					$this->set_into_fields($row);
				}
			}

			if ($this->fields['task'] == 'class') {
				$stmt = \DB\pdo_query("select w.title, x.start, x.rank as rank, w.id as workshop_id
					from workshops w, xtra_sessions x
				where x.id = :id
				and w.id = x.workshop_id ",
				array(':id' => $this->fields['table_id']));
				while ($row = $stmt->fetch()) {
					$this->set_into_fields($row);
				}
			}

			if ($this->fields['task'] == 'show') {
				$stmt = \DB\pdo_query("select w.title, s.start , 0 as rank, w.id as workshop_id
					from workshops w, workshops_shows ws, shows s 
				where s.id = :id
				and ws.show_id = s.id 
				and w.id = ws.workshop_id",
 
				array(':id' => $this->fields['table_id']));
				$titles = null;
				while ($row = $stmt->fetch()) {
					if ($titles) { $titles .= ",<br> "; }
					$titles .= $row['title'];					
					$this->set_into_fields($row);
				}
				$row['title'] = $titles;
				$this->set_into_fields($row);
			}
		}
	}	
	
	
}
	
?>