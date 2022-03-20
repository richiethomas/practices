<?php if ($ac == 'del') { ?>
<div class='alert alert-danger'><p>Really <a class='btn btn-outline-danger' href='/admin-error-log/condel'>delete the error log</a>?</p></div>
<?php } ?>

<div class="row"><div class="col-sm-4">
<form action="/admin-error-log/deldate" method="post">
	<?php
echo \Wbhkit\multi_drop('deldate', $dates_opts, null, 'Dates to delete', 5);
echo \Wbhkit\submit('delete dates'); ?>	
</form>
</div></div>

<h2>Error Log <small><a class='btn btn-outline-danger' href='/admin-error-log/del'>delete entire log</a></small></h2>
<pre>
<?php 
if (is_array($log)) {
	echo implode($log); 
}
?>
</pre>
