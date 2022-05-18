<?php
echo "<h2><a href='/admin-workshop/view/{$wk['id']}'>{$wk['title']}</a></h2>\n";
echo "<div class='row mt-md-3 admin-edit-workshop'>\n";

		// enrollment column
		echo "<div class='col-md-8'><h2>Enrollment Info</h2>\n";

		//show enrollment totals at top
		echo  "<p>totals: (".implode(" / ", array_values($stats)).")</p>\n";
		
		echo "<form action='/admin-bulk-status/change/{$wk['id']}' method='post'>\n";
		
		// list students for each status
		foreach ($statuses as $stid => $status_name) {
			echo  "<h4>{$status_name} (".$stats[$stid].")</h4>\n";
			foreach ($lists[$stid] as $s) {
				echo "<div class='row my-3'><div class='col-md-5'>".
					Wbhkit\checkbox('users', $s['id'], "<a href='/admin-users/view/{$s['id']}'>{$s['nice_name']}</a>", false, true)."</div></div>";
			}
		}
		echo "<div class='mt-4'>".
		Wbhkit\drop('st', $statuses, 0, 'target status').
		Wbhkit\checkbox('confirm', 1, 'email confirmation').
		Wbhkit\submit("update status").
		"</div>
		</form>\n";		
		
		
		echo  "</div>"; // end of column
		echo  "</div>\n"; //end of row
		
?>