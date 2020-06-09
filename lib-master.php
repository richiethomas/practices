<?php
/*
using Bootstrap 4.4
tested on PHP 7.4.2
*/

require "vendor/autoload.php"; // i barely understand this; might not have enough classes to justify it

date_default_timezone_set ( 'America/Los_Angeles' );
session_start();

// maybe i don't need these next three anymore? i dnuno :(
ini_set("include_path", '/home/whines/php:' . ini_get("include_path") ); // willhines.net
ini_set("include_path", '/home/willfahg/php:' . ini_get("include_path") ); // willhinesimprov.com
ini_set("include_path", '/Applications/MAMP/bin/php/php7.4.2/lib/php:' . ini_get("include_path") ); // local laptop

// set function for autoloading classes
spl_autoload_register(function ($className) {
        $className = str_replace('\\', DIRECTORY_SEPARATOR, $className); // for subdirectories in 'classes'
        $file = __DIR__.DIRECTORY_SEPARATOR."classes".DIRECTORY_SEPARATOR."{$className}.class.php";
        if (is_readable($file)) require_once $file;
});

// some constants
define('LOCAL', ($_SERVER['SERVER_NAME'] == 'localhost') ? true : false);
define('DEBUG_MODE', true);
define('ERROR_LOG', 'info.txt');
define('URL', "http://{$_SERVER['HTTP_HOST']}/");
define('ONLINE_LOCATION_ID', 8);
define('TIMEZONE', 'PDT');
define('LATE_HOURS', 12);
define('REMINDER_HOURS', 24);
define('USER_PHOTO_MAX_BYTES', 5000000);

if (LOCAL) {
	define('WEBMASTER', "will@willhines.net");	
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
include 'libs/lib-users.php';
include 'libs/lib-workshops.php';
include 'libs/lib-enrollments.php';
include 'libs/lib-lookups.php';
include 'libs/lib-emails.php';
include 'libs/lib-xtra-sessions.php';
include 'libs/lib-teachers.php';


$statuses = Lookups\get_statuses();
$locations = Lookups\get_locations();
define('ENROLLED', Lookups\find_status_by_value('enrolled'));
define('WAITING', Lookups\find_status_by_value('waiting'));
define('DROPPED', Lookups\find_status_by_value('dropped'));
define('INVITED', Lookups\find_status_by_value('invited'));
$carriers = array();
$error = '';
$message = '';
$body = '';
$smtp = null; // global smtp object for sending mail, keeping connection open

Wbhkit\set_vars(array('ac', 'wid', 'uid', 'key', 'page'));

// set workshop info into memory
if ($wid) {
	$wk = Workshops\get_workshop_info($wid);
} else {
	$wk = Workshops\get_empty_workshop();
}

// set user info into memory
$already_here_key = (isset($_SESSION['s_key']) ? $_SESSION['s_key'] : null);

$key = Users\check_for_stored_or_passed_key(); // checks for key in REQUEST and SESSION and COOKIE, not logged in otherwise
if ($key) {
	$u = Users\key_to_user($key);
} else {
	$u = Users\get_empty_user();
}

// is this the first page this visitor has visited
if (isset($u['ukey']) && $u['ukey'] != $already_here_key) {
	$logger->info("{$u['fullest_name']} logged in.");
}

$view = new View();

// group 2 or higher for admin pasges
if (strpos($sc, 'admin') !== false) {
	Users\reject_user_below(2); // group 2 or higher for admin
}

Emails\check_reminder(); // every single time anyone loads a page, sheesh

