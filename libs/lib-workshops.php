<?php
namespace Workshops;
	
// workshops
function get_workshop_info($id) {
	$statuses = \Lookups\get_statuses();
	$locations = \Lookups\get_locations();
	
	$stmt = \DB\pdo_query("select w.* from workshops w where w.id = :id", array(':id' => $id));
	while ($row = $stmt->fetch()) {
		$row = fill_out_workshop_row($row);
		return $row;

	}
	return false;
}

function fill_out_workshop_row($row, $get_enrollment_stats = true) {
	$locations = \Lookups\get_locations();
	
	foreach (array('address', 'city', 'state', 'zip', 'place', 'lwhere') as $loc_field) {
		$row[$loc_field] = $locations[$row['location_id']][$loc_field];
	}
		
	if ($row['when_public'] == 0 ) {
		$row['when_public'] = '';
	}
	$row['soldout'] = 0; // so many places in the code refer to this
	$row = format_workshop_startend($row);
	
	
	//get teacher info
	$trow = \Teachers\get_teacher_by_id($row['teacher_id']);
	$row['teacher_email'] = $trow['email'];
	$row['teacher_name'] = $trow['nice_name'];
	$row['teacher_user_id'] = $trow['user_id'];
	$row['teacher_id'] = $trow['id'];
	$row['teacher_key'] = $trow['ukey'];
		
	$row['costdisplay'] = $row['cost'] ? "\${$row['cost']} USD" : 'Free';
	
	// xtra session info
	$row['sessions'] = \XtraSessions\get_xtra_sessions($row['id']);	
	$row['total_class_sessions'] = 1;
	$row['total_show_sessions'] = 0;
	foreach ($row['sessions'] as $sess) {
		if ($sess['class_show']) {
			$row['total_show_sessions']++;
		} else {
			$row['total_class_sessions']++;
		}
	}
	
	// when is the next starting session
	// if all are in past, set this to most recent one
	$row['nextstart_raw'] = $row['start'];
	$row['nextend_raw'] = $row['end'];
	$row['nextstart_url'] = $row['online_url'];
	$row['nextsession_show'] = 0;
	$row['nextsession_extra'] = 0;
	if (!\Wbhkit\is_future($row['nextstart_raw'])) {
		foreach ($row['sessions'] as $s) {
			if (\Wbhkit\is_future($s['start'])) {
				$row['nextsesssion_extra'] = 1;
				$row['nextstart_raw'] = $s['start'];
				$row['nextend_raw'] = $s['end'];
				if ($s['online_url']) { $row['nextstart_url'] = $s['online_url']; }
				if ($s['class_show'] == 1) { $row['nextsession_show'] = 1; }
				break; // found the next start
			}
		}
	}
	// now that we've found it, format it nicely
	$row['nextstart'] = \Wbhkit\friendly_when($row['nextstart_raw']);
	$row['nextend'] = \Wbhkit\friendly_when($row['nextend_raw']);
		
	if (strtotime($row['end']) >= strtotime('now')) { 
		$row['upcoming'] = 1; 
	} else {
		$row['upcoming'] = 0;
	}
	$row = check_last_minuteness($row);
	
	if ($get_enrollment_stats) {
		$row = set_enrollment_stats($row);
		if ($row['enrolled'] >= $row['capacity'] || $row['waiting'] > 0 || $row['invited'] > 0) { 
			$row['soldout'] = 1;
		} else {
			$row['soldout'] = 0;
		}	
	}
	
	return $row;
	
}

// pass in the workshop row as it comes from the database table
// add some columns with date / time stuff figured out
function format_workshop_startend($row) {
	$row['showstart'] = \Wbhkit\friendly_date($row['start']).' '.\Wbhkit\friendly_time($row['start']);
	$row['showend'] = \Wbhkit\friendly_time($row['end']);
	if ($row['cancelled']) {
		$row['title'] = "CANCELLED: {$row['title']}";
	}
	$row['when'] = "{$row['showstart']}-{$row['showend']}";
		
	return $row;
}

// used in fill_out_workshop_row and also get_sessions_to_come
// expects 'id' and 'capacity' to be set
function set_enrollment_stats($row) {
	$statuses = \Lookups\get_statuses();
	
	$enrollments = \Enrollments\get_enrollments($row['id']);
	foreach ($statuses as $sid => $sname) {
		$row[$sname] = $enrollments[$sid];
	}	
	$row['paid'] = how_many_paid($row);
	$row['open'] = ($row['enrolled'] >= $row['capacity'] ? 0 : $row['capacity'] - $row['enrolled']);
	return $row;
}


