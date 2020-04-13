<div class='row'><div class='col-md-10'><h2>Revenues</h2>
<form action='<?php echo $sc; ?>' method='post'>
<?php echo \Wbhkit\texty('searchstart', $searchstart, 'Search Start'); ?>
<?php echo \Wbhkit\texty('searchend', $searchend, 'Search End'); ?>
<?php echo \Wbhkit\submit('Update'); ?>


<?php
$weeknav = "<p><a href='admin_revenue.php?searchstart=$lastweekstart&searchend=$lastweekend'>last week</a> | <a href='admin_revenue.php'>this week</a> | <a href='admin_revenue.php?searchstart=$nextweekstart&searchend=$nextweekend'>next week</a></p>\n";
echo $weeknav;
?>


<?php echo \Wbhkit\hidden('ac', 'rev'); ?>

<table class='table table-striped'>
	<thead><tr>
		<th>workshop</th>
		<th>attended / enrolled / capacity</th>
		<th>cost</th>
		<th>suggested: attended / enrolled</th>
		<th>revenue</th>
		<th>expenses</th>
		<th>profit</th></tr>
	</thead><tbody>
<?php
		$totals = array(
			'revenue' => 0,
			'expenses' => 0,
			'suggested_attended' => 0,
			'suggested_enrolled' => 0
		);
		foreach ($workshops_list as $wid => $wk) {
			echo "<tr><td>({$wk['id']}) <a href='admin.php?wid={$wk['id']}&ac=ed'>{$wk['title']}</a><br><span class='text-secondary'>{$wk['place']}</span></td>
			<td>{$wk['attended']} / {$wk['enrolled']} / {$wk['capacity']}</td>
			<td>{$wk['cost']}</td>
			<td>".($wk['cost']*$wk['attended'])." / ".($wk['cost']*$wk['enrolled'])."</td>
			<td>".Wbhkit\texty("revenue_{$wk['id']}", $wk['revenue'], 0)."</td>
			<td>".Wbhkit\texty("expenses_{$wk['id']}", $wk['expenses'], 0)."</td>
			<td>".($wk['revenue']-$wk['expenses'])."</td></tr>\n";
			$totals['revenue'] += $wk['revenue'];
			$totals['expenses'] += $wk['expenses'];
			$totals['suggested_attended'] += $wk['cost']*$wk['attended'];
			$totals['suggested_enrolled'] += $wk['cost']*$wk['enrolled'];
		}
		echo "<tr><td>Totals:</td><td colspan=2>&nbsp;</td><td>{$totals['suggested_attended']} / {$totals['suggested_enrolled']}</td><td>{$totals['revenue']}</td><td>{$totals['expenses']}</td><td>".($totals['revenue']-$totals['expenses'])."</td></tr>\n";
		echo "</tbody></table>".Wbhkit\submit('Also Update')."</form>\n";
		echo $weeknav; 
		
		
		echo "</div></div>\n";
		
?>