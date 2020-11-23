<?php

include 'lib-master.php';

$email = null;
$link_email_sent_flag = false;

include 'login_actions.php'; // for logging out and updating display name

// if nothing else happens, we'll render 'home'
$view->data['upcoming_workshops'] = Workshops\get_workshops_list_no_html(0, $page);
$view->data['link_email_sent_flag'] = $link_email_sent_flag;
$view->data['email'] = $email;

$view->renderPage('home');





