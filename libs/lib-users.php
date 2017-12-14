<?php
namespace Users;
	
// users
function get_user_by_email($email) {
	$sql = "select u.* from users u where email = '".\Database\mres($email)."'";
	$rows = \Database\mysqli( $sql) or \Database\db_error();
	while ($row = mysqli_fetch_assoc($rows)) {
		return add_extra_user_info($row);
	}
	return false;
}

function get_user_by_id($id) {
	$sql = "select u.* from users u where u.id = ".\Database\mres($id);
	$rows = \Database\mysqli( $sql) or \Database\db_error();
	while ($row = mysqli_fetch_assoc($rows)) {
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

function current_key() {
	global $key;
	if (isset($_REQUEST['key']) && $_REQUEST['key']) {
		//print_r($_REQEUST);
		$key = $_REQUEST['key'];
	} elseif (isset($_SESSION['s_key']) && $_SESSION['s_key']) {
		//print_r($_SESSION);
		$key = $_SESSION['s_key'];
	} elseif (isset($_COOKIE['c_key']) && $_COOKIE['c_key']) {
		//print_r($_COOKIE);
		$key = $_COOKIE['c_key'];
	}

	// remember it
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
	$sql = "update users set ukey = '".\Database\mres($key)."' where id = ".\Database\mres($uid);
	\Database\mysqli( $sql) or \Database\db_error();
	$_SESSION['s_key'] = $key;
	return $key;
}

function get_key($uid) {
	$sql = "select ukey from users where id = ".\Database\mres($uid);
	$rows = \Database\mysqli( $sql) or \Database\db_error();
	while ($row = mysqli_fetch_assoc($rows)) {
		if ($row['ukey']) { return $row['ukey']; }
	}
	return gen_key($u['id']);
}

function key_to_user($key) {
	$sql = "select id from users where ukey = '".\Database\mres($key)."'";
	$rows = \Database\mysqli( $sql) or \Database\db_error();
	while ($row = mysqli_fetch_assoc($rows)) {
		return get_user_by_id($row['id']);
	}
	return false;
}


function make_user($email) {
	$db = \Database\wh_set_db_link();
	if (validate_email($email)) {
		$sql = "insert into users (email, joined) VALUES ('".\Database\mres($email)."', '".date("Y-m-d H:i:s")."')";
		$rows = \Database\mysqli( $sql) or \Database\db_error();
		$key = gen_key($db->insert_id);
		return get_user_by_email($email);
	} else {
		return false;
	}
}

function validate_email($emailaddress) {
	$pattern = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';

	if (preg_match($pattern, $emailaddress) === 1) {
		return true;
	} else {
		return false;
	}
}

function login_prompt() {
	global $sc, $email;
	
	$f = \Wbhkit\form_validation_javascript('log_in');
	
	$f .= "<p>Submit your name and email and the site will email you a link to log in:</p>
	<form id='log_in' action='$sc' method='post' novalidate>".
	\Wbhkit\hidden('ac', 'link').
	\Wbhkit\texty('display_name', '', 'Real Name', 'Jane Doe', 'We list who is registered in the workshop description.').		
	\Wbhkit\texty('email', $email, 'Email', 'something@something.com', 'We send a log in link to this address.', 'This must be a valid email', ' required ', 'email').
	\Wbhkit\submit('Log In').
	"</form>";
	
	return $f;
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
	if (isset($u) && $u && verify_key($key, $u['ukey'], $error, 0)) {
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
	
	$where = '';
	if ($needle != 'everyone') {
		$where = "where a.email like '%".\Database\mres($needle)."%'";
		$where .= " or a.phone like '%".\Database\mres($needle)."%'";
		
	}
	
	$sql = "SELECT a.id, a.email, a.display_name, a.phone, COUNT(b.id) AS 'classes', a.joined  
	FROM 
		users a 
	   LEFT JOIN
	   (SELECT id, user_id FROM registrations) b
	   ON a.id = b.user_id
	   $where
	group by a.email
	order by ".$order_by[$sort];
	
	$rows = \Database\mysqli( $sql) or \Database\db_error();
	$stds = array();
	while ($row = mysqli_fetch_assoc($rows)) {
		$row = set_nice_name($row);
		$stds[$row['id']] = $row;
	}
	return $stds;
}

function delete_student($uid = 0) {
	if (!$uid) {
		return false;
	}
	$sql = "delete from registrations where user_id = ".\Database\mres($uid);
	\Database\mysqli($sql) or \Database\db_error();
	$sql = "delete from users where id = ".\Database\mres($uid);
	\Database\mysqli($sql) or \Database\db_error();
	$sql = "delete from status_change_log where user_id = ".\Database\mres($uid);
	\Database\mysqli($sql) or \Database\db_error();
	return true;
	
}

function edit_display_name($u) {
	global $sc;
	$body = '';
	$body .= "<form action='$sc' method='post'>\n";
	$body .= \Wbhkit\hidden('uid', $u['id']);
	$body .= \Wbhkit\hidden('ac', 'updatedn');
	$body .= \Wbhkit\texty('display_name', $u['display_name'], 'Real name', 'Jane Doe', 'We list who is registered in the workshop description.');
	$body .= \Wbhkit\submit('Update Real Name');
	$body .= "</form>\n";
	
	return $body;
}

function update_display_name(&$u,  &$message, &$error) {

	// update user info
	if ($error) {
		return false;
	} else {
		$sql = sprintf("update users set display_name = '%s' where id = %u",
		\Database\mres($u['display_name']),
		\Database\mres($u['id']));		
		\Database\mysqli($sql) or \Database\db_error();
		$u = get_user_by_id($u['id']); // updated so the form is correctly populated on refill
		$message = "Display name updated to '{$u['display_name']}'";
		return true;
	}

}

// user id is here for admin side 
// even though user side does not need it
function edit_change_email($u) {
	global $sc;
	$body = '';
	$body .= \Wbhkit\form_validation_javascript('changeEmail');
	$body .= "<form id='changeEmail' action='$sc' method='post' novalidate>\n";
	$body .= \Wbhkit\hidden('ac', 'cemail');
	$body .= \Wbhkit\hidden('uid', $u['id']);
	$body .= \Wbhkit\texty('newemail', null, 'New email', 'someone@somewhere.com', 'We will email a login link to this address', 'Must be a valid email', ' required ', 'email');
	$body .= \Wbhkit\submit('Change Email');
	$body .= "</form>";
	return $body;	
}

function change_email($ouid, $newe) {
	$news = get_user_by_email($newe); 
	$olds = get_user_by_id($ouid);
	if ($news) {
		// new student exists, so merge into new
		$sql = "select * from registrations where user_id = ".\Database\mres($ouid);
		$rows = \Database\mysqli($sql) or \Database\db_error();
		while ($row = mysqli_fetch_assoc($rows)) {
			
			//does new email already have this registation?
			$sql2 = "select * from registrations where user_id = ".\Database\mres($news['id'])." and workshop_id = ".\Database\mres($row['workshop_id']);
			$rows2 = \Database\mysqli($sql2) or \Database\db_error();
			if (mysqli_num_rows($rows2) == 0) {
				$sql3 = "update registrations set user_id = ".\Database\mres($news['id'])." where workshop_id = ".\Database\mres($row['workshop_id'])." and user_id = ".\Database\mres($ouid);
				
				\Database\mysqli($sql3) or \Database\db_error();
			}
		}
		
		// copy text preferences from old id
		$sql = "update users set send_text = ".\Database\mres($olds['send_text']).", carrier_id = ".\Database\mres($olds['carrier_id']).", phone = '".\Database\mres($olds['phone'])."' where id = ".\Database\mres($news['id']);
		\Database\mysqli($sql3) or \Database\db_error();
		
		
		// update records in change log
		$sql = "udpate status_change_log set user_id = ".\Database\mres($news['id'])." where user_id = ".\Database\mres($olds['id']);
		\Database\mysqli($sql) or \Database\db_error();
		
		delete_student($ouid);
		return true;
	} else {
		// new email is not yet a student, so just rename old
		$sql = "update users set email = '".\Database\mres($newe)."' where id = '".\Database\mres($ouid)."'";
		\Database\mysqli($sql) or \Database\db_error();
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
	$body .= \Wbhkit\hidden('uid', $u['id']);
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

	// $u must include $carrier_id, $phone, $send_text
	$carrier_id = $u['carrier_id'];
	$phone = $u['phone'];
	$phone = preg_replace('/\D/', '', $phone); // just numbers for phone
	$send_text = $u['send_text'];

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
		$sql = sprintf("update users set send_text = %u, phone = '%s', carrier_id = %u where id = %u",
		\Database\mres($send_text),
		\Database\mres($phone),
		\Database\mres($carrier_id),
		\Database\mres($u['id']));
		\Database\mysqli($sql) or \Database\db_error();
		$u = get_user_by_id($u['id']); // updated so the form is correctly populated on refill
		$message = 'Preferences updated!';
		return true;
	}

}
