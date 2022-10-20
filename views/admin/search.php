<div class='row'><div class='col-md-12'><h2>Find Students</h2>
<form action ='/admin-search/search' method='post'>
<?php
$search = 1;
include 'assets/ajax/search_box.php';
?>
	<div class="form-group">
	<label for="search-box" class="form-label">Email address</label>
	<input type="text" class="form-control" id="search-box" name="needle" autocomplete="off" value="<?php if ($needle) { echo $needle; } ?>">
	<div id="suggesstion-box"></div>
	</div>
<?php	
	echo "Sort by: ".Wbhkit\radio('sort', $search_opts, $sort);
?>
<div class="clearfix"><?php echo Wbhkit\submit('search'); ?></div>
</form>

<p>Or click this button to list <a class='btn btn-primary' href='/admin-search/search/everyone'>all students</a>
<?php
if ($needle == 'everyone') {
	echo "<a class='btn btn-primary' href='/admin-search/zero'>remove the zeroes</a>";
}
?>	
</p>
<?php					
if ($needle) {
	echo "<h3>Matches for '$needle'</h3>\n";
	if (count($all) == 0) {
		echo "<p>No matches!</p>";
		if ($u->validate_email($needle)) {
			echo "<p>Would you like to add <a class='btn btn-primary' href='/admin-search/adduser/$needle'>{$needle}</a> as a user?</p>\n";
		}
	} else {
		echo "<ul>\n";
		foreach ($all as $s) {
			echo "<li><a href=\"/admin-users/view/{$s['id']}/{$needle}\">{$s['fullest_name']}</a> ({$s['classes']}) ".($needle == 'everyone' ? date ('Y M j, g:ia', strtotime($s['joined'])) : '')."</li>\n";
		}
		echo "</ul>\n";
	}

}
?>
</div></div>
