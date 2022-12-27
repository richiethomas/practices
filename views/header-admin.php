
<!doctype html>
<html lang="en">
  <head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Admin</title>

<!-- Bootstrap core CSS -->	
<link href="/assets/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>

	<!-- Custom styles for this template -->
	<link href="/assets/admin2.css" rel="stylesheet">

	
 </head>
  <body class="admin">
 
 <header>
 <nav class="navbar navbar-expand-md navbar-dark" aria-label="WGIS Admin Navbar">
   <div class="container-fluid">
	 <a class="navbar-brand" href="/admin"><img src='/images/logo_white_blue_bg.png' alt='WGIS admin navbar' width='75'></a>
     <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#wgis_admin_nav" aria-controls="wgis_admin_nav" aria-expanded="false" aria-label="Toggle navigation">
       <span class="navbar-toggler-icon"></span>
     </button>
     <div class="collapse navbar-collapse" id="wgis_admin_nav">
		<ul class="nav">
			<li class="nav-item"><a class="nav-link" href="/admin">Dashboard</a></li>
		    <li class="nav-item dropdown">
		      <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Things</a>
		      <div class="dropdown-menu nav-item">
		        <a class="dropdown-item nav-link" href="/admin-archives">Classes</a>
		        <a class="dropdown-item nav-link" href="/admin-search">Students</a>
		        <a class="dropdown-item nav-link" href="/admin-teachers">Teachers</a>
		        <a class="dropdown-item nav-link" href="/admin-teams">Teams</a>
		      </div>
		    </li>
		    <li class="nav-item dropdown">
		      <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Tools</a>
		      <div class="dropdown-menu nav-item">
		        <a class="dropdown-item nav-link" href="/admin-emails">Get Emails</a>
		        <a class="dropdown-item nav-link" href="/admin-reminders">Force Reminders</a>
		        <a class="dropdown-item nav-link" href="/admin-tasks">Assign Tasks</a>
		        <a class="dropdown-item nav-link" href="/admin-bulk-workshops">Bulk Edit Classes</a>
		        <a class="dropdown-item nav-link" href="/admin-conflicts">Check Conflicts</a>
		        <a class="dropdown-item nav-link" href="/admin-shows">List Shows</a>
		      </div>
		    </li>
		    <li class="nav-item dropdown">
		      <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Logs</a>
		      <div class="dropdown-menu nav-item">
		        <a class="dropdown-item nav-link" href="/admin-debug-log">Debug Log</a>
		        <a class="dropdown-item nav-link" href="/admin-error-log">Error Log</a>
		        <a class="dropdown-item nav-link" href="/admin-status-log">Status Log</a>
		        <a class="dropdown-item nav-link" href="/admin-email-log">Email Log</a>
		      </div>
		    </li>

<?php if ($u->check_user_level(4)) { ?>
		    <li class="nav-item dropdown">
		      <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Money</a>
		      <div class="dropdown-menu nav-item">
		        <a class="dropdown-item nav-link" href="/admin-registrations">Registrations</a>
		        <a class="dropdown-item nav-link" href="/admin-payments">Payments</a>
		        <a class="dropdown-item nav-link" href="/admin-revbyclass">Rev By Class</a>
		        <a class="dropdown-item nav-link" href="/admin-revbydate">Rev By Date</a>
		      </div>
		    </li>

<?php } ?>

			<li class="nav-item back-to-website"><a class="nav-link" href="/" ><i class='bi-chevron-left'></i> To Website</a>
		  </li>
		</ul>
     </div>
   </div>
 </nav>
</header>

 <main class="p-4">
 
 