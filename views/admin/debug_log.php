<?php if ($ac == 'del') { ?>
<div class='alert alert-danger'><p>Really <a class='btn btn-outline-danger' href='<?php echo $sc;?>?ac=condel'>delete the debug log</a>?</p></div>
<?php } ?>

<div class="row"><div class="col-sm-4">
<form action="<?php echo $sc; ?>" method="post">
	<?php
echo \Wbhkit\hidden('ac', 'deldate');
echo \Wbhkit\multi_drop('deldate', $dates_opts, null, 'Dates to delete', 5);
echo \Wbhkit\submit('delete dates'); ?>	
</form>
</div></div>

<h2>Debug Log <small><a class='btn btn-outline-danger' href='<?php echo $sc; ?>?ac=del'>delete entire log</a></small></h2>
<pre>
<?php 
if (!is_array($log)) { $log = array(); }
echo implode($log); 
?>
</pre>
