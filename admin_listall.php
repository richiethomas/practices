<?php
$sc = "admin_listall.php";
$heading = "practices: admin";
include 'lib-master.php';

Wbhkit\set_vars(array('needle')); // search term, if any

$view->data['needle'] = $needle;
$view->data['workshops_list'] = Workshops\get_search_results($page, $needle);
$view->data['add_workshop_form'] = Workshops\add_workshop_form($wk);

$view->renderPage('admin_listall');
	




