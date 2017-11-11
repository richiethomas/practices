<?php $heading = isset($heading) ? $heading: 'will hines practices'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title><?=$heading?></title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">

	<!-- Latest compiled and minified JavaScript -->
	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>	

<style>
.row {
	margin-bottom: 3rem;
}

table th.workshop-name {
	width: 500px;
}

</style>

</head>
<body>
	
<?php
echo "<div class=\"container\">\n";

if ($sc == 'admin.php') {
	echo "<h1 class=\"display-3\"><a href=\"{$sc}\">{$heading}</a></h1>\n";
} else {
	echo "<div class=\"jumbotron\">";
	echo "<h1 class=\"display-3\"><a href=\"{$sc}\">{$heading}</a></h1>\n";	
	echo "<p class=\"lead\">Greetings. This is a list of improv practices being taught or at least organized by Will Hines.";
	//echo (logged_in() ? '' : " Log in below, then you can enroll.")."</p>";	
	echo "</div>\n";
}



if (isset($error) && $error) {
	echo "<div class='alert alert-danger' role='alert'>$error</div>\n";
}
if (isset($message) && $message) {
	echo "<div class='alert alert-success' role='alert'>$message</div>\n";
}

?>		
