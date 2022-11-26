<?php
	
namespace Teachers;

$teacher_attic = array();

function is_teacher($uid) {
	$stmt = \DB\pdo_query("select t.*, u.email, u.display_name, u.ukey, u.time_zone from teachers t, users u where u.id = t.user_id and t.user_id = :id", array(':id' => $uid));
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		$row = fill_out_teacher_row($row); 
		return $row;
	}
	return false;
}

function get_teacher_by_id(?int $tid) {
	
	if ($tid) {
		global $teacher_attic;
	
		foreach ($teacher_attic as $tattic_id => $tattic_row) {
			if ($tattic_id == $tid) { return $tattic_row; }
		}
	
		$stmt = \DB\pdo_query("select t.*, u.email, u.display_name, u.ukey, u.time_zone from teachers t, users u where u.id = t.user_id and t.id = :id", array(':id' => $tid));
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$row = fill_out_teacher_row($row); 
			$teacher_attic[$row['id']] = $row; // save teacher data in global variable
			return $row;
		}
	}
	return empty_teacher();
}

function fill_out_teacher_row($row) {
	$tempu = new \User();
	$tempu->replace_fields($row);
	$tempu->finish_setup();
	return $tempu->fields; 
}




function make_teacher($uid) {
	
	global $last_insert_id;

	$stmt = \DB\pdo_query("select * from teachers where user_id = :id", array(':id' => $uid));
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		return $row['id']; // if exists, return the teacher id
	}

	$stmt = \DB\pdo_query("insert into teachers (user_id) VALUES (:id)" , array(':id' => $uid));
	
	return $last_insert_id;
}

function get_teacher_fields($t) {
	if (!$t || !isset($t['id'])) {
		$t = empty_teacher();
	}
	return 
		\Wbhkit\hidden('tid', $t['id']).
		\Wbhkit\textarea('bio', $t['bio']).
		\Wbhkit\checkbox('active', 1, null, $t['active']).
		\Wbhkit\texty('default_rate', $t['default_rate']).
		\Wbhkit\texty('student_email', $t['student_email']).
		\Wbhkit\submit('Update');
}


function get_all_teachers($only_active = false) {
	$stmt = \DB\pdo_query("select t.*, u.email, u.display_name, u.ukey, u.time_zone from teachers t, users u where t.user_id = u.id".($only_active ? ' and t.active = 1' : ''));
	$teachers = array();
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		$row = fill_out_teacher_row($row); 
		$teachers[$row['id']] = $row;
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
	// get an active teacher with only upcoming classes
	$stmt = \DB\pdo_query("select wk.* from workshops wk where (teacher_id = :tid or co_teacher_id = :ctid) and start > :now and wk.hidden = 0 order by start", array(':now' => date(MYSQL_FORMAT), ':tid' => $tid, ':ctid' => $tid));
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		$wk = new \Workshop();
		$wk->set_by_id($row['id']);
		$workshops[] = $wk;
	}
	return $workshops;
}

function get_teacher_all_classes($tid) {
	
	$workshops = array();
	// get an active teacher and all classes they taught
	$stmt = \DB\pdo_query("select wk.* from workshops wk where (teacher_id = :tid or co_teacher_id = :ctid)  order by start desc", array(':tid' => $tid, ':ctid' => $tid));
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		$wk = new \Workshop();
		$wk->set_by_id($row['id']);
		$workshops[] = $wk;
	}
	return $workshops;
}


function teachers_dropdown_array($only_active = false, array $teachers = array()) {
	if (count($teachers) == 0) {
		$teachers = get_all_teachers($only_active);
	} 
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


function upload_teacher_photo($t, &$message, &$error) {
	$file_field_name = (string) "teacher_photo";
	
	//var_dump($_FILES);
	
	// Check file size
	if ($_FILES["teacher_photo"]["size"] > USER_PHOTO_MAX_BYTES) {
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
		'default_rate' => 0,
		'student_email' => null
	);
	
}




