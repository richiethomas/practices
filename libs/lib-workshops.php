<?php
namespace Workshops;
	
// workshops
function get_workshop_info(int $id = 0) {

	if ($id == 0) {
		return get_empty_workshop();
	}

	$stmt = \DB\pdo_query("select w.* from workshops w where w.id = :id", array(':id' => $id));
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		$row = fill_out_workshop_row($row);
		return $row;

	}
	return array();
}

function fill_out_workshop_row(array $row, bool $get_enrollment_stats = true) {
	global $lookups;
		
	foreach (array('address', 'city', 'state', 'zip', 'place', 'lwhere') as $loc_field) {
		$row[$loc_field] = $lookups->locations[$row['location_id']][$loc_field];
	}
		
	if ($row['when_public'] == 0 ) {
		$row['when_public'] = '';
	}
	$row['soldout'] = 0; // so many places in the code refer to this
	$row = format_workshop_startend($row);
	
	// create short title if it's more then 2 words
	$row['short_title'] = $row['title'];
    if (str_word_count($row['short_title'], 0) > 2) {
        $words = str_word_count($row['short_title'], 2);
        $pos   = array_keys($words);
        $row['short_title']  = substr($row['short_title'], 0, $pos[2]);
    }
	
	//get teacher info
	if ($row['teacher_id']) {
		$row['teacher_info'] = \Teachers\get_teacher_by_id($row['teacher_id']);
	}
	if ($row['co_teacher_id']) {
		$row['co_teacher_info'] = \Teachers\get_teacher_by_id($row['co_teacher_id']);
	}
		
	if ($row['cost'] == 1) {
		$row['costdisplay'] = 'Pay what you can';
	} elseif ($row['cost'] > 1) {
		$row['costdisplay'] = "\${$row['cost']} USD";
	} else {
		$row['costdisplay'] = 'Free';
	}

	$row['costdisplay'] = figure_costdisplay($row['cost']);

	// url stuff
	$row = parse_online_url($row);

	// xtra session info
	$row['sessions'] = \XtraSessions\get_xtra_sessions($row['id']);	
	$row['total_class_sessions'] = 1;
	foreach ($row['sessions'] as $sess) {
		$row['total_class_sessions']++;
	}
	
	$row['total_show_sessions'] = 0;
	$row['class_shows'] = get_class_shows($row);
	foreach ($row['class_shows'] as $cs) {
		$row['total_show_sessions']++;
	}
	$row['total_sessions'] = $row['total_class_sessions'] + $row['total_show_sessions'];

	$row['time_summary'] = $row['total_class_sessions'].' class'.\Wbhkit\plural($row['total_class_sessions'], '', 'es');
	
	if ($row['total_show_sessions'] > 0) {
		$row['time_summary'] .= ', '.$row['total_show_sessions'].' show'.\Wbhkit\plural($row['total_show_sessions']);
	}

	// set full when
	$row['full_when'] = $row['when'];
	if (!empty($row['sessions'])) {
		$row['full_when'] .= "<br>\n";
		foreach ($row['sessions'] as $s) {
			$row['full_when'] .= "{$s['friendly_when']}<br>\n";
		}
	}
	if (count($row['class_shows']) > 0 ) {
		if (empty($row['sessions'])) {  $row['full_when'] .= "<br>\n"; }
		foreach ($row['class_shows'] as $cs) {
			$row['full_when'] .= "Show: {$cs->fields['friendly_when']}<br>\n"; 
		}
	}
		
	if (strtotime($row['end']) >= strtotime('now')) { 
		$row['upcoming'] = 1; 
	} else {
		$row['upcoming'] = 0;
	}
	$row = check_last_minuteness($row);
	
	if ($get_enrollment_stats) {

		// set teacher pay
	
		$row['teacher_pay'] = 		$row['total_class_sessions']*$row['teacher_info']['default_rate'] + 		$row['total_show_sessions']*($row['teacher_info']['default_rate']/2);
		
		$row = set_actual_revenue($row);
		$row = set_enrollment_stats($row);

	}
	
	return $row;
	
}

