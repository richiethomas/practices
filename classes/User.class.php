<?php
	
class User extends WBHObject {
	
	
	function __construct() {		
		parent::__construct(); // load logger, lookups
						
		$fields = array(
			'id' => null,
			'email' => null,
			'display_name' => null,
			'ukey' => null,
			'send_text'=> null,
			'carrier_id' => null,
			'phone' => null,
			'new_email' => null,
			'temp_ukey' => null,
			'group_id' => null,
			'joined' => null,
			'nice_name' => null,
			'fullest_name' => null	
			);
			
		$this->set_into_fields($fields);
				

	}

	function set_by_email(string $email) {
		
		global $last_insert_id;

		$this->fields = array();
		
		$stmt = \DB\pdo_query("select u.* from users u where email = :email", array(':email' => $email));
		while ($row = $stmt->fetch()) {
			$this->set_into_fields($row);
			$this->set_nice_name();
			return true;
		}
	
		// didn't find one? make one
		if ($this->validate_email($email)) {
			$stmt = \DB\pdo_query("insert into users (email, joined) VALUES (:email, '".date("Y-m-d H:i:s")."')", array(':email' => $email));
			$this->set_by_id($last_insert_id); // fast way to get all fields in this object
			$this->set_nice_name();
			$this->get_key();
			return true;
		} else {
			$this->error = "Invalid email: '{$email}'";
		}
		return false; // invalid email
	}


	function set_by_id(int $id) {
		
		if (!$id) {
			$this->error = "We need a non-zero id number. You gave me: '{$id}'";
			return false;
		}
		
		$this->fields = array();
		
		$stmt = \DB\pdo_query("select u.* from users u where u.id = :id", array(":id" => $id));
		while ($row = $stmt->fetch()) {
			$this->set_into_fields($row);
		}
		$this->set_nice_name(); // nice name, confirm key
		return true;
	}

	function set_by_key($key) {
		$this->fields = array();
		$stmt = \DB\pdo_query("select * from users where ukey = :key", array(':key' => $key));
		while ($row = $stmt->fetch()) {
			$this->set_into_fields($row);
			$this->set_nice_name();
			return $this->fields['id'];
		}
		return false;
	}


	// needs display_name, email
	function set_nice_name() {	
		if (isset($this->fields['display_name']) && $this->fields['display_name']) {
			$this->fields['nice_name'] = $this->fields['display_name']; 	
			$this->fields['fullest_name'] = "{$this->fields['display_name']} ({$this->fields['email']})";	
		} else {
			$this->fields['nice_name'] = $this->fields['fullest_name'] = $this->fields['email'];
		}
		return true;
	}


	function set_nice_name_in_row(array $row) {	
		// expecting variable $row which is a row of table 'user'
		if ($row['display_name']) {
			$row['nice_name'] = $row['display_name']; 	
			$row['fullest_name'] = "{$row['display_name']} ({$row['email']})";	
		} else {
			$row['nice_name'] = $row['fullest_name'] = $row['email'];
		}
		return $row;
	}


	
	function get_key(bool $force_new = false) {
		if (!isset($this->fields['ukey']) || !$this->fields['ukey'] || $force_new) {
			$key = substr(md5(uniqid(mt_rand(), true)), 0, 16);
			$stmt = \DB\pdo_query("update users set ukey = :ukey where id = :uid", array(':ukey' => $key, ':uid' => $this->fields['id']));
			$this->fields['ukey'] = $key;
		}
		return $this->fields['ukey'];
	}

	function check_for_stored_or_passed_key() {
		$key = null;
		if (isset($_REQUEST['key']) && $_REQUEST['key']) {
			$key = $_REQUEST['key'];
		} elseif (isset($_SESSION['s_key']) && $_SESSION['s_key']) {
			$key = $_SESSION['s_key'];
		} elseif (isset($_COOKIE['c_key']) && $_COOKIE['c_key']) {
			$key = $_COOKIE['c_key'];
		}
		$_SESSION['s_key'] = $key;
		$this->remember_key($key); // sets the cookie

		// remember it, return it
		return $key;
	}

