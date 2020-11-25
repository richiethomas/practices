<?php
namespace Enrollments;	

// registrations
function get_enrollments($id) {
	global $lookups;
	$stmt = \DB\pdo_query("select count(*) as total, status_id from registrations where workshop_id = :wid group by status_id", array(':wid' => $id));
	$enrollments = [];
	while ($row = $stmt->fetch()) {
		$enrollments[$row['status_id']] = $row['total'];
	}
	foreach ($lookups->statuses as $sid => $sname) {
		if (!isset($enrollments[$sid])) { $enrollments[$sid] = 0; }		
	}
	return $enrollments;
}

function get_an_enrollment($wk, $u) {
	global $lookups;
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
		$row['status_name'] = $lookups->statuses[$row['status_id']];
		return $row;
	}
	return get_empty_enrollment();
}


function get_enrollment_ids_for_user($uid) {
	$stmt = \DB\pdo_query("select * from registrations where user_id = :uid", array(':uid' => $uid));
	$es = array();
	while ($row = $stmt->fetch()) {
		$es[] = $row['id'];
	}
	return $es;	
}

// figures what the user should be
// figures if there's been a previous registration
function handle_enroll($wk, $u, $confirm = true) {
	global $error, $logger;
	
	// check incoming data
	if (!\Workshops\is_complete_workshop($wk)) {
		$error = 'The workshop ID was not passed along.';
		$logger->notice('handle_enroll:'.$error);
		
		return false;
	}
	if (!\Users\is_complete_user($u)) {
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
			$confirm = false; // no need for an email message
		} else {
			$keyword = 'is now';
		}
		$message = "'{$u['nice_name']}' $keyword enrolled in '{$wk['title']}'!  Info emailed to <b>{$u['email']}</b>.";
	} elseif ($status_id == WAITING) {
		if (!$before) {
			$keyword = 'has been added to';
		} elseif ($before['status_id'] == WAITING) {
			$keyword = 'is still on';
			$confirm = false; // no need for an email message
		} else {
			$keyword = 'is now on';
		}		
		$message = "This practice is full. '{$u['nice_name']}' $keyword the waiting list.";
	} elseif ($status_id == 'already') {
		$message = "'{$u['nice_name']}' has already been registered.";
		$confirm = false; // no need for an email message		
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
	if ($wk['upcoming'] == 0) {
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



function update_paid($wid, $uid, $paid = 1, $eid = null) {
		
	// get enrollment ID if need be	
	if (!$eid) {
		$stmt = \DB\pdo_query("select id from registrations where workshop_id = :wid and user_id = :uid", array(':wid' => $wid, ':uid' => $uid));
		while ($row = $stmt->fetch()) {
			$eid = $row['id'];
		}
	}
	
	// get paid status before
	$paid_before = 0;
	$stmt = \DB\pdo_query("select paid from registrations where workshop_id = :wid and user_id = :uid", array(':wid' => $wid, ':uid' => $uid));
	while ($row = $stmt->fetch()) {
		$paid_before = $row['paid'];
	}
	
	if ($paid != $paid_before) {
		$stmt = \DB\pdo_query("update registrations set paid = :paid where id = :rid", array(':paid' => $paid, ':rid' => $eid));
		// send payment confirmation
		$payee = \Users\get_user_by_id($uid);
		$workshop = \Workshops\get_workshop_info($wid);

		if ($paid == 1) {
		
			$body = "<p>This is automated email to confirm that I've received your payment for class.</p>";
			$body .= "<p>Class: {$workshop['title']} {$workshop['showstart']} (California time, PST)</p>\n";
			$body .= "<p>Student: {$payee['nice_name']}</p>";
			$body .= "<p>Amount: \${$workshop['cost']} (USD)</p>\n";
			$body .= "<p>Thanks!<br>-Will</p>\n";
				
			\Emails\centralized_email($payee['email'], "Payment received for {$workshop['title']} {$workshop['showstart']} (PDT)", $body); 
			
			return "Set user '{$payee['nice_name']}' to 'paid' for workshop '{$workshop['title']}'";
		} else {
			return "Set user '{$payee['nice_name']}' to 'unpaid' for workshop '{$workshop['title']}'";
		}
		
	}
	return false; // no update needed
	
}

function update_paid_by_enrollment_id($eid, $paid = 1) {
	
	// get the workshop id and user id
	$stmt = \DB\pdo_query("select user_id, workshop_id from registrations where id = :id", array(':id' => $eid));
	while ($row = $stmt->fetch()) {
		return update_paid($row['workshop_id'], $row['user_id'], $paid, $eid);
	}
	return false;

}



function change_status($wk, $u, $status_id = ENROLLED, $confirm = true) {
				
	$e = get_an_enrollment($wk, $u);
	global $lookups;
	if ($e['status_id'] != $status_id) {
		
		$stmt = \DB\pdo_query("update registrations set status_id = :status_id,  last_modified = '".date("Y-m-d H:i:s")."' where workshop_id = :wid and user_id = :uid", array(':status_id' => $status_id, ':wid' => $wk['id'], ':uid' => $u['id']));
		
		update_change_log($wk, $u, $status_id);	
		if ($confirm) { \Emails\confirm_email($wk, $u, $status_id); }
		return "Updated user ({$u['email']}) to status '{$lookups->statuses[$status_id]}' for {$wk['title']}.";
	}
	return "User ({$u['email']}) was already status '{$lookups->statuses[$status_id]}' for {$wk['title']}.";
}

function update_change_log($wk, $u, $status_id) {
	if (!$wk['id'] || !$u['id'] || !$status_id) {
		return false;
	}
	
	global $logger;
	
	global $lookups;
	
	$stmt = \DB\pdo_query("insert into status_change_log (workshop_id, user_id, status_id, happened) VALUES (:wid, :uid, :status_id, '".date('Y-m-d H:i:s', time())."')", 
	array(':wid' => $wk['id'],
	':uid' => $u['id'],
	':status_id' => $status_id));
	
	$logger->info("{$u['fullest_name']} is now '{$lookups->statuses[$status_id]}' for '{$wk['title']}'");
	
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
	$sql = "select u.*, r.status_id,  r.paid, r.registered, r.last_modified  from registrations r, users u where r.workshop_id = :wid";
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
	global $key, $view, $lookups;
	if (!$u || !isset($u['id'])) {
		return "<p>Not logged in!</p>\n";
	}
	
	$mysqlnow = date("Y-m-d H:i:s");
	
	$sql = "select *, r.id as enrollment_id 
	from registrations r, workshops w, locations l 
	where r.workshop_id = w.id 
	and w.location_id = l.id 
	and r.user_id = :uid 
	and ( (w.start >= :now) || (r.status_id = :enrolled_id) ) 
	order by w.start desc";
	$params = array(':uid' => $u['id'], ':now' => $mysqlnow, ':enrolled_id' => ENROLLED);
	
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
		$d['soldout'] = $wk['soldout'];
		$d['upcoming'] = $wk['upcoming'];
		$d['when'] = $wk['when'];
		$d['sessions'] = $wk['sessions'];
		$d['teacher_name'] = $wk['teacher_name'];
		$d['teacher_id'] = $wk['teacher_id'];
		if ($d['status_id'] == WAITING) {
			$e = get_an_enrollment($wk, $u); 
			$d['rank'] = $e['rank']; 
		} else {
			$d['rank'] = null;
		}
		$past_classes[] = $d;
	}
	
	$view->data['guest_id'] = $u['id'];
	$view->data['statuses'] = $lookups->statuses;
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
	
function get_empty_enrollment() {
	return array(
		'id' => false,
		'user_id' => false,
		'workshop_id' => false,
		'status_id' => false,
		'paid' => false,
		'registered' => false, 	
		'last_modified' => false,
		'while_soldout' => false);
	
}
	
