<?php
$heading = 'course catalog';
include 'lib-master.php';


$view->data['fb_description'] = "Types of classes offered at WGIS.";
$view->renderPage('about/catalog');