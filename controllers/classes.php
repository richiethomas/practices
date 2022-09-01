<?php
$view->data['heading'] = "classes";

$wh = new WorkshopsHelper();

$view->data['mode'] =  (string) ($params[2] ?? 'full');
$view->data['upcoming_workshops'] = $wh->get_workshops_list_no_html();
$view->data['unavailable_workshops'] = $wh->get_unavailable_workshops(); 
$view->data['fb_description'] = "Compact list of upcoming course at WGIS.";

$view->renderPage('classes');

