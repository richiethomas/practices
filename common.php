<?php
date_default_timezone_set ( 'America/Los_Angeles' );
session_start();
include 'common.php';
include 'time_difference.php';

define('DEBUG_MODE', false);
define('URL', "http://{$_SERVER['HTTP_HOST']}/practices/");
define('WEBMASTER', "will@willhines.net");
ini_set('sendmail_from','will@willhines.net'); 

$statuses = get_statuses();

define('ENROLLED', find_status_by_value('enrolled'));
define('WAITING', find_status_by_value('waiting'));
define('DROPPED', find_status_by_value('dropped'));
define('INVITED', find_status_by_value('invited'));

$late_hours = '18';
$carriers = array();




