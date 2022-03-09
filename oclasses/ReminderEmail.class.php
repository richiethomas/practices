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

}
	
?>