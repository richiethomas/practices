<?php if ($ac == 'del') { ?>
<div class='alert alert-danger'><p>Really <a class='btn btn-outline-danger' href='<?php echo $sc;?>?ac=condel'>delete the mail log</a>?</p></div>
<?php } ?>
<h2>Email Activity <small><a class='btn btn-outline-danger' href='<?php echo $sc; ?>?ac=del'>delete log</a></small></h2>
<pre>
<?php echo $log; ?>
</pre>
