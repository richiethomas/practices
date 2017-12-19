<?php
namespace Enrollments;	

// registrations
function get_enrollments($id, $status_id = ENROLLED) {
	$sql = "select count(*) as total from registrations where workshop_id = ".\Database\mres($id)." and status_id = '".\Database\mres($status_id)."'";
	$rows = \Database\mysqli( $sql) or \Database\db_error();
	while ($row = mysqli_fetch_assoc($rows)) {
		return $row['total'];
	}
	return 0;
}

function get_an_enrollment($wk, $u) {
	$statuses = \Lookups\get_statuses();
	$sql = "select r.* from registrations r where r.workshop_id = ".\Database\mres($wk['id'])." and user_id = ".\Database\mres($u['id']);


	$rows = \Database\mysqli( $sql) or \Database\db_error($sql);
	while ($row = mysqli_fetch_assoc($rows)) {
		$sql2 = "select r.* from registrations r where r.workshop_id = ".\Database\mres($wk['id'])." and r.status_id = '".\Database\mres($row['status_id'])."' order by last_modified";
		$rows2 = \Database\mysqli( $sql2) or \Database\db_error();
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


// figures what the user should be
// figures if there's been a previous registration
function handle_enroll($wk, $u, $confirm = true) {
	global $error;
	
	// check incoming data
	if (!$wk) {
		$error = 'The workshop ID was not passed along.';
		return false;
	}
	if (!$u && !$email) {
		$error = 'We need a logged in user or an email.';
		return false;
	}

	$before = get_an_enrollment($wk, $u);  // take note of 'before' enrollment
	$status_id = enroll($wk, $u); // actually enroll them (or update enrollment)
	
	
	// finicky confirmation message
	$keyword = '';
	if ($status_id == ENROLLED) {
		if (!$before) {
			$keyword = 'has been';
		} elseif ($before['status_id'] == ENROLLED) {
			$keyword = 'is still';
		} else {
			$keyword = 'is now';
		}
		$message = "'{$u['nice_name']}' $keyword enrolled in '{$wk['title']}'!";
	} elseif ($status_id == WAITING) {
		if (!$before) {
			$keyword = 'has been added to';
		} elseif ($before['status_id'] == WAITING) {
			$keyword = 'is still on';
		} else {
			$keyword = 'is now on';
		}		
		$message = "This practice is full. '{$u['nice_name']}' $keyword the waiting list.";
	} elseif ($status_id == 'already') {
		$message = "'{$u['nice_name']}' has already been registered.";
	} else {
		$message = "Not sure what happened. Tried to enroll and got this status id: ".$status_id;
	}		
	if ($confirm) { \Emails\confirm_email($wk, $u, $status_id); }
	return $message;
}

// enrolls
// first figures if the person is already enrolled
function enroll($wk, $u) {
	$wid = $wk['id'];
	$uid = $u['id'];
	
	// if person is already registered
	$sql = "select  * from registrations where workshop_id = ".\Database\mres($wid)." and user_id = ".\Database\mres($uid);
	$rows = \Database\mysqli( $sql) or \Database\db_error();
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
					change_status($wk, $u, ENROLLED, true);
					return ENROLLED;
				} else {
					change_status($wk, $u, WAITING, true);
					return WAITING;
				} 
				break;
			case INVITED:
				change_status($wk, $u, ENROLLED, true);
				return ENROLLED;
				break;
			default:
				change_status($wk, $u, ENROLLED, true);
				return ENROLLED;
				break;	
		}
	}
	
	// if not registered
	if (($wk['enrolled']+$wk['invited']) < $wk['capacity'] && $wk['waiting'] == 0) {
		$status_id = ENROLLED;
	} else {
		$status_id = WAITING;
	}

	$sql = sprintf("INSERT INTO registrations (workshop_id, user_id, status_id, registered, last_modified) VALUES (%u, %u, '%s', '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."')",
		\Database\mres($wid),
		\Database\mres($uid),
		\Database\mres($status_id));
		\Database\mysqli( $sql) or \Database\db_error();
	
	update_change_log($wk, $u, $status_id); 

	return $status_id;
}


