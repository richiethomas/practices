<?php
	
namespace XtraSessions;


function get_xtra_sessions(int $workshop_id) {
	
	global $wk;
	
	$stmt = \DB\pdo_query("
select *
from xtra_sessions x
where workshop_id = :id 
order by start", array(':id' => $workshop_id));
	$sessions = array();
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		
		$row = $wk->format_times_one_level($row);
		$row['url'] = $wk->parse_online_url($row['online_url']);
		$sessions[] = $row;
	}
	return $sessions;
}	


function get_xtra_session(int $xtra_id = 0) {
	
	if (!$xtra_id) { return empty_xtra_session(); }
	global $wk;
	
	$stmt = \DB\pdo_query("select * from xtra_sessions where id = :id", array(':id' => $xtra_id));
	$sessions = array();
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		$row = $wk->format_times_one_level($row);
		$row['url'] = $wk->parse_online_url($row['online_url']);
		return $row;
	}
	return empty_xtra_session();
}	

function empty_xtra_session() {
	return array(
		'id' => null,
		'workshop_id' => null,
		'start' => null,
		'end' => null,
		'class_show' => 0,
		'rank' => null,
		'online_url' => null,
		'reminder_sent' => null,
		'location_id' => null
	);
}

function xtra_session_fields() {
	
	global $lookups;
	
	$location_opts = $lookups->locations_drop();
	$locaiton_opts[0] = '';
	
	return
	\Wbhkit\texty('start_xtra', null, null, null, null, 'Required', ' required ').
	\Wbhkit\texty('end_xtra', null, null, null, null, 'Required', ' required ').
	\Wbhkit\textarea('online_url_xtra').
	\Wbhkit\drop('location_id', $location_opts).
	\Wbhkit\checkbox('class_show', 1);
	
}

function add_xtra_session(int $workshop_id, string $start, string $end, ?string $online_url = null, ?int $class_show = 0, ?int $location_id = 0) {
	
	
	$stmt = \DB\pdo_query("insert into xtra_sessions (workshop_id, start, end, online_url, class_show, location_id)
	VALUES (:wid, :start, :end, :url, :class_show, :location_id)",
	array(':wid' => $workshop_id, 
	':start' => date(MYSQL_FORMAT, strtotime($start)), 
	':end' => date(MYSQL_FORMAT, strtotime($end)),
	':url' => $online_url,
	':class_show' => $class_show,
	':location_id' => $location_id));
	update_ranks($workshop_id);
	
}

function update_all_ranks() {
	$stmt = \DB\pdo_query("select distinct(workshop_id) from xtra_sessions");
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		update_ranks($row['workshop_id']);
	}
	
}

function update_ranks(int $workshop_id)  {
	$stmt = \DB\pdo_query("select * from xtra_sessions where workshop_id = :id order by start", array(':id' => $workshop_id));
	$sessions = array();
	$rank = 2;
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		set_one_rank($row['id'], $rank);
		$rank++;
	}
	return true;
}

function set_one_rank(int $xtra_session_id, int $rank) {
	if (\DB\pdo_query("update xtra_sessions set rank = :rank where id = :id", array(':id' => $xtra_session_id, ':rank' => $rank))) {
		return true;
	}
	return false;
}

function delete_xtra_session(int $xtra_session_id) {
	
	$stmt = \DB\pdo_query("delete from xtra_sessions where id = :id", array(':id' => $xtra_session_id));
	return true;
	
}

function add_a_week(\Workshop $wk, ?int $class_show = 0, ?int $location_id = 0) {
	//function add_xtra_session($workshop_id, $start, $end, $online_url = null) {

	$start = $wk->fields['start'];
	$end = $wk->fields['end'];
	foreach ($wk->sessions as $s) {
		$start = $s['start'];
		$end = $s['end'];
	}
	
	$start = date(MYSQL_FORMAT, strtotime("+1 week", strtotime($start)));
	$end = date(MYSQL_FORMAT, strtotime("+1 week", strtotime($end)));
	
	add_xtra_session($wk->fields['id'], $start, $end, $class_show, $location_id);
	$wk->finish_setup();
	return $wk;

}


