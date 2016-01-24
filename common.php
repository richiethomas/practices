<?php
date_default_timezone_set ( 'America/Los_Angeles' );
session_start();
include 'wbh_common.php';
include 'time_difference.php';

define('DEBUG_MODE', false);
define('URL', "http://{$_SERVER['HTTP_HOST']}/practices/");
define('WEBMASTER', "will@willhines.net");
ini_set('sendmail_from','will@willhines.net'); 

$statuses = wbh_get_statuses();

define('ENROLLED', wbh_find_status_by_value('enrolled'));
define('WAITING', wbh_find_status_by_value('waiting'));
define('DROPPED', wbh_find_status_by_value('dropped'));
define('INVITED', wbh_find_status_by_value('invited'));

$late_hours = '12';
$carriers = array();

// users
function wbh_get_user_by_email($email) {
	$sql = "select u.* from users u where email = '".mres($email)."'";
	$rows = wbh_mysqli( $sql) or wbh_db_error();
	while ($row = mysqli_fetch_assoc($rows)) {
		return wbh_add_extra_user_info($row);
	}
	return false;
}

function wbh_get_user_by_id($id) {
	$sql = "select u.* from users u where u.id = ".mres($id);
	$rows = wbh_mysqli( $sql) or wbh_db_error();
	while ($row = mysqli_fetch_assoc($rows)) {
		return wbh_add_extra_user_info($row);
	}
	return false;
}

function wbh_add_extra_user_info($row) {	
	// expecting variable $row which is a row of table 'user'
	$row['ukey'] = wbh_check_key($row['ukey'], $row['id']);
	return $row;
}

function wbh_current_key() {
	global $key;
	if (isset($_REQUEST['key']) && $_REQUEST['key']) {
		$key = $_REQUEST['key'];
		$_SESSION['s_key'] = $key;
	} elseif (isset($_SESSION['s_key']) and $_SESSION['s_key']) {
		$key = $_SESSION['s_key'];
	}
	return $key;
}

function wbh_check_key($key, $uid) {
	if ($key) { 
		return $key;
	} else {
		return wbh_get_key($uid); 
	}
}

function wbh_verify_key($passed, $true, &$error, $show_error = 1) {
	global $u;
	if ($passed != $true) {
		if ($show_error) {
			$error = "Hmmm. I can't verify that you are who you say you are. Want me to email you a fresh link? ".wbh_get_trans_form();
		}
		return false;
	} else {
		return true;
	}
}

function wbh_gen_key($uid) {
	$key = substr(md5(uniqid(mt_rand(), true)), 0, 16);
	$sql = "update users set ukey = '".mres($key)."' where id = ".mres($uid);
	wbh_mysqli( $sql) or wbh_db_error();
	$_SESSION['s_key'] = $key;
	return $key;
}

function wbh_get_key($uid) {
	$sql = "select ukey from users where id = ".mres($uid);
	$rows = wbh_mysqli( $sql) or wbh_db_error();
	while ($row = mysqli_fetch_assoc($rows)) {
		if ($row['ukey']) { return $row['ukey']; }
	}
	return wbh_gen_key($u['id']);
}

function wbh_key_to_user($key) {
	$sql = "select id from users where ukey = '".mres($key)."'";
	$rows = wbh_mysqli( $sql) or wbh_db_error();
	while ($row = mysqli_fetch_assoc($rows)) {
		return wbh_get_user_by_id($row['id']);
	}
	return false;
}


function wbh_make_user($email) {
	$db = wh_set_db_link();
	if (wbh_validate_email($email)) {
		$sql = "insert into users (email, joined) VALUES ('".mres($email)."', '".date("Y-m-d H:i:s")."')";
		$rows = wbh_mysqli( $sql) or wbh_db_error();
		$key = wbh_gen_key(mysqli_insert_id ( $db ));
		return wbh_get_user_by_email($email);
	} else {
		return false;
	}
}

function wbh_validate_email($emailaddress) {
	$pattern = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';

	if (preg_match($pattern, $emailaddress) === 1) {
		return true;
	} else {
		return false;
	}
}


function wbh_get_trans_form() {
	global $sc, $email;
	return "<form class='form-inline' action='$sc' method='post'>\n".
	wbh_hidden('ac', 'link').
	wbh_texty('email', $email, 0, 'Email').
	wbh_submit('log in').
	"</form>";
}

function wbh_email_link($u) {
		if (!isset($u['id'])) {
			return false;
		}
		$key = wbh_get_key($u['id']);
		$trans = URL."index.php?key=$key";
		$transcripts = wbh_get_transcript($u);

		if (count($transcripts) == 0) {
			$point = "Use this link to log in:";
		} else {
			$point = "You have taken ".count($transcripts)." practices. Click below to go to the site:";
		}

		$body = "You are: {$u['email']}

$point
{$trans}

".wbh_email_footer();

		return mail($u['email'], "Log in to 'Will Hines practices'", $body, "From: ".WEBMASTER);
}


function wbh_logged_in() {
	global $u, $key;
	if (isset($u) && $u && wbh_verify_key($key, $u['ukey'], $error, 0)) {
		return true;
	} else {
		return false;
	}
}

