<?php
/*
using Bootstrap 4.0
tested on PHP 7.0.15
*/
date_default_timezone_set ( 'America/Los_Angeles' );
session_start();
include 'wbh_webkit.php';
include 'time_difference.php';

include 'lib-users.php';
include 'lib-workshops.php';
include 'lib-enrollments.php';
include 'lib-lookups.php';
include 'lib-emails.php';

define('DEBUG_MODE', false);
define('URL', "http://{$_SERVER['HTTP_HOST']}/practices/");
define('WEBMASTER', "will@willhines.net");
ini_set('sendmail_from','will@willhines.net'); 

$statuses = Lookups\get_statuses();

define('ENROLLED', Lookups\find_status_by_value('enrolled'));
define('WAITING', Lookups\find_status_by_value('waiting'));
define('DROPPED', Lookups\find_status_by_value('dropped'));
define('INVITED', Lookups\find_status_by_value('invited'));

$late_hours = '24';
$carriers = array();


