<?php $heading = isset($heading) ? $heading: 'will hines practices'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title><?=$heading?></title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	
	<!-- Latest compiled and minified CSS -->
	<!--link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous"-->
	<link rel="stylesheet" href="bootstrap/bootstrap.min.css">

    <!-- iconic (open source version) -->
    <link href="open-iconic/font/css/open-iconic-bootstrap.css" rel="stylesheet">

	<!-- Latest compiled and minified JavaScript -->
	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
	<!--script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script-->	
	<script src="bootstrap/bootstrap.min.js"></script>

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
echo "<div class=\"container\">\n";

if (strpos($sc, 'admin') !== false ) {
	echo "<h1 class=\"display-2\"><a href=\"admin.php\">{$heading}</a></h1>\n";
	echo "<ul class='nav nav-pills nav-fill'>\n";
	echo nav_link($sc, 'admin.php', 'list workshops', 'people');
	echo nav_link($sc, 'admin_emails.php', 'get emails', 'envelope-closed');
	echo nav_link($sc, 'admin_revenue.php', 'revenues', 'dollar');
	echo nav_link($sc, 'admin_search.php', 'find students', 'magnifying-glass');
	echo nav_link($sc, 'admin_status.php', 'status log', 'graph');
	echo nav_link($sc, 'admin_mail_log.php', 'email activity', 'clipboard');
	echo "</ul>\n";
	
} else {
	echo "<div class=\"jumbotron bg-gradient-info  text-light\">";
	echo "<h1 class=\"display-2\"><a class=\"text-light\" href=\"{$sc}\">{$heading}</a></h1>\n";	
	echo "<p class=\"lead text-dark\">Greetings. This is a list of improv practices taught by Will Hines.";
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

?>		