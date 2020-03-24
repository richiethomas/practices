<?php echo $links; ?>
<table class="table table-striped table-bordered"><thead class="thead-dark">
		<tr>
			<th class="workshop-name" scope="col"><span class="oi oi-people" title="people" aria-hidden="true"></span> Workshop</th>
			<th scope="col"><span class="oi oi-calendar" title="calendar" aria-hidden="true"></span> When (PST)</th>
			<th scope="col"><span class="oi oi-map" title="map" aria-hidden="true"></span> Where</th>
			<th scope="col"><span class="oi oi-pulse" title="pulse" aria-hidden="true"></span> Status</th>
			<th scope="col"><span class="oi oi-task" title="task" aria-hidden="true"></span> Action</th>
		</tr></thead>
			<tbody>
<?php
		
	foreach ($rows as $t) {
		$cl = 'table-';
		if ($t['type'] == 'past') {
			$cl .= 'light';
		} elseif ($t['status_id'] == ENROLLED) {
			$cl .= 'success';
		} else {
			$cl .= 'warning';
		}
		
		echo "<tr class='$cl'><td>";
		if ($admin) {
			echo "<a href=\"admin.php?wid={$t['workshop_id']}&ac=ed\">{$t['title']}</a>";
		} else {
			echo "<a href=\"index.php?wid={$t['workshop_id']}\">{$t['title']}</a>";
		}
		echo "</td><td>{$t['when']}</td><td>{$t['place']}</td><td>";
		echo "{$statuses[$t['status_id']]}";
		if ($t['status_id'] == WAITING) {
			echo " (spot {$t['rank']})";
		}
		echo "</td><td><a href='index.php?wid={$t['workshop_id']}'><span class=\"oi oi-info\" title=\"info\" aria-hidden=\"true\"></span> info</a></td></tr>\n";
	}
	
?>
</tbody></table>
<?php echo $links; ?>
