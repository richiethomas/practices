<?php
	
namespace Teachers;


function get_teacher_info($uid) {
	$stmt = \DB\pdo_query("select * from teachers where user_id = :id", array(':id' => $uid));
	while ($row = $stmt->fetch()) {
		return $row;
	}
	return false;
}

function add_teacher_row($uid) {
	
}

function get_teacher_form($t = null) {
	global $sc;
	if (!$t && !isset($t['id'])) {
		$t = empty_teacher();
	}
	return 
		"<form id='update_teacher' action='$sc' method='post'>".
		/Wbhkit/hidden('id', $t['id']).
		/Wbhkit/textarea('bio', $t['bio']).
		/Wbhkit/texty('house_cut', $t['bio']).
		/Wbhkit/checbox('active', 1, null, $t['active']).
		/Wbhkit/submit('Update').
		"</form>";		
}

function get_teacher_photo($t) {
	
}


function update_teacher_info($t) {
	
}

function empty_teacher() {
	return array(
		'id' => null,
		'user_id' => null,
		'house_cut' => null,
		'bio' => null,
		'active' => 0
	);
	
}


