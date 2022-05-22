<?php 
$heading = isset($heading) ? "wgis: $heading" : "World's Greatest Improv School"; 
?>
<!doctype html>
<html lang="en">
  <head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo $heading; ?></title>
	
	<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

	<!-- Bootstrap core CSS -->	
<!-- 	<link href="/assets/boot.css" rel="stylesheet">
	<link href="/assets/bootstrap-icons.css" rel="stylesheet"> -->

<link href="/assets/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2" crossorigin="anonymous"></script>
	
	 	
	<!-- Custom styles for this template -->
	<link href="/assets/wgis.css" rel="stylesheet">
		
	
<?php
if (!isset($fb_image)) {
	$fb_image = "http://wgimprovschool.com/images/logo_square_small.jpg";
}
echo "	<meta property=\"og:image\" content=\"{$fb_image}\">\n";


if (!isset($fb_title)) {
	$fb_title = $heading;
}
echo "	<meta property=\"og:title\" content=\"{$fb_title}\">\n";

if (isset($fb_description)) {
	echo "	<meta property=\"og:description\" content=\"{$fb_description}\">\n";
}

?>	
	
  </head>
  <body>
	<header>
		<div class="container-lg container-fluid">
	<nav class="navbar navbar-expand-md navbar-light bg-white">
		 <a class="navbar-brand" href="/"><span>World's Greatest Improv School</span></a>
		  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		  </button>
		  <div class="collapse navbar-collapse" id="navbarsExampleDefault">
			<ul class="navbar-nav ml-auto justify-content-end">
			    <?php 
				if ($u->check_user_level(2)) { 
						echo "<li class=\"nav-item\"> <a class=\"btn btn-outline-primary nav-link\" href=\"/admin\">Admin</a> </li>\n";
			    } // end of check user level  
				foreach( get_nav_items() as $nav_item ) {
					$i = 01;					
					if (isset($nav_item['children'])) {
						echo "				<li class=\"nav-item dropdown\">
					<a class=\"nav-link dropdown-toggle\" href=\"#\" id=\"dropdown{$i}\" data-bs-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">{$nav_item['title']}</a>
					<div class=\"dropdown-menu\">\n";
						$i++;
							
							foreach( $nav_item['children'] as $nav_item_child) { 
								echo "						<a class=\"dropdown-item\" href=\"{$nav_item_child['href']}\">{$nav_item_child['title']}</a>\n";
							}
							echo "					</div></li>\n";
					
					} else { // this item has no children
						echo "				<li class=\"nav-item\"> <a class=\"nav-link\" href=\"{$nav_item['href']}\">{$nav_item['title']}</a></li>\n";
					}
				}
				if ($u->logged_in()) {		
					echo "			<li class=\"nav-item\"><a class=\"nav-link\" href=\"/you\" title=\"User Profile\"><i class='bi-person'></i> {$u->fields['nice_name']}</a>";					
					echo "</li>\n";
				} else {
			    	echo "			<li class=\"nav-item\"><a class=\"btn btn-outline-primary my-2 my-sm-0\" data-bs-toggle=\"modal\" data-bs-target=\"#login-modal\">Login</a></li>\n";
				}
			echo "</ul>";	
		 echo "</div>";
echo "	</nav>";

// time zone
if ($u->logged_in()) {
	echo "<p class='rounded-pill border bg-light p-2 col-sm-4'><i class='bi-clock'></i> <a href='/you'>{$u->fields['time_zone']} - {$u->fields['time_zone_friendly']}</a></p>";	
} 


echo "</div>";
echo " </header>";
  
 if ($page != 'home') {
	 echo "
	 <main>
	 	<div class=\"container-lg container-fluid\">
	 	<article> <!-- Using the <article> tag to signify this is a straight page or post -->
";
 	
 }