function figure_costdisplay(int $cost) {
	if ($cost == 1) {
		return 'Pay what you can';
	} elseif ($cost > 1) {
		return "\${$cost} USD";
	} else {
		return 'Free';
	}
}


// pass in the workshop row as it comes from the database table
// add some columns with date / time stuff figured out
function format_workshop_startend(array $row) {
	
	$tzadd = ' ('.TIMEZONE.')';
	
	$row['showstart'] = \Wbhkit\friendly_date($row['start']).' '.\Wbhkit\friendly_time($row['start']);
	$row['showend'] = \Wbhkit\friendly_time($row['end']);
	$row['when'] = "{$row['showstart']}-{$row['showend']}".$tzadd;
	$row['showstart'] .= $tzadd;
	$row['showend'] .= $tzadd;
	return $row;
}

// used in fill_out_workshop_row and also get_sessions_to_come
// expects 'id' and 'capacity' to be set
function set_enrollment_stats(array $row) {
	
	global $lookups;
	$eh = new \EnrollmentsHelper();
	
	$enrollments = $eh->set_enrollments_for_workshop($row['id']);
	foreach ($enrollments as $sname => $svalue) {
		$row[$sname] = $svalue;
	}	
	
	$row['open'] = $row['capacity'] - $row['enrolled'];
	if ($row['open'] < 0) { $row['open'] = 0; }
	
	if ($row['enrolled'] >= $row['capacity']) { 
		$row['soldout'] = 1;
	} else {
		$row['soldout'] = 0;
	}	
	
	
	return $row;
}


function set_actual_revenue(array $row) {
	
	$stmt = \DB\pdo_query("select paid, pay_override from registrations r where r.workshop_id = :wid", array(':wid' => $row['id']));
	$total = 0;
	while ($reg = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		if ($reg['paid']) {
			$total += ($reg['pay_override'] ? $reg['pay_override'] : $row['cost']);
		}
	}
	$row['actual_revenue'] = $total;
	return $row;
}




function check_last_minuteness(array $wk) {
	
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


function get_unavailable_workshops() {
	
	$mysqlnow = date("Y-m-d H:i:s");
	
	$stmt = \DB\pdo_query("
select * from workshops where date(start) >= :when1 and when_public >= :when2 order by when_public asc, start asc", array(":when1" => $mysqlnow, ":when2" => $mysqlnow)); 
		
	$sessions = array();
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		$sessions[] = fill_out_workshop_row($row);
	}
	return $sessions;
	
}



function get_application_workshops() {
	
	$mysqlnow = date("Y-m-d H:i:s");
	
	$stmt = \DB\pdo_query("
select * from workshops where date(start) >= :when1 and when_public < :when2 and application = 1 order by start asc", array(":when1" => $mysqlnow, ":when2" => $mysqlnow)); 
		
	$sessions = array();
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		$sessions[] = fill_out_workshop_row($row);
	}
	return $sessions;
	
}

function get_workshops_dropdown(?string $start = null, ?string $end = null) {
	
	$stmt = \DB\pdo_query("select w.* from workshops w order by start desc");
	$workshops = array();
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		$row = format_workshop_startend($row);
		$workshops[$row['id']] = $row['title'].' ('.date('Y-M-d', strtotime($row['start'])).')';
	}
	return $workshops;
}


// for admins eyes only
function get_search_results(string $page = "1", ?string $needle = null) {
	global $view;
	
	// get IDs of workshops
	$sql = "select w.* from workshops w ";
	if ($needle) { $sql .= " where w.title like '%$needle%' "; }
	$sql .= " order by start desc"; // get all
		
	// prep paginator
	$paginator  = new \Paginator( $sql );
	$rows = $paginator->getData($page);
	$links = $paginator->createLinks(7, 'search results', $needle ? "&needle=".urlencode($needle) : null);

	// calculate enrollments, etc
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


function get_workshops_list_no_html() {
	
	// get IDs of workshops
	$mysqlnow = date("Y-m-d H:i:s");

	$sql = "(select w.* from workshops w where when_public < '$mysqlnow' and start >= '$mysqlnow')"; // get public ones to come
	
	$sql .=
		" UNION
		(select w.* from workshops w, xtra_sessions x where x.workshop_id = w.id and w.when_public < '$mysqlnow' and x.start >= '$mysqlnow')";  
	
	$sql .=
		" UNION
		(select w.* from workshops w, workshops_shows sw, shows s where sw.workshop_id = w.id and sw.show_id = s.id and w.when_public < '$mysqlnow' and s.start > '$mysqlnow')";  

	$sql .= " order by start asc";  // temporary, should be asc
	
	$stmt = \DB\pdo_query($sql);
	$workshops = array();
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		$workshops[] = fill_out_workshop_row($row);
	}

	return $workshops;
}