// this checks for open spots, and makes sure invites have gone out to anyone on waiting list
// i call this in places just to make sure i haven't neglected the waiting list
function check_waiting($wk) {
	$wk = \Workshops\get_workshop_info($wk['id']); // make sure it's up to date
	$msg = '';
	if ($wk['type'] == 'past') {
		return 'Workshop is in the past';
	}
	while (($wk['enrolled']+$wk['invited']) < $wk['capacity'] && $wk['waiting'] > 0) {
		$sql = "select * from registrations where workshop_id = ".\Database\mres($wk['id'])." and status_id = '".WAITING."' order by last_modified limit 1";
		$rows = \Database\mysqli( $sql) or \Database\db_error();
		while ($row = mysqli_fetch_assoc($rows)) {
			$u = \Users\get_user_by_id($row['user_id']);
			$msg .= change_status($wk, $u, INVITED, true);
		}
		$wk = \Workshops\get_workshop_info($wk['id']); //update lists
	}
	if ($msg) { return $msg; }
	return "No invites sent.";
}

function update_attendance($wid, $uid, $attended = 1) {
	$sql = "update registrations set attended = ".\Database\mres($attended)." where workshop_id = ".\Database\mres($wid)." and user_id = ".\Database\mres($uid);
	//echo "$sql<br>\n";
	\Database\mysqli( $sql) or \Database\db_error();
	return "Updated user ($uid) workshop ($wid) to attended: $attended";
}

function change_status($wk, $u, $status_id = ENROLLED, $confirm = true) {
		
	$e = get_an_enrollment($wk, $u);
	$statuses = \Lookups\get_statuses();
	if ($e['status_id'] != $status_id) {
		$sql = "update registrations set status_id = '".\Database\mres($status_id)."',  last_modified = '".date("Y-m-d H:i:s")."' where workshop_id = ".\Database\mres($wk['id'])." and user_id = ".\Database\mres($u['id']);
		\Database\mysqli( $sql) or \Database\db_error();
		update_change_log($wk, $u, $status_id);	
	}
	
	if ($confirm) { \Emails\confirm_email($wk, $u, $status_id); }
	$return_msg = "Updated user ({$u['email']}) to status '{$statuses[$status_id]}' for {$wk['showtitle']}.";
	return $return_msg;
}

function update_change_log($wk, $u, $status_id) {
	if (!$wk['id'] || !$u['id'] || !$status_id) {
		return false;
	}
	$sql = sprintf("insert into status_change_log (workshop_id, user_id, status_id, happened) VALUES (%u, %u, %u, '%s')",
	\Database\mres ($wk['id']),
	\Database\mres ($u['id']),
	\Database\mres ($status_id),
	date('Y-m-d H:i:s', time()));
	\Database\mysqli($sql) or \Database\db_error();
	return true;
}

function get_status_change_log($wk = null) {

	global $sc, $late_hours;
	$sql = "select s.*, u.email, u.display_name, st.status_name, wk.title, wk.start, wk.end, wk.cancelled from status_change_log s, users u, statuses st, workshops wk where";
	if ($wk) { 
		$sql .= " workshop_id = ".\Database\mres($wk['id'])." and "; 
	}
	$sql .= " s.workshop_id = wk.id and s.user_id = u.id and s.status_id = st.id order by happened desc";

	$rows = \Database\mysqli($sql) or \Database\db_error();
	$log = array();

	while ($row = mysqli_fetch_assoc($rows)) {
		$row = \Workshops\format_workshop_startend($row);
		$row = \Users\set_nice_name($row);
		if (!$wk) {
			// skip old ones for the global change log
			if (strtotime($row['start']) < strtotime("24 hours ago")) {
				continue;
			}
		}
		$row['last_enrolled'] = get_last_enrolled($row['workshop_id'], $row['user_id']);
		$log[] = $row;
	}
	return $log;
}

