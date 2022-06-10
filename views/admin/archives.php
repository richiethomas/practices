	<h2>All Classes</h2>

<p><small><a href='#addworkshop'>add a class</a></small></p>

<form action="/admin-archives/search" method="post">
<?php
echo \Wbhkit\texty('needle', $needle, 'Search by title');
echo \Wbhkit\submit();
?>
</form>
	
<?php echo $workshops_list; ?>

<a id='addworkshop'></a><div class='row'><div class='col-md-4'>
<form id='add_wk' action='/admin-workshop/ad' method='post' novalidate>
<?php echo \Wbhkit\form_validation_javascript('add_wk'); ?>
<fieldset name='session_add'><legend>Add Workshop</legend>
<?php echo $wk->get_workshop_fields().
\Wbhkit\submit('Add'); ?>
</fieldset></form>
	
</div></div>


