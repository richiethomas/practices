<?php
namespace Workshops;
	
// workshops
function get_recent_workshops_simple(?int $limit = 100) {
	
	$stmt = \DB\pdo_query("select * from workshops order by id desc limit $limit");
	$all = array();
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		$all[$row['id']] = $row;
	}
	return $all;
}


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
	$row['tags_array'] = get_tags($row['tags']);	
	
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

	// xtra session stuff
	$row = fill_out_xtra_sessions($row);
	
	// set full when
	$row['full_when'] = $row['when'];
	if (!empty($row['sessions'])) {
		$row['full_when'] .= "<br>\n";
		foreach ($row['sessions'] as $s) {
			$row['full_when'] .= 
				($s['class_show'] == 1 ? 'Show: ' : '').
				"{$s['friendly_when']}<br>\n";
		}
	}

	// set full when - cali time
	$row['full_when_cali'] = $row['when_cali'];
	if (!empty($row['sessions'])) {
		$row['full_when_cali'] .= "<br>\n";
		foreach ($row['sessions'] as $s) {
			$row['full_when_cali'] .= 
				($s['class_show'] == 1 ? 'Show: ' : '').
				"{$s['friendly_when_cali']}<br>\n";
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
	
	global $u; // $u->fields['time_zone'] is set in User class
		
	$tz = $u->fields['time_zone'];

	$tzadd = " ({$u->fields['time_zone_friendly']})";
	
	$row['start_tz'] = \Wbhkit\convert_tz($row['start'], $tz);
	$row['end_tz'] = \Wbhkit\convert_tz($row['end'], $tz);
	$row['when_public_tz'] = ((isset($row['when_public']) && $row['when_public']) ? \Wbhkit\convert_tz($row['when_public'], $tz) : null);
	
	$row['showstart'] = \Wbhkit\friendly_date($row['start_tz']).' '.\Wbhkit\friendly_time($row['start_tz']);
	$row['showend'] = \Wbhkit\friendly_time($row['end_tz']);
	$row['when'] = "{$row['showstart']}-{$row['showend']}".$tzadd;
	$row['showstart'] .= $tzadd;
	$row['showend'] .= $tzadd;	

	$tzcali = " (".TIMEZONE.")";
	$row['showstart_cali'] = \Wbhkit\friendly_date($row['start']).' '.\Wbhkit\friendly_time($row['start']);
	$row['showend_cali'] = \Wbhkit\friendly_time($row['end']);
	$row['when_cali'] = "{$row['showstart_cali']}-{$row['showend_cali']}".$tzcali;
	$row['showstart_cali'] .= $tzcali;
	$row['showend_cali'] .= $tzcali;	
	
	
	
	foreach (array('start', 'end', 'when_public') as $tv) {
		if (isset($row[$tv])) {
			$row[$tv] = \Wbhkit\present_ts($row[$tv]);
		}
	}
	
	return $row;
}


function fill_out_xtra_sessions($row) {
	// xtra session info
	$row['sessions'] = \XtraSessions\get_xtra_sessions($row['id']);	
	$row['total_class_sessions'] = 1;
	$row['total_show_sessions'] = 0;
	$row['total_sessions'] = 1;
	foreach ($row['sessions'] as $sess) {
		if ($sess['class_show'] == 1) {
			$row['total_show_sessions']++;
		} else {
			$row['total_class_sessions']++;
		}
		$row['total_sessions']++;
	}

	$row['time_summary'] = $row['total_class_sessions'].' class'.\Wbhkit\plural($row['total_class_sessions'], '', 'es');

	if ($row['total_show_sessions'] > 0) {
		$row['time_summary'] .= ', '.$row['total_show_sessions'].' show'.\Wbhkit\plural($row['total_show_sessions']);
	}

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
	
	$mysqlnow = date(MYSQL_FORMAT);
	
	$stmt = \DB\pdo_query("
select * from workshops where date(start) >= :when1 and when_public >= :when2 and hidden = 0 order by when_public asc, start asc", array(":when1" => $mysqlnow, ":when2" => $mysqlnow)); 
		
	$sessions = array();
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		$sessions[] = fill_out_workshop_row($row);
	}
	return $sessions;
	
}



function get_application_workshops() {
	
	$mysqlnow = date(MYSQL_FORMAT);
	
	$stmt = \DB\pdo_query("select * from workshops where date(start) >= :when1 and when_public < :when2 and application = 1 and hidden = 0 order by start asc", array(":when1" => $mysqlnow, ":when2" => $mysqlnow)); 
		
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
	if ($needle) { 
		$sql .= " , teachers t, users u 
		where (w.teacher_id = t.id or w.co_teacher_id = t.id) 
		and t.user_id = u.id
		and (w.title like '%$needle%' or u.display_name like '%$needle%')"; 
	}
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
	$mysqlnow = date(MYSQL_FORMAT);

	$sql = "(select w.* from workshops w where when_public < '$mysqlnow' and start >= '$mysqlnow' and w.hidden = 0)"; // get public ones to come
	
	$sql .=
		" UNION
		(select w.* from workshops w, xtra_sessions x where x.workshop_id = w.id and w.when_public < '$mysqlnow' and x.start >= '$mysqlnow' and w.hidden = 0)";  
	
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
	$mysql_lastmonth = date(MYSQL_FORMAT, strtotime("-8 weeks"));
	$mysqlnow = date(MYSQL_FORMAT, strtotime("now"));

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

// for calendar
function get_sessions_to_come(bool $get_enrollments = true, bool $hidden = false) {
	
	global $lookups;
	
	// get IDs of workshops
	$mysqlnow = date(MYSQL_FORMAT, strtotime("-3 hours"));
	
	$stmt = \DB\pdo_query("
(select w.id, title, start, end, capacity, cost, 0 as xtra, 0 as class_show, notes, teacher_id, co_teacher_id, 1 as rank, '' as override_url, online_url, application, w.location_id, w.start as course_start
from workshops w
where start >= date('$mysqlnow') ".($hidden ? '' : " and w.hidden = 0").") 
union
(select x.workshop_id as id, w.title, x.start, x.end, w.capacity, w.cost, 1 as xtra,  x.class_show, w.notes, w.teacher_id, w.co_teacher_id, x.rank, x.online_url as override_url, w.online_url, w.application, w.location_id, w.start as course_start
from xtra_sessions x, workshops w 
where w.id = x.workshop_id and x.start >= date('$mysqlnow') ".($hidden ? '' : " and w.hidden = 0")." ) 
order by start asc"); 
	
	$teachers = \Teachers\get_all_teachers(); // avoid getting same teacher multiple times	
	$sessions = array();
	$enrollments = array();
	
	$total_sessions = array();
	
	
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
				
		
		$row['total_sessions'] = check_total_sessions($row['id'], $total_sessions);
		
		$row['lwhere'] = $lookups->locations[$row['location_id']]['lwhere'];
		
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
		
		$row = parse_online_url($row);
		$row = format_workshop_startend($row);
		
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

// used in "get sessions to come"
function check_total_sessions(int $wid, array &$total_sessions) {
	foreach ($total_sessions as $id => $val) {
		if ($wid == $id) {
			return $val;
		}
	}
	$row['id'] = $wid;
	$row = fill_out_xtra_sessions($row);
	$total_sessions[$wid] = $row['total_sessions'];
	return $row['total_sessions'];
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
	
	$stmt = \DB\pdo_query("select w.* from workshops w WHERE w.start >= :start and w.start <= :end order by ".($byclass ? '' : ' teacher_id, ')." start desc", array(':start' => date(MYSQL_FORMAT, strtotime($start)), ':end' => date(MYSQL_FORMAT, strtotime($end))));
	
	$workshops = array();
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		$workshops[$row['id']] = fill_out_workshop_row($row);
	}
	return $workshops;
}	


function get_teacher_pay(array $wk) {
	
	return ($wk['total_class_sessions'] * $wk['teacher_info']['default_rate']) +
		($wk['total_show_sessions'] * ($wk['teacher_info']['default_rate'] / 2));

}


// for "payroll" page
function get_sessions_bydate(?string $start = null, ?string $end = null) {
	if (!$start) { $start = "Jan 1 1000"; }
	if (!$end) { $end = "Dec 31 3000"; }
	
	//echo "select w.* from workshops w WHERE w.start >= '".date(MYSQL_FORMAT, strtotime($start))."' and w.end <= '".date(MYSQL_FORMAT, strtotime($end))."' order by start desc";
	
	// get IDs of workshops
	$mysqlstart = date(MYSQL_FORMAT, strtotime($start));
	$mysqlend = date(MYSQL_FORMAT, strtotime($end));
	$mysqlnow = date(MYSQL_FORMAT, strtotime("-3 hours"));
	
	$stmt = \DB\pdo_query("
(select w.id, 0 as xtra_id, 0 as show_id, title, start, end, capacity, cost, 0 as xtra, 0 as class_show, notes, teacher_id, 1 as rank, '' as override_url, online_url, when_teacher_paid, actual_pay, 0 as show_teacher_id
from workshops w 
where w.start >= :start1 and w.end <= :end1)
union
(select x.workshop_id, x.id as xtra_id, 0 as show_id,  w.title, x.start, x.end, w.capacity, w.cost, 1 as xtra, class_show, w.notes, w.teacher_id, x.rank, x.online_url as override_url, w.online_url, x.when_teacher_paid, x.actual_pay, 0 as show_teacher_id 
from xtra_sessions x, workshops w
where w.id = x.workshop_id and x.start >= :start2 and x.end <= :end2)
order by teacher_id, start asc
",
array(':start1' => $mysqlstart,
':end1' => $mysqlend,
':start2' => $mysqlstart,
':end2' => $mysqlend)); 	
	
//	$stmt = \DB\pdo_query("select w.* from workshops w WHERE w.start >= :start and w.end <= :end order by teacher_id, start desc", array(':start' => date(MYSQL_FORMAT, strtotime($start)), ':end' => date(MYSQL_FORMAT, strtotime($end))));
	
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
		'application' => 0,
		'hidden' => 0,
		'tags' => null
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
	\Wbhkit\texty('tags', $wk['tags']).
	\Wbhkit\drop('lid', $lookups->locations_drop(), $wk['location_id'], 'Location', null, 'Required', ' required ').
	\Wbhkit\textarea('online_url', $wk['online_url'], 'Online URL').	
	\Wbhkit\texty('start', $wk['start'], null, null, null, 'Required', ' required ').
	\Wbhkit\texty('end', $wk['end'], null, null, null, 'Required', ' required ').
	\Wbhkit\texty('cost', $wk['cost']).
	\Wbhkit\texty('capacity', $wk['capacity']).
	\Wbhkit\checkbox('application', 1, 'Taking applications', $wk['application']).
	\Wbhkit\checkbox('hidden', 1, 'Hidden', $wk['hidden']).
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
	if (!$wk['hidden']) { $wk['hidden'] = 0; }
	if (!$wk['tags']) { $wk['tags'] = null; }
	
		
	$params = array(':title' => $wk['title'],
		':start' => date(MYSQL_FORMAT, strtotime($wk['start'])),
		':end' => date(MYSQL_FORMAT, strtotime($wk['end'])),
		':cost' => $wk['cost'],
		':capacity' => $wk['capacity'],
		':lid' => $wk['location_id'],
		':online_url' => $wk['online_url'],
		':notes' => $wk['notes'],
		':public' => date(MYSQL_FORMAT, strtotime($wk['when_public'])),
		':tid' => $wk['teacher_id'],
		':ctid' => $wk['co_teacher_id'],
		':reminder_sent' => $wk['reminder_sent'],
		':application' => $wk['application'],
		':hidden' => $wk['hidden'],
		':tags' => $wk['tags']
	);
		
		if ($ac == 'up') {
			$params[':wid'] = $wk['id'];
			$sql = "update workshops set title = :title, start = :start, end = :end, cost = :cost, capacity = :capacity, location_id = :lid, online_url = :online_url,  notes = :notes, when_public = :public, reminder_sent = :reminder_sent, teacher_id = :tid, co_teacher_id = :ctid, application = :application, hidden = :hidden, tags = :tags where id = :wid";			
			$stmt = \DB\pdo_query($sql, $params);
			return $wk['id'];
		} elseif ($ac = 'ad') {
			$stmt = \DB\pdo_query("insert into workshops (title, start, end, cost, capacity, location_id, online_url, notes, when_public, reminder_sent, teacher_id, co_teacher_id, application, hidden, tags)
			VALUES (:title, :start, :end, :cost, :capacity, :lid, :online_url,  :notes,  :public, :reminder_sent, :tid, :ctid, :application, :hidden, :tags)",
			$params);
			return $last_insert_id; // set as a global by my dbo routines
		}
	
}

function delete_workshop(int $workshop_id) {
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
			$class_dates .= 
			($s['class_show'] ? 'Show: ' : '').	
			"{$s['friendly_when']}".
			($s['online_url'] ? " - {$s['online_url']}" : '')."\n";
		}
	}
	if ($class_dates) {
		$class_dates = "\n\nClass Sessions:\n------------\n{$class_dates}";
	}


	
	return 
		preg_replace("/\n\n+/", 
					"\n\n", 
					"{$wk['title']} - {$wk['showstart']}\n\n".
					"Main zoom link:\n".($wk['location_id'] == ONLINE_LOCATION_ID ? "{$wk['online_url']}\n" : '').
						$class_dates.
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

function get_tags(string $tags_string = null) {
	
	$tags_array = array();
	if ($tags_string) {
		$tags_array = explode(',', $tags_string);
		foreach ($tags_array as $k =>$v) {
			$tags_array[$k] = strtolower(trim($v));
		}
		sort($tags_array);
	}
	return $tags_array;
	
}
function print_tags($wk) {
	
	$output = null;
		
	if (count($wk['tags_array']) > 0) {
		foreach ($wk['tags_array'] as $tag) {
			$output .= "\t\t<span data-tag='{$tag}' class='classtag badge bg-light text-dark rounded-pill me-3 border'>".strtoupper($tag)."</span>\n";
		}
	}

	if ($wk['open'] <= 2 && $wk['open'] > 0) {
		$output .= "\t\t<span data-tag='few spots left' class='classtag badge rounded-pill me-3 border dangerlight'>{$wk['open']} SPOT".strtoupper(\Wbhkit\plural($wk['open']))." LEFT</span>\n";
	}
	
	return $output;

}

function update_tags(int $id, string $tags) {
	$sql = "update workshops set tags = :tags where id = :id";			
	$stmt = \DB\pdo_query($sql, array(':id' => $id, ':tags' => $tags));
	return true;
}

function update_hidden(int $id, string $hidden) {
	$sql = "update workshops set hidden = :hidden where id = :id";			
	$stmt = \DB\pdo_query($sql, array(':id' => $id, ':hidden' => $hidden));
	return true;
}


function email_teacher_info($wk) {
	$output = null;	
	
	if ($wk['teacher_info']['student_email'] || ($wk['co_teacher_id'] && $wk['co_teacher_info']['student_email'])) {
		$output .= "TEACHER CONTACT<br>\n---------------<br>\n";
	}
	
	if ($wk['teacher_info']['student_email']) {
		$output .= "If you wish to contact your teacher, their contact email is: {$wk['teacher_info']['student_email']}.<br>\n";
	}
	if ($wk['co_teacher_id'] && $wk['co_teacher_info']['student_email']) {
		$output .= "If you wish to contact your co-teacher, their contact email is: {$wk['co_teacher_info']['student_email']}.<br>\n";
	}
	
	return $output;
}