<p>
	<a class='btn btn-primary' href='#add'><span class='oi oi-people' title='people' aria-hidden='true'></span> add a workshop</a> 
	<a class='btn btn-primary' href='admin_emails.php'><span class='oi oi-envelope-closed' title='envelope-closed' aria-hidden='true'></span> get emails</a> 
	<a class='btn btn-primary' href='admin_revenue'><span class='oi oi-dollar' title='dollar' aria-hidden='true'></span> revenues</a>
	<a class='btn btn-primary' href='admin_search.php'><span class='oi oi-magnifying-glass' title='magnifying-glass' aria-hidden='true'></span> find students</a>
	<a class='btn btn-primary' href='admin_status.php'><span class='oi oi-graph' title='graph' aria-hidden='true'></span> change log</a>
</p>
<h2>All Workshops</h2>
		
<?php echo $workshops_list; ?>

<a id='add'></a><div class='row'><div class='col-md-4'>
<?php echo $add_workshop_form; ?>
</div></div>
