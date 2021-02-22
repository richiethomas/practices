<?php
	
namespace XtraSessions;


function get_xtra_sessions($workshop_id) {
	
	$stmt = \DB\pdo_query("
select *
from xtra_sessions x
where workshop_id = :id 
order by start", array(':id' => $workshop_id));
	$sessions = array();
	while ($row = $stmt->fetch()) {
		$row['friendly_when'] = \Wbhkit\friendly_when($row['start']).'-'.\Wbhkit\friendly_time($row['end']);
		$sessions[] = $row;
	}
	return $sessions;
}	


function get_xtra_session($xtra_id = 0) {
	
	if (!$xtra_id) { return empty_xtra_session(); }
	
	$stmt = \DB\pdo_query("select * from xtra_sessions where id = :id", array(':id' => $xtra_id));
	$sessions = array();
	while ($row = $stmt->fetch()) {
		$row['friendly_when'] = \Wbhkit\friendly_when($row['start']).'-'.\Wbhkit\friendly_time($row['end']);
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
		'rank' => null,
		'online_url' => null,
		'reminder_sent' => null
	);
}

function xtra_session_fields($wk) {
	
	return
	\Wbhkit\texty('start_xtra', null, null, null, null, 'Required', ' required ').
	\Wbhkit\texty('end_xtra', null, null, null, null, 'Required', ' required ').
	\Wbhkit\texty('online_url_xtra').
	\Wbhkit\hidden('wid', $wk['id']);
	
}

function add_xtra_session($workshop_id, $start, $end, $online_url = null) {
	$stmt = \DB\pdo_query("insert into xtra_sessions (workshop_id, start, end, online_url)
	VALUES (:wid, :start, :end, :url)",
	array(':wid' => $workshop_id, 
	':start' => date('Y-m-d H:i:s', strtotime($start)), 
	':end' => date('Y-m-d H:i:s', strtotime($end)),
	':url' => $online_url));
	update_ranks($workshop_id);
	
}

function update_all_ranks() {
	$stmt = \DB\pdo_query("select distinct(workshop_id) from xtra_sessions");
	while ($row = $stmt->fetch()) {
		update_ranks($row['workshop_id']);
	}
	
}

function update_ranks($workshop_id)  {
	$stmt = \DB\pdo_query("select * from xtra_sessions where workshop_id = :id order by start", array(':id' => $workshop_id));
	$sessions = array();
	$rank = 2;
	while ($row = $stmt->fetch()) {
		set_one_rank($row['id'], $rank);
		$rank++;
	}
	return true;
}

function set_one_rank($xtra_session_id, $rank) {
	if (\DB\pdo_query("update xtra_sessions set rank = :rank where id = :id", array(':id' => $xtra_session_id, ':rank' => $rank))) {
		return true;
	}
	return false;
}

function delete_xtra_session($xtra_session_id) {
	
	$stmt = \DB\pdo_query("delete from xtra_sessions where id = :id", array(':id' => $xtra_session_id));
	return true;
	
}

function add_a_week($wk) {
	//function add_xtra_session($workshop_id, $start, $end, $online_url = null) {

	$start = $wk['start'];
	$end = $wk['end'];
	foreach ($wk['sessions'] as $s) {
		$start = $s['start'];
		$end = $s['end'];
	}
	
	$start = date('Y-m-d H:i:s', strtotime("+1 week", strtotime($start)));
	$end = date('Y-m-d H:i:s', strtotime("+1 week", strtotime($end)));
	
	add_xtra_session($wk['id'], $start, $end);
	return \Workshops\fill_out_workshop_row($wk); 
	
	

}

	
