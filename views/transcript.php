<?php 
echo $links; 


if ($admin) {
	echo "<form action='$sc' method='post'>\n";
	echo Wbhkit\hidden('ac', 'at'); 	
	echo Wbhkit\hidden('uid', "$uid"); 	
}

?>

<table class="table table-striped table-bordered"><thead class="thead-dark">
		<tr>
			<th class="workshop-name" scope="col"><span class="oi oi-people" title="people" aria-hidden="true"></span> <?php if ($admin) { echo "(paid?) "; } ?>Workshop</th>
			<th scope="col"><span class="oi oi-calendar" title="calendar" aria-hidden="true"></span> When (<?php echo TIMEZONE; ?>)</th>
			<th scope="col"><span class="oi oi-map" title="map" aria-hidden="true"></span> Where</th>
			<th scope="col"><span class="oi oi-pulse" title="pulse" aria-hidden="true"></span> Status</th>
			<th scope="col"><span class="oi oi-task" title="task" aria-hidden="true"></span> Action</th>
		</tr></thead>
			<tbody>
<?php
		
	foreach ($rows as $t) {
		$cl = 'table-';
		if ($t['upcoming'] == 0) {
			$cl .= 'light';
		} elseif ($t['status_id'] == ENROLLED) {
			$cl .= 'success';
		} else {
			$cl .= 'warning';
		}
		
		
		$sessions = '';
		if (!empty($t['sessions'])) {
			$sessions ="<p>\n";
			$sessions .= "{$t['when']}";
			foreach ($t['sessions'] as $s) {
				$sessions .= "<br>\n{$s['friendly_when']}";
			}
			$sessions .= "</p>\n";
			$t['when'] = $sessions; // replace the when variable 
		}
		
		echo "<tr class='$cl'><td>";
		if ($admin) {
			echo Wbhkit\checkbox('paids', $t['enrollment_id'], "<a href=\"admin.php?wid={$t['workshop_id']}&ac=ed\">{$t['title']}</a>", $t['attended'], true);
		} else {
			echo "<a href=\"index.php?wid={$t['workshop_id']}\">{$t['title']}</a>";
		}
		echo "</td><td>{$t['when']} (".TIMEZONE.")</td><td>{$t['place']}</td><td>";
		echo "{$statuses[$t['status_id']]}";
		if ($t['status_id'] == WAITING) {
			echo " (spot {$t['rank']})";
		}
		echo "</td><td><a href='index.php?wid={$t['workshop_id']}'><span class=\"oi oi-info\" title=\"info\" aria-hidden=\"true\"></span> info</a></td></tr>\n";
	}
	
?>
</tbody></table>
<?php 
if ($admin) {
	echo Wbhkit\submit("update paid");
	echo "</form>\n";
}
echo $links; 
?>