function get_students($wid, $status_id = ENROLLED) {
	$sql = "select u.*, r.status_id,  r.attended, r.registered, r.last_modified  from registrations r, users u where r.workshop_id = ".\Database\mres($wid);
	if ($status_id) { $sql .= " and status_id = '".\Database\mres($status_id)."'"; }
	$sql .= " and r.user_id = u.id order by last_modified";
	$rows = \Database\mysqli( $sql) or \Database\db_error();
	$stds = array();
	while ($row = mysqli_fetch_assoc($rows)) {
		$row['last_enrolled'] = get_last_enrolled($wid, $row['id']);
		$row = \Users\set_nice_name($row);
		$stds[$row['id']] = $row;
	}
	return $stds;
}


function get_last_enrolled($wid = 0, $uid = 0) {
	$sql = "select * from  status_change_log scl where workshop_id = ".\Database\mres($wid)." and user_id = ".\Database\mres($uid)." order by happened desc";
	
	$rows = \Database\mysqli($sql) or \Database\db_error();
	while ($row = mysqli_fetch_assoc($rows)) {
		if ($row['status_id'] == ENROLLED) {
			return $row['happened'];
		}
	}
	return false;
}

function list_students($wid, $status_id = ENROLLED) {
	global $sc;
	$stds = get_students($wid, $status_id);
	$body = '';
	foreach ($stds as $uid => $s) {
		$s['ukey'] = \Users\check_key($s['ukey'], $uid);
		$body .= "<div class='row'><div class='col-md-6'><a href='admin_student.php?uid={$s['id']}&wid={$wid}'>{$s['nice_name']}</a> <small>".date('M j g:ia', strtotime($s['last_modified']))."</small></div>".
		"<div class='col-md-6'>
		<a class='btn btn-primary' href='$sc?ac=cs&wid={$wid}&uid={$uid}'>change status</a> <a class='btn btn-danger' href='$sc?ac=conrem&uid={$uid}&wid={$wid}'>remove</a></div>".
		"</div>\n";
	}
	return $body;
}


function get_transcript_tabled($u, $admin = false, $page = 1) {
	global $key, $view;
	if (!$u || !isset($u['id'])) {
		return "<p>Not logged in!</p>\n";
	}
	$sql = "select * from registrations r, workshops w, locations l where r.workshop_id = w.id and w.location_id = l.id and r.user_id = ".\Database\mres($u['id'])." order by w.start desc";

	// rank

	$paginator  = new \Paginator( \Database\wh_set_db_link(), $sql );
	$rows = $paginator->getData($page);	
	if (count($rows->data) == 0) {
		return "<p>You have not taken any practices! Which is fine, but that's why this list is empty.</p>\n";
	}
	
	// prep data
	$links = $paginator->createLinks();
	$past_classes = array();
	foreach ($rows->data as $d) {
		$wk = \Workshops\get_workshop_info($d['workshop_id']);
		$d['type'] = $wk['type'];
		$d['when'] = $wk['when'];
		if ($d['status_id'] == WAITING) {
			$e = get_an_enrollment($wk, $u); 
			$d['rank'] = $e['rank']; 
		} else {
			$d['rank'] = null;
		}
		$past_classes[] = $d;
	}
	
	$view->data['statuses'] = \Lookups\get_statuses();
	$view->data['admin'] = $admin;
	$view->data['links'] = $links;
	$view->data['rows'] = $past_classes;
	return $view->renderSnippet('admin_transcript');
}


function drop_session($wk, $u) {
	$sql = sprintf('delete from registrations where workshop_id = %u and user_id = %u',
		\Database\mres($wk['id']),
		\Database\mres($u['id']));
	\Database\mysqli( $sql) or db_error();
	update_change_log($wk, $u, DROPPED); // really should be a new status like "REMOVED" 
	check_waiting($wk);
	return true;
}	
	
	
?>