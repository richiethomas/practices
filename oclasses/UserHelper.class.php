<?php
	
class UserHelper extends WBHObject {
	
	public User $u;
	public string $sc;
	
	function __construct(string $sc) {		
		parent::__construct(); // load logger, lookups
		$this->sc = $sc;
		$this->u = new User();
	}
	
	// returns students -- maybe i'll make them an object at some point!
	function find_students(string $needle = 'everyone', string $sort = 'n') {
	
		$order_by = array('n' => 'a.email', 't' => 'classes desc', 'd' => 'a.joined desc');

		$sql = "SELECT a.id, a.email, a.display_name, a.phone, COUNT(b.id) AS 'classes', a.joined  
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
			$where .= " or a.phone like :needle2";
			$where .= " or a.display_name like :needle3";
		
			$sql = preg_replace('/WHERECLAUSE/', $where, $sql);
			$stmt = \DB\pdo_query($sql, array(':needle1' => "%$needle%", ':needle2' => "%$needle%", ':needle3' => "%$needle%" ));
		}
	
		$stds = array();
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$this->u->replace_fields($row);
			$this->u->set_nice_name();
			$stds[$row['id']] = $this->u->fields;
		}
		return $stds;
	}



	function edit_display_name(User $u) {
		$body = '';
		$body .= "<form action='{$this->sc}/updatedn' method='post'>\n";
		$body .= \Wbhkit\hidden('guest_id', $u->fields['id']);
		$body .= \Wbhkit\texty('display_name', $u->fields['display_name'], 'Real name', 'Jane Doe', 'Can be a nickname.');
		$body .= \Wbhkit\submit('Update Real Name');
		$body .= "</form>\n";
	
		return $body;
	}




	// user id is here for admin side 
	// even though user side does not need it
	function edit_change_email(User $u) {

		$body = '';
		$body .= \Wbhkit\form_validation_javascript('changeEmail');
		$body .= "<form id='changeEmail' action='{$this->sc}/cemail' method='post' novalidate>\n";
		$body .= \Wbhkit\hidden('guest_id', $u->fields['id']);
		$body .= \Wbhkit\texty('newemail', $u->fields['email'], 'New email', null, 'We will email a login link to this address', 'Must be a valid email', ' required ', 'email');
		$body .= \Wbhkit\submit('Change Email');
		$body .= "</form>";
		return $body;	
	}




	function edit_text_preferences(User $u) {
		$body = '';
		$body .= "";
				
		$body .= \Wbhkit\form_validation_javascript('edit_text_preferences');
		$body .= "<div class='row'><div class='col'>\n";
		$body .= "<form id='edit_text_preferences' action='{$this->sc}/updateu' method='post' novalidate>\n";
		$body .= \Wbhkit\hidden('guest_id', $u->fields['id']);
		$body .= \Wbhkit\checkbox('send_text', 1, 'Send text updates?', $u->fields['send_text']);
		$body .= \Wbhkit\drop('carrier_id', $this->lookups->carriers_drop, $u->fields['carrier_id'], 'phone network', null, "You must pick a carrier if you want text updates.", ' required ');
		$body .= \Wbhkit\texty('phone', $u->fields['phone'], 'phone number', null, '10 digit phone number', 'Phone must be 10 digits, no letters or spaces or dashes', ' required minlength="10" maxlength="11" pattern="\d+" ');
		$body .= \Wbhkit\submit('Update Text Preferences');
		$body .= "</form>\n";
		$body .= "</div></div> <!-- end of col and row -->\n";
	
		return $body;
	}


	function edit_group_level(User $u) {
		return "<form action='{$this->sc}/updategroup' method='post'>\n".
		\Wbhkit\hidden('guest_id', $u->fields['id']).
		\Wbhkit\drop('group_id', $this->lookups->groups, $u->fields['group_id'], 'Group', 'Clearance level').
		\Wbhkit\submit('Update Group Level').
		"</form>\n";	
	}

	function delete_user(int $uid) {
		$ud = new User($this->logger, $this->lookups);
		$ud->set_by_id($uid);
		$ud->delete_user();
	}
	
	
}
?>