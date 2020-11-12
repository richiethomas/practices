	<h2>All Workshops</h2>

<p><small><a href='#addworkshop'>add a workshop</a></small></p>

<form action="<?php echo $sc; ?>" method="post">
<?php
echo \Wbhkit\texty('needle', $needle, 'Search by title');
echo \Wbhkit\hidden('page', 'all');
echo \Wbhkit\submit();
?>
</form>
	
<?php echo $workshops_list; ?>

<a id='addworkshop'></a><div class='row'><div class='col-md-4'>
<?php echo $add_workshop_form; ?>
</div></div>