function get_unpaid_students() {
	// get IDs of workshops
	$mysql_lastmonth = date("Y-m-d H:i:s", strtotime("-8 weeks"));
	$mysqlnow = date("Y-m-d H:i:s", strtotime("now"));

	$stmt = \DB\pdo_query("
select r.workshop_id, w.title, u.email, u.display_name, r.user_id, w.start, w.cost
from workshops w, registrations r, users u
where 
(w.start >= date('$mysql_lastmonth') and w.start <= date('$mysqlnow'))
and r.workshop_id = w.id
and r.user_id = u.id
and r.status_id = ".ENROLLED."
and r.paid = 0
and w.cost > 0
order by w.start");
	
	$unpaid = array();	
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		$row['nice_name'] = ($row['display_name'] ? $row['display_name'] : $row['email']);
		$unpaid[] = $row;
	}
	return $unpaid;
}

// data only, for admin_calendar
function get_sessions_to_come(bool $get_enrollments = true) {
	
	// get IDs of workshops
	$mysqlnow = date("Y-m-d H:i:s", strtotime("-3 hours"));
	
	$stmt = \DB\pdo_query("
(select w.id, title, start, end, capacity, cost, 0 as xtra, 0 as class_show, notes, teacher_id, co_teacher_id, 1 as rank, '' as override_url, online_url, application
from workshops w
where start >= date('$mysqlnow'))
union
(select x.workshop_id, w.title, x.start, x.end, w.capacity, w.cost, 1 as xtra,  0 as class_show, w.notes, w.teacher_id, w.co_teacher_id, x.rank, x.online_url as override_url, w.online_url, w.application
from xtra_sessions x, workshops w 
where w.id = x.workshop_id and x.start >= date('$mysqlnow'))
union
(select ws.workshop_id, w.title, s.start, s.end, w.capacity, w.cost, 1 as xtra, 1 as class_show, w.notes, s.teacher_id, 0 as co_teacher_id, 0 as rank, null as override_url, s.online_url, w.application
	from shows s, workshops w, workshops_shows ws
	where ws.show_id = s.id and ws.workshop_id = w.id
	and s.start >= date('$mysqlnow'))
order by start asc"); 
	
	$teachers = \Teachers\get_all_teachers(); // avoid getting same teacher multiple times	
	$sessions = array();
	$enrollments = array();
	
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		$teach = \Teachers\find_teacher_in_teacher_array($row['teacher_id'], $teachers);
		if ($teach) {
			$row['teacher_name'] = $teach['nice_name'];
			$row['teacher_user_id'] = $teach['user_id'];
		}
		
		if ($row['co_teacher_id']) {
			$co_teach = \Teachers\find_teacher_in_teacher_array($row['co_teacher_id'], $teachers);
			if ($co_teach) {
				$row['co_teacher_name'] = $co_teach['nice_name'];
				$row['co_teacher_user_id'] = $co_teach['user_id'];
			}
			
		}
		
		$row['costdisplay'] = figure_costdisplay($row['cost']);
		
		if ($get_enrollments) {
			foreach ($enrollments as $e_wid => $e_row) {
				if ($row['id'] == $e_wid) {
					$sessions[] = array_merge($row, $e_row);
					continue(2);
				}
			}
			$enrollments[$row['id']] = set_enrollment_stats(array('id' => $row['id'], 'capacity' => $row['capacity']));
			$sessions[] = array_merge($row, $enrollments[$row['id']]);
		} else {
			$sessions[] = $row;
		}
	}
	return $sessions;
}


