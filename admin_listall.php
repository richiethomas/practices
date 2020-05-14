<?php
$sc = "admin_listall.php";
$heading = "practices: admin";
include 'lib-master.php';

$view->data['workshops_list'] = Workshops\get_workshops_list(1, $page);
$view->data['add_workshop_form'] = Workshops\add_workshop_form($wk);

$view->renderPage('admin_listall');
	




