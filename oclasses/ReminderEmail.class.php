<?php

class ReminderEmail extends WBHObject {
		
	function __construct() {		
		parent::__construct(); // load logger, lookups
		
		$this->tablename = "reminder_emails";

		$this->fields = array(
				'id' => null,
				'slug' => null,
				'subject' => null,
				'body' => null);
				
		$this->cols = $this->fields; // let fields be given extra cols later
		

	}
	
	function get_reminder_emails_dropdown() {
		
		// get tasks for the next 24 hours where reminder has not been sent
		$stmt = \DB\pdo_query("
			select * from reminder_emails order by id desc"); 

		$res = array();
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$res[$row['id']] = $row['slug'];
		}
		return $res;
	}

}
	
?>