function how_many_paid(array $wk) {
	$stmt = \DB\pdo_query("select count(*) as total_paid from registrations where workshop_id = :wid and paid = 1", array(':wid' => $wk['id']));
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		return $row['total_paid'];
	}
	return 0;
}

// for "revenues" page
function get_workshops_list_bydate(?string $start = null, ?string $end = null, bool $byclass = true) {
	if (!$start) { $start = "Jan 1 1000"; }
	if (!$end) { $end = "Dec 31 3000"; }
	
	$stmt = \DB\pdo_query("select w.* from workshops w WHERE w.start >= :start and w.start <= :end order by ".($byclass ? '' : ' teacher_id, ')." start desc", array(':start' => date('Y-m-d H:i:s', strtotime($start)), ':end' => date('Y-m-d H:i:s', strtotime($end))));
	
	$workshops = array();
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		$workshops[$row['id']] = fill_out_workshop_row($row);
	}
	return $workshops;
}	


function get_teacher_pay(array $wk) {
	
	if ($wk['teacher_id'] == 1) {
		return 0;
	} else {
		return ($wk['total_class_sessions'] * $wk['teacher_info']['default_rate']) +
			($wk['total_show_sessions'] * ($wk['teacher_info']['default_rate'] / 2));
	}

	return 0;
}


// for "payroll" page
function get_sessions_bydate(?string $start = null, ?string $end = null) {
	if (!$start) { $start = "Jan 1 1000"; }
	if (!$end) { $end = "Dec 31 3000"; }
	
	//echo "select w.* from workshops w WHERE w.start >= '".date('Y-m-d H:i:s', strtotime($start))."' and w.end <= '".date('Y-m-d H:i:s', strtotime($end))."' order by start desc";
	
	// get IDs of workshops
	$mysqlstart = date("Y-m-d H:i:s", strtotime($start));
	$mysqlend = date("Y-m-d H:i:s", strtotime($end));
	$mysqlnow = date("Y-m-d H:i:s", strtotime("-3 hours"));
	
	$stmt = \DB\pdo_query("
(select w.id, 0 as xtra_id, 0 as show_id, title, start, end, capacity, cost, 0 as xtra, 0 as class_show, notes, teacher_id, 1 as rank, '' as override_url, online_url, when_teacher_paid, actual_pay, 0 as show_teacher_id
from workshops w 
where w.start >= :start1 and w.end <= :end1)
union
(select x.workshop_id, x.id as xtra_id, 0 as show_id,  w.title, x.start, x.end, w.capacity, w.cost, 1 as xtra, 0 as class_show, w.notes, w.teacher_id, x.rank, x.online_url as override_url, w.online_url, x.when_teacher_paid, x.actual_pay, 0 as show_teacher_id 
from xtra_sessions x, workshops w
where w.id = x.workshop_id and x.start >= :start2 and x.end <= :end2)
union
(select ws.workshop_id, 0 as xtra_id, s.id as show_id, w.title, s.start, s.end, w.capacity, w.cost, 1 as xtra, 1 as class_show, w.notes, w.teacher_id, 0 as rank, s.online_url as override_url, w.online_url, s.when_teacher_paid, s.actual_pay, s.teacher_id as show_teacher_id
from workshops_shows ws, workshops w, shows s
where w.id = ws.workshop_id and ws.show_id = s.id and s.start >= :start3 and s.end <= :end3)
order by teacher_id, start asc
",
array(':start1' => $mysqlstart,
':end1' => $mysqlend,
':start2' => $mysqlstart,
':end2' => $mysqlend,
':start3' => $mysqlstart,
':end3' => $mysqlend)); 	
	
//	$stmt = \DB\pdo_query("select w.* from workshops w WHERE w.start >= :start and w.end <= :end order by teacher_id, start desc", array(':start' => date('Y-m-d H:i:s', strtotime($start)), ':end' => date('Y-m-d H:i:s', strtotime($end))));
	
	$sessions = array();
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		$t = \Teachers\get_teacher_by_id($row['teacher_id']);
		$row['teacher_name'] = $t['nice_name']; 
		$row['teacher_default_rate'] = $t['default_rate'];
		$sessions[] = $row;
	}
	return $sessions;
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
		'when_public' => null,
		'sold_out_late' => null,
		'teacher_id' => 1,
		'co_teacher_id' => null,
		'reminder_sent' => 0,
		'application' => 0
	);
}

