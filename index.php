<?php
$sc = "index.php";
include 'db.php';
include 'lib-master.php';

Wbhkit\set_vars(array('ac', 'wid', 'uid', 'email', 'v', 'key', 'message', 'phone', 'carrier_id', 'send_text', 'newemail', 'display_name'));


$key = Users\current_key(); // checks for key in REQUEST and SESSION and COOKIE, not logged in otherwise
$error = '';
$message = '';

if ($wid) {
	$wk = Workshops\get_workshop_info($wid);
	Enrollments\check_waiting($wk);
	if (!$v) { $v = 'winfo'; } // if we've passed in a workshop id, let's show it
}

if ($uid) {
	$u = Users\get_user_by_id($uid);
} elseif ($email) {
	$u = Users\get_user_by_email($email);
} elseif ($key) {
	$u = Users\key_to_user($key);
}

$body = '';

include 'index_actions.php';
include 'index_views.php';


$heading = 'improv practices';
include 'header.php';
echo $body;
include 'footer.php';





