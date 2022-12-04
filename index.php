<?php
//
// routing stuff
//
include 'lib-master.php'; // variables, objects, includes, defaults

//
// parse params out of URL
//
$request  = substr($_SERVER['REQUEST_URI'],1); // strip leading slash
$sc = $request = preg_replace('/(.*)\?.*/', "$1", $request); // get rid of trailing "?akdasfsdhfdskj" type stuff
$params = explode("/", $request);
$action = (isset($params[1]) ? $params[1] : 'view'); // action defaults to 'view'

$requested_controller = $params[0];


//
// then check controllers
//
$controllers = array(
	

	// pages = views with no controller, set title/description here
	"about-school" => array(0, 'about/school', 'about wgis', 'About WGIS'),
	"about-works" => array(0, 'about/works', 'how it works', "How WGIS works: signups, paying, etc."),
	"community" => array(0, 'community', 'community', "Being a part of the WGIS community"),
	"shows" => array(0, 'shows', 'shows', 'WGIS in-person shows'),
	"privacy" => array(0, 'about/privacy', 'privacy policy', 'WGIS Privacy Policy'),


	'home' => array(0, 'home'),
	'workshop' => array(0, 'workshop'),
	'you' => array(0,'you'),
	'calendar' => array(0,'calendar'),
	'teachers' => array(0,'teachers'),
	'workshop' => array(0,'workshop'),
	'payment' => array(0,'payment'),
	'classes' => array(0,'classes'),
	'about-catalog' => array(0,'catalog'),
	
	'user' => array(2, 'user'),
	
	'admin' => array(3, 'admin/dashboard'),
	'admin-workshop' => array(3,'admin/workshop'),
	'admin-messages' => array(3,'admin/messages'),
	'admin-change-status' => array(3,'admin/change-status'),
	'admin-users' => array(3,'admin/users'),
	'admin-search' => array(3,'admin/search'),
	'admin-archives' => array(3,'admin/archives'),
	'admin-shows' => array(3,'admin/shows'),
	'admin-teachers' => array(3,'admin/teachers'),
	'admin-emails' => array(3,'admin/emails'),
	'admin-bulk-status' => array(3,'admin/bulk-status'),
	'admin-bulk-workshops' => array(3,'admin/bulk-workshops'),
	'admin-tasks' => array(2,'admin/tasks'),
	'admin-reminder-emails' => array(3,'admin/reminder-emails'),
	'admin-registrations' => array(3, 'admin/registrations'),
	'admin-reminders' => array(3, 'admin/reminders'),
	'admin-conflicts' => array(3, 'admin/conflicts'),
	'admin-status-log' => array(3, 'admin/status-log'),
	'admin-error-log' => array(3, 'admin/error-log'),
	'admin-debug-log' => array(3, 'admin/debug-log'),
	'admin-email-log' => array(3, 'admin/email-log'),

	'admin-revbyclass' => array(4, 'admin/revenue_byclass'),
	'admin-revbydate' => array(4, 'admin/revenue_bydate'),
	'admin-payments' => array(4, 'admin/payments')

);	

// synonyms for controllers
$synonyms = array(
	'jams' => 'community',
	'merch' => 'community',
	'news' => 'community',
	'teams' => 'shows'
);

$controller_file = 'home';

// check controllers
if (array_key_exists($requested_controller, $controllers)) {	
	$controller_file = set_controller($controllers[$requested_controller]);
}

//check synonyms
if (array_key_exists($requested_controller, $synonyms)) {
	$controller_file = set_controller($controllers[$synonyms[$requested_controller]]);
}

include "controllers/{$controller_file}.php";


function set_controller($controller_info) {

	global $u, $view;
	$controller_file = 'home'; // default
	
	if ($controller_info[0] == 0 || $u->check_user_level($controller_info[0])) {
		if (isset($controller_info[2]) && isset($controller_info[3])) { // view with no controller
			$view->data['heading'] = $controller_info[2];
			$view->data['fb_description'] = $controller_info[3];
			$view->renderPage($controller_info[1]);
			exit;			
		} else {
			$controller_file = $controller_info[1]; 
		}
	}
	return $controller_file;
	
}

	


