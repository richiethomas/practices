<?php $heading = isset($heading) ? "wgis: $heading": "wgis"; ?>
<!doctype html>
<html lang="en">
  <head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?=$heading?></title>
	<!-- Bootstrap core CSS -->
	<link href="dist/css/bootstrap.min.css" rel="stylesheet">
	<!-- Custom styles for this template -->
	<link href="wgis.css" rel="stylesheet">
	<meta name="google-signin-client_id" content="989168310652-al6inpe49ep29r9i2ppb0t8j58k1pt22.apps.googleusercontent.com">
	<script src="https://apis.google.com/js/platform.js" async defer></script>
	
    <!-- iconic (open source version) -->
    <link href="open-iconic/font/css/open-iconic-bootstrap.css" rel="stylesheet">
	
<?php
if (isset($fb_image)) {
	echo "<meta property=\"og:image\" content=\"{$fb_image}\">\n";
}
?>	
	
  </head>
  <body class="<?php if ($_SERVER['SCRIPT_NAME'] == '/index.php') { echo 'home'; } ?>">
	<header>
	<nav class="navbar navbar-expand-md navbar-light bg-white container-lg container-fluid">
		 <a class="navbar-brand" href="index.php"><span>World's Greatest Improv School</span></a>
		  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		  </button>
		  <div class="collapse navbar-collapse" id="navbarsExampleDefault">
			<ul class="navbar-nav ml-auto justify-content-end">
				
			    <?php if (Users\check_user_level(2)) { ?>
				 	<li class="nav-item"> <a class="btn btn-outline-primary" href="admin.php">Admin</a> </li>	  
			    <?php } // end of check user level 2 ?>
				
				<?php foreach( get_nav_items() as $nav_item ){
					$i = 01;					
					
					if (isset($nav_item['children'])){
						?>
						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle" href="#" id="dropdown<?php echo $i; $i++;?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo $nav_item['title']; ?></a>
							<div class="dropdown-menu" aria-labelledby="dropdown01">
							<?php
							foreach( $nav_item['children'] as $nav_item_child){
								?> <a class="dropdown-item" href="<?php echo $nav_item_child['href'] ?>"><?php echo	 $nav_item_child['title'] ?></a><?php
							}
							?>
										</div>
								    </li>
								    <?php
					} else {
						// This item has no children
						?> <li class="nav-item"> <a class="nav-link" href="<?php echo $nav_item['href'] ?>"><?php echo $nav_item['title'] ?></a> </li><?php
					}
				}?>
				
		  		<?php if (Users\logged_in()) { ?>		
							
				
				<li class="nav-item"><a class="user-logged-in" href="you.php" title="User Profile"><span class="oi oi-person" title="person" aria-hidden="true"></span>  <?php echo "{$u['nice_name']}"; ?></a></li>
				
				<?php } else { ?>
			    <li class="nav-item"> <a class="btn btn-outline-primary my-2 my-sm-0" data-toggle="modal" data-target="#login-modal">Login</a></li>
			    <?php } ?>
			</ul>
		  </div>
	</nav>
  </header>
  
  
  
 <?php 
 

 
 
 
 if ($page != 'home') {
	 echo "
	 <main role=\"main\">
	 	<div class=\"container-lg container-fluid\">
	 	<article> <!-- Using the <article> tag to signify this is a straight page or post -->
";
 }

 ?>
