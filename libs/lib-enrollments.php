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


function get_enrollment_prompt($wk, $u) {
	
	global $sc;
	
	if ($wk['type'] == 'past') {
		$point = "This workshop is IN THE PAST.";
	} elseif ($wk['cancelled'] == true) {
		$point = "This workshop is CANCELLED.";
	} else {
		if (\Users\logged_in()) {
			$e = get_an_enrollment($wk, $u);
	
			$enroll_link = "$sc?ac=enroll&wid={$wk['id']}";
	
			switch ($e['status_id']) {
				case ENROLLED:
					$point = "You are ENROLLED in the practice listed below. Would you like to <a class='btn btn-primary' href='$sc?ac=drop&wid={$wk['id']}&uid={$u['id']}&key={$key}&v=winfo'>drop</a> it?";
					break;
				case WAITING:
					$point = "You are spot number {$e['rank']} on the WAIT LIST for the practice listed below. Would you like to <a class='btn btn-primary' href='$sc?ac=drop&wid={$wk['id']}&uid={$u['id']}&key={$key}&v=winfo'>drop</a> it?";
					break;
				case INVITED:
					$point = "A spot opened up in the practice listed below. Would you like to <a class='btn btn-primary' href='$sc?ac=accept&wid={$wk['id']}&uid={$u['id']}&key={$key}&v=winfo'>accept</a> it, or <a class='btn btn-primary' href='$sc?ac=decline&wid={$wk['id']}&uid={$u['id']}&key={$key}&v=winfo'>decline</a> it?";
					break;
				case DROPPED:
					$point = "You have dropped out of the practice listed below. Would you like to <a class='btn btn-primary'  href='$enroll_link'>re-enroll</a>?";
					break;
				default:
		
					$point = "You are not currenty signed up for the practice listed below. ".
						($wk['type'] == 'soldout' 
						? "It is full. Want to <a class='btn btn-primary' href='$enroll_link'>join the wait list</a>?"
						: "Want to <a class='btn btn-primary' href='$enroll_link'>enroll</a>?");
		
					break;
			}
		} else {
			$point = "If you wish to enroll, you must first log in <a href='$sc'>on the front page</a>.";
		}
		
	}		
	
	return $point;
}

// figures what the user should be
// figures if there's been a previous registration
function handle_enroll($wk, $u, $email = null, $confirm = true) {
	global $error;
	if (!$wk) {
		$error = 'The workshop ID was not passed along.';
		return false;
	}
	if (!$u && !$email) {
		$error = 'We need a logged in user or an email.';
		return false;
	}
	// check user, the email
	if ($u) {
		$email = $u['email'];
	} else {
		if (\Users\validate_email($email)) {
			// check for a user with this email
			$u = \Users\get_user_by_email($email);
			// no? make one.
			if (!$u) { $u = \Users\make_user($email); }
		} else {
			$error = "I think that is not a valid email.";
			return false;
		}
	}

	// if they were enrolled, we'll adjust the language of confirmation message
	$before = get_an_enrollment($wk, $u); 
	$status_id = enroll($wk, $u);
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
	if ($confirm) { \Emails\confirm_email($wk, $u, $status_id); }
	if (DEBUG_MODE) {
		mail(WEBMASTER, $message, $message, "From: ".WEBMASTER);
	}
	return $message;
}

