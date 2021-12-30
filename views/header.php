<?php 
$heading = isset($heading) ? "wgis: $heading" : "World's Greatest Improv School"; 
?>
<!doctype html>
<html lang="en">
  <head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php 
		echo $heading; 
		?></title>
	
	<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

	<!-- Bootstrap core CSS -->	
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
	 	
	<!-- Custom styles for this template -->
	<link href="/wgis.css" rel="stylesheet">
	
    <!-- iconic (open source version) -->
    <link href="/open-iconic/font/css/open-iconic-bootstrap.css" rel="stylesheet">
	
	
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
				if ($u->check_user_level(3)) { 
						echo "<li class=\"nav-item\"> <a class=\"btn btn-outline-primary nav-link\" href=\"/admin\">Admin</a> </li>\n";
			    } // end of check user level 3 
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
					echo "			<li class=\"nav-item\"><a class=\"nav-link\" href=\"/you\" title=\"User Profile\"><span class=\"oi oi-person nav-link p-0\" title=\"person\" aria-hidden=\"true\"></span>{$u->fields['nice_name']}</a>";					
					echo "</li>\n";
				} else {
			    	echo "			<li class=\"nav-item\"><a class=\"btn btn-outline-primary my-2 my-sm-0\" data-bs-toggle=\"modal\" data-bs-target=\"#login-modal\">Login</a></li>\n";
				}
			echo "</ul>";	
		 echo "</div>";
echo "	</nav>";
if ($u->logged_in()) {
	echo "<div><span class=\"oi oi-clock p-0\" title=\"time zone\" aria-hidden=\"true\"></span> <a href='/you'>{$u->fields['time_zone']} - {$u->fields['time_zone_friendly']}</a></div>";	
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
