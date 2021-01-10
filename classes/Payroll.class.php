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
		
		if ($this->fields['task'] && $this->fields['table_id']) {
			if ($this->fields['task'] == 'workshop') {
				$stmt = \DB\pdo_query("select w.title, w.start, u.email, u.display_name, 1 as rank
					from workshops w, teachers t, users u
				where w.id = :id
				and w.teacher_id = t.id and t.user_id = u.id",
				 array(':id' => $this->fields['table_id']));
				while ($row = $stmt->fetch()) {
					$row['teacher_name'] = $row['display_name'] ? $row['display_name'] : $row['email'];
					$this->set_into_fields($row);
				}
			}

			if ($this->fields['task'] == 'class') {
				$stmt = \DB\pdo_query("select w.title, x.start, u.email, u.display_name, x.rank as rank
					from workshops w, xtra_sessions x, teachers t, users u
				where x.id = :id
				and w.id = x.workshop_id 
				and w.teacher_id = t.id and t.user_id = u.id",
				array(':id' => $this->fields['table_id']));
				while ($row = $stmt->fetch()) {
					$row['teacher_name'] = $row['display_name'] ? $row['display_name'] : $row['email'];
					$this->set_into_fields($row);
				}
			}

			if ($this->fields['task'] == 'show') {
				$stmt = \DB\pdo_query("select w.title, s.start , u.email, u.display_name, 0 as rank
					from workshops w, workshops_shows ws, shows s, teachers t, users u 
				where s.id = :id
				and w.id = ws.workshop_id 				
				and w.teacher_id = t.id and t.user_id = u.id",
 
				array(':id' => $this->fields['table_id']));
				$titles = null;
				while ($row = $stmt->fetch()) {
					if ($titles) { $titles .= ", "; }
					$titles .= $row['title'];					
					$row['teacher_name'] = $row['display_name'] ? $row['display_name'] : $row['email'];
 				}
				$row['titles'] = $titles;
				$this->set_into_fields($row);
			}
		}
	}	
	
	
}
	
?>