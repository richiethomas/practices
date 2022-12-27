<?php

class ReminderEmailsHelper extends WBHObject {


	function __construct() {		
		parent::__construct(); 
	}

	function get_reminder_emails(?int $limit = 200) {
		
		$stmt = \DB\pdo_query(		
			"select re.*
			from reminder_emails re 
		order by id desc limit $limit"); 

		$res = array();
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$re = new ReminderEmail();
			$re->set_into_fields($row);
			$res[] = $re;
		}
		return $res;
	}
	
}
	
?>