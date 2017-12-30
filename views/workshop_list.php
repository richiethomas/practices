<?php echo $links; ?>
<table class="table table-striped table-bordered"><thead class="thead-dark">
		<tr>
			<th class="workshop-name" scope="col"><span class="oi oi-people" title="people" aria-hidden="true"></span> Workshop</th>
			<th scope="col"><span class="oi oi-calendar" title="calendar" aria-hidden="true"></span> When</th>
			<th scope="col"><span class="oi oi-map" title="map" aria-hidden="true"></span> Where</th>
			<th scope="col"><span class="oi oi-dollar" title="dollar" aria-hidden="true"></span> Cost</th>
			<th scope="col"><span class="oi oi-clipboard" title="clipboard" aria-hidden="true"></span> Spots</th>
			<th scope="col"><span class="oi oi-task" title="task" aria-hidden="true"></span> Action</th>
		</tr></thead>
			<tbody>
				
<?php				
		foreach ( $rows as $row ) {
			$public = '';
			if ($admin && $row['when_public']) {
				$public = "<br><small>Public: ".date('D M j - g:ia', strtotime($wk['when_public']))."</small>\n";
			}	
					
			$cl = 'table-';
			if (date('z', strtotime($row['start'])) == date('z')) { // today
				$cl .= 'info'; 
			} elseif ($row['type'] == 'soldout') {
				$cl .= 'danger';
			} elseif ($row['type'] == 'open') {
				$cl .= 'success';
			} elseif ($row['type'] == 'past') {
				$cl .= 'light';
			} else  {
				$cl = '';
			}
		
			echo "<tr class='$cl'>";
			$titlelink = ($admin 
				? "<a href='$sc?wid={$row['id']}&ac=ed'>{$row['title']}</a>"
				: "<a href='$sc?wid={$row['id']}'>{$row['title']} <span class=\"oi oi-info\" title=\"info\" aria-hidden=\"true\"></span></a>");
			
			echo "<td>{$titlelink}".($row['notes'] ? "<p class='small text-muted'>{$row['notes']}</p>" : '')."</td>
			<td>{$row['when']}{$public}</td>
			<td>{$row['place']}</td>
			<td>{$row['cost']}</td>
			<td>".number_format($row['open'], 0)." of ".number_format($row['capacity'], 0).",<br> ".number_format($row['waiting']+$row['invited'])." waiting</td>
	";
			if ($admin) {
				echo "<td><a href=\"$sc?wid={$row['id']}\">Clone</a></td></tr>\n";
			} else {
				echo "<td><a href=\"{$sc}?wid={$row['id']}&v=winfo\"><span class=\"oi oi-info\" title=\"info\" aria-hidden=\"true\"></span> info</a></td></tr>\n";
			}
		}

?>
</tbody></table>
<?php echo $links; ?>