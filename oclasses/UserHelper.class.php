<?php
	
class UserHelper extends WBHObject {
	
	public User $u;
	public string $sc;
	
	function __construct(string $sc) {		
		parent::__construct(); 
		$this->sc = $sc;
		$this->u = new User();
	}
	
	// returns students -- maybe i'll make them an object at some point!
	function find_students(string $needle = 'everyone', string $sort = 'n') {
	
		$order_by = array('n' => 'a.email', 't' => 'classes desc', 'd' => 'a.joined desc');

		$sql = "SELECT a.id, a.email, a.display_name, COUNT(b.id) AS 'classes', a.joined  
		FROM 
			users a 
		   LEFT JOIN
		   (SELECT id, user_id FROM registrations) b
		   ON a.id = b.user_id
		   WHERECLAUSE
		group by a.email
		order by ".$order_by[$sort];
	
		if ($needle == 'everyone') {
			$sql = preg_replace('/WHERECLAUSE/', '', $sql);
			$stmt = \DB\pdo_query($sql);
		} else {
			$where = "where a.email like :needle1";
			$where .= " or a.display_name like :needle2";
		
			$sql = preg_replace('/WHERECLAUSE/', $where, $sql);
			$stmt = \DB\pdo_query($sql, array(':needle1' => "%$needle%", ':needle2' => "%$needle%" ));
		}
	
		$stds = array();
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$this->u->replace_fields($row);
			$this->u->finish_setup();
			$stds[$row['id']] = $this->u->fields;
		}
		return $stds;
	}



	function edit_display_name(User $u) {
		$body = '';
		$body .= "<form action='{$this->sc}/updateuser/{$u->fields['id']}' method='post'>\n";
		$body .= \Wbhkit\texty('display_name', $u->fields['display_name'], 'Real name', 'Jane Doe', 'first and last human being name');
		$body .= \Wbhkit\submit('Update Real Name');
		$body .= "</form>\n";
	
		return $body;
	}

	function edit_public_attributes(User $u) {
		if (!$u->fields['time_zone']) { $u->fields['time_zone'] = DEFAULT_TIME_ZONE; }
		
		$body = '';
		$body .= "<form action='{$this->sc}/updateuser/{$u->fields['id']}' method='post'>\n";
		$body .= \Wbhkit\drop('time_zone', \Wbhkit\get_time_zones(), $u->fields['time_zone']);
		$body .= \Wbhkit\texty('display_name', $u->fields['display_name'], 'Real name', 'Jane Doe', 'first and last human being name');
		$body .= \Wbhkit\checkbox('opt_out', 1, 'Opt out of emails', $u->fields['opt_out'])."<br>\n";
		$body .= \Wbhkit\submit('Update Profile');
		$body .= "</form>\n";
		return $body;
		
		
	}

	// user id is here for admin side 
	// even though user side does not need it
	function edit_change_email(User $u) {

		$body = '';
		//$body .= \Wbhkit\form_validation_javascript('changeEmail');
		$body .= "<form id='changeEmail' action='{$this->sc}/cemail/{$u->fields['id']}' method='post' novalidate>\n";
		$body .= \Wbhkit\texty('newemail', $u->fields['email'], 'New email', null, 'We will email a login link to this address', 'Must be a valid email', ' required ', 'email');
		$body .= \Wbhkit\submit('Change Email');
		$body .= "</form>";
		return $body;	
	}


	function edit_group_level(User $u) {
		global $lookups;
		
		return "<form action='{$this->sc}/updategroup/{$u->fields['id']}' method='post'>\n".
		\Wbhkit\drop('group_id', $lookups->groups, $u->fields['group_id'], 'Group', 'Clearance level').
		\Wbhkit\submit('Update Group Level').
		"</form>\n";	
	}

	function delete_user(int $uid) {
		$ud = new User();
		$ud->set_by_id($uid);
		$ud->delete_user();
	}
	
}
?>