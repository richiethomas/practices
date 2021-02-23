<?php $heading = isset($heading) ? "wgis: $heading": "wgis"; ?>
<!doctype html>
<html lang="en">
  <head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?=$heading?></title>
	
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha256-4+XzXVhsDmqanXGHaHvgh1gMQKX40OUvDEBTu8JcmNs=" crossorigin="anonymous"></script>
	
	<!-- Bootstrap core CSS -->	
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
	
	<!-- Custom styles for this template -->
	<link href="wgis.css" rel="stylesheet">
	
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
		  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		  </button>
		  <div class="collapse navbar-collapse" id="navbarsExampleDefault">
			<ul class="navbar-nav ml-auto justify-content-end">
				
			    <?php if ($u->check_user_level(2)) { ?>
				 	<li class="nav-item"> <a class="btn btn-outline-primary nav-link" href="admin.php">Admin</a> </li>	  
			    <?php } // end of check user level 2 ?>
				
				<?php foreach( get_nav_items() as $nav_item ){
					$i = 01;					
					
					if (isset($nav_item['children'])){
						?>
						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle" href="#" id="dropdown<?php echo $i; $i++;?>" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo $nav_item['title']; ?></a>
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
				
		  		<?php if ($u->logged_in()) { ?>		
							
				
				<li class="nav-item"><a class="user-logged-in" href="you.php" title="User Profile"><span class="oi oi-person" title="person" aria-hidden="true"></span>  <?php echo "{$u->fields['nice_name']}"; ?></a></li>
				
				<?php } else { ?>
			    <li class="nav-item"> <a class="btn btn-outline-primary my-2 my-sm-0" data-bs-toggle="modal" data-bs-target="#login-modal">Login</a></li>
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