function wbh_find_students($needle = 'everyone', $sort = 'n') {
	
	if ($sort != 'n' && $sort != 't' && $sort != 'd') {
		$sort = 'n';
	}
	$order_by = array('n' => 'a.email', 't' => 'classes desc', 'd' => 'a.joined desc');
	
	$where = '';
	if ($needle != 'everyone') {
		$where = "where a.email like '%".mres($needle)."%'";
	}
	
	$sql = "SELECT a.id, a.email, COUNT(b.id) AS 'classes', a.joined  
	FROM 
		users a 
	   LEFT JOIN
	   (SELECT id, user_id FROM registrations) b
	   ON a.id = b.user_id
	   $where
	group by a.email
	order by ".$order_by[$sort];
	
	$rows = wbh_mysqli( $sql) or wbh_db_error();
	$stds = array();
	while ($row = mysqli_fetch_assoc($rows)) {
		$stds[$row['id']] = $row;
	}
	return $stds;
}

function wbh_change_email($ouid, $newe) {
	$news = wbh_get_user_by_email($newe); 
	$olds = wbh_get_user_by_id($ouid);
	if ($news) {
		// new student exists, so merge into new
		$sql = "select * from registrations where user_id = ".mres($ouid);
		$rows = wbh_mysqli($sql) or wbh_db_error();
		while ($row = mysqli_fetch_assoc($rows)) {
			
			//does new email already have this registation?
			$sql2 = "select * from registrations where user_id = ".mres($news['id'])." and workshop_id = ".mres($row['workshop_id']);
			$rows2 = wbh_mysqli($sql2) or wbh_db_error();
			if (mysqli_num_rows($rows2) == 0) {
				$sql3 = "update registrations set user_id = ".mres($news['id'])." where workshop_id = ".mres($row['workshop_id'])." and user_id = ".mres($ouid);
				
				wbh_mysqli($sql3) or wbh_db_error();
			}
		}
		
		// copy text preferences from old id
		$sql = "update users set send_text = ".mres($olds['send_text']).", carrier_id = ".mres($olds['carrier_id']).", phone = '".mres($olds['phone'])."' where id = ".mres($news['id']);
		wbh_mysqli($sql3) or wbh_db_error();
		
		
		// update records in change log
		$sql = "udpate status_change_log set user_id = ".mres($news['id'])." where user_id = ".mres($olds['id']);
		wbh_mysqli($sql) or wbh_db_error();
		
		wbh_delete_student($ouid);
		return true;
	} else {
		// new email is not yet a student, so just rename old
		$sql = "update users set email = '".mres($newe)."' where id = '".mres($ouid)."'";
		wbh_mysqli($sql) or wbh_db_error();
		return true;
	}
	return true;
}


function wbh_delete_student($uid = 0) {
	if (!$uid) {
		return false;
	}
	$sql = "delete from registrations where user_id = ".mres($uid);
	wbh_mysqli($sql) or wbh_db_error();
	$sql = "delete from users where id = ".mres($uid);
	wbh_mysqli($sql) or wbh_db_error();
	$sql = "delete from status_change_log where user_id = ".mres($uid);
	wbh_mysqli($sql) or wbh_db_error();
	return true;
	
}

function wbh_get_statuses() {
	global $statuses;
	if (is_array($statuses) && count($statuses) > 0) {
		return $statuses;
	}
	$statuses = array();
	$sql = "select * from statuses order by id";
	$rows = wbh_mysqli($sql) or wbh_db_error();
	while ($row = mysqli_fetch_assoc($rows)) {
		$statuses[$row['id']] = $row['status_name'];
	}
	return $statuses;
	
}

function wbh_find_status_by_value($stname) {
	$statuses = wbh_get_statuses();
	foreach ($statuses as $status_id => $status_name) {
		if ($status_name == $stname) {
			return $status_id;
		}
	}
	return false;
}

function wbh_get_carriers($update = 0) {
	global $carriers;
	if (is_array($carriers) && count($carriers) > 0 && !$update) {
		return $carriers;
	}
	$carriers = array();
	$sql = "select * from carriers order by id";
	$rows = wbh_mysqli($sql) or wbh_db_error();
	while ($row = mysqli_fetch_assoc($rows)) {
		$carriers[$row['id']] = $row;
	}
	return $carriers;
	
}

function wbh_get_carriers_drop() {
	$carriers = wbh_get_carriers();
	$cardrop = '';
	$cardrop[0] = 'No Network';
	foreach ($carriers as $c) {
		$cardrop[$c['id']] = $c['network'];
	}
	return $cardrop;
}


