<?php
$heading = 'shows';
include 'lib-master.php';


$view->data['teams'] = Shows\get_teams($u['id']);
$view->data['u'] = $u;
$view->renderPage('shows');