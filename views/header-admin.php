<!doctype html>
<html lang="en">
  <head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Admin</title>
	<!-- Bootstrap core CSS -->
	<link href="dist/css/bootstrap.min.css" rel="stylesheet">
	<!-- Custom styles for this template -->
	<link href="admin.css" rel="stylesheet">
    <!-- iconic (open source version) -->
    <link href="open-iconic/font/css/open-iconic-bootstrap.css" rel="stylesheet">

<style>	
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
  <body class="admin d-flex align-content-stretch align-items-stretch">
	  
  <header>
	<nav class="navbar p-0">
		<a class="navbar-brand" href="admin.php"><span>World's Greatest Improv School</span></a>
		<ul class="nav">
			<li class="nav-item"><a class="nav-link active" href="admin.php">Dashboard</a></li>
			<li class="nav-item"><hr/></li>
			<li class="nav-item"><a class="nav-link" href="admin_listall.php">Classes</a></li>
			<li class="nav-item"><a class="nav-link" href="admin_shows.php">Shows</a></li>
			<li class="nav-item"><a class="nav-link" href="admin_teachers.php">Teachers</a></li>
			<li class="nav-item"><a class="nav-link" href="admin_search.php">Students</a> </li>
			<li class="nav-item"><a class="nav-link" href="admin_emails.php">Emails</a> </li>

<?php if ($u->check_user_level(3)) { ?>
			<li><hr/></li>
			<li class="nav-item"><a class="nav-link" href="admin_reminders.php">Reminders</a></li>
    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Logs</a>
      <div class="dropdown-menu nav-item">
        <a class="dropdown-item nav-link" href="admin_debug_log.php">Debug Log</a>
        <a class="dropdown-item nav-link" href="admin_error_log.php">Error Log</a>
        <a class="dropdown-item nav-link" href="admin_status_log.php">Status Log</a>
      </div>
    </li>
			<li class="nav-item"><a class="nav-link" href="admin_revenue.php">Revenue</a></li>
			<li class="nav-item"><a class="nav-link" href="admin_payroll.php">Payroll</a></li>
<?php } ?>
			<li><hr/></li>

			<li class="nav-item user-item"><a class="nav-link d-flex align-items-center" href="you.php"><span class="oi oi-person" title="person" aria-hidden="true"></span> Will Hines</a></li>
			<li><hr/></li>
			<li class="nav-item back-to-website"><a class="nav-link" href="index.php" ><?php svg_code('chevron_lightgray_left');?>To Website</a>
		  </li>
		</ul>
	</nav>
  </header>
  
 <main class="p-4">
 
 