function wbh_edit_text_preferences($u) {
	global $sc, $ac;
	$carriers = wbh_get_carriers_drop();
	$body = '';
	$body .= "<div class='row'><div class='col-md-4'>\n";
	$body .= "<form action='$sc' method='post'>\n";
	$body .= wbh_hidden('uid', $u['id']);
	$body .= wbh_hidden('ac', 'updateu');
	$body .= wbh_checkbox('send_text', 1, 'Send text updates?', $u['send_text']);
	
	// carrier validation
	$error = null;
	if ($ac == 'updateu' && $u['send_text'] == 1 && $u['carrier_id'] == 0) {
		$error = "You must pick a carrier if you want text updates.";
	}
	$body .= wbh_drop('carrier_id', $carriers, $u['carrier_id'], 'phone network', null, $error);

	// phone validation
	if ($ac == 'updateu' && $u['send_text'] == 1 && strlen($u['phone']) != 10) {
		$help = null;
		$error = 'Phone must be 10 digits, no letters or spaces or dashes';
	} else {
		$help = '10 digit phone number';
		$error = null;
	}
	$body .= wbh_texty('phone', $u['phone'], 'phone number', null, $help, $error);

	$body .= wbh_submit('Update Text Preferences');
	$body .= "</form>\n";
	$body .= "</div></div> <!-- end of col and row -->\n";
	
	return $body;
}


function wbh_update_text_preferences(&$u,  &$message, &$error) {


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
		mres($send_text),
		mres($phone),
		mres($carrier_id),
		mres($u['id']));
		wbh_mysqli($sql) or wbh_db_error();
		$u = wbh_get_user_by_id($u['id']); // updated so the form is correctly populated on refill
		$message = 'Preferences updated!';
		return true;
	}

}

// workshops
function wbh_get_workshop_info($id) {
	$sql = "select w.*, l.place, l.lwhere from workshops w LEFT OUTER JOIN locations l on w.location_id = l.id where w.id = ".mres($id);
	$rows = wbh_mysqli( $sql) or wbh_db_error();
	while ($row = mysqli_fetch_assoc($rows)) {
		
		if ($row['when_public'] == 0 ) {
			$row['when_public'] = '';
		}
		$row = wbh_format_workshop_startend($row);		
		$row['enrolled'] = wbh_get_enrollments($id);
		$row['invited'] = wbh_get_enrollments($id, INVITED);
		$row['waiting'] = wbh_get_enrollments($id, WAITING);
		$row['open'] = ($row['enrolled'] >= $row['capacity'] ? 0 : $row['capacity'] - $row['enrolled']);
		if (strtotime($row['start']) < strtotime('now')) { 
			$row['type'] = 'past'; 
		} elseif ($row['enrolled'] >= $row['capacity'] || $row['waiting'] > 0 || $row['invited'] > 0) { 
			$row['type'] = 'soldout'; 
		} else {
			$row['type'] = 'open';
		}
		
		$row = wbh_check_last_minuteness($row);
		
		return $row;
	}
	return false;
}

function wbh_check_last_minuteness($wk) {
	
	/* 
		there's two flags:
			1) workshops have "sold_out_late" meaning the workshop was sold out within $late_hours of the start. We update this to 1 or 0 everytime the web site selects the workshop info from the db.
			2) registrations have a "while_sold_out" flag. if it is set to 1, then you were enrolled in this workshop while it was sold out within $late_hours of its start. we also check this every time we select the workshop info. but this never gets set back to zero. 
	*/ 
			
	global $late_hours;
	$hours_left = (strtotime($wk['start']) - strtotime('now')) / 3600;
	if ($hours_left > 0 && $hours_left < $late_hours) {
		// have we never checked if it's sold out
		if ($wk['sold_out_late'] == -1) {
			if ($wk['type'] == 'soldout') {
				$sql = 'update workshops set sold_out_late = 1 where id = '.mres($wk['id']);
				wbh_mysqli( $sql) or wbh_db_error();
				
				$sql = "update registrations set while_soldout = 1 where workshop_id = ".mres($wk['id'])." and status_id = '".ENROLLED."'";
				wbh_mysqli( $sql) or wbh_db_error();
				
				$wk['sold_out_late'] = 1;
			} else {
				$sql = 'update workshops set sold_out_late = 0 where id = '.mres($wk['id']);
				wbh_mysqli( $sql) or wbh_db_error();
				$wk['sold_out_late'] = 0;
			}
		}
	}
	return $wk;
}


function wbh_get_workshop_info_tabled($id) {
	$wk = wbh_get_workshop_info($id);
	return "<table class=\"table table-striped\">
		<tbody>
		<tr><td>Title:</td><td>{$wk['title']}</tr>
		<tr><td>Description:</td><td>{$wk['notes']}</td></tr>
		<tr><td>When:</td><td>{$wk['when']}</tr>
		<tr><td>Where:</td><td>{$wk['place']} {$wk['lwhere']}</tr>
		<tr><td>Cost:</td><td>{$wk['cost']}</td></tr>
		<tr><td>Open Spots:</td><td>{$wk['open']} (of {$wk['capacity']})</td></tr>
		<tr><td>Waiting:</td><td>".($wk['waiting']+$wk['invited'])."</td></tr>
		</tbody>
		</table>";
}

function wbh_get_workshops_dropdown($start = null, $end = null) {
	$sql = "select w.*, l.place, l.lwhere 
	from workshops w LEFT OUTER JOIN locations l on w.location_id = l.id order by start desc";
	$rows = wbh_mysqli( $sql) or wbh_db_error();
	$workshops = array();
	while ($row = mysqli_fetch_assoc($rows)) {
		$row = wbh_format_workshop_startend($row);
		$workshops[$row['id']] = $row['showtitle'];
	}
	return $workshops;
}

