<?php
	
namespace XtraSessions;


function get_xtra_sessions($workshop_id) {
	
	$stmt = \DB\pdo_query("select * from xtra_sessions where workshop_id = :id order by start", array(':id' => $workshop_id));
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
		'class_show' => null,
		'rank' => null,
		'online_url' => null,
		'reminder_sent' => null
	);
}

function xtra_session_fields($wk) {
	
	return
	\Wbhkit\texty('start', null, null, null, null, 'Required', ' required ').
	\Wbhkit\texty('end', null, null, null, null, 'Required', ' required ').
	\Wbhkit\texty('online_url').
	\Wbhkit\checkbox('class_show', 1, 'Class Show?', 0).				
	\Wbhkit\hidden('wid', $wk['id']);
	
}

function add_xtra_session($workshop_id, $start, $end, $class_show = 0, $online_url = null) {
	if (!$class_show) { $class_show = 0; }	
	$stmt = \DB\pdo_query("insert into xtra_sessions (workshop_id, start, end, class_show, online_url)
	VALUES (:wid, :start, :end, :class_show, :url)",
	array(':wid' => $workshop_id, 
	':start' => date('Y-m-d H:i:s', strtotime($start)), 
	':end' => date('Y-m-d H:i:s', strtotime($end)),
	':class_show' => $class_show,
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

function add_sessions_to_when($when, $sessions) {
	
	$sessions_list = '';
	if (!empty($sessions)) {
		$sessions_list = "{$when}"; // first session is the $when
		foreach ($sessions as $s) {
			$sessions_list .= "<br>\n{$s['friendly_when']}".($s['class_show'] ? ' <b>(show)</b> ': '')."";
		}
		$sessions_list .= "<br>\n";
		return $sessions_list; // return the list of sessions, which includes the $when
	}
	return $when; // if sessions is empty, return just the $when
	
}
	