	function remember_key($key) {
		setcookie('c_key', $key, time() + 31449600); // a year!
	}


	function delete_user() {
		if (!$this->logged_in()) {
			return false;
		}
		$stmt = \DB\pdo_query("delete from status_change_log where user_id = :uid", array(':uid' => $this->fields['id']));
		$stmt = \DB\pdo_query("delete from registrations where user_id = :uid", array(':uid' => $this->fields['id']));
		$stmt = \DB\pdo_query("delete from users where id = :uid", array(':uid' => $this->fields['id']));
		
		$this->fields = array();
		
		return true;
	
	}

	function validate_email(string $emailaddress) {
		$pattern = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';

		if (preg_match($pattern, $emailaddress) === 1) {
			return true;
		} else {
			return false;
		}
	}



	function email_link() {
			if (!$this->logged_in()) {
				return false;
			}
			$trans = URL."index.php?key=".$this->get_key();
			$body = "<p>Use this link to log in:</p>
	<p>{$trans}</p><p>(Sent: ".date('D M n, Y g:ia').")</p>".\Emails\email_footer();

			return \Emails\centralized_email($this->fields['email'], "Log in to WGIS", $body);
			
			
	}

	function logged_in() {
		if (isset($this->fields['id']) && $this->fields['id'] > 0) {
			return true;
		} else {
			return false;
		}
	}

	function soft_logout() {
		unset($_SESSION['s_key']);
	    unset($_COOKIE['c_key']);
	    setcookie('c_key', null, -1);
		$this->fields = array(); // clear current user
		$this->message = 'You are logged out!';
	}

	function hard_logout() {
	
		if ($this->logged_in()) {
			$this->get_key(true); // force change the key
		}
		$this->soft_logout();
	
	}

	function check_user_level(int $level) {

		if ($this->logged_in() && isset($this->fields['group_id']) && $this->fields['group_id'] >= $level) {
			return true;
		}
		return false;
	}

	function reject_user_below(int $at_least) {
		global $view;
		
		if ($this->logged_in() && isset($this->fields['group_id']) && $this->fields['group_id'] >= $at_least) {
			return true;
		} 
		$view->renderPage('admin/notcleared');
		exit();
		return false;		
	}	
	
	

	function update_display_name($display_name) {

		$this->fields['display_name'] = $display_name;
		
		// update user info
		$stmt = \DB\pdo_query("update users set display_name = :name where id = :uid", array(':name' => $this->fields['display_name'], ':uid' => $this->fields['id']));
		return true;

	}

	function update_text_preferences(string $phone, string $send_text, string $carrier_id) {
		
		$this->fields['phone'] = $phone;
		$this->fields['send_text'] = $send_text;
		$this->fields['carrier_id'] = $carrier_id;

		// $u must include $carrier_id, $phone, $send_text
		$this->fields['phone'] = preg_replace('/\D/', '', $this->fields['phone']); // just numbers for phone
		$this->fields['send_text'] = 
			$this->fields['send_text'] ? $this->fields['send_text'] : 0;

		// only validate data if they want texts, else who cares?
		if ($this->fields['send_text'] == 1) {
			if (strlen($this->fields['phone']) != 10) {
				$this->error = 'Phone number must be ten digits.';
			} 
			if ($this->fields['carrier_id'] == 0) {
				$this->error = 'You must pick a carrier if you want text updates.';
			}
		}

		// update user info
		if ($this->error) {
			return false;
		} else {
		
			$stmt = \DB\pdo_query("update users set send_text = :send_text, phone = :phone, carrier_id = :carrier_id where id = :uid",
			array(':send_text' => $this->fields['send_text'],
			':phone' => $this->fields['phone'],
			':carrier_id' => $this->fields['carrier_id'],
			':uid' => $this->fields['id']));
			return true;
		}

	}


	function update_group_level(int $glevel) {

		if ($this->fields['id'] == 1) { return false; } // no changing user 1 (will's) level -- go directly to DB for that

		$this->fields['group_id'] = $glevel;
		$stmt = \DB\pdo_query("update users set group_id = :gid where id = :uid", array(':gid' => $glevel, ':uid' => $this->fields['id']));
		return true;
	}	


