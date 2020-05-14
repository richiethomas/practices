<?php
$heading = 'improv practices';
$sc = "index.php";
include 'lib-master.php';
	


include 'login_actions.php'; // for logging out and updating display name

// if nothing else happens, we'll render 'home'
$view->data['upcoming_workshops'] = Workshops\get_workshops_list(0, $page);
$view->data['transcript'] = Enrollments\get_transcript_tabled($u, 0, $page); 
$view->data['unavailable_workshops'] = Workshops\get_unavailable_workshops(); 
$view->renderPage('home');





