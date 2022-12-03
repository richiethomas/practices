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
$ac = (isset($params[1]) ? $params[1] : 'view'); // action defaults to 'view'

//
// check "pages" first
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
foreach ($pages as $p => $pinfo) {
	if ($params[0] == $p) {
		set_page($pinfo);
	}
}

//
// then check synonyms for existing pages
//
$synonyms = array(
	'jams' => 'community',
	'merch' => 'community',
	'news' => 'community',
	'teams' => 'shows'
);
foreach ($synonyms as $sk => $sv) {
	if ($params[0] == $sk) {
		if (isset($pages[$sv])) {
			set_page($pages[$sv]);
		}
	}
}


//
// then check controllers
//
$controllers = array(
	0 => array(
		'home' => 'home',
		'workshop' => 'workshop',
		'you' => 'you',
		'calendar' => 'calendar',
		'teachers' => 'teachers',
		'workshop' => 'workshop',
		'payment' => 'payment',
		'classes' => 'classes',
		'about-catalog' => 'catalog'
	),
	
	2 => array (
		'admin' => 'admin/dashboard',
		'admin-workshop' => 'admin/workshop',
		'admin-messages' => 'admin/messages',
		'admin-change-status' => 'admin/change-status',
		'admin-users' => 'admin/users',
		'admin-search' => 'admin/search',
		'admin-archives' => 'admin/archives',
		'admin-shows' => 'admin/shows',
		'admin-teachers' => 'admin/teachers',
		'admin-emails' => 'admin/emails',
		'admin-bulk-status' => 'admin/bulk-status',
		'admin-bulk-workshops' => 'admin/bulk-workshops',
		'admin-tasks' => 'admin/tasks',
		'admin-reminder-emails' => 'admin/reminder-emails'
		),	
	3 => array (
		'admin-revbyclass' => 'admin/revenue_byclass',
		'admin-revbydate' => 'admin/revenue_bydate',
		'admin-payments' => 'admin/payments',
		'admin-registrations' => 'admin/registrations',
		'admin-reminders' => 'admin/reminders',
		'admin-conflicts' => 'admin/conflicts',
		'admin-status-log' => 'admin/status-log',
		'admin-error-log' => 'admin/error-log',
		'admin-debug-log' => 'admin/debug-log',
		'admin-email-log' => 'admin/email-log'
			)
);

$controller = 'home'; // default
//print_r($controllers);
//die;

//special 'user' check
if ($params[0] == 'user') {
	if ($u->logged_in() && Teachers\is_teacher($u->fields['id'])) {
		$controller = 'user';
	}
}

foreach ($controllers as $level => $files) {
	if ($u->check_user_level($level) || $level == 0) {
		foreach ($files as $k => $f) {
			if ($params[0] == $k) {				
				$controller = $f;
			}
		}
	}
}
include "controllers/{$controller}.php";


// for the no-controller pages
function set_page($pinfo) {
	global $view;
	$view->data['heading'] = $pinfo[1];
	$view->data['fb_description'] = $pinfo[1];
	$view->renderPage($pinfo[0]);
	exit;
}