function add_workshop_form(array $wk) {
	return "<form id='add_wk' action='/admin-workshop/ad' method='post' novalidate>".
	\Wbhkit\form_validation_javascript('add_wk').
	"<fieldset name='session_add'><legend>Add Workshop</legend>".
	workshop_fields($wk).
	\Wbhkit\submit('Add').
	"</fieldset></form>";
	
}

function workshop_fields(array $wk) {
	
	global $lookups;
	
	return \Wbhkit\texty('title', $wk['title'], null, null, null, 'Required', ' required ').
	\Wbhkit\drop('lid', $lookups->locations_drop(), $wk['location_id'], 'Location', null, 'Required', ' required ').
	\Wbhkit\textarea('online_url', $wk['online_url'], 'Online URL').	
	\Wbhkit\texty('start', $wk['start'], null, null, null, 'Required', ' required ').
	\Wbhkit\texty('end', $wk['end'], null, null, null, 'Required', ' required ').
	\Wbhkit\texty('cost', $wk['cost']).
	\Wbhkit\texty('capacity', $wk['capacity']).
	\Wbhkit\checkbox('application', 1, 'Taking applications', $wk['application']).
	\Wbhkit\textarea('notes', $wk['notes']).
	\Wbhkit\drop('teacher_id', \Teachers\teachers_dropdown_array(), $wk['teacher_id'], 'Teacher', null, 'Required', ' required ').
	\Wbhkit\drop('co_teacher_id', \Teachers\teachers_dropdown_array(), $wk['co_teacher_id'], 'Co-teacher', null).
	\Wbhkit\texty('when_public', $wk['when_public'], 'When Public').
	\Wbhkit\checkbox('reminder_sent', 1, 'Reminder sent?', $wk['reminder_sent']);
	
}

// $ac can be 'up' or 'ad'
function add_update_workshop(array $wk, string $ac = 'up') {
	
	global $last_insert_id;
	
	// fussy MySQL 5.7
	if (!$wk['cost']) { $wk['cost'] = 0; }
	if (!$wk['capacity']) { $wk['capacity'] = 0; }
	if (!$wk['when_public']) { $wk['when_public'] = NULL; }
	if (!$wk['start']) { $wk['start'] = NULL; }
	if (!$wk['end']) { $wk['end'] = NULL; }
	if (!$wk['teacher_id']) { $wk['teacher_id'] = 1; }
	if (!$wk['co_teacher_id']) { $wk['co_teacher_id'] = NULL; }
	if (!$wk['reminder_sent']) { $wk['reminder_sent'] = 0; }
	if (!$wk['application']) { $wk['application'] = 0; }
	
		
	$params = array(':title' => $wk['title'],
		':start' => date('Y-m-d H:i:s', strtotime($wk['start'])),
		':end' => date('Y-m-d H:i:s', strtotime($wk['end'])),
		':cost' => $wk['cost'],
		':capacity' => $wk['capacity'],
		':lid' => $wk['location_id'],
		':online_url' => $wk['online_url'],
		':notes' => $wk['notes'],
		':public' => date('Y-m-d H:i:s', strtotime($wk['when_public'])),
		':tid' => $wk['teacher_id'],
		':ctid' => $wk['co_teacher_id'],
		':reminder_sent' => $wk['reminder_sent'],
		':application' => $wk['application']);
		
		if ($ac == 'up') {
			$params[':wid'] = $wk['id'];
			$sql = "update workshops set title = :title, start = :start, end = :end, cost = :cost, capacity = :capacity, location_id = :lid, online_url = :online_url,  notes = :notes, when_public = :public, reminder_sent = :reminder_sent, teacher_id = :tid, co_teacher_id = :ctid, application = :application where id = :wid";			
			$stmt = \DB\pdo_query($sql, $params);
			return $wk['id'];
		} elseif ($ac = 'ad') {
			$stmt = \DB\pdo_query("insert into workshops (title, start, end, cost, capacity, location_id, online_url, notes, when_public, reminder_sent, teacher_id, co_teacher_id, application)
			VALUES (:title, :start, :end, :cost, :capacity, :lid, :online_url,  :notes,  :public, :reminder_sent, :tid, :ctid, :application)",
			$params);
			return $last_insert_id; // set as a global by my dbo routines
		}
	
}

