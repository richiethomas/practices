<?php
$heading = 'faq';
include 'lib-master.php';


$view->data['faq'] = Emails\get_faq();
$view->renderPage('faq');

