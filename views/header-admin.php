<!doctype html>
<html lang="en">
  <head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Admin</title>
	<!-- Bootstrap core CSS -->


	<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

	<!-- Bootstrap core CSS -->	
	<link href="/assets/boot.css" rel="stylesheet">
	<link href="/assets/bootstrap-icons.css" rel="stylesheet">

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
	 	

	<!-- Custom styles for this template -->
	<link href="/assets/admin.css" rel="stylesheet">

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
		<a class="navbar-brand" href="/admin"><span>World's Greatest Improv School</span></a>
		<ul class="nav">
			<li class="nav-item"><a class="nav-link active" href="/admin">Dashboard</a></li>
			<li class="nav-item"><hr/></li>
			<li class="nav-item"><a class="nav-link" href="/admin-archives">Classes</a></li>
			<li class="nav-item"><a class="nav-link" href="/admin-shows">Shows</a></li>
			<li class="nav-item"><a class="nav-link" href="/admin-teachers">Teachers</a></li>
			<li class="nav-item"><a class="nav-link" href="/admin-search">Students</a> </li>
			<li class="nav-item"><a class="nav-link" href="/admin-emails">Emails</a> </li>
			<li class="nav-item"><a class="nav-link" href="/admin-bulk-workshops">Bulk</a> </li>

<?php if ($u->check_user_level(3)) { ?>
			<li><hr/></li>
			<li class="nav-item"><a class="nav-link" href="/admin-reminders">Reminders</a></li>
    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Logs</a>
      <div class="dropdown-menu nav-item">
        <a class="dropdown-item nav-link" href="/admin-debug">Debug Log</a>
        <a class="dropdown-item nav-link" href="/admin-error">Error Log</a>
        <a class="dropdown-item nav-link" href="/admin-status">Status Log</a>
      </div>
    </li>
			<li class="nav-item"><a class="nav-link" href="/admin-revenue">Revenue</a></li>
			<li class="nav-item"><a class="nav-link" href="/admin-payroll">Payroll</a></li>
<?php } ?>
			<li><hr/></li>

			<li class="nav-item user-item"><a class="nav-link d-flex align-items-center" href="/you"><i class='bi-person'></i> <?php echo $u->fields['nice_name']; ?></a></li>
			<li><hr/></li>
			<li class="nav-item back-to-website"><a class="nav-link" href="/" ><i class='bi-chevron-left'></i> To Website</a>
		  </li>
		</ul>
	</nav>
  </header>
  
 <main class="p-4">
 
 