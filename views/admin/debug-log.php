<?php if ($ac == 'del') { ?>
<div class='alert alert-danger'><p>Really <a class='btn btn-outline-danger' href='/admin-debug/condel'>delete the debug log</a>?</p></div>
<?php } ?>

<div class="row"><div class="col-sm-4">
<form action="/admin-debug/deldate" method="post">
	<?php
echo \Wbhkit\multi_drop('deldate', $dates_opts, null, 'Dates to delete', 5);
echo \Wbhkit\submit('delete dates'); ?>	
</form>
</div></div>

<h2>Debug Log <small><a class='btn btn-outline-danger' href='/admin-debug/del'>delete entire log</a></small></h2>
<pre>
<?php 
if (!is_array($log)) { $log = array(); }
echo implode($log); 
?>
</pre>
