<div class='row'><div class='col-md-9'><h2>attendance for <a href='<?php echo $sc; ?>?ac=ed&wid=<?php echo $wk['id']; ?>'><?php echo $wk['showtitle']; ?></a></h2>
	
<div id='emaillists'>
<form action='<?php echo $sc; ?>?' method='post'>
<?php echo Wbhkit\hidden('wid', $wk['id']); ?>
<?php echo Wbhkit\hidden('ac', 'at'); ?>
<?php
foreach ($statuses as $stid => $status_name) {
	echo "<h3>{$status_name}</h3>\n";
	$stds = $students[$stid];
	foreach ($stds as $as) {
		echo"<p>".Wbhkit\checkbox('users', $as['id'], $as['email'], $as['attended'], true).'</p>';
	}
}
echo Wbhkit\submit("update attendance");
?>
</form>
</div>
</div></div>
