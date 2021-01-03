<div class='row'><div class='col-md-12'><h2>Find Students</h2>
<form action ='<?php echo $sc; ?>' method='post'>
<?php	
	echo Wbhkit\hidden('ac', 'search');
	
?>	
<?php
include 'ajax-jquery-search.php';
?>
	<div class="form-group">
	<label for="search-box" class="form-label">Email address</label>
	<input type="text" class="form-control" id="search-box" name="needle" autocomplete="off" value="<?php if ($needle) { echo $needle; } ?>">
	<div id="suggesstion-box"></div>
	</div>
<?php	
	//echo Wbhkit\texty('needle', $needle, 'Enter an email or part of an email:');
	echo "Sort by: ".Wbhkit\radio('sort', $search_opts, $sort);
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
		if ($u->validate_email($needle)) {
			echo "<p>Would you like to add <a class='btn btn-primary' href='admin_users.php?ac=adduser&needle=$needle'>{$needle}</a> as a user?</p>\n";
		}
	} else {
		echo "<ul>\n";
		foreach ($all as $s) {
			echo "<li><a href=\"admin_users.php?guest_id={$s['id']}&needle={$needle}\">{$s['fullest_name']}</a> ".($s['phone'] ? ", {$s['phone']}" : '')." ({$s['classes']}) ".($needle == 'everyone' ? date ('Y M j, g:ia', strtotime($s['joined'])) : '')."</li>\n";
		}
		echo "</ul>\n";
	}

}
?>
</div></div>
