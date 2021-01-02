<?php

class ClassShow extends WBHObject {
	
	public array $fields;	
	public array $wks;
	public User $teacher;
	
	
	function __construct() {		
		parent::__construct(); // load logger, lookups

		$this->fields = array(
				'id' => null,
				'start' => null,
				'end' => null,
				'teacher_id' => null,
				'actual_pay' => null,
				'online_url' => null,
				'reminder_sent' => 0,
				'when_teacher_paid' => null);	
		
		$this->teacher = new User();
		$this->wks = array();			

	}

	function set_by_id(int $id) {
		
		if (!$id) {
			$this->error = "No show found for id '{$id}'";
			return false;
		}
	
		$stmt = \DB\pdo_query("select * from shows s where id = :id", array(':id' => $id));

		while ($row = $stmt->fetch()) {
			$this->set_into_fields($row);
			$this->format_start_end();
			return true;
		}
		$this->error = "No show found for id '{$id}'";
		return false;

	}
	
	function format_start_end() {
		$this->set_mysql_datetime_field('start', $this->fields['start']);
		$this->set_mysql_datetime_field('end', $this->fields['end']);
		$this->set_mysql_datetime_field('when_teacher_paid', $this->fields['when_teacher_paid']);
		if (!$this->fields['actual_pay']) { $this->fields['actual_pay'] = 0; }
		if (!$this->fields['reminder_sent']) { $this->fields['reminder_sent'] = 0; }
		$this->fields['friendly_when'] = \Wbhkit\friendly_when($this->fields['start']).'-'.\Wbhkit\friendly_time($this->fields['end']);
	}	
	
	function save_data() {
		global $last_insert_id;
		
		// make sure datetime fields are formatted for mysql
		$this->format_start_end();
		
		// set params string
		$fieldnames = array('start', 'end', 'teacher_id', 'online_url', 'actual_pay', 'reminder_sent', 'when_teacher_paid');
		$params = array();
		foreach ($fieldnames as $f) {
			$params[":{$f}"] = $this->fields[$f];
		}
		
		//insert or update
		if ($this->fields['id']) {
			$params[':id'] = $this->fields['id'];
			$stmt = \DB\pdo_query("update shows set start = :start, end = :end, teacher_id = :teacher_id, online_url = :online_url, actual_pay = :actual_pay, when_teacher_paid = :when_teacher_paid, reminder_sent = :reminder_sent where id = :id", $params);
			return true;
		} else {
			$query = "insert into shows (start, end, teacher_id, online_url, actual_pay, when_teacher_paid, reminder_sent) VALUES (:start, :end, :teacher_id, :online_url, :actual_pay, :when_teacher_paid, :reminder_sent)";
			
			$db = \DB\get_connection();
			$stmt = $db->prepare($query);
			
			foreach ($fieldnames as $f) {
				if ($this->fields[$f] !== null) {
					$stmt->bindParam(":{$f}", $this->fields[$f]);
				} else {
					$stmt->bindValue(":{$f}", null, PDO::PARAM_INT);
				}
			}
			$stmt->execute();
			$this->fields['id'] = $db->lastInsertId();
			return true;	
		}
	}
	
	function set_workshops() {
		
		if (!$this->fields['id']) {
			$this->error = "No id set for class show.";
			return false;
		}
		
		$stmt = \DB\pdo_query("select ws.*, wk.title from workshops_shows ws, workshops wk where ws.workshop_id = wk.id and ws.show_id = :id order by id", array(':id' => $this->fields['id']));
		$this->wks = array();
		while ($row = $stmt->fetch()) {
			$this->wks[] = $row;
		}	
		return true;
	}
	
	function set_teacher() {
		if (!$this->fields['teacher_id']) {
			$this->error = "No id set for teacher.";
			return false;
		}
		$stmt = \DB\pdo_query("select user_id from teachers where id = :id", array(':id' => $this->fields['teacher_id']));
		while ($row = $stmt->fetch()) {
			$this->teacher = new User();
			$this->teacher->set_by_id($row['user_id']);
			return true;
		}	
		$this->error = "No teacher found for id {$this->fields['teacher_id']}";
		return false;	
	}
	
	function associate_workshop(int $wid) {
		if (!$this->fields['id']) {
			$this->error = "No id set for class show.";
			return false;
		}
		$params = array(':show_id' => $this->fields['id'], ':workshop_id' => $wid);
		$stmt = \DB\pdo_query("select * from workshops_shows where show_id = :show_id and workshop_id = :workshop_id", $params);
		while ($row = $stmt->fetch()) {
			$this->message = "workshop $wid is already associated with show {$this->fields['id']}";
			return true;
		}	
		$stmt = \DB\pdo_query("insert into workshops_shows (show_id, workshop_id) VALUES (:show_id, :workshop_id)", $params);
		$this->message = "Associated workshop $wid with show {$this->fields['id']}";
		return true;
	}
	
	function remove_workshop($wid) {
		if (!$this->fields['id']) {
			$this->error = "No id set for class show.";
			return false;
		}
		$params = array(':show_id' => $this->fields['id'], ':workshop_id' => $wid);
		$stmt = \DB\pdo_query("delete from workshops_shows where show_id = :show_id and workshop_id = :workshop_id", $params);
		$this->message = "Removed workshop $wid from show {$this->fields['id']}";
		return true;
	}
	
	function delete_show() {
		if (!$this->fields['id']) {
			$this->error = "No id set for class show.";
			return false;
		}
		$params = array(':show_id' => $this->fields['id']);
		$stmt = \DB\pdo_query("delete from workshops_shows where show_id = :show_id", $params);
		$stmt = \DB\pdo_query("delete from shows where id = :show_id", $params);
		$this->message = "Deleted show {$this->fields['id']}";
		$this->fields = array();
		return true;
	}

}
	
?>