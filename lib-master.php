<?php
/*
using Bootstrap 5.1.1
tested on PHP 7.4.9
*/

// debug timer
define('TIMER', false);
$start_hrtime = 0;
if (TIMER) {
	$start_hrtime=hrtime(true);
}

session_start();

//
// autoloading classes and paths
//
require "vendor/autoload.php"; // i barely understand this; might not have enough classes to justify it

ini_set("include_path", '/Applications/MAMP/bin/php/php7.4.2/lib/php:' . ini_get("include_path") ); // local laptop
ini_set("include_path", '/home/wgimrenl/php:' . ini_get("include_path") ); // wgimprovschool.com

spl_autoload_register(function ($className) {
        $className = str_replace('\\', DIRECTORY_SEPARATOR, $className); // for subdirectories in 'oclasses'
        $file = __DIR__.DIRECTORY_SEPARATOR."oclasses".DIRECTORY_SEPARATOR."{$className}.class.php";
        if (is_readable($file)) require_once $file;
});

//
// constants and variables
//
$error = '';
$message = '';
$body = '';
$last_insert_id = null;
date_default_timezone_set ( 'America/Los_Angeles' );
define('LOCAL', ($_SERVER['SERVER_NAME'] == 'localhost') ? true : false);
define('DEBUG_MODE', true);
define('DEBUG_LOG', 'info.txt');
define('ERROR_LOG', 'error_log.txt');
if (LOCAL) {
	define('URL', "http://{$_SERVER['HTTP_HOST']}/");
} else {
	define('URL', "https://{$_SERVER['HTTP_HOST']}/");
}
define('ONLINE_LOCATION_ID', 8);
define('TIMEZONE', 'PDT');
define('LATE_HOURS', 24);
define('REMINDER_HOURS', 24);
define('USER_PHOTO_MAX_BYTES', 5000000);

if (LOCAL) {
	define('WEBMASTER', "will@willhines.net");	
} elseif (strpos($_SERVER['SERVER_NAME'], 'wgimprovschool.com') !== false) {
	define('WEBMASTER', "classes@wgimprovschool.com");
} else {
	define('WEBMASTER', "will@willhinesimprov.com");
}

//
// objects
//
include 'libs/lib-logger.php';
include 'libs/db_pdo.php';
include 'libs/wbh_webkit.php';
include 'libs/wbh_webkit_pagination.php';
include 'libs/lib-workshops.php';
include 'libs/lib-emails.php';
include 'libs/lib-xtra-sessions.php';
include 'libs/lib-teachers.php';
include 'libs/lib-reminders.php';
	
$lookups = new Lookups;	
define('ENROLLED', $lookups->find_status_by_value('enrolled'));
define('WAITING', $lookups->find_status_by_value('waiting'));
define('DROPPED', $lookups->find_status_by_value('dropped'));
define('APPLIED', $lookups->find_status_by_value('applied'));
define('SMARTENROLL', 100); // special status ENROLL or WAIT pending capacity -- see Enrollment.class.php

$smtp = null; // global smtp object for sending mail, keeping connection open

$wk = \Workshops\get_empty_workshop();
$u = new User(); // set empty user
$view = new View();

//
// login stuff
//
$key = $u->check_for_stored_key(); 
if ($key) {
	$u->set_by_key($key);
} 

// check to see if we should send reminders every time anyone loads a page
Reminders\check_reminders(); 


//
// nav bar stuff for header and footer
//
function get_nav_items(){
	$nav_items = array();
	$nav_items[] = array('title' => "Calendar", "href" => "/calendar");
	$nav_items[] = array('title' => "About", "href" => "/about-school", 'children' => array(

		array('title' => "Teachers", "href" => "/teachers"),
		array('title' => "School", "href" => "/about-school"),
		array('title' => "Catalog", "href" => "/about-catalog"),
		array('title' => "How It Works", "href" => "/about-works")
	));
	$nav_items[] = array('title' => "Community", "href" => "/community");
	$nav_items[] = array('title' => "Teams", "href" => "/teams");
	return $nav_items;
}

function show_hrtime() {
	global $start_hrtime;
	echo figure_hrtime($start_hrtime);
}

function figure_hrtime($start) {
	return ((hrtime(true)-$start) / 1e+6);
}

