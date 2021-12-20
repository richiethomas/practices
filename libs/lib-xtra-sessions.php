<?php
	
namespace XtraSessions;


function get_xtra_sessions(int $workshop_id) {
	
	$stmt = \DB\pdo_query("
select *
from xtra_sessions x
where workshop_id = :id 
order by start", array(':id' => $workshop_id));
	$sessions = array();
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		$row['friendly_when'] = \Wbhkit\friendly_when($row['start']).'-'.\Wbhkit\friendly_time($row['end']);
		
		$row = \Workshops\parse_online_url($row);
		
		$sessions[] = $row;
	}
	return $sessions;
}	


function get_xtra_session(int $xtra_id = 0) {
	
	if (!$xtra_id) { return empty_xtra_session(); }
	
	$stmt = \DB\pdo_query("select * from xtra_sessions where id = :id", array(':id' => $xtra_id));
	$sessions = array();
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		$row['friendly_when'] = \Wbhkit\friendly_when($row['start']).'-'.\Wbhkit\friendly_time($row['end']);
		
		$row = \Workshops\parse_online_url($row);
		
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
		'reminder_sent' => null
	);
}

function xtra_session_fields(array $wk) {
	
	return
	\Wbhkit\texty('start_xtra', null, null, null, null, 'Required', ' required ').
	\Wbhkit\texty('end_xtra', null, null, null, null, 'Required', ' required ').
	\Wbhkit\textarea('online_url_xtra').
	\Wbhkit\checkbox('class_show', 1);
	
}

function add_xtra_session(int $workshop_id, string $start, string $end, ?string $online_url = null, ?int $class_show = 0) {
	
	
	$stmt = \DB\pdo_query("insert into xtra_sessions (workshop_id, start, end, online_url, class_show)
	VALUES (:wid, :start, :end, :url, :class_show)",
	array(':wid' => $workshop_id, 
	':start' => date('Y-m-d H:i:s', strtotime($start)), 
	':end' => date('Y-m-d H:i:s', strtotime($end)),
	':url' => $online_url,
	':class_show' => $class_show));
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

function add_a_week(array $wk, ?int $class_show = 0) {
	//function add_xtra_session($workshop_id, $start, $end, $online_url = null) {

	$start = $wk['start'];
	$end = $wk['end'];
	foreach ($wk['sessions'] as $s) {
		$start = $s['start'];
		$end = $s['end'];
	}
	
	$start = date('Y-m-d H:i:s', strtotime("+1 week", strtotime($start)));
	$end = date('Y-m-d H:i:s', strtotime("+1 week", strtotime($end)));
	
	add_xtra_session($wk['id'], $start, $end, $class_show);
	return \Workshops\fill_out_workshop_row($wk); 

}


