<?php
include 'lib-master.php';

//
// routing stuff
//

//
// params
//
$request  = substr($_SERVER['REQUEST_URI'],1); // strip leading slash
$sc = $request;
$params = array();
$params = explode("/", $request);
$ac = (isset($params[1]) ? $params[1] : 'view'); // action defaults to 'view'

// debug messages
if (false) {
	echo "<pre>\n";
	print_r($_SERVER['REQUEST_URI'])."<br>\n";
	print_r($_GET);
	print_r($_POST);
	print_r($params);
	echo "</pre>";
}



//
// check "pages" first
//
$pages = array(
	
	"about-catalog" => array(
		'about/catalog', 
		'course catalog',
		'Types of classes offered at WGIS.'),
	
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
		
	"teams" => array(
		'teams',
		'teams',
		'WGIS house teams'),
		
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
	'shows' => 'community',
	'merch' => 'community',
	'news' => 'community'
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
	'home',
	'workshop',
	'you',
	'calendar',
	'teachers',
	'workshop');

$controller = 'home'; // default
foreach ($controllers as $c) {
	if ($params[0] == $c) {
		$controller = $c;
	}
}

// check admin controllers (if user is above level 3)
if ($u->check_user_level(3)) {
	$admin_controllers = array (
		'admin' => 'admin/dashboard',
		'admin-workshop' => 'admin/workshop'
	);

	foreach ($admin_controllers as $ci => $cv) {
		if ($params[0] == $ci) {
			$controller = $cv;
		}
	}
}

include "controllers/{$controller}.php";


function set_page($pinfo) {
	global $view;
	$view->data['heading'] = $pinfo[1];
	$view->data['fb_description'] = $pinfo[1];
	$view->renderPage($pinfo[0]);
	exit;
}



