<?php
$heading = 'improv practices: faq';
$sc = "index.php";
include 'lib-master.php';


$view->data['faq'] = Emails\get_faq();
$view->renderPage('faq');

