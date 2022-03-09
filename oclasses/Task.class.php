<?php

class Task extends WBHObject {
		
	public User $user;
	public ReminderEmail $reminder_email;
	
	function __construct() {		
		parent::__construct(); // load logger, lookups
		
		$this->tablename = "tasks";

		$this->fields = array(
				'id' => null,
				'user_id' => null,
				'reminder_email_id' => null,
				'title' => null,
				'event_when' => null,
				'reminder_sent' => 0,
				'payment_amount' => 0);
				
		$this->cols = $this->fields; // let fields be given extra cols later
		
		$this->user = new User();
		$this->reminder_email = new ReminderEmail();

	}

	function finish_setup() {		
		
		if ($this->fields['event_when']) {
			$this->set_mysql_datetime_field('event_when', $this->fields['event_when']);
		}
  		return true;
	}	
	
	function localize_event_when() {
		if ($this->user->fields['time_zone'] != DEFAULT_TIME_ZONE) { 
			$this->fields['local_event_when'] = \Wbhkit\convert_tz($this->fields['event_when'], $this->user->fields['time_zone']);
			
			// just to get short time zone
			$datetime = new \DateTime($row['local_event_when']);
			$datetime->setTimezone(new \DateTimeZone($row['time_zone']));
			$this->user->fields['short_time_zone'] = $datetime->format('T');
			
		} else {
			$this->fields['local_event_when'] = $this->fields['event_when'];
			$this->user->fields['time_zone'] = DEFAULT_TIME_ZONE; 
			$this->user->fields['short_time_zone'] = TIME_ZONE;
		}
		
		
	}	
		
	function prep_reminder_body() {
		
		$nice_name = 
			$this->user->fields['display_name'] ? 
				$this->user->fields['display_name'] : 
				$this->user->fields['email'];

		$event_when_public = 
			\Wbhkit\figure_year_minutes(strtotime($this->fields['local_event_when'])).
			" ({$this->user->fields['short_time_zone']})";	

		$body = $this->reminder_email->fields['body'];
		$body = preg_replace('/USERNAME/', $nice_name, $body);
		$body = preg_replace('/USEREMAIL/', $this->user->fields['email'], $body);
		$body = preg_replace('/EVENTWHEN/', $event_when_public, $body);
		$body = preg_replace('/\R/', "<br>", $body);
		
		$this->reminder_email->fields['body'] = $body;
		
		return $this->reminder_email->fields['body'];
		
	}	
		
	function update_reminder_sent(bool $flag = true) {
		
		$stmt = \DB\pdo_query("update tasks set reminder_sent = :flag where id = :id", array(
			':id' => $this->fields['id'],
			':flag' => $flag
		));
		
	}
		
	function finish_delete() {
		if (!$this->fields['id']) {
			$this->error = "No id set for task.";
			return false;
		}
		$stmt = \DB\pdo_query("delete from payrolls where task = 'task' and table_id = :id", array(":id" => $this->fields['id']));
		$this->user = new User();
		return true;
	}

}
	
?>