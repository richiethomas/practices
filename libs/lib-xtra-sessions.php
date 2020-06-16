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

function add_xtra_session($workshop_id, $start, $end, $class_show = 0) {
	if (!$class_show) { $class_show = 0; }	
	$stmt = \DB\pdo_query("insert into xtra_sessions (workshop_id, start, end, class_show)
	VALUES (:wid, :start, :end, :class_show)",
	array(':wid' => $workshop_id, 
	':start' => date('Y-m-d H:i:s', strtotime($start)), 
	':end' => date('Y-m-d H:i:s', strtotime($end)),
	':class_show' => $class_show));
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
	
