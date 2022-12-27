<?php

class TeamsHelper extends WBHObject {


	public array $teams;
	
	function __construct() {		
		parent::__construct(); 
		$this->teams = array();
	}

	function get_teams(?string $after = null) {
		
		$sql = "select id from teams ";

		if ($after) {
			$sql .= " where formed >= '".date('Y-m-d', strtotime($after))."'";
		}
		$sql .= " order by formed";
		
		$stmt = \DB\pdo_query($sql);
		
		$this->teams = array();
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$t = new Team();
			$t->set_by_id($row['id']);
			$this->teams[] = $t;
		}
		return $this->teams;
	}
	
}
	
?>