function wbh_friendly_time($time_string) {
	$ts = strtotime($time_string);
	$minutes = date('i', $ts);
	if ($minutes == 0) {
		return date('ga', $ts);
	} else {
		return date('g:ia', $ts);
	}
}

function wbh_friendly_date($time_string) {
	$now_doy = date('z'); // day of year
	$wk_doy = date('z', strtotime($time_string)); // workshop day of year
	
	if ($wk_doy - $now_doy < 7) {
		return date('L', strtotime($time_string)); // Monday, Tuesday, Wednesday
	} elseif (date('Y', strtotime($time_string)) != date('Y')) {  
		return date('D M j, Y', strtotime($time_string));
	} else {
		return date('D M j', strtotime($time_string));
	}
}	
	
	

// pass in the workshop row as it comes from the database table
// add some columns with date / time stuff figured out
function wbh_format_workshop_startend($row) {	
	if (date('Y', strtotime($row['start'])) != date('Y')) {
		$row['showstart'] = date('D M j, Y - g:ia', strtotime($row['start']));
	} else {
		$row['showstart'] = date('D M j - g:ia', strtotime($row['start']));
	}
	$row['showend'] = wbh_friendly_time($row['end']);
	$row['friendly_when'] = wbh_friendly_date($row['start']).' '.wbh_friendly_time($row['start']);
	$row['showtitle'] = "{$row['title']} - {$row['showstart']}-{$row['showend']}";
	$row['when'] = "{$row['showstart']}-{$row['showend']}";
	
	return $row;
}

function wbh_get_workshops_list($admin = 0) {
	global $sc;
	$sql = 'select w.*, l.place, l.lwhere 
	from workshops w LEFT OUTER JOIN locations l on w.location_id = l.id ';
	$sql .= $admin ? " order by start desc" : " order by start asc";
	$rows = wbh_mysqli( $sql) or wbh_db_error();
	$body = "<table class='table table-striped'><thead><tr>
		<th width='500'>Title</th>
		<th>When</th>
		<th>Where</th>
		<th>Cost</th>
		<th>Spots</th>
		<th>Action</th>
		</tr></thead><tbody>\n";

	$i = 0;
	while($row = mysqli_fetch_assoc($rows)) {
		$wk = wbh_get_workshop_info($row['id']);

		if ($wk['type'] == 'past' && !$admin) { continue; }
		if (strtotime($wk['when_public']) > time() && !$admin) {
			continue;
		}
		
		$public = '';
		if ($admin && $wk['when_public']) {
			$public = "<br><small>Public: ".date('D M j - g:ia', strtotime($wk['when_public']))."</small>\n";
		}	
			
		$i++;
		
		if ($wk['type'] == 'soldout') {
			$cl = 'error';
		} elseif ($wk['type'] == 'open') {
			$cl = 'success';
		} elseif ($wk['type'] == 'past') {
			$cl = 'muted';
		} else  {
			$cl = '';
		}
		
		
		
		$body .= "<tr class='$cl'>";
		$titlelink = ($admin ? "<a href='$sc?wid={$row['id']}&v=ed'>{$wk['title']}</a>" : $wk['title']);
		$body .= "<td>{$titlelink}".($wk['notes'] ? "<p class='small text-muted'>{$wk['notes']}</p>" : '')."</td>
		<td>{$wk['when']}{$public}</td>
		<td>{$wk['place']}</td>
		<td>{$wk['cost']}</td>
		<td>".number_format($wk['open'], 0)." of ".number_format($wk['capacity'], 0).",<br> ".number_format($wk['waiting']+$wk['invited'])." waiting</td>
";
		if ($admin) {
			$body .= "<td><a href=\"$sc?wid={$row['id']}\">Clone</a></td></tr>\n";
		} else {
			$call = ($wk['type'] == 'soldout' ? 'Join Wait List' : 'Enroll');
			$body .= "<td><a href=\"{$sc}?wid={$row['id']}&ac=enroll\">{$call}</a></td></tr>\n";
		}
	}
	if (!$i) {
		return "<p>No upcoming workshops!</p>\n";
	}
	$body .= "</tbody></table>\n";
	return $body;
}


function wbh_get_workshops_list_raw($start = null, $end = null) {
	$sql = "select w.*, l.place, l.lwhere 
	from workshops w LEFT OUTER JOIN locations l on w.location_id = l.id WHERE 1 = 1 ";
	if ($start) {
		$sql .= " and w.start >= '".date('Y-m-d H:i:s', strtotime($start))."'";
	}
	if ($end) {
		$sql .= " and w.end <= '".date('Y-m-d H:i:s', strtotime($end))."'";
	}
	$sql .= " order by start desc";
	$rows = wbh_mysqli( $sql) or wbh_db_error();
	$workshops = array();
	while ($row = mysqli_fetch_assoc($rows)) {
		$row['showstart'] = date('D M j - g:ia', strtotime($row['start']));
		$row['showend'] = date('g:ia', strtotime($row['end']));		
		$row['showtitle'] = "{$row['title']} - {$row['showstart']}-{$row['showend']}";
		$workshops[$row['id']] = $row;
	}
	return $workshops;
}

