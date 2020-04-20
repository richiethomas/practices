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

function add_xtra_session($workshop_id, $start, $end) {
	$stmt = \DB\pdo_query("insert into xtra_sessions (workshop_id, start, end)
	VALUES (:wid, :start, :end)",
	array(':wid' => $workshop_id, 
	':start' => date('Y-m-d H:i:s', strtotime($start)), 
	':end' => date('Y-m-d H:i:s', strtotime($end))));
	
}

function delete_xtra_session($xtra_session_id) {
	
	$stmt = \DB\pdo_query("delete from xtra_sessions where id = :id", array(':id' => $xtra_session_id));
	return true;
	
}
	