function check_last_minuteness($wk) {
	
	/* 
		there's two flags:
			1) workshops have "sold_out_late" meaning the workshop was sold out within LATE_HOURS of the start. We update this to 1 or 0 everytime the web site selects the workshop info from the db.
			2) registrations have a "while_sold_out" flag. if it is set to 1, then you were enrolled in this workshop while it was sold_out_late (i.e. sold out within $late_hours of its start). we also check this every time we select the workshop info. but this never gets set back to zero. 
			If a "while sold out" person drops, that's not cool. They held a spot during a sold out time close to the start of the workshop.
	*/ 
			
	$hours_left = (strtotime($wk['start']) - strtotime('now')) / 3600;
	if ($hours_left > 0 && $hours_left < LATE_HOURS) {
		// have we never checked if it's sold out
		if ($wk['sold_out_late'] == -1) {
			if ($wk['soldout'] == 1) {
				
				$stmt = \DB\pdo_query("update workshops set sold_out_late = 1 where id = :wid", array(':wid' => $wk['id']));				

				$stmt = \DB\pdo_query("update registrations set while_soldout = 1 where workshop_id = :wid and status_id = '".ENROLLED."'", array(':wid' => $wk['id']));
				
				$wk['sold_out_late'] = 1;
			} else {

				$stmt = \DB\pdo_query("update workshops set sold_out_late = 0 where id = :wid", array(':wid' => $wk['id']));

				$wk['sold_out_late'] = 0;
			}
		}
	}
	return $wk;
}

function get_workshops_dropdown($start = null, $end = null) {
	
	$locations = \Lookups\get_locations();
	$stmt = \DB\pdo_query("select w.* from workshops w order by start desc");
	$workshops = array();
	while ($row = $stmt->fetch()) {
		$row = format_workshop_startend($row);
		$workshops[$row['id']] = $row['title'];
	}
	return $workshops;
}


// for admins eyes only
function get_search_results($page = 1, $needle = null) {
	global $view;
	
	// get IDs of workshops
	$sql = "select w.* from workshops w ";
	if ($needle) { $sql .= " where w.title like '%$needle%' "; }
	$sql .= " order by start desc"; // get all
		
	// prep paginator
	$paginator  = new \Paginator( $sql );
	$rows = $paginator->getData($page);
	$links = $paginator->createLinks(7, 'search results', "&needle=".urlencode($needle));

	// calculate enrollments, ranks, etc
	if ($rows->total > 0) {
		$workshops = array();
		foreach ($rows->data as $row ) {
			$workshops[] = fill_out_workshop_row($row);
		}
	} else {
		return "<p>No upcoming workshops!</p>\n";	// this skips $body variable contents	
	}
		
	// prep view
	$view->data['links'] = $links;
	$view->data['rows'] = $workshops;
	return $view->renderSnippet('admin/search_workshops');	
}


function get_workshops_list($admin = 0, $page = 1) {
	
	global $view;
	
	// get IDs of workshops
	$mysqlnow = date("Y-m-d H:i:s");

	$sql = '(select w.* from workshops w ';
	if (!$admin) {
		$sql .= "where when_public < '$mysqlnow' and date(start) >= date('$mysqlnow')"; // get public ones to come
	}
	$sql .= ")"; // end first select statement of UNION
	
	// second select: search xtra sessions. 
	// UNION should automatically remove duplicate rows,
	// so multiple workshop/xtra sessions should only show up once in results?
	$sql .=
		"UNION
		(select w.* from workshops w, xtra_sessions x where x.workshop_id = w.id
	and w.when_public < '$mysqlnow' and date(x.start) >= date('$mysqlnow'))";  

	if ($admin) {
		$sql .= " order by start desc"; // get all
	} else {
		$sql .= " order by start asc";  // temporary, should be asc
	}

		
	// prep paginator
	$paginator  = new \Paginator( $sql );
	$rows = $paginator->getData($page);
	$links = $paginator->createLinks();

	// calculate enrollments, ranks, etc
	if ($rows->total > 0) {
		$workshops = array();
		foreach ($rows->data as $row ) {
			$workshops[] = fill_out_workshop_row($row);
		}
	} else {
		return "<p>No upcoming workshops!</p>\n";	// this skips $body variable contents	
	}
		
	// prep view
	$view->data['links'] = $links;
	$view->data['admin'] = $admin;
	$view->data['rows'] = $workshops;
	return $view->renderSnippet('workshop_list');
}

