<!doctype html>
<html lang="en">
  <head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php page_title();?></title>
	<!-- Bootstrap core CSS -->
	<link href="dist/css/bootstrap.min.css" rel="stylesheet">
	<!-- Custom styles for this template -->
	<link href="wgis.css" rel="stylesheet">
  </head>
  <body class="<?php echo body_class();?>">
	<header>
	<nav class="navbar navbar-expand-md navbar-light bg-white container-lg container-fluid"">
		 <a class="navbar-brand" href="#"><span>World's Greatest Improv School</span></a>
		  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		  </button>
		  <div class="collapse navbar-collapse" id="navbarsExampleDefault">
			<ul class="navbar-nav ml-auto justify-content-end">
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
				<?php if (is_user_logged_in()) {?>
				<li class="nav-item"><?php user_profile_link_and_image();?></li>
				<?php } else { ?>
			    <li class="nav-item"> <a class="btn btn-outline-primary my-2 my-sm-0" data-toggle="modal" data-target="#login-modal">Login</a></li>
			    <?php } ?>
			</ul>
		  </div>
	</nav>
  </header>