<?php
	
namespace Teachers;


function is_teacher($uid) {
	$stmt = \DB\pdo_query("select t.*, u.email, u.display_name, u.ukey from teachers t, users u where u.id = t.user_id and t.user_id = :id", array(':id' => $uid));
	while ($row = $stmt->fetch()) {
		$row = fill_out_teacher_row($row); 
		return $row;
	}
	return false;
}

function get_teacher_by_id($tid) {
	$stmt = \DB\pdo_query("select t.*, u.email, u.display_name, u.ukey from teachers t, users u where u.id = t.user_id and t.id = :id", array(':id' => $tid));
	while ($row = $stmt->fetch()) {
		$row = fill_out_teacher_row($row); 
		return $row;
	}
	return false;
}

function fill_out_teacher_row($row) {
	$tempu = new \User();
	$tempu->replace_fields($row);
	$tempu->set_nice_name();
	return $tempu->fields; 
}




function make_teacher($uid) {
	
	global $last_insert_id;

	$stmt = \DB\pdo_query("select * from teachers where user_id = :id", array(':id' => $uid));
	while ($row = $stmt->fetch()) {
		return $row['id']; // if exists, return the teacher id
	}

	$stmt = \DB\pdo_query("insert into teachers (user_id) VALUES (:id)" , array(':id' => $uid));
	
	return $last_insert_id;
}

function get_teacher_form($t) {
	global $sc;
	if (!$t || !isset($t['id'])) {
		$t = empty_teacher();
	}
	return 
		"<form id='update_teacher' action='$sc' method='post'>".
		\Wbhkit\hidden('tid', $t['id']).
		\Wbhkit\hidden('ac', 'up').			
		\Wbhkit\textarea('bio', $t['bio']).
		\Wbhkit\checkbox('active', 1, null, $t['active']).
		\Wbhkit\texty('default_rate', $t['default_rate']).
		\Wbhkit\submit('Update').
		"</form>";		
}


function get_all_teachers($only_active = false) {
	$stmt = \DB\pdo_query("select t.*, u.email, u.display_name, u.ukey from teachers t, users u where t.user_id = u.id".($only_active ? ' and t.active = 1' : ''));
	$teachers = array();
	while ($row = $stmt->fetch()) {
		$row = fill_out_teacher_row($row); 
		$teachers[] = $row;
	}
	
	usort($teachers, function($a, $b) {
	    return $a['nice_name'] <=> $b['nice_name'];
	});
	
	return $teachers;
}

function get_faculty() {
	$teachers = get_all_teachers();
	$faculty = array();
	foreach ($teachers as $id => $t) {
		if ($t['active'] == 1) {
			$t['classes'] = get_teacher_upcoming_classes($t['id']);
			$faculty[] = $t;
		}
	}
	return $faculty;
}

function get_teacher_upcoming_classes($tid) {
	
	$workshops = array();
	// get all active teachers, and also upcoming courses they are teaching	
	$stmt = \DB\pdo_query("select wk.* from workshops wk where teacher_id = :tid and start > :now order by start", array(':now' => date("Y-m-d H:i:s"), ':tid' => $tid));
	while ($row = $stmt->fetch()) {
		$workshops[] = \Workshops\fill_out_workshop_row($row, false); // don't need enrollment stats 
	}
	return $workshops;
}

function get_teacher_all_classes($tid) {
	
	$workshops = array();
	// get all active teachers, and also upcoming courses they are teaching	
	$stmt = \DB\pdo_query("select wk.* from workshops wk where teacher_id = :tid order by start desc", array(':tid' => $tid));
	while ($row = $stmt->fetch()) {
		$workshops[] = \Workshops\fill_out_workshop_row($row, false); // don't need enrollment stats 
	}
	return $workshops;
}


function teachers_dropdown_array() {
	$teachers = get_all_teachers();
	$opts = array();
	foreach ($teachers as $t) {
		$opts[$t['id']] = $t['nice_name'];
	}
	return $opts;
}

function get_teacher_photo_src($uid) {
	if (file_exists("photos/user_{$uid}.jpg")) {
		return "/photos/user_{$uid}.jpg";
	} else {
		return false;
	}
}

function teacher_photo($uid, $xtra_classes = null) {
	if ($src = get_teacher_photo_src($uid)) {
		$row = is_teacher($uid);
		return "<a href='teachers.php?tid={$row['id']}'><img class='img-fluid border $xtra_classes' src='$src'></a>";
	}
	return "";
}

function upload_teacher_photo_form($t) {
	global $sc;
	return
		"<form action=\"$sc\" method=\"post\" enctype=\"multipart/form-data\">\n".
		\Wbhkit\hidden ('tid', $t['id']).
	\Wbhkit\hidden ('ac', 'photo').
	\Wbhkit\hidden ('MAX_FILE_SIZE', USER_PHOTO_MAX_BYTES).
	\Wbhkit\fileupload('teacher_photo', 'Upload/Replace Teacher Photo (JPG file type only)').
	\Wbhkit\submit ('Upload Photo').
			"</form>\n";
	
}
function upload_teacher_photo($t, &$message, &$error) {
	$file_field_name = "teacher_photo";
	
	// Check file size
	if ($_FILES[$file_field_name]["size"] > USER_PHOTO_MAX_BYTES) {
	  $error = "File rejected: greater than 5MB";
	  return false;
	}
	if ($_FILES[$file_field_name]["size"] == 0) {
	  $error = "No files uploaded.";
	  return false;
	}
	$file_parts = pathinfo($_FILES[$file_field_name]['name']);
	if ($file_parts['extension'] != 'jpg') {
		$error = "File must be in JPG format.";
		return false;
	}

	if (move_uploaded_file($_FILES[$file_field_name]["tmp_name"], "photos/user_{$t['user_id']}.jpg")) {
		$message = "photo uploaded!";
		return true;
	} else {
		$error = "There was an error uploading your file.";
		return false;
	}
	
}

function update_teacher_info($t) {
	
	if (!$t['id'] || !$t['user_id']) {
		return false;
	}	
	$t = \Wbhkit\fill_out($t, empty_teacher());
	$params = \Wbhkit\make_params($t, empty_teacher());
	$update_sql = \Wbhkit\create_update_sql(empty_teacher());
	$stmt = \DB\pdo_query("update teachers set $update_sql where id = :id", $params);
	
	return $t['id'];
}


function empty_teacher() {
	return array(
		'id' => null,
		'user_id' => null,
		'bio' => null,
		'active' => 0,
		'default_rate' => 0
	);
	
}


