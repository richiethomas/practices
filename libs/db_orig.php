<?php
namespace Database;
$db = '';
wh_set_db_link();
$webmaster = 'whines@gmail.com';

function mysqli($sql) {
	$db = wh_set_db_link();
	$rows = $db->query($sql) or db_error();	
	return $rows;
}

function wh_set_db_link() {
	global $db;
	if (!$db) {
		$db = new mysqli(host, username, password, database)
		if ($db->connect_errno) {
		    die("Failed to connect to MySQL: (" . $db->connect_errno . ") " . $db->connect_error);
		}
	}
	return $db;
}

function db_error($extra_info = null) {
	wh_db_error($extra_info);
}
function wh_db_error($extra_info = null) {
	$db = wh_set_db_link();
	if ($db->errno) {
		die("DB error: (" . $db->errno . ") " . $db->error);
	}
	
}

function mres($thing) {
	$db = \Database\wh_set_db_link();
	return $db->real_escape_string($thing);
}

