<?php
namespace Enrollments;	

// registrations
function get_enrollments($id) {
	global $statuses;
	$stmt = \DB\pdo_query("select count(*) as total, status_id from registrations where workshop_id = :wid group by status_id", array(':wid' => $id));
	$enrollments = [];
	while ($row = $stmt->fetch()) {
		$enrollments[$row['status_id']] = $row['total'];
	}
	foreach ($statuses as $sid => $sname) {
		if (!isset($enrollments[$sid])) { $enrollments[$sid] = 0; }		
	}
	return $enrollments;
}

function get_an_enrollment($wk, $u) {
	$statuses = \Lookups\get_statuses();
	$stmt = \DB\pdo_query("select r.* from registrations r where r.workshop_id = :wid and user_id = :uid", array(':wid' => $wk['id'], ':uid' => $u['id']));

	while ($row = $stmt->fetch()) {
		
		if ($row['status_id'] == WAITING) {
			$stmt2 = \DB\pdo_query("select r.* from registrations r where r.workshop_id = :wid and r.status_id = :sid order by last_modified", array(':wid' => $wk['id'], ':sid' => $row['status_id']));
			$i = 1;
			while ($row2 = $stmt2->fetch()) {
				if ($row2['id'] == $row['id']) {
					break;
				}
				$i++;
			}
			$row['rank'] = $i;
		} else {
			$row['rank'] = null;
		}
		$row['status_name'] = $statuses[$row['status_id']];
		return $row;
	}
	return false;
}