function delete_workshop(int $workshop_id) {
	$stmt = \DB\pdo_query("delete from workshops_shows where workshop_id = :wid", array(':wid' => $workshop_id));
	$stmt = \DB\pdo_query("delete from registrations where workshop_id = :wid", array(':wid' => $workshop_id));
	$stmt = \DB\pdo_query("delete from xtra_sessions where workshop_id = :wid", array(':wid' => $workshop_id));
	$stmt = \DB\pdo_query("delete from workshops where id = :wid", array(':wid' => $workshop_id));

}

function is_public(array $wk) {
	if (isset($wk['when_public']) && $wk['when_public'] && strtotime($wk['when_public']) > time()) {
		return false;
	}
	return true;
}

function is_complete_workshop(array $wk) {
	if (is_array($wk) && isset($wk['id']) && $wk['id']) {
		return true;
	}
	return false;
}

function get_class_shows(array $wk) {
	$class_shows = array();

	$stmt = \DB\pdo_query("select s.*
		from workshops_shows ws, shows s
		where ws.show_id = s.id and ws.workshop_id = :id order by start", array(':id' => $wk['id']));
		
	//echo \DB\interpolateQuery("select show_idvfrom workshops_shows wsvxwhere ws.workshop_id = :id", array(':id' => $wk['id']));
	
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		$cs = new \ClassShow();
		$cs->set_into_fields($row);
		$cs->format_row();
		$class_shows[] = $cs;
	}
	return $class_shows;
}

function get_cut_and_paste_roster(array $wk, ?array $enrolled = null) {
	$names = array();
	$just_emails = array();
	$eh = new \EnrollmentsHelper();
	
	if (!isset($enrolled)) {
		$enrolled = $eh->get_students($wk['id'], ENROLLED);
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
			$class_dates .= "{$s['friendly_when']}".
			($s['online_url'] ? " - {$s['online_url']}" : '')."\n";
		}
	}
	if ($class_dates) {
		$class_dates = "\n\nClass Sessions:\n------------\n{$class_dates}";
	}
	
	$class_shows_text = '';
	foreach ($wk['class_shows'] as $cs) {
		$class_shows_text .= "{$cs->fields['friendly_when']}";
		if ($cs->fields['online_url'] != $wk['online_url']) {
			$class_shows_text .= ", {$cs->fields['online_url']}";
		}
		$class_shows_text .= "\n";
	}
	if ($class_shows_text) {
		$class_shows_text = "\n\nClass Shows:\n------------\n{$class_shows_text}\n";
	}

	
	return 
		preg_replace("/\n\n+/", 
					"\n\n", 
					"{$wk['title']} - {$wk['showstart']}\n\n".
					"Main zoom link:\n".($wk['location_id'] == ONLINE_LOCATION_ID ? "{$wk['online_url']}\n" : '').
						$class_dates.
						$class_shows_text.
					"\nNames and Emails\n---------------\n".implode("\n", $names)."\n\nJust the emails\n---------------\n".implode(",\n", $just_emails));
	
}

function get_recent_workshops_dropdown(int $limit = 40) {
	$stmt = \DB\pdo_query("select * from workshops order by id desc limit $limit");
	$all = array();
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		$all[$row['id']] = $row['title']. ' ('.	\Wbhkit\friendly_date($row['start']).' '.\Wbhkit\friendly_time($row['start']).')';
	}
	return $all;
}

function parse_online_url($row) {
	
	preg_match('/^(\S+)\s*([\S\s]*)/', $row['online_url'], $url_parts);

	$row['online_url_just_url'] = ($url_parts[1] ?? '');
	$row['online_url_the_rest'] = preg_replace('/\n/', '<br>', $url_parts[2] ?? '');
	$row['online_url_display'] = preg_replace('/\n/', '<br>', $row['online_url']);
	
	return $row;
	
}