function get_unavailable_workshops() {
	
	$mysqlnow = date("Y-m-d H:i:s");
	
	$stmt = \DB\pdo_query("
select * from workshops where date(start) >= :when1 and when_public >= :when2 order by when_public asc, start asc", array(":when1" => $mysqlnow, ":when2" => $mysqlnow)); 
		
	$sessions = array();
	while ($row = $stmt->fetch()) {
		$sessions[] = fill_out_workshop_row($row);
	}
	return $sessions;
	
}

// data only, for admin_calendar
function get_sessions_to_come() {
	
	// get IDs of workshops
	$mysqlnow = date("Y-m-d H:i:s", strtotime("-3 hours"));
	
	$stmt = \DB\pdo_query("
(select w.id, title, start, end, capacity, cost, 0 as xtra, 0 as class_show, notes, teacher_id, 1 as rank, '' as override_url, online_url from workshops w where start >= date('$mysqlnow'))
union
(select x.workshop_id, w.title, x.start, x.end, w.capacity, w.cost, 1 as xtra, x.class_show, w.notes, w.teacher_id, x.rank, x.online_url as override_url, w.online_url from xtra_sessions x, workshops w, users u where w.id = x.workshop_id and x.start >= date('$mysqlnow'))
order by start asc"); 
		
	$sessions = array();
	while ($row = $stmt->fetch()) {
		$trow = \Teachers\get_teacher_by_id($row['teacher_id']);
		$row['teacher_name'] = $trow['nice_name'];
		$row['teacher_user_id'] = $trow['user_id'];
		$sessions[] = set_enrollment_stats($row);
	}
	return $sessions;
}


function how_many_paid($wk) {
	$stmt = \DB\pdo_query("select count(*) as total_paid from registrations where workshop_id = :wid and paid = 1", array(':wid' => $wk['id']));
	while ($row = $stmt->fetch()) {
		return $row['total_paid'];
	}
	return 0;
}

// for "revenues" page
function get_workshops_list_bydate($start = null, $end = null) {
	if (!$start) { $start = "Jan 1 1000"; }
	if (!$end) { $end = "Dec 31 3000"; }
	
	//echo "select w.* from workshops w WHERE w.start >= '".date('Y-m-d H:i:s', strtotime($start))."' and w.end <= '".date('Y-m-d H:i:s', strtotime($end))."' order by start desc";
	
	$stmt = \DB\pdo_query("select w.* from workshops w WHERE w.start >= :start and w.end <= :end order by teacher_id, start desc", array(':start' => date('Y-m-d H:i:s', strtotime($start)), ':end' => date('Y-m-d H:i:s', strtotime($end))));
	
	$workshops = array();
	while ($row = $stmt->fetch()) {
		$workshops[$row['id']] = fill_out_workshop_row($row);
	}
	return $workshops;
}	

function get_empty_workshop() {
	return array(
		'id' => null,
		'title' => null,
		'location_id' => null,
		'online_url' => null,
		'start' => null,
		'end' => null,
		'cost' => null,
		'capacity' => null,
		'notes' => null,
		'revenue' => null,
		'expenses' => null,
		'when_public' => null,
		'sold_out_late' => null,
		'cancelled' => null,
		'teacher_id' => 1,
		'school_fee' => 0,
		'reminder_sent' => 0
	);
}

function add_workshop_form($wk) {
	global $sc;
	return "<form id='add_wk' action='admin_edit.php' method='post' novalidate>".
	\Wbhkit\form_validation_javascript('add_wk').
	"<fieldset name='session_add'><legend>Add Workshop</legend>".
	\Wbhkit\hidden('ac', 'ad').
	workshop_fields($wk).
	\Wbhkit\submit('Add').
	"</fieldset></form>";
	
}

function workshop_fields($wk) {
	return \Wbhkit\texty('title', $wk['title'], null, null, null, 'Required', ' required ').
	\Lookups\locations_drop($wk['location_id']).
	\Wbhkit\texty('online_url', $wk['online_url'], 'Online URL').	
	\Wbhkit\texty('start', $wk['start'], null, null, null, 'Required', ' required ').
	\Wbhkit\texty('end', $wk['end'], null, null, null, 'Required', ' required ').
	\Wbhkit\texty('cost', $wk['cost']).
	\Wbhkit\texty('school_fee', $wk['school_fee']).
	\Wbhkit\texty('capacity', $wk['capacity']).
	\Wbhkit\textarea('notes', $wk['notes']).
	\Wbhkit\drop('teacher_id', \Teachers\teachers_dropdown_array(), $wk['teacher_id'], 'Teacher', null, 'Required', ' required ').
	\Wbhkit\texty('revenue', $wk['revenue']).
	\Wbhkit\texty('expenses', $wk['expenses']).
	\Wbhkit\checkbox('cancelled', 1, null, $wk['cancelled']).	
	\Wbhkit\texty('when_public', $wk['when_public'], 'When Public').
	\Wbhkit\checkbox('reminder_sent', 1, 'Reminder sent?', $wk['reminder_sent']);
	
}

// $ac can be 'up' or 'ad'
function add_update_workshop($wk, $ac = 'up') {
	
	global $last_insert_id;
	
	// fussy MySQL 5.7
	if (!$wk['cancelled']) { $wk['cancelled'] = 0; }
	if (!$wk['revenue']) { $wk['revenue'] = 0; }
	if (!$wk['expenses']) { $wk['expenses'] = 0; }
	if (!$wk['cost']) { $wk['cost'] = 0; }
	if (!$wk['capacity']) { $wk['capacity'] = 0; }
	if (!$wk['when_public']) { $wk['when_public'] = NULL; }
	if (!$wk['start']) { $wk['start'] = NULL; }
	if (!$wk['end']) { $wk['end'] = NULL; }
	if (!$wk['teacher_id']) { $wk['teacher_id'] = 1; }
	if (!$wk['school_fee']) { $wk['school_fee'] = 0; }
	if (!$wk['reminder_sent']) { $wk['reminder_sent'] = 0; }
	
		
	$params = array(':title' => $wk['title'],
		':start' => date('Y-m-d H:i:s', strtotime($wk['start'])),
		':end' => date('Y-m-d H:i:s', strtotime($wk['end'])),
		':cost' => $wk['cost'],
		':capacity' => $wk['capacity'],
		':lid' => $wk['location_id'],
		':online_url' => $wk['online_url'],
		':notes' => $wk['notes'],
		':revenue' => $wk['revenue'],
		':expenses' => $wk['expenses'],
		':public' => date('Y-m-d H:i:s', strtotime($wk['when_public'])),
		':cancelled' => $wk['cancelled'],
		':tid' => $wk['teacher_id'],
		':school_fee' => $wk['school_fee'],
		':reminder_sent' => $wk['reminder_sent']);
		
		if ($ac == 'up') {
			$params[':wid'] = $wk['id'];
			$sql = "update workshops set title = :title, start = :start, end = :end, cost = :cost, capacity = :capacity, location_id = :lid, online_url = :online_url, notes = :notes, revenue = :revenue, expenses = :expenses, when_public = :public, cancelled = :cancelled, reminder_sent = :reminder_sent, teacher_id = :tid, school_fee = :school_fee where id = :wid";
			$stmt = \DB\pdo_query($sql, $params);
			return $wk['id'];
		} elseif ($ac = 'ad') {
			$stmt = \DB\pdo_query("insert into workshops (title, start, end, cost, capacity, location_id, online_url, notes, revenue, expenses, when_public, cancelled, reminder_sent, teacher_id, school_fee)
			VALUES (:title, :start, :end, :cost, :capacity, :lid, :online_url, :notes, :revenue, :expenses, :public, :cancelled, :reminder_sent, :tid, :school_fee)",
			$params);
			return $last_insert_id; // set as a global by my dbo routines
		}
	
}

function delete_workshop($workshop_id) {
	$stmt = \DB\pdo_query("delete from registrations where workshop_id = :wid", array(':wid' => $workshop_id));
	$stmt = \DB\pdo_query("delete from xtra_sessions where workshop_id = :wid", array(':wid' => $workshop_id));
	$stmt = \DB\pdo_query("delete from workshops where id = :wid", array(':wid' => $workshop_id));

}

function is_public($wk) {
	if (isset($wk['when_public']) && $wk['when_public'] && strtotime($wk['when_public']) > time()) {
		return false;
	}
	return true;
}

function is_complete_workshop($wk) {
	if (is_array($wk) && isset($wk['id']) && $wk['id']) {
		return true;
	}
	return false;
}

function get_cut_and_paste_roster($wk, $enrolled = null) {
	$names = array();
	$just_emails = array();
	
	if (!isset($enrolled)) {
		$enrolled = \Enrollments\get_students($wk['id'], ENROLLED);
	}

	foreach ($enrolled as $s) {
		$names[] = "{$s['nice_name']} {$s['email']}";
		$just_emails[] = "{$s['email']}";
	}
	sort($names);
	sort($just_emails);
	
	$class_dates = $wk['when']."\n";
	if (!empty($wk['sessions'])) {
		foreach ($wk['sessions'] as $s) {
			$class_dates .= "{$s['friendly_when']}".($s['class_show'] ? ' (show)': '').
			($s['online_url'] ? " - {$s['online_url']}" : '')."\n";
		}
	}
	if ($class_dates) {
		$class_dates = "\n\nClass Sessions:\n(some sessions may their own zoom links)\n------------\n{$class_dates}";
	}
	
	return 
		preg_replace("/\n\n+/", 
					"\n\n", 
					"{$wk['title']} - {$wk['showstart']}\n\n".
					"Main zoom link:\n".($wk['location_id'] == ONLINE_LOCATION_ID ? "{$wk['online_url']}\n" : '').
						$class_dates.
					"\nNames and Emails\n---------------\n".implode("\n", $names)."\n\nJust the emails\n---------------\n".implode(",\n", $just_emails));
	
}

