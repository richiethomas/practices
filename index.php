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
// check "pages" first (no controller needed)
//
$pages = array(
	
	"about-school" => array(
		'about/school',
		'about wgis',
		'About WGIS'),
	
	"about-works" => array(
		'about/works',
		'how it works',
		"How WGIS works: signups, paying, etc."),
		
	"community" => array(
		'community',
		'community',
		"Being a part of the WGIS community"),
		
	"shows" => array(
		'shows',
		'shows',
		'WGIS in-person shows'),
		
	"privacy" => array(
		'about/privacy',
		'privacy policy',
		'WGIS Privacy Policy')
);

//
// synonyms for pages
//
$synonyms = array(
	'jams' => 'community',
	'merch' => 'community',
	'news' => 'community',
	'teams' => 'shows'
);


if (array_key_exists($requested_controller, $pages)) {
	set_page($pages[$requested_controller]);
}
if (array_key_exists($requested_controller, $synonyms)) {
	set_page($pages[$synonyms[$requested_controller]]);
}


//
// then check controllers
//
$controllers = array(
	
	'home' => array(0, 'home'),
	'workshop' => array(0, 'workshop'),
	'you' => array(0,'you'),
	'calendar' => array(0,'calendar'),
	'teachers' => array(0,'teachers'),
	'workshop' => array(0,'workshop'),
	'payment' => array(0,'payment'),
	'classes' => array(0,'classes'),
	'about-catalog' => array(0,'catalog'),
	
	'admin' => array(2, 'admin/dashboard'),
	'admin-workshop' => array(2,'admin/workshop'),
	'admin-messages' => array(2,'admin/messages'),
	'admin-change-status' => array(2,'admin/change-status'),
	'admin-users' => array(2,'admin/users'),
	'admin-search' => array(2,'admin/search'),
	'admin-archives' => array(2,'admin/archives'),
	'admin-shows' => array(2,'admin/shows'),
	'admin-teachers' => array(2,'admin/teachers'),
	'admin-emails' => array(2,'admin/emails'),
	'admin-bulk-status' => array(2,'admin/bulk-status'),
	'admin-bulk-workshops' => array(2,'admin/bulk-workshops'),
	'admin-tasks' => array(2,'admin/tasks'),
	'admin-reminder-emails' => array(2,'admin/reminder-emails'),
	'admin-registrations' => array(2, 'admin/registrations'),
	'admin-reminders' => array(2, 'admin/reminders'),
	'admin-conflicts' => array(2, 'admin/conflicts'),
	'admin-status-log' => array(2, 'admin/status-log'),
	'admin-error-log' => array(2, 'admin/error-log'),
	'admin-debug-log' => array(2, 'admin/debug-log'),
	'admin-email-log' => array(2, 'admin/email-log'),

	'admin-revbyclass' => array(3, 'admin/revenue_byclass'),
	'admin-revbydate' => array(3, 'admin/revenue_bydate'),
	'admin-payments' => array(3, 'admin/payments')

);	

$controller_file = 'home'; // default

//special 'user' check
if ($requested_controller == 'user') {
	if ($u->logged_in() && Teachers\is_teacher($u->fields['id'])) {
		$controller_file = 'user';
	}
}

// all controllers besides user
if (array_key_exists($requested_controller, $controllers)) {
	if ($controllers[$requested_controller][0] == 0 || $u->check_user_level($controllers[$requested_controller][0])) {
		$controller_file = $controllers[$requested_controller][1]; 
	}
}

include "controllers/{$controller_file}.php";


// for the no-controller pages
function set_page($pinfo) {
	global $view;
	$view->data['heading'] = $pinfo[1];
	$view->data['fb_description'] = $pinfo[2];
	$view->renderPage($pinfo[0]);
	exit;
}