// registrations
function wbh_get_enrollments($id, $status_id = ENROLLED) {
	$sql = "select count(*) as total from registrations where workshop_id = ".mres($id)." and status_id = '".mres($status_id)."'";
	$rows = wbh_mysqli( $sql) or wbh_db_error();
	while ($row = mysqli_fetch_assoc($rows)) {
		return $row['total'];
	}
	return 0;
}

function wbh_get_an_enrollment($wk, $u) {
	$statuses = wbh_get_statuses();
	$sql = "select r.* from registrations r where r.workshop_id = ".mres($wk['id'])." and user_id = ".mres($u['id']);
	$rows = wbh_mysqli( $sql) or wbh_db_error($sql);
	while ($row = mysqli_fetch_assoc($rows)) {
		$sql2 = "select r.* from registrations r where r.workshop_id = ".mres($wk['id'])." and r.status_id = '".mres($row['status_id'])."' order by last_modified";
		$rows2 = wbh_mysqli( $sql2) or wbh_db_error();
		$i = 1;
		while ($row2 = mysqli_fetch_assoc($rows2)) {
			if ($row2['id'] == $row['id']) {
				break;
			}
			$i++;
		}
		$row['rank'] = $i;
		$row['status_name'] = $statuses[$row['status_id']];
		return $row;
	}
	return false;
}

function wbh_handle_enroll($wk, $u, $email, $confirm = true) {
	global $error;
	if (!$wk) {
		$error = 'The workshop ID was not passed along.';
		return false;
	}
	if (!$u) {
		if (wbh_validate_email($email)) {
			$u = wbh_make_user($email);
		} else {
			$error = "I think that is not a valid email.";
			return false;
		}
	}
	if (!$email) {
		$email = $u['email'];
	}
	$before = wbh_get_an_enrollment($wk, $u); // if they were already enrolled
	$status_id = wbh_enroll($wk, $u);
	$keyword = '';
	if ($status_id == ENROLLED) {
		if (!$before) {
			$keyword = 'has been';
		} elseif ($before['status_id'] == ENROLLED) {
			$keyword = 'is still';
		} else {
			$keyword = 'is now';
		}
		$message = "'{$email}' $keyword enrolled in '{$wk['title']}'!";
	} elseif ($status_id == WAITING) {
		if (!$before) {
			$keyword = 'has been added to';
		} elseif ($before['status_id'] == WAITING) {
			$keyword = 'is still on';
		} else {
			$keyword = 'is now on';
		}		
		$message = "This practice is full. '{$email}' $keyword the waiting list.";
	} elseif ($status_id == 'already') {
		$message = "'{$email}' has already been registered.";
	} else {
		$message = "Not sure what happened. Tried to enroll and got this status id: ".$status_id;
	}		
	if ($confirm) { wbh_confirm_email($wk, $u, $status_id); }
	if (DEBUG_MODE) {
		mail(WEBMASTER, $message, $message, "From: ".WEBMASTER);
	}
	return $message;
}


function wbh_enroll($wk, $u) {
	$wid = $wk['id'];
	$uid = $u['id'];
	
	// is this person already registered? then we do different things depending on current status
	$sql = "select  * from registrations where workshop_id = ".mres($wid)." and user_id = ".mres($uid);
	$rows = wbh_mysqli( $sql) or wbh_db_error();
	while ($row = mysqli_fetch_assoc($rows)) {
		switch($row['status_id']) {
			case ENROLLED:
				return 'already';
				break;
			case WAITING:
				return WAITING;
				break;
			case DROPPED:
				if (($wk['enrolled']+$wk['invited']+$wk['waiting']) < $wk['capacity']) {
					wbh_change_status($wk, $u, ENROLLED, true);
					return ENROLLED;
				} else {
					wbh_change_status($wk, $u, WAITING, true);
					return WAITING;
				} 
				break;
			case INVITED:
				wbh_change_status($wk, $u, ENROLLED, true);
				return ENROLLED;
				break;
			default:
				wbh_change_status($wk, $u, ENROLLED, true);
				return ENROLLED;
				break;	
		}
	}
	
	// if we haven't returned, then there was no registration. make a new registration
	if (($wk['enrolled']+$wk['invited']) < $wk['capacity'] && $wk['waiting'] == 0) {
		$status_id = ENROLLED;
	} else {
		$status_id = WAITING;
	}

	$sql = sprintf("INSERT INTO registrations (workshop_id, user_id, status_id, registered, last_modified) VALUES (%u, %u, '%s', '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."')",
		mres($wid),
		mres($uid),
		mres($status_id));
	wbh_mysqli( $sql) or wbh_db_error();
	
	wbh_update_change_log($wk, $u, $status_id); 

	return $status_id;
}


