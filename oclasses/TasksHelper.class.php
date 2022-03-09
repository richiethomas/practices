<?php

class TasksHelper extends WBHObject {


	public array $tasks;
	
	function __construct() {		
		parent::__construct(); // load logger, lookups
		$this->tasks = array();
	}

	function get_upcoming_tasks() {
		
		
		// get tasks for the next 24 hours where reminder has not been sent
		$stmt = \DB\pdo_query("
			select t.*, u.display_name, u.email, u.time_zone, re.slug, re.subject, re.body 
		from tasks t, users u, reminder_emails re 
		where t.reminder_sent = 0 
		and t.user_id = u.id
		and t.reminder_email_id = re.id
		and t.event_when > now() 
		and t.event_when < DATE_ADD(now(), INTERVAL 1 DAY)"); 

		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$new_task = new Task();
			$new_task->set_into_fields(array(
				'id' => $row['id'],
				'user_id' => $row['user_id'],
				'reminder_email_id' =>$row['reminder_email_id'],
				'title' => $row['title'],
				'event_when' => $row['event_when'],
				'reminder_sent' => $row['reminder_sent'],
				'payment_amount' => $row['payment_amount']
			));
				
			$new_task->user->set_into_fields(array(
				'id' => $row['user_id'],
				'display_name' => $row['display_name'],
				'email' => $row['email'],
				'time_zone' => $row['time_zone']
			));	
			$new_task->reminder_email->set_into_fields(array(
				'id' => $row['reminder_email_id'],
				'slug' => $row['slug'],
				'subject' => $row['subject'],
				'body' => $row['body']
			));	
			$new_task->localize_event_when();
			$new_task->prep_reminder_body();
			$this->tasks[] = $new_task;
		}
		return $this->tasks;
	}
	

	
}
	
?>