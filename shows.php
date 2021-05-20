<?php
$heading = 'shows and jams';
include 'lib-master.php';


$view->data['fb_description'] = "A description of shows running at WGIS.";
$view->renderPage('shows');