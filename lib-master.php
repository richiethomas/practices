<?php
/*
using Bootstrap 4.5
tested on PHP 7.4.2
*/

define('TIMER', FALSE);
$start_hrtime = 0;
if (TIMER) {
	$start_hrtime=hrtime(true);
}

function show_hrtime() {
	global $start_hrtime;
	echo figure_hrtime($start_hrtime);
}

function figure_hrtime($start) {
	return ((hrtime(true)-$start) / 1e+6);
}

if (!isset($sc)) { $sc = $_SERVER['SCRIPT_NAME']; }

require "vendor/autoload.php"; // i barely understand this; might not have enough classes to justify it

date_default_timezone_set ( 'America/Los_Angeles' );
session_start();

// maybe i don't need these next three anymore? i dnuno :(
ini_set("include_path", '/home/willfahg/php:' . ini_get("include_path") ); // willhinesimprov.com
ini_set("include_path", '/Applications/MAMP/bin/php/php7.4.2/lib/php:' . ini_get("include_path") ); // local laptop
ini_set("include_path", '/home/wgimrenl/php:' . ini_get("include_path") ); // wgimprovschool.com



// set function for autoloading classes
spl_autoload_register(function ($className) {
        $className = str_replace('\\', DIRECTORY_SEPARATOR, $className); // for subdirectories in 'classes'
        $file = __DIR__.DIRECTORY_SEPARATOR."classes".DIRECTORY_SEPARATOR."{$className}.class.php";
        if (is_readable($file)) require_once $file;
});

// some constants
define('LOCAL', ($_SERVER['SERVER_NAME'] == 'localhost') ? true : false);
define('DEBUG_MODE', true);
define('DEBUG_LOG', 'info.txt');
define('ERROR_LOG', 'error_log.txt');
define('URL', "https://{$_SERVER['HTTP_HOST']}/");
define('ONLINE_LOCATION_ID', 8);
define('TIMEZONE', 'PST');
define('LATE_HOURS', 24);
define('REMINDER_HOURS', 24);
define('USER_PHOTO_MAX_BYTES', 5000000);

if (LOCAL) {
	define('WEBMASTER', "will@willhines.net");	
} elseif (strpos($_SERVER['SERVER_NAME'], 'wgimprovschool.com') !== false) {
	define('WEBMASTER', "will@wgimprovschool.com");
} else {
	define('WEBMASTER', "will@willhinesimprov.com");
}

// set objects, code, etc
$last_insert_id = null;
include 'libs/lib-logger.php';
include 'libs/db_pdo.php';
include 'libs/wbh_webkit.php';
include 'libs/wbh_webkit_pagination.php';
include 'libs/time_difference.php';
include 'libs/lib-workshops.php';
include 'libs/lib-emails.php';
include 'libs/lib-xtra-sessions.php';
include 'libs/lib-teachers.php';
include 'libs/lib-reminders.php';
include 'libs/lib-danny.php';
	
//include 'libs/lib-enrollments.php';
//include 'libs/lib-lookups.php';
//include 'libs/lib-users.php';

	
$lookups = new Lookups;	
define('ENROLLED', $lookups->find_status_by_value('enrolled'));
define('WAITING', $lookups->find_status_by_value('waiting'));
define('DROPPED', $lookups->find_status_by_value('dropped'));
define('INVITED', $lookups->find_status_by_value('invited'));
define('SMARTENROLL', 100); // special status ENROLL or WAIT pending capacity -- see Enrollment.class.php

$error = '';
$message = '';
$body = '';
$smtp = null; // global smtp object for sending mail, keeping connection open

Wbhkit\set_vars(array('ac', 'wid', 'uid', 'key', 'page'));

// set workshop info into memory
if ($wid) {
	$wk = \Workshops\get_workshop_info($wid);
} else {
	$wk = \Workshops\get_empty_workshop();
}

$u = new User(); // set empty user

// set user info into memory
$already_here_key = (isset($_SESSION['s_key']) ? $_SESSION['s_key'] : null);

$key = $u->check_for_stored_or_passed_key(); // checks for key in REQUEST and SESSION and COOKIE, not logged in otherwise
if ($key) {
	$u->set_by_key($key);
} 

// is this the first page this visitor has visited
if (isset($u->fields['ukey']) && $u->fields['ukey'] != $already_here_key) {
	$logger->info("{$u->fields['fullest_name']} logged in.");
}


$view = new View();

// group 2 or higher for admin pasges
if (strpos($sc, 'admin') !== false) {
	$u->reject_user_below(2); // group 2 or higher for admin
}

// check to see if we should send reminders every time anyone loads a page
Reminders\check_reminders(); 