<h2>Status Change Log</h2>

<?php
if (count($log) == 0) {
	echo "<p>No change log!</p>\n";
} else {

	echo "<table class='table'>
		<tr><th>user</th>".(isset($wk['id']) && $wk['id'] ? '' : '<th>workshop</th>')."<th>status</th><th>changed /<br>last enrolled<br>(hours before)</th></tr>\n";
			
	foreach ($log as $row) {
		$wkname = "<a href='admin_edit.php?wid={$row['workshop_id']}'>{$row['title']}</a><br><small>{$row['showstart']}</small>";
		$row_class = '';
		$last_enrolled = '';

		if ($row['status_id'] == DROPPED && $row['last_enrolled']) {
			$hours_before = round((strtotime($row['start']) - strtotime($row['happened'])) / 3600);
			$last_enrolled = "/<br>".date('j-M-y g:ia', strtotime($row['last_enrolled']))." ($hours_before)";
			if ($hours_before < LATE_HOURS) {
				$row_class = 'danger';
			}
		} else {
			$last_enrolled = "";
		}
	
		echo "<tr class='$row_class'>
			<td><a href=\"admin_student.php?uid={$row['user_id']}\">{$row['nice_name']}</a></td>
			".(isset($wk['id']) && $wk['id'] ? '' : "<td>$wkname</td>")."
			<td>{$row['status_name']}</td>
			<td><small>".date('j-M-y g:ia', strtotime($row['happened']))."{$last_enrolled}</small></td>
		</tr>\n";
	}
	echo "</table>\n";
} // end of "if no rows" loop

?>	
	