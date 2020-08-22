<?php $heading = isset($heading) ? "wgis: $heading": "wgis"; ?>
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

<meta name="google-signin-client_id" content="989168310652-al6inpe49ep29r9i2ppb0t8j58k1pt22.apps.googleusercontent.com">
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


.workshop-info { background-color: #bee5eb; }
.workshop-danger { background-color: #f5c6cb; }
.workshop-success { background-color: #c3e6cb; }
.workshop-light { background-color: #fdfdfe; }


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
?>
<div class="row align-items-center">
	<div class="col-sm-3"><a href="admin.php"><img alt="wgis" class="img-fluid" src='assets/branding/wgis_letters.jpg'></a></div>
	<div class="col-sm-9"><h1><a href="admin.php">admin pages</a> <a class='text-muted' href='index.php'>(user side)</a></h1></div>
</div>	
	<?php
	
	echo "<ul class='nav nav-pills nav-fill'>\n";
	echo nav_link($sc, 'admin.php', 'upcoming', 'calendar');
	echo nav_link($sc, 'admin_emails.php', 'get emails', 'envelope-closed');
	
	if (Users\check_user_level(3)) { echo nav_link($sc, 'admin_revenue.php', 'revenues', 'dollar'); }
	
	echo nav_link($sc, 'admin_search.php', 'find students', 'magnifying-glass');
?>	
    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Logs</a>
      <div class="dropdown-menu">
        <a class="dropdown-item" href="admin_debug_log.php">Debug Log</a>
        <a class="dropdown-item" href="admin_status_log.php">Status Log</a>
        <a class="dropdown-item" href="admin_error_log.php">Error Log</a>
      </div>
    </li>
<?php	
	echo nav_link($sc, 'admin_listall.php', 'all workshops', 'book');
	echo nav_link($sc, 'admin_teachers.php', 'teachers', 'star');
	echo "</ul>\n";
	
} else {
	?>
<div class="row"><div class="col-sm text-center">
<a href="index.php"><img class="img-fluid" src="assets/branding/wgis_banner.jpg" alt="world's greatest improv school"></a>
<h1 class="sr-only"><a class="text-light" href="index.php">world's greatest improv school</a></h1>	
</div></div>

	
<?php
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

