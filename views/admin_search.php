<div class='row'><div class='col-md-12'><h2>Find Students</h2>
<form action ='<?php echo $sc; ?>' method='post'>
<?php	
	echo Wbhkit\hidden('ac', 'search');
	echo Wbhkit\texty('needle', $needle, 'Enter an email or part of an email:');
	echo Wbhkit\radio('sort', $search_opts, $sort);
?>
<div class="clearfix"><?php echo Wbhkit\submit('search'); ?></div>
</form>

<p>Or click this button to list <a class='btn btn-primary' href='<?php echo $sc; ?>?ac=search&needle=everyone'>all students</a>
<?php
if ($needle == 'everyone') {
	echo "<a class='btn btn-primary' href='$sc?ac=zero'>remove the zeroes</a>";
}
?>	
</p>
<?php					
if ($needle) {
	echo "<h3>Matches for '$needle'</h3>\n";
	if (count($all) == 0) {
		echo "<p>No matches!</p>";
	} else {
		echo "<ul>\n";
		foreach ($all as $s) {
			echo "<li><a href=\"admin_student.php?guest_id={$s['id']}&needle={$needle}\">{$s['fullest_name']}</a> ".($s['phone'] ? ", {$s['phone']}" : '')." ({$s['classes']}) ".($needle == 'everyone' ? date ('Y M j, g:ia', strtotime($s['joined'])) : '')."</li>\n";
		}
		echo "</ul>\n";
	}

}
?>
</div></div>