// this checks for open spots, and makes sure invites have gone out to anyone on waiting list
// i call this in places just to make sure i haven't neglected the waiting list
function wbh_check_waiting($wk) {
	$wk = wbh_get_workshop_info($wk['id']); // make sure it's up to date
	$msg = '';
	if ($wk['type'] == 'past') {
		return 'Workshop is in the past';
	}
	while (($wk['enrolled']+$wk['invited']) < $wk['capacity'] && $wk['waiting'] > 0) {
		$sql = "select * from registrations where workshop_id = ".mres($wk['id'])." and status_id = '".WAITING."' order by last_modified limit 1";
		$rows = wbh_mysqli( $sql) or wbh_db_error();
		while ($row = mysqli_fetch_assoc($rows)) {
			$u = wbh_get_user_by_id($row['user_id']);
			$msg .= wbh_change_status($wk, $u, INVITED, true);
		}
		$wk = wbh_get_workshop_info($wk['id']); //update lists
	}
	if ($msg) { return $msg; }
	return "No invites sent.";
}

function wbh_next_waiting($wk) {
	$sql = "select * from registrations where workshop_id = ".mres($wk['id'])." and status_id = '".WAITING."' order by last_modified limit 1";
	$rows = wbh_mysqli( $sql) or wbh_db_error();
	while ($row = mysqli_fetch_assoc($rows)) {
		return wbh_get_user_by_id($row['user_id']);
	}
	return false;
}

function wbh_update_attendance($wid, $uid, $attended = 1) {
	$sql = "update registrations set attended = ".mres($attended)." where workshop_id = ".mres($wid)." and user_id = ".mres($uid);
	//echo "$sql<br>\n";
	wbh_mysqli( $sql) or wbh_db_error();
	return "Updated user ($uid) workshop ($wid) to attended: $attended";
}

function wbh_change_status($wk, $u, $status_id = ENROLLED, $confirm = true) {
	
	$e = wbh_get_an_enrollment($wk, $u);
	$statuses = wbh_get_statuses();
	if ($e['status_id'] != $status_id) {
		$sql = "update registrations set status_id = '".mres($status_id)."',  last_modified = '".date("Y-m-d H:i:s")."' where workshop_id = ".mres($wk['id'])." and user_id = ".mres($u['id']);
		wbh_mysqli( $sql) or wbh_db_error();
		wbh_update_change_log($wk, $u, $status_id);	
	}
	
	if ($confirm) { wbh_confirm_email($wk, $u, $status_id); }
	$return_msg = "Updated user ({$u['email']}) to status '{$statuses[$status_id]}' for {$wk['showtitle']}.";
	if (DEBUG_MODE) {
		mail(WEBMASTER, "{$u['email']} now '{$statuses['status_id']}' for '{$wk['showtitle']}'", $return_msg, "From: ".WEBMASTER);
	}
	
	return $return_msg;
}

function wbh_update_change_log($wk, $u, $status_id) {
	if (!$wk['id'] || !$u['id'] || !$status_id) {
		return false;
	}
	$sql = sprintf("insert into status_change_log (workshop_id, user_id, status_id, happened) VALUES (%u, %u, %u, '%s')",
	mres ($wk['id']),
	mres ($u['id']),
	mres ($status_id),
	date('Y-m-d H:i:s', time()));
	wbh_mysqli($sql) or wbh_db_error();
	return true;
}

function wbh_get_status_change_log($wk = null) {

	global $sc;
	$sql = "select s.*, u.email, st.status_name, wk.title, wk.start, wk.end from status_change_log s, users u, statuses st, workshops wk where";
	if ($wk) { 
		$sql .= " workshop_id = ".mres($wk['id'])." and "; 
	}
	$sql .= " s.workshop_id = wk.id and s.user_id = u.id and s.status_id = st.id order by happened desc";

	$rows = wbh_mysqli($sql) or wbh_db_error();
	$log = '';
	while ($row = mysqli_fetch_assoc($rows)) {
		$row = wbh_format_workshop_startend($row);
		$wkname = '';
		if (!$wk) {
			// skip old ones for the global change log
			if (strtotime($row['start']) < strtotime("24 hours ago")) {
				continue;
			}
			$wkname = "<a href='$sc?v=ed&wid={$row['workshop_id']}'>{$row['title']}</a><br><small>{$row['showstart']}</small></td><td>";
		}
		$log .= "<tr><td>{$row['email']}</td><td>$wkname{$row['status_name']}</td><td><small>".date('j-M-y g:ia', strtotime($row['happened']))."</small></td></tr>\n";
	}
	if (!$log) {
		$log = 'No recorded updates.';
	} else {
		$log = "<table class='table'>$log</table>\n";
	}
	return $log;
}

function wbh_get_students($wid, $status_id = ENROLLED) {
	$sql = "select u.*, r.status_id,  r.attended, r.registered, r.last_modified  from registrations r, users u where r.workshop_id = ".mres($wid);
	if ($status_id) { $sql .= " and status_id = '".mres($status_id)."'"; }
	$sql .= " and r.user_id = u.id order by last_modified";
	$rows = wbh_mysqli( $sql) or wbh_db_error();
	$stds = array();
	while ($row = mysqli_fetch_assoc($rows)) {
		$stds[$row['id']] = $row;
	}
	return $stds;
}

