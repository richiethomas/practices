<?php

$db = '';
wh_set_db_link();
$webmaster = 'whines@gmail.com';


function wh_set_db_link() {
	global $db;
	if (!$db) {
		$db = mysqli_connect('localhost', 'whines_workshops', 'meet1962', 'whines_workshops');
		if (!$db) {
		    die('Connect Error: ' . mysqli_connect_error());
		}
	}
	return $db;
}

function wbh_db_error($extra_info = null) {
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