	function change_email_phase_one(string $new_email) {
		
		if ($this->is_email_available($new_email)) {
			$stmt = \DB\pdo_query("update users set new_email = :email where id = :uid", array(':email' => $new_email, ':uid' => $this->fields['id']));
		
			$sub = 'email update at WGIS';
			$link = URL."you.php?ac=concemail";
			$ebody = "<p>You requested to change what email you use at the WGIS web site. Use the link below to do that:</p><p>$link</p>";
			\Emails\centralized_email($new_email, $sub, $ebody);
			return true;
		} else {
			$this->error = "The email '{$new_email}' is already being used! Pick a different email or email ".WEBMASTER." for help.";
			return false;
		}
	}	


	// assumes email is in 'new email' 
	function user_finish_change_email() {
		if ($this->is_email_available($this->fields['new_email'])) {
			// now we can update email
			$stmt = \DB\pdo_query("update users set email = :email where id = :uid", array(':email' => $this->fields['new_email'], ':uid' => $this->fields['id']));	
			$this->fields['email'] = $this->fields['new_email'];
			$this->set_nice_name();
			$this->logger->info("change email phase two: update email for user '{$this->fields['id']}' to new email '{$this->fields['email']}'");
			return true;
		} else {
			$this->error = "The email '{$this->fields['new_email']}' is already being used! Pick a different email or email ".WEBMASTER." for help.";
			return false;
		}
		
	}

	function admin_change_email(string $old_email, string $new_email) {

		$oldu = new User();
		$oldu->set_by_email($old_email);
		$newu = new User();
		$newu->set_by_email($new_email);
	
		$this->logger->info("admin change email: '$old_email' to '$new_email' (start)");
		
		//print_r($oldu->fields);
		//print_r($newu->fields);
		
		if ($newu->logged_in()) {
			
			// new student exists, so merge old info into new
			$stmt = \DB\pdo_query("select workshop_id from registrations where user_id = :uid", array(':uid' => $newu->fields['id']));
			while ($row = $stmt->fetch()) {
			
				//does current (old) email already have this registation?
				$stmt2 = \DB\pdo_query("select * from registrations where user_id = :uid and workshop_id = :wid", array(':uid' => $oldu->fields['id'], ':wid' => $row['workshop_id']));
				$rows2 = $stmt2->fetchAll();
				if (count($rows2) == 0) {
					$stmt3 = \DB\pdo_query("update registrations set user_id = :uid where workshop_id = :wid and user_id = :uid2", array(':uid' => $oldu->fields['id'], ':wid' => $row['workshop_id'], ':uid2' => $newu->fields['id']));
					$this->logger->info("admin change email: update registration uid for workshop {$row['workshop_id']} from uid '{$newu->fields['id']}' to uid {$oldu->fields['id']}");
				}
			}
						
			// update records in change log
			$stmt = \DB\pdo_query("update status_change_log set user_id = :uid where user_id = :uid2", array(':uid' => $oldu->fields['id'], ':uid2' => $newu->fields['id']));
		
			//update teacher records
			$stmt = \DB\pdo_query("update teachers set user_id = :uid where user_id = :uid2", array(':uid' => $oldu->fields['id'], ':uid2' => $newu->fields['id']));
		
		
			$newu->delete_user(); // we've absorbed your data, now you may die
			
		}
		// now we can update email
		$stmt = \DB\pdo_query("update users set email = :email where id = :uid", array(':email' => $new_email, ':uid' => $oldu->fields['id']));	
		$this->logger->info("admin change email: update email for uid '{$oldu->fields['id']}' to new email '$new_email'");
		return true;
	}


	private function is_email_available($email) {
		$stmt = \DB\pdo_query("select * from users where email = :email", array(':email' => $email));
		while ($row = $stmt->fetch()) {
			return false; // not available
		}	
		return true; // available
	}

	
}


?>
