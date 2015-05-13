<?php $heading = isset($heading) ? $heading: 'will hines practices'; ?>
<!DOCTYPE html>
<html>
<head>
	
<!--

design quick-fixes taken from:
	http://24ways.org/2012/how-to-make-your-site-look-half-decent/
	and
	http://designshack.net/articles/css/10-great-google-font-combinations-you-can-copy/	
	Readable Theme from http://bootswatch.com/
	
-->	
	<title><?=$heading?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">

	<!-- Optional theme -->
	<!--link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css"-->
	<link rel="stylesheet" href="bootstrap.readable.min.css">


	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
	
	<!-- jquery -->
	<script src="//code.jquery.com/jquery-1.11.2.min.js"></script>
	<script src="backstretch.js"></script>

<script>
	$( document ).ready(function() {
	    $.backstretch("bb4.jpg");
	});
</script>
	
	
<style>
.row {
	margin-bottom: 40px;
}

.container {	
	background: url(cream_dust_transparent.png) repeat 0;
}

h1, h2, h3 { 
    text-shadow: 1px 1px 1px #ccc;
	font-weight: normal;
}
.page-header { 
    box-shadow: 0 0 1em 1em #ccc;
}

</style>
</head>
<body>
	
<?php
echo "<div class=\"container\">\n";

if ($sc == 'admin.php') {
	echo "<h1><a href=\"{$sc}\">{$heading}</a></h1>\n";
} else {
	echo "<div class=\"jumbotron page-header\">";
	echo "<h1><a href=\"{$sc}\">{$heading}</a></h1>\n";	
	echo "<p>Greetings. This is a list of improv practices being taught or at least organized by Will Hines.".(wbh_logged_in() ? '' : " Log in below, then you can enroll.")."</p>";	
	echo "</div>\n";
}



if (isset($error) && $error) {
	echo "<div class='alert alert-danger'>$error</div>\n";
}
if (isset($message) && $message) {
	echo "<div class='alert alert-success'>$message</div>\n";
}

?>		
