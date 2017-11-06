<?php
$sc = "admin.php";
include 'db.php';
include 'lib-master.php';
include 'validate.php';

if (!Validate\is_validated()) {
	include 'header.php';
	Validate\validate_user() or die();
	include 'footer.php';
	exit;
}

Wbhkit\set_vars(array('ac', 'wid', 'uid', 'email', 'title', 'notes', 'start', 'end', 'active', 'lid', 'lplace', 'lwhere', 'cost', 'capacity', 'notes', 'st', 'v', 'con', 'note', 'subject', 'workshops', 'revenue', 'expenses', 'searchstart', 'searchend', 'lmod', 'needle', 'newe', 'sms', 'phone', 'carrier_id', 'send_text', 'when_public', 'sort', 'display_name'));

if ($wid) {
	$wk = Workshops\get_workshop_info($wid);
} else {
	$wk = Workshops\empty_workshop();
}
if ($uid) {
	$u = Users\get_user_by_id($uid);
} elseif ($email) {
	$u = Users\get_user_by_email($email);
} else {
	$u = array();
}
$body = '';


include 'admin_actions.php';
include 'admin_views.php';

$heading = "practices: admin";
include 'header.php';
 echo $body;
include 'footer.php';