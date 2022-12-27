<?php

class Team extends WBHObject {
		
	public array $users;
	
	function __construct() {		
		parent::__construct(); 
		
		$this->tablename = "teams";

		$this->fields = array(
				'id' => null,
				'title' => null,
				'formed' => null,
				'active' => 1,
				'online' => 0);
				
		$this->cols = $this->fields; // let fields be given extra cols later
		$this->users = array();

	}


	function get_form_fields() {
		
		return 
			\Wbhkit\texty('title', $this->fields['title'], null, 'Team Name', null, null, ' required ').
			\Wbhkit\texty('formed', $this->fields['formed'], null, 'mm/dd/yyyy', null, null, ' required ', 'date').
			\Wbhkit\checkbox('active', 1, 'Active', $this->fields['active']).
			\Wbhkit\checkbox('online', 1, 'Online', $this->fields['online']).
			\Wbhkit\submit($this->fields['id'] ? 'Update' : 'Add');
		
	}

	function finish_setup(bool $include_users = true) {		

		if ($include_users) {
			// get users
			$sql = "select user_id from teams_users where team_id = :id";
			$stmt = \DB\pdo_query($sql, array(':id' => $this->fields['id']));
			$this->users = array(); // clear existing users
			while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
				$u = new User();
				$u->set_by_id($row['user_id']);
				$this->users[] = $u;			
			}		
			
			if (count($this->users) > 0) {
				uasort($this->users, array($this, 'sort_users'));
			}
		}
		
		$this->set_mysql_date_field('formed', $this->fields['formed']);
		if (!$this->fields['online']) { $this->fields['online'] = 0; }
		if (!$this->fields['active']) { $this->fields['active'] = 0; }
  		return true;
	}	
	
	function sort_users($a, $b) {
		return strcasecmp($a->fields['nice_name'], $b->fields['nice_name']);
	}
	
	function finish_delete() {
		
		if ($this->fields['id']) {
			$tid = $this->fields['id'];
			$stmt = \DB\pdo_query("delete from teams_users where team_id = :tid", array(':tid' => $tid));
		}
		
	}
		
	function add_member(int $uid) {

		if ($this->fields['id']) {
			$tid = $this->fields['id'];
			$stmt = \DB\pdo_query("delete from teams_users where user_id = :uid and team_id = :tid", array(':uid' => $uid, ':tid' => $tid));
			$stmt = \DB\pdo_query("insert into teams_users (user_id, team_id) VALUES (:uid, :tid)", array(':uid' => $uid, ':tid' => $tid));
			$this->set_by_id($tid);
		}
		
	}

	function remove_member(int $uid) {
		if ($this->fields['id']) {
			$tid = $this->fields['id'];
			$stmt = \DB\pdo_query("delete from teams_users where user_id = :uid and team_id = :tid", array(':uid' => $uid, ':tid' => $tid));
			$this->set_by_id($tid);
		}
	}

}
	
?>