// internal function to sort by email field
function wbh_sort_by_email($a, $b) {
    return strcasecmp($a["email"], $b["email"]);
}

function wbh_list_students($wid, $status_id = ENROLLED) {
	global $sc;
	$stds = wbh_get_students($wid, $status_id);
	$body = '';
	$es = '';
	foreach ($stds as $uid => $s) {
		$s['ukey'] = wbh_check_key($s['ukey'], $uid);
		$body .= "<div class='row'><div class='col-md-6'><a href='admin.php?v=astd&uid={$s['id']}&wid={$wid}'>{$s['email']}</a> ".($s['attended'] ? '(attended)' : '')."<small>".date('M j g:ia', strtotime($s['last_modified']))."</small></div>".
		"<div class='col-md-6'>
		<a class='btn btn-primary' href='$sc?v=cs&wid={$wid}&uid={$uid}'>change status</a> <a class='btn btn-danger' href='$sc?v=rem&uid={$uid}&wid={$wid}'>remove</a></div>".
		"</div>\n";
		//$body .= "<div class='row'>&nbsp;</div>\n";
		$es .= "{$s['email']}\n";
	}
	return $body;
}

function wbh_confirm_email($wk, $u, $status_id = ENROLLED) {
	$statuses = wbh_get_statuses();
	if (!isset($u['key']) || !$u['key']) {
		$key = wbh_get_key($u['id']);
	} else {
		$key = $u['key'];
	}
	$e = wbh_get_an_enrollment($wk, $u); 
	$drop = URL."index.php?key=$key&ac=drop&wid={$wk['id']}";
	$trans = URL."index.php?key=$key&wid={$wk['id']}";
	$accept = URL."index.php?ac=accept&wid={$wk['id']}&key=$key";
	$decline = URL."index.php?ac=decline&wid={$wk['id']}&key=$key";
	$enroll = URL."index.php?key=$key&ac=enroll&wid={$wk['id']}";
	$textpref = URL."index.php?key=$key&v=text";
	$call = '';
	$late = '';
		
	if ($e['while_soldout']) { 
		$message .= '<br><br>'.wbh_get_dropping_late_warning();
	}
	
	
	$send_faq = false;
	switch ($status_id) {
		case 'already':
		case ENROLLED:
			$sub = "ENROLLED: {$wk['showtitle']}";
			$point = "You are ENROLLED in {$wk['showtitle']}.";
			$call = "To DROP, click here:\n{$drop}";
			if ($wk['cost'] > 0) {
				$call .= "\n\nPay in person or venmo. On the day of the workshop is fine. Venmo link:\nhttp://venmo.com/willhines?txn=pay&share=friends&amount={$wk['cost']}&note=improv%20workshop";
			}
			$send_faq = true;
			break;
		case WAITING:
			$sub = "WAIT LIST: {$wk['showtitle']}";
			$point = "You are wait list spot {$e['rank']} for {$wk['showtitle']}:";
			$call = "To DROP, click here:\n{$drop}";
			break;
		case INVITED:
			$sub = "INVITED: {$wk['showtitle']}";
			$point = "A spot opened in {$wk['showtitle']}:";
			$call = "To ACCEPT, click here:\n{$accept}\n\nTo DECLINE, click here:\n{$decline}";
			break;
		case DROPPED:
			$sub = "DROPPED: {$wk['showtitle']}";
			$point = "You have dropped out of {$wk['showtitle']}";
			if ($e['while_soldout'] == 1) {
				$late .= "\n".wbh_get_dropping_late_warning();
			}
			$call = "If you change your mind, re-enroll here:\n{$enroll}";
			break;
		default:
			$sub = "{$statuses[$status_id]}: {$wk['showtitle']}";
			$point = "You are a status of '{$statuses[$status_id]}' for {$wk['showtitle']}";
			break;
	}

	$text = '';
	if ($u['send_text']) {
		$textmsg = $point.' for more info: '.wbh_shorten_link($trans);
		wbh_send_text($u, $textmsg);
	}
	
	$notifcations = '';
	if (!$u['send_text']) {
		$notifications = "\nWould you want to be notified via text? You can set text preferences:\n".$textpref;
	}

	$body = "You are: {$u['email']}

$point $late
$notifications

Title: {$wk['title']}
Description: {$wk['notes']}
When: {$wk['when']}
Where: {$wk['place']} {$wk['lwhere']}
Cost: {$wk['cost']}

$call

".wbh_email_footer($send_faq);	
	
	return mail($u['email'], $sub, $body, "From: ".WEBMASTER);
}


function wbh_send_text($u, $msg) {
	if (!$u['send_text'] || !$u['carrier_id'] || !$u['phone'] || strlen($u['phone']) != 10) {
		return false;
	}
	$carriers = wbh_get_carriers();
	$to = $u['phone'].'@'.$carriers[$u['carrier_id']]['email'];	
	$mailed=  mail($to, '', $msg, "From: ".WEBMASTER);
	return $mailed;
}


