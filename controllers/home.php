<?php

$email = null;
$link_email_sent_flag = false;


include 'login_actions.php'; // for logging out and updating display name

// if nothing else happens, we'll render 'home'
$view->data['upcoming_workshops'] = Workshops\get_workshops_list_no_html();
$view->data['link_email_sent_flag'] = $link_email_sent_flag;
$view->data['unavailable_workshops'] = Workshops\get_unavailable_workshops(); 
$view->data['application_workshops'] = Workshops\get_application_workshops(); 
$view->data['email'] = $email;
$view->data['userhelper'] = new UserHelper($sc);

$view->renderPage('home');





