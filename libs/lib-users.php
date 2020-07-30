<?php
namespace Users;
	
// users

function get_empty_user() {
		
	return array(
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
}

function get_user_by_email($email) {
	global $last_insert_id;
	
	$stmt = \DB\pdo_query("select u.* from users u where email = :email", array(':email' => $email));
	while ($row = $stmt->fetch()) {
		return add_extra_user_info($row);
	}
	
	$new_user_id = null;
	// didn't find one? make one
	if (validate_email($email)) {
		$stmt = \DB\pdo_query("insert into users (email, joined) VALUES (:email, '".date("Y-m-d H:i:s")."')", array(':email' => $email));
		$new_user_id = $last_insert_id;
		$key = gen_key($new_user_id);
		return get_user_by_id($new_user_id);
	}
	return false; // invalid email
}


function get_user_by_id($id) {
	$stmt = \DB\pdo_query("select u.* from users u where u.id = :id", array(":id" => $id));
	while ($row = $stmt->fetch()) {
		return add_extra_user_info($row);
	}
	return false;
}

function add_extra_user_info($row) {	
	// expecting variable $row which is a row of table 'user'
	$row['ukey'] = check_key($row['ukey'], $row['id']);
	return set_nice_name($row);
}


function set_nice_name($row) {
	if ($row['display_name']) {
		$row['nice_name'] = "{$row['display_name']}"; 	
		$row['fullest_name'] = "{$row['display_name']} ({$row['email']})";	
	} else {
		$row['nice_name'] = $row['fullest_name'] = $row['email'];
	}
	return $row;
}

function check_for_stored_or_passed_key() {
	global $key;
	if (isset($_REQUEST['key']) && $_REQUEST['key']) {
		$key = $_REQUEST['key'];
	} elseif (isset($_SESSION['s_key']) && $_SESSION['s_key']) {
		$key = $_SESSION['s_key'];
	} elseif (isset($_COOKIE['c_key']) && $_COOKIE['c_key']) {
		$key = $_COOKIE['c_key'];
	}

	// remember it
	return remember_key($key);
}

function remember_key($key) {
	$_SESSION['s_key'] = $key;
	setcookie('c_key', $key, time() + 31449600); // a year!
	return $key;
}

function check_key($key, $uid) {
	if ($key) { 
		return $key;
	} else {
		return get_key($uid); 
	}
}

function verify_key($passed, $true, &$error, $show_error = 1) {
	global $u;
	if ($passed != $true) {
		if ($show_error) {
			$error = "Hmmm. I can't verify that you are who you say you are. Try logging in below.";
		}
		return false;
	} else {
		return true;
	}
}

function gen_key($uid) {
	$key = substr(md5(uniqid(mt_rand(), true)), 0, 16);
	$stmt = \DB\pdo_query("update users set ukey = :ukey where id = :uid", array(':ukey' => $key, ':uid' => $uid));
	return $key;
}

function get_key($uid) {
	$stmt = \DB\pdo_query("select ukey from users where id = :uid", array(':uid' => $uid));
	while ($row = $stmt->fetch()) {
		if ($row['ukey']) { return $row['ukey']; }
	}
	return gen_key($uid);
}

function key_to_user($key) {
	$stmt = \DB\pdo_query("select * from users where ukey = :key", array(':key' => $key));
	while ($row = $stmt->fetch()) {
		return add_extra_user_info($row);
	}
	return false;
}


function validate_email($emailaddress) {
	$pattern = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';

	if (preg_match($pattern, $emailaddress) === 1) {
		return true;
	} else {
		return false;
	}
}



function email_link($u) {
		if (!isset($u['id'])) {
			return false;
		}
		$trans = URL."index.php?key=".get_key($u['id']);
		$body = "<p>Use this link to log in:</p>
<p>{$trans}</p>".\Emails\email_footer();

		return \Emails\centralized_email($u['email'], "Log in to 'Will Hines practices'", $body);
}

function logged_in() {
	global $u, $key;
	if (isset($u) && $u && verify_key($key, $u['ukey'], $error, 0) && isset($u['id']) && $u['id'] > 0) {
		return true;
	} else {
		return false;
	}
}


function logout(&$key, &$u, &$message) {
	
	if (isset($u['id']) && $u['id']) {
		$key = gen_key($u['id']); // change the key
	}
	
	unset($_SESSION['s_key']);
    unset($_COOKIE['c_key']);
    setcookie('c_key', null, -1);
	
	$key = '';
	$u = null;
	$message = 'You are logged out!';
}


function find_students($needle = 'everyone', $sort = 'n') {
	
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
	while ($row = $stmt->fetch()) {
		$row = set_nice_name($row);
		$stds[$row['id']] = $row;
	}
	return $stds;
}

function delete_student($uid = 0) {
	if (!$uid) {
		return false;
	}
	$stmt = \DB\pdo_query("delete from status_change_log where user_id = :uid", array(':uid' => $uid));
	$stmt = \DB\pdo_query("delete from registrations where user_id = :uid", array(':uid' => $uid));
	$stmt = \DB\pdo_query("delete from users where id = :uid", array(':uid' => $uid));
	
	
	
	return true;
	
}

function edit_display_name($u) {
	global $sc;
	$body = '';
	$body .= "<form action='$sc' method='post'>\n";
	$body .= \Wbhkit\hidden('guest_id', $u['id']);
	$body .= \Wbhkit\hidden('ac', 'updatedn');
	$body .= \Wbhkit\texty('display_name', $u['display_name'], 'Real name', 'Jane Doe', 'Can be a nickname.');
	$body .= \Wbhkit\submit('Update Real Name');
	$body .= "</form>\n";
	
	return $body;
}

function update_display_name(&$u,  &$message, &$error) {

	global $logger;

	// update user info
	if ($error) {
		return false;
	} else {
		$stmt = \DB\pdo_query("update users set display_name = :name where id = :uid", array(':name' => $u['display_name'], ':uid' => $u['id']));
		$message = "Display name updated to '{$u['display_name']}'";
		$logger->info($message);
		return true;
	}

}



function change_email_phase_one($u, $new_email) {
	global $logger;
	
	$stmt = \DB\pdo_query("update users set new_email = :email where id = :uid", array(':email' => $new_email, ':uid' => $u['id']));
	
	$logger->info("change email phase one: user id {$u['id']}, new email '$new_email'");
	
	$sub = 'email update at will hines practices';
	$link = URL."you.php?key={$u['ukey']}&ac=concemail";
	$ebody = "<p>You requested to change what email you use at the Will Hines practices web site. Use the link below to do that:</p><p>$link</p>";
	\Emails\centralized_email($new_email, $sub, $ebody);

}

// user id is here for admin side 
// even though user side does not need it
function edit_change_email($u) {
	global $sc;
	$body = '';
	$body .= \Wbhkit\form_validation_javascript('changeEmail');
	$body .= "<form id='changeEmail' action='$sc' method='post' novalidate>\n";
	$body .= \Wbhkit\hidden('ac', 'cemail');
	$body .= \Wbhkit\hidden('guest_id', $u['id']);
	$body .= \Wbhkit\texty('newemail', $u['email'], 'New email', null, 'We will email a login link to this address', 'Must be a valid email', ' required ', 'email');
	$body .= \Wbhkit\submit('Change Email');
	$body .= "</form>";
	return $body;	
}

function change_email($ouid, $newe) {
	global $logger;
	
	$olds = get_user_by_id($ouid);
	
	$logger->info("change email phase two: request to set user id $ouid to new email '$newe'");
	
	//does new student exist?
	$stmt = \DB\pdo_query("select u.* from users u where email = :email", array(':email' => $newe));
	$news = false;
	while ($row = $stmt->fetch()) {
		$news = add_extra_user_info($row);
		$logger->info("change email phase two: found user '{$news['id']}' with email '{$news['email']}'");
		
	}	
	
	if ($news) {
		// new student exists, so merge into new
		$stmt = \DB\pdo_query("select * from registrations where user_id = :uid", array(':uid' => $ouid));
		while ($row = $stmt->fetch()) {
			
			//does new email already have this registation?
			$stmt2 = \DB\pdo_query("select * from registrations where user_id = :uid and workshop_id = :wid", array(':uid' => $news['id'], ':wid' => $row['workshop_id']));
			$rows2 = $stmt2->fetchAll();
			if (count($rows2) == 0) {
				$stmt3 = \DB\pdo_query("update registrations set user_id = :uid where workshop_id = :wid and user_id = :uid2", array(':uid' => $news['id'], ':wid' => $row['workshop_id'], ':uid2' => $ouid));
				$logger->info("change email phase two: update registration uid '$ouid' wid {$row['workshop_id']} to uid {$news['id']}");
			}
		}
		
		// copy text preferences from old id
		$stmt = \DB\pdo_query("update users set send_text = :sendtext, carrier_id = :carrier_id, phone = :phone where id = :uid", array(':sendtext' => $olds['send_text'], ':carrier_id' => $olds['carrier_id'], ':phone' => $olds['phone'], ':uid' => $news['id']));
				
		// update records in change log
		$stmt = \DB\pdo_query("update status_change_log set user_id = :uid where user_id = :uid2", array(':uid' => $news['id'], ':uid2' => $olds['id']));
		
		//update teacher records
		$stmt = \DB\pdo_query("update teachers set user_id = :uid where user_id = :uid2", array(':uid' => $news['id'], ':uid2' => $olds['id']));
		
		delete_student($ouid);
		return true;
	} else {
		// new email is not yet a student, so just rename old
		$stmt = \DB\pdo_query("update users set email = :email where id = :uid", array(':email' => $newe, ':uid' => $ouid));	
		$logger->info("change email phase two: update email for '$ouid' to new email '$newe'");
		return true;
	}
	return true;
}



function edit_text_preferences($u) {
	global $sc, $ac;
	$carriers = \Lookups\get_carriers_drop();
	
	$body = '';
	$body .= "";
		
	$body .= \Wbhkit\form_validation_javascript('edit_text_preferences');
	$body .= "<div class='row'><div class='col'>\n";
	$body .= "<form id='edit_text_preferences' action='$sc' method='post' novalidate>\n";
	$body .= \Wbhkit\hidden('guest_id', $u['id']);
	$body .= \Wbhkit\hidden('ac', 'updateu');
	$body .= \Wbhkit\checkbox('send_text', 1, 'Send text updates?', $u['send_text']);
	$body .= \Wbhkit\drop('carrier_id', $carriers, $u['carrier_id'], 'phone network', null, "You must pick a carrier if you want text updates.", ' required ');
	$body .= \Wbhkit\texty('phone', $u['phone'], 'phone number', null, '10 digit phone number', 'Phone must be 10 digits, no letters or spaces or dashes', ' required minlength="10" maxlength="11" pattern="\d+" ');
	$body .= \Wbhkit\submit('Update Text Preferences');
	$body .= "</form>\n";
	$body .= "</div></div> <!-- end of col and row -->\n";
	
	return $body;
}


function update_text_preferences(&$u,  &$message, &$error) {

	global $logger;

	// $u must include $carrier_id, $phone, $send_text
	$carrier_id = $u['carrier_id'];
	$phone = $u['phone'];
	$phone = preg_replace('/\D/', '', $phone); // just numbers for phone
	$send_text = $u['send_text'] ? $u['send_text'] : 0;

	// only validate data if they want texts, else who cares?
	if ($send_text == 1) {
		if (strlen($phone) != 10) {
			$error = 'Phone number must be ten digits.';
		} 
		if ($carrier_id == 0) {
			$error = 'You must pick a carrier if you want text updates.';
		}
	}

	// update user info
	if ($error) {
		return false;
	} else {
		
		$stmt = \DB\pdo_query("update users set send_text = :send_text, phone = :phone, carrier_id = :carrier_id where id = :uid",
		array(':send_text' => $send_text,
		':phone' => $phone,
		':carrier_id' => $carrier_id,
		':uid' => $u['id']));

		// update $u array with info so form is populated correctly
		foreach (['send_text', 'phone', 'carrier_id'] as $key) {
			$u[$key] = $$key;
		}
		$message = 'Text preferences updated!';
		$logger->debug($message." for user {$u['id']}");
		
		return true;
	}

}


function edit_group_level($u) {
	global $sc;
	return "<form action='$sc' method='post'>\n".
	\Wbhkit\hidden('guest_id', $u['id']).
	\Wbhkit\hidden('ac', 'updategroup').
	\Wbhkit\drop('group_id', \Lookups\groups_drop(), $u['group_id'], 'Group', 'Clearance level').
	\Wbhkit\submit('Update Group Level').
	"</form>\n";	
}

function update_group_level($u) {
	global $logger;

	if ($u['id'] == 1) { return false; } // no changing user 1 (will's) level -- go directly to DB for that

	$stmt = \DB\pdo_query("update users set group_id = :gid where id = :uid", array(':gid' => $u['group_id'], ':uid' => $u['id']));
	$u = get_user_by_id($u['id']); // updated so the form is correctly populated on refill
	$message = "Display name updated to '{$u['display_name']}'";
	$logger->info("User {$u['email']} updated to group level '{$u['group_id']}'");
	return true;
}

function check_user_level($level) {
	global $u;
	if (isset($u) && isset($u['group_id']) && $u['group_id'] >= $level) {
		return true;
	}
	return false;
}

function reject_user_below($at_least) {
	global $u, $view;
	if (!isset ($u) || !isset($u['id']) || !isset($u['group_id']) || $u['group_id'] < $at_least) {
		$view->renderPage('admin/notcleared');
		exit();
		return false;
	}
	return true;
}

function is_complete_user($u) {
	if (is_array($u) && isset($u['id']) && $u['id']) {
		return true;
	}
	return false;
}


