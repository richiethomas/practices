<?php
namespace Database;

$db = '';
wh_set_db_link();
$webmaster = 'whines@gmail.com';

function mysqli($sql) {
	$db = wh_set_db_link();
	$rows = mysqli_query($db, $sql) or db_error();	
	return $rows;
}

function wh_set_db_link() {
	global $db;
	if (!$db) {
		//$db = mysqli_connect(servername ('localhost'), username, password, database name);
		if (!$db) {
		    die('Connect Error: ' . mysqli_connect_error());
		}
	}
	return $db;
}

function db_error($extra_info = null) {
	wh_db_error($extra_info);
}
function wh_db_error($extra_info = null) {
	global $webmaster;
	//mail($webmaster, 'db error', mysql_error()."\n$extra_info", "From: $webmaster");
	$db = wh_set_db_link();
	if (mysqli_error ( $db )) {
		echo mysqli_error($db);
		die;
	}
	
}

function mres($thing) {
	$db = \Database\wh_set_db_link();
	return mysqli_real_escape_string($db, $thing);
}

