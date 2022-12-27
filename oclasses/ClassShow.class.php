<?php

class ClassShow extends WBHObject {
	
	public array $wks;
	public User $teacher;
	
	
	function __construct() {		
		parent::__construct();
		
		$this->tablename = "shows";

		$this->fields = array(
				'id' => null,
				'start' => null,
				'end' => null,
				'teacher_id' => null,
				'online_url' => null,
				'reminder_sent' => 0);
				
		$this->cols = $this->fields; // let fields be given extra cols later
		
		$this->teacher = new User();
		$this->wks = array();			

	}

	function finish_setup() {
		$this->set_mysql_datetime_field('start', $this->fields['start']);
		$this->set_mysql_datetime_field('end', $this->fields['end']);
		if (!$this->fields['reminder_sent']) { $this->fields['reminder_sent'] = 0; }
		$this->fields['friendly_when'] = \Wbhkit\friendly_when($this->fields['start']).'-'.\Wbhkit\friendly_time($this->fields['end']);
		
		
		$row = array('online_url' => $this->fields['online_url']);
		$row = \Workshops\parse_online_url($row);
		$this->set_into_fields($row);
			
	}	

	
	function set_workshops() {
		
		if (!$this->fields['id']) {
			$this->error = "No id set for class show.";
			return false;
		}
		
		$stmt = \DB\pdo_query("select ws.*, wk.title, wk.start from workshops_shows ws, workshops wk where ws.workshop_id = wk.id and ws.show_id = :id order by id", array(':id' => $this->fields['id']));
		$this->wks = array();
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
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
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
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
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
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