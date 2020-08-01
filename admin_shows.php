<?php
$heading = "shows";
include 'lib-master.php';


$vars = array('show_id', 'title', 'notes', 'start');
Wbhkit\set_vars($vars);

echo "$show_id - $title - $notes - $start<br>\n";

if ($ac == 'adup') {
	if (Shows\add_update_show($show_id, $start, $title, $notes)) {
		$message = "Added show '$title'"; 
	} else {
		$error = "Show '$title' failed to add! I wonder why.";
	};
}

$view->data['s'] = $show_id ? Shows\get_show($show_id) : Shows\get_empty_show();
$view->data['shows'] = Shows\get_shows();
$view->renderPage('admin/shows');