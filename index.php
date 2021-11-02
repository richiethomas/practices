<?php
include 'lib-master.php';

//
// routing stuff
//
if (false) {
	echo "<pre>\n";
	print_r($_SERVER['REQUEST_URI']);
	//print_r($_GET);
	//print_r($_POST);
	//print_r($params);
	echo "</pre>";
}

//
// params
//
$request  = substr($_SERVER['REQUEST_URI'],1); // strip leading slash
$params = explode("/", $request);
$ac = (isset($params[1]) ? $params[1] : null);


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
		'WGIS house teams'
	)			
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
	'you');

$controller = 'home';
foreach ($controllers as $c) {
	if ($params[0] == $c) {
		$controller = $c;
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



