<?php
$view->data['heading'] = "classes";

$view->data['mode'] =  (string) ($params[2] ?? 'full');
$view->data['upcoming_workshops'] = Workshops\get_workshops_list_no_html();
$view->data['fb_description'] = "Compact list of upcoming course at WGIS.";

$view->renderPage('classes');