// figures what the user should be
// figures if there's been a previous registration
function handle_enroll($wk, $u, $confirm = true) {
	global $error, $logger;
	
	// check incoming data
	if (!$wk) {
		$error = 'The workshop ID was not passed along.';
		$logger->notice('handle_enroll:'.$error);
		
		return false;
	}
	if (!$u) {
		$error = 'We need a user.';
		$logger->notice('handle_enroll:'.$error);
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
	$stmt = \DB\pdo_query("select  * from registrations where workshop_id = :wid and user_id = :uid", array(':wid' => $wid, ':uid' => $uid));
	
	while ($row = $stmt->fetch()) {
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

	$stmt = \DB\pdo_query("INSERT INTO registrations (workshop_id, user_id, status_id, registered, last_modified) VALUES (:wid, :uid, :status_id, '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."')", array(':wid' => $wid, ':uid' => $uid, ':status_id' => $status_id));
	
	update_change_log($wk, $u, $status_id); 

	return $status_id;
}


// this checks for open spots, and makes sure invites have gone out to anyone on waiting list
// i call this in places just to make sure i haven't neglected the waiting list
function check_waiting($wk) {
	$wk = \Workshops\fill_out_workshop_row($wk); // make sure it's up to date
	$msg = '';
	if ($wk['type'] == 'past') {
		return 'Workshop is in the past';
	}
	while (($wk['enrolled']+$wk['invited']) < $wk['capacity'] && $wk['waiting'] > 0) {
		
		$stmt = \DB\pdo_query("select * from registrations where workshop_id = :wid and status_id = '".WAITING."' order by last_modified limit 1", array(':wid' => $wk['id']));
		
		while ($row = $stmt->fetch()) {
			$u = \Users\get_user_by_id($row['user_id']);
			$msg .= change_status($wk, $u, INVITED, true);
		}
		$wk = \Workshops\fill_out_workshop_row($wk); //update lists
	}
	if ($msg) { return $msg; }
	return "No invites sent.";
}

function update_attendance($wid, $uid, $attended = 1) {
	$stmt = \DB\pdo_query("update registrations set attended = :attended where workshop_id = :wid and user_id = :uid", array(':attended' => $attended, ':wid' => $wid, ':uid' => $uid));
	return "Updated user ($uid) workshop ($wid) to attended: $attended";
}

function change_status($wk, $u, $status_id = ENROLLED, $confirm = true) {
				
	$e = get_an_enrollment($wk, $u);
	$statuses = \Lookups\get_statuses();
	if ($e['status_id'] != $status_id) {
		
		$stmt = \DB\pdo_query("update registrations set status_id = :status_id,  last_modified = '".date("Y-m-d H:i:s")."' where workshop_id = :wid and user_id = :uid", array(':status_id' => $status_id, ':wid' => $wk['id'], ':uid' => $u['id']));
		
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
	
	global $logger;
	
	$statuses = \Lookups\get_statuses();
	
	$stmt = \DB\pdo_query("insert into status_change_log (workshop_id, user_id, status_id, happened) VALUES (:wid, :uid, :status_id, '".date('Y-m-d H:i:s', time())."')", 
	array(':wid' => $wk['id'],
	':uid' => $u['id'],
	':status_id' => $status_id));
	
	$logger->info("{$u['fullest_name']} is now '{$statuses[$status_id]}' for '{$wk['showtitle']}'");
	
	return true;
}

function get_status_change_log($wk = null) {

	global $sc;
	
	$sql = "select s.*, u.email, u.display_name, st.status_name, wk.title, wk.start, wk.end, wk.cancelled from status_change_log s, users u, statuses st, workshops wk where WORKSHOPMAYBE  s.workshop_id = wk.id and s.user_id = u.id and s.status_id = st.id order by happened desc";
	if ($wk) { 
		$sql = preg_replace('/WORKSHOPMAYBE/', " workshop_id = :wid and ", $sql);
		$stmt = \DB\pdo_query($sql, array(':wid' => $wk['id']));
	} else {
		$sql = preg_replace('/WORKSHOPMAYBE/', '', $sql);
		$stmt = \DB\pdo_query($sql);
	}
	$log = array();

	while ($row = $stmt->fetch()) {
		$row = \Workshops\format_workshop_startend($row);
		$row = \Users\set_nice_name($row);
		if (!$wk) {
			// skip old ones for the global change log
			if (strtotime($row['start']) < strtotime("24 hours ago")) {
				continue;
			}
		}
		if ($row['status_id'] == DROPPED) {
			$row['last_enrolled'] = get_last_enrolled($row['workshop_id'], $row['user_id'], $row['happened']);
		}
		$log[] = $row;
	}
	return $log;
}

function get_students($wid, $status_id = ENROLLED) {
	$sql = "select u.*, r.status_id,  r.attended, r.registered, r.last_modified  from registrations r, users u where r.workshop_id = :wid";
	if ($status_id) { 
		$sql .= " and status_id = :sid and r.user_id = u.id order by last_modified"; 
		$stmt = \DB\pdo_query($sql, array(':wid' => $wid, ':sid' => $status_id));
	} else {
		$sql .= " and r.user_id = u.id order by last_modified"; 
		$stmt = \DB\pdo_query($sql, array(':wid' => $wid));
		
	}
	$stds = array();
	while ($row = $stmt->fetch()) {
		$row = \Users\set_nice_name($row);
		$stds[$row['id']] = $row;
	}
	return $stds;
}


function get_last_enrolled($wid = 0, $uid = 0, $before = null) {
	if (!$before) { 
		$before = "now()"; 
	}
	$stmt = \DB\pdo_query("select * from  status_change_log scl where workshop_id = :wid and user_id = :uid and happened <= :before order by happened desc", array(':wid' => $wid, ':uid' => $uid, ':before' => $before));	
	while ($row = $stmt->fetch()) {
		if ($row['status_id'] == ENROLLED) {
			return $row['happened'];
		}
	}
	return false;
}

function get_transcript_tabled($u, $admin = false, $page = 1) {
	global $key, $view;
	if (!$u || !isset($u['id'])) {
		return "<p>Not logged in!</p>\n";
	}
	$sql = "select * from registrations r, workshops w, locations l where r.workshop_id = w.id and w.location_id = l.id and r.user_id = :uid order by w.start desc";
	$params = array(':uid' => $u['id']);
	
	// rank

	$paginator  = new \Paginator( $sql, $params );
	$rows = $paginator->getData($page);	
	if (count($rows->data) == 0) {
		return "<p>You have not taken any practices! Which is fine, but that's why this list is empty.</p>\n";
	}
	
	// prep data
	$links = $paginator->createLinks();
	$past_classes = array();
	foreach ($rows->data as $d) {
		
		
		// build a workshop array from data we have
		$wk_fields = \Workshops\get_empty_workshop();
		foreach ($wk_fields as $field => $fieldvalue) {
			$wk[$field] = $d[$field];
		}
		$wk['id'] = $d['workshop_id']; // make sure id is correct
		$wk = \Workshops\fill_out_workshop_row($wk);
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
	return $view->renderSnippet('transcript');
}


function drop_session($wk, $u) {
	
	$stmt = \DB\pdo_query('delete from registrations where workshop_id = :wid and user_id = :uid', array(':wid' => $wk['id'], ':uid' => $u['id']));	
	update_change_log($wk, $u, DROPPED); // really should be a new status like "REMOVED" 
	check_waiting($wk);
	return true;
}	
	
	
