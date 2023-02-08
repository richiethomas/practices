<?php

// debug timer
define('TIMER', true);
$start_hrtime = 0;
if (TIMER) {
    $start_hrtime = hrtime(true);
}

session_start();

//
// autoloading classes and paths
//
require "vendor/autoload.php"; // i barely understand this; might not have enough classes to justify it

ini_set("include_path", '/home/wgimrenl/php:' . ini_get("include_path")); // wgimprovschool.com

if (php_sapi_name() === 'cli') {
    // we are running on command line mode
    // so lets fake the super globals
    $_SERVER = [
        'SERVER_NAME' => 'localhost',
        'HTTP_HOST' => 'localhost',
        'SCRIPT_NAME' => '',
        'SERVER_URI' => '/',
        'PHP_SELF' => ''
    ];
    $_GET = [];
    $_POST = [];
    $_FILES = [];
    $_COOKIE = [];
    $_SESSION = [
        's_key' => 'my_fake_skeyfortests'
    ];
    $_REQUEST = [];
}

//
// constants and variables
//
$error = '';
$message = '';
$body = '';
$last_insert_id = null;

define('DEFAULT_TIME_ZONE', 'America/Los_Angeles');
date_default_timezone_set(DEFAULT_TIME_ZONE);
$dateTime = new DateTime();
$dateTime->setTimeZone(new DateTimeZone(DEFAULT_TIME_ZONE));
define('TIME_ZONE', $dateTime->format('T'));


define('MYSQL_FORMAT', 'Y-m-d H:i:s');
define('MYSQL_DATE', 'Y-m-d');
define('LOCAL', in_array($_SERVER['SERVER_NAME'], array('localhost', '127.0.0.1', 'wgimprovstaging.com')) ? true : false);

// to control logging levels, see lib-logger.php
define('DEBUG_LOG', 'info.txt');
define('ERROR_LOG', 'error_log.txt');

define('URL', (LOCAL ? "http://{$_SERVER['HTTP_HOST']}/" : "https://{$_SERVER['HTTP_HOST']}/"));
define('ONLINE_LOCATION_ID', 8);
define('LATE_HOURS', 12);
define('REMINDER_HOURS', 24);
define('USER_PHOTO_MAX_BYTES', 5000000);
define('WEBMASTER', (LOCAL ? "will@willhines.net" : "classes@wgimprovschool.com"));
define('TEACHERPAY', 'teacher pay');

//
// objects
//
include 'libs/lib-logger.php';
include 'libs/db_pdo.php';
include 'libs/wbh_webkit.php';
include 'libs/wbh_webkit_pagination.php';
include 'libs/lib-emails.php';
include 'libs/lib-xtra-sessions.php';
include 'libs/lib-teachers.php';
include 'libs/lib-reminders.php';

define('LEVEL1ICON', 'emoji-smile');
define('LEVEL2ICON', 'controller');
define('LEVEL3ICON', 'translate');
define('LEVEL4ICON', 'flower2');

