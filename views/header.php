<?php $heading = isset($heading) ? $heading: 'will hines improv'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title><?=$heading?></title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	
	<!-- Latest compiled and minified JS and CSS -->
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">	

    <!-- iconic (open source version) -->
    <link href="open-iconic/font/css/open-iconic-bootstrap.css" rel="stylesheet">

	<!-- Google Fonts -->
	<link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Poppins:wght@500&display=swap" rel="stylesheet">

<meta name="google-signin-client_id" content="989168310652-2mk8v22d2vone6maq7jcumb9il0r9r2o.apps.googleusercontent.com">
<script src="https://apis.google.com/js/platform.js" async defer></script>


<style>
	

body {
	font-family: 'Open Sans', sans-serif;
}	
	
h1, h2, h3, h4, h5, h6 {
	font-family: 'Poppins', sans-serif;
}
table th.workshop-name {
	width: 300px;
}


div.workshop-info { background-color: #bee5eb; }
div.workshop-danger { background-color: #f5c6cb; }
div.workshop-success { background-color: #c3e6cb; }
div.workshop-light { background-color: #fdfdfe; }


div.admin-edit-workshop h2,
div.admin-edit-workshop h3,
div.admin-edit-workshop h4
{
	margin-top: 2rem;
}


li.show {
	font-weight: bold;
	font-style: italic;
}

</style>

</head>
<body>

<?php
echo "<div class=\"container-fluid\">\n";

if (strpos($sc, 'admin') !== false && \Users\check_user_level(2)) {
	echo "<h1><a href=\"admin.php\">{$heading}</a> <small><a class='text-muted' href='index.php'>(user side)</a></small></h1>\n";
	echo "<ul class='nav nav-pills nav-fill'>\n";
	echo nav_link($sc, 'admin.php', 'upcoming', 'calendar');
	echo nav_link($sc, 'admin_emails.php', 'get emails', 'envelope-closed');
	
	if (Users\check_user_level(3)) { echo nav_link($sc, 'admin_revenue.php', 'revenues', 'dollar'); }
	
	echo nav_link($sc, 'admin_search.php', 'find students', 'magnifying-glass');
	echo nav_link($sc, 'admin_status.php', 'status log', 'graph');
	echo nav_link($sc, 'admin_debug_log.php', 'debug log', 'clipboard');
	echo nav_link($sc, 'admin_listall.php', 'all workshops', 'book');
	echo nav_link($sc, 'admin_teachers.php', 'teachers', 'star');
	echo "</ul>\n";
	
} else {
	echo "<div class=\"my-3 p-3 bg-info text-light\">";
	echo "<h1 class=\"display-1\"><a class=\"text-light\" href=\"index.php\">{$heading}</a></h1>\n";	
	echo "<p class=\"lead text-dark\">These workshops are taught online using the <a class='text-dark' href='http://www.zoom.us/'>Zoom</a> app.";
	echo "</div>\n";
}

if (isset($error) && $error) {
	echo "<div class='alert alert-danger' role='alert'>
		$error
	<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">
	    <span aria-hidden=\"true\">&times;</span>
	  </button></div>\n";
}

if (isset($message) && $message) {
	echo "<div class='alert alert-success' role='alert'>
		$message
	<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">
	    <span aria-hidden=\"true\">&times;</span>
	  </button></div>\n";
}

function nav_link($sc, $page, $text, $icon) {
	return "<li class='nav-item'><a class='nav-link ".(strpos($sc, $page) !== false ? 'active' : '')."' href='{$page}'><span class='oi oi-{$icon}' title='{$icon}' aria-hidden='true'></span> {$text}</a></li>\n";
	
}