// enrolls
// first figures if the person is already enrolled
function enroll($wk, $u) {
	$wid = $wk['id'];
	$uid = $u['id'];
	
	// is this person already registered? then we do different things depending on current status
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
	
	// if we haven't returned, then there was no registration. make a new registration
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
	if (DEBUG_MODE) {
		mail(WEBMASTER, "{$u['email']} now '{$statuses['status_id']}' for '{$wk['showtitle']}'", $return_msg, "From: ".WEBMASTER);
	}
	
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
	$sql = "select s.*, u.email, u.display_name, st.status_name, wk.title, wk.start, wk.end from status_change_log s, users u, statuses st, workshops wk where";
	if ($wk) { 
		$sql .= " workshop_id = ".\Database\mres($wk['id'])." and "; 
	}
	$sql .= " s.workshop_id = wk.id and s.user_id = u.id and s.status_id = st.id order by happened desc";

	$rows = \Database\mysqli($sql) or \Database\db_error();
	$log = '';

	if ($wk) {
		$log = "<tr><th>user</th><th>status</th><th>changed / last enrolled</th></tr>\n";
	} else {
		$log = "<tr><th>user</th><th>workshop</th><th>status</th><th>changed / last enrolled</th></tr>\n";
	}

	while ($row = mysqli_fetch_assoc($rows)) {
		$row = \Workshops\format_workshop_startend($row);
		$row = \Users\set_nice_name($row);
		$wkname = '';
		if (!$wk) {
			// skip old ones for the global change log
			if (strtotime($row['start']) < strtotime("24 hours ago")) {
				continue;
			}
			$wkname = "<a href='$sc?v=ed&wid={$row['workshop_id']}'>{$row['title']}</a><br><small>{$row['showstart']}</small></td><td>";
		}
		$last_enrolled = get_last_enrolled($row['workshop_id'], $row['user_id']);
		$row_class = '';
		if ($row['status_id'] == DROPPED && $last_enrolled) {
			
			$hours_before = round((strtotime($row['start']) - strtotime($row['happened'])) / 3600);
			
			$last_enrolled = "/<br>".date('j-M-y g:ia', strtotime($last_enrolled))." ($hours_before)";
			if ($hours_before < $late_hours) {
				$row_class = 'danger';
			}
		} else {
			$last_enrolled = "<td>&nbsp;</td>";
		}

		$log .= "<tr class='$row_class'><td>{$row['nice_name']}</td><td>$wkname {$row['status_name']}</td><td><small>".date('j-M-y g:ia', strtotime($row['happened']))."$last_enrolled</small></td>\n";
		$log .= "</tr>\n";
		
	}
	if (!$log) {
		$log = 'No recorded updates.';
	} else {
		$log = "<table class='table'>$log</table>\n";
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
	global $key;


	if (!$u || !isset($u['id'])) {
		return "<p>Not logged in!</p>\n";
	}

	$statuses = \Lookups\get_statuses();

	$sql = "select * from registrations r, workshops w, locations l where r.workshop_id = w.id and w.location_id = l.id and r.user_id = ".\Database\mres($u['id'])." order by w.start desc";

	$paginator  = new \Paginator( \Database\wh_set_db_link(), $sql );
	$rows = $paginator->getData($page);	
	
	if (count($rows->data) == 0) {
		return "<p>You have not taken any practices! Which is fine, but that's why this list is empty.</p>\n";
	}

	$body = $paginator->createLinks();
	$body .= '<table class="table table-striped table-bordered"><thead class="thead-dark">
		<tr>
			<th class="workshop-name" scope="col"><span class="oi oi-people" title="people" aria-hidden="true"></span> Workshop</th>
			<th scope="col"><span class="oi oi-calendar" title="calendar" aria-hidden="true"></span> When</th>
			<th scope="col"><span class="oi oi-map" title="map" aria-hidden="true"></span> Where</th>
			<th scope="col"><span class="oi oi-pulse" title="pulse" aria-hidden="true"></span> Status</th>
			<th scope="col"><span class="oi oi-task" title="task" aria-hidden="true"></span> Action</th>
		</tr></thead>
			<tbody>';
	
	foreach ($rows->data as $t) {
		$wk = \Workshops\get_workshop_info($t['workshop_id']);
		$cl = 'table-';
		if ($wk['type'] == 'past') {
			$cl .= 'light';
		} elseif ($t['status_id'] == ENROLLED) {
			$cl .= 'success';
		} else {
			$cl .= 'warning';
		}
		
		$body .= "<tr class='$cl'><td>";
		if ($admin) {
			$body .= "<a href=\"admin.php?wid={$t['workshop_id']}&ac=ed\">{$t['title']}</a>";
		} else {
			$body .= "<a href=\"index.php?wid={$t['workshop_id']}\">{$t['title']}</a>";
		}
		$body .= "</td><td>{$wk['when']}</td><td>{$t['place']}</td><td>";
		$body .= "{$statuses[$t['status_id']]}";
		if ($t['status_id'] == WAITING) {
			$e = get_an_enrollment($wk, $u); 
			$body .= " (spot {$e['rank']})";
		}
		$body .= "</td><td><a href='index.php?wid={$t['workshop_id']}'><span class=\"oi oi-info\" title=\"info\" aria-hidden=\"true\"></span> info</a></td></tr>\n";
	}
	$body .= "</tbody></table>\n";
	$body .= $paginator->createLinks();
	return $body;
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