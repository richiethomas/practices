<div class='row'><div class='col-md-9'><h2>attendance for <a href='admin.php?ac=ed&wid=<?php echo $wk['id']; ?>'><?php echo $wk['title']; ?></a></h2>
	
<div id='emaillists'>
<form action='<?php echo $sc; ?>?' method='post'>
<?php echo Wbhkit\hidden('wid', $wk['id']); ?>
<?php echo Wbhkit\hidden('ac', 'at'); ?>
<?php
foreach ($statuses as $stid => $status_name) {
	echo "<h3>{$status_name}</h3>\n";
	$stds = $students[$stid];
	foreach ($stds as $as) {
		echo"<p>".Wbhkit\checkbox('users', $as['id'], "<a href='admin_student.php?uid={$as['id']}'>{$as['fullest_name']}</a>", $as['attended'], true).'</p>';
	}
}
echo Wbhkit\submit("update attendance");
?>
</form>
</div>
</div></div>
