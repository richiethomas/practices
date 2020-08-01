<?php
	
namespace Shows;


function get_teams($uid) {
	$stmt = \DB\pdo_query("select * from teams where user_id = :uid order by id", array(':uid' => $uid));
	$opts = array();
	$teams = array();
	while ($row = $stmt->fetch()) {
		$teams[] = $row;
	}
	return $teams;
}

function get_shows() {
	$stmt = \DB\pdo_query("select * from shows order by start desc");
	$shows = array();
	while ($row = $stmt->fetch()) {
		$row['nice_start'] = \Wbhkit\friendly_when($row['start']);
		$shows[] = $row;
	}
	return $shows;
}

function get_show($show_id) {
	if (!$show_id) { return get_empty_show(); }
	
	$stmt = \DB\pdo_query("select * from shows where id = :show_id", array(':show_id' => $show_id));
	$shows = array();
	while ($row = $stmt->fetch()) {
		$row['nice_start'] = \Wbhkit\friendly_when($row['start']);
		return $row;
	}
	return get_empty_show();
}

function get_empty_show() {
	return array(
		'id' => null,
		'start' => null,
		'title' => null,
		'notes' => null
	);

}
function get_show_form_fields($s = null) {

	if (!$s) { $s = get_empty_show(); }
	
	return
	"<form id='add_show' action='admin_shows.php' method='post' novalidate>".
	\Wbhkit\form_validation_javascript('add_show').
	"<fieldset name='show_add'><legend>".($s['id'] ? 'Edit' : 'Add')." Show</legend>".
	\Wbhkit\texty('start', $s['start'], null, null, null, 'Required', ' required ').
	\Wbhkit\texty('title', $s['title'], null, null, null, 'Required', ' required ').
	\Wbhkit\textarea('notes', $s['notes']).
	\Wbhkit\hidden('show_id', $s['id']).
	\Wbhkit\hidden('ac', 'adup').
	\Wbhkit\submit(($s['id'] ? 'Edit' : 'Add').' Show').
	"</fieldset></form>";

}

function get_team_form_fields($uid) {
		
	return
	\Wbhkit\texty('team_name', null, null, null, null, 'Required', ' required ').
	\Wbhkit\texty('members', null, null, null, null, 'Required', ' required ').
	\Wbhkit\hidden('user_id', $uid);

}

function get_teams_radio_fields($uid) {
	$teams = get_teams($uid);
	if ($teams) {
		$opts = array();
		foreach ($teams as $t) {
			$opts[$t['id']] = "{$t['team_name']} {$t['members']}";
		}
		return \Wbhkit\radio('old_team', $opts);
	}
	return false;
}

function get_show_fields() {
	$shows = get_shows();
	$cboxes = null;
	
	foreach ($shows as $s) {
		if (\Wbhkit\is_future($s['start'])) {
			$cboxes .= \Wbhkit\checkbox('shows', $s['id'], $s['title'], false, true)."<br>\n";
		}
	}
	return $cboxes;
}

function add_update_show($show_id, $start, $title, $notes) {

	global $last_insert_id;
	
	$params = array(':start' => date('Y-m-d H:i:s', strtotime($start)),
		':title' => $title,
		':notes' => $notes);

	if ($show_id) {
		$params[':id'] = $show_id;
		$sql = "update shows set start = :start, title = :title, notes = :notes where id = :id";
		if ($stmt = \DB\pdo_query($sql, $params)) {
			return $show_id;
		} else {
			return false;
		}
	} else {
		$sql = "insert into shows (start, title, notes) VALUES (:start, :title, :notes)";
		if ($stmt = \DB\pdo_query($sql, $params)) {
			return $last_insert_id;
		} else {
			return false;
		}
	}
}
