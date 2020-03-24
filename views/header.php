<?php $heading = isset($heading) ? $heading: 'will hines online workshops'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title><?=$heading?></title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	
	<!-- Latest compiled and minified JS and CSS -->
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>	
	
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
	

    <!-- iconic (open source version) -->
    <link href="open-iconic/font/css/open-iconic-bootstrap.css" rel="stylesheet">


<style>
table th.workshop-name {
	width: 300px;
}

div.admin-edit-workshop h2,
div.admin-edit-workshop h3,
div.admin-edit-workshop h4
{
	margin-top: 2rem;
}

</style>

</head>
<body>

<?php
echo "<div class=\"container-fluid\">\n";

if (strpos($sc, 'admin') !== false ) {
	echo "<h1><a href=\"admin.php\">{$heading}</a> <small><a class='text-muted' href='index.php'>(user side)</a></small></h1>\n";
	echo "<ul class='nav nav-pills nav-fill'>\n";
	echo nav_link($sc, 'admin.php', 'list workshops', 'people');
	echo nav_link($sc, 'admin_emails.php', 'get emails', 'envelope-closed');
	echo nav_link($sc, 'admin_revenue.php', 'revenues', 'dollar');
	echo nav_link($sc, 'admin_search.php', 'find students', 'magnifying-glass');
	echo nav_link($sc, 'admin_status.php', 'status log', 'graph');
	echo nav_link($sc, 'admin_mail_log.php', 'error log', 'clipboard');
	echo "</ul>\n";
	
} else {
	echo "<div class=\"my-3 p-3 bg-info text-light\">";
	echo "<h1 class=\"display-1\"><a class=\"text-light\" href=\"{$sc}\">{$heading}</a></h1>\n";	
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