function wbh_shorten_link($link) {
	
	// bit.ly registered token is: 70cc52665d5f7df5eaeb2dcee5f1cdba14f5ec94
	// under whines@gmail.com / meet1962
	
	//tempoary while working locally
	$link = preg_replace('/localhost:8888/', 'www.willhines.net', $link);
	$link = urlencode($link);
	$response = file_get_contents("https://api-ssl.bitly.com/v3/shorten?access_token=70cc52665d5f7df5eaeb2dcee5f1cdba14f5ec94&longUrl={$link}&format=txt");
	return $response;
	
}

function wbh_get_dropping_late_warning() {
	global $late_hours;
	return "NOTE: You are dropping within {$late_hours} hours of the start, and there was a waiting list. If I can't get someoen to take your spot, I might ask you to pay anyway.";
	
}


function wbh_get_transcript_tabled($u, $admin = false) {
	global $key;
	$statuses = wbh_get_statuses();
	$transcripts = wbh_get_transcript($u);
	if (count($transcripts) == 0) {
		return "<p>You have not taken any practices! Which is fine, but that's why this list is empty.</p>\n";
	}

	$body = '';
	$body .= "<table class='table table-striped'><thead><tr><th>Title</th><th>When</th><th>Where</th><th>Status</th><th>Action</th></tr></thead>\n";
	$body .= "<tbody>";
	
	foreach ($transcripts as $t) {
		$wk = wbh_get_workshop_info($t['workshop_id']);
		$e = wbh_get_an_enrollment($wk, $u); 
		if ($wk['type'] == 'past') {
			$cl = 'muted';
		} elseif ($t['status_id'] == ENROLLED) {
			$cl = 'success';
		} else {
			$cl = 'warning';
		}

		$body .= "<tr class='$cl'><td>";
		if ($admin) {
			$body .= "<a href=\"admin.php?wid={$t['workshop_id']}&v=ed\">{$t['title']}</a>";
		} else {
			$body .= $t['title'];
		}
		$body .= "</td><td>{$wk['when']}</td><td>{$t['place']}</td><td>";
		$body .= "{$statuses[$t['status_id']]}";
		if ($t['status_id'] == WAITING) {
			$body .= " (spot {$e['rank']})";
		}
		$body .= "</td><td><a href='index.php?v=view&wid={$t['workshop_id']}'>More Info</a></td></tr>\n";
	}
	$body .= "</tbody></table>\n";
	return $body;
}

function wbh_get_transcript($u) {
	$statuses = wbh_get_statuses();
	$sql = "select * from registrations r, workshops w, locations l where r.workshop_id = w.id and w.location_id = l.id and r.user_id = ".mres($u['id'])." order by w.start desc";
	$rows = wbh_mysqli( $sql) or wbh_db_error();
	$transcripts = array();
	while ($row = mysqli_fetch_assoc($rows)) {
		$row['status_name'] = $statuses[$row['status_id']];
		$transcripts[] = $row;
	}
	return $transcripts;
}

function wbh_drop_session($wk, $u) {
	$sql = sprintf('delete from registrations where workshop_id = %u and user_id = %u',
		mres($wk['id']),
		mres($u['id']));
	wbh_mysqli( $sql) or wbh_db_error();
	wbh_check_waiting($wk);
	return true;
}

function wbh_email_footer($faq = false) {

	$faqadd = '';
	if ($faq) {
		$faqadd = strip_tags(wbh_get_faq());
	}
	return "
$faqadd
		
Thanks!
		
-Will Hines
HQ: 1948 Hillhurst Ave. Los Angeles, CA 90027
";
}

function wbh_get_faq() {
	
return "<h2>Questions</h2>
<dl>
<dt>Can I drop out?</dt>
<dd>Yes, use the link in your confirmation email to go to the web site, where you can drop out.</dd>

<dt>If there is a cost, how should I pay?</dt>
<dd>In cash, at the practice. Or Venmo it to whines@gmail.com
Venmo link: http://venmo.com/willhines?txn=pay&share=friends&note=improv%20workshop</dd>

<dt>What if I'm on a waiting list?</dt>
<dd>You'll get an email the moment a spot opens up, with a link to ACCEPT or DECLINE.</dd>

<dt>What's the late policy? Or the policy on leaving early?</dt>
<dd>Arriving late or leaving early is fine. If you're late I might ask you to wait to join in until I say so.</dd>

<dt>What levels?</dt>
<dd>Anyone can sign up. The description may recommend a level but I won't enforce it.</dd>
</dl>";
}

// locations

function wbh_get_locations() {
	$sql = "select * from locations";
	$rows = wbh_mysqli( $sql) or wbh_db_error();
	$locations = array();
	while ($row = mysqli_fetch_assoc($rows)) {
		$locations[$row['id']]['place'] = $row['place'];
		$locations[$row['id']]['lwhere'] = $row['lwhere'];
	}
	return $locations;
}

function wbh_locations_drop($lid = null) {
	$l = wbh_get_locations();
	$opts = array();
	foreach ($l as $id => $info) {
		$opts[$id] = $info['place'];
	}
	return wbh_drop('lid', $opts, $lid, 'Location');
}


function wbh_mysqli($sql) {
	$db = wh_set_db_link();
	$rows = mysqli_query($db, $sql) or wbh_db_error();	
	return $rows;
}


