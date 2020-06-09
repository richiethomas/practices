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
		<th>paid / enrolled / capacity</th>
		<th>cost</th>
		<th>suggested: paid / enrolled / capacity</th>
		<th>revenue</th>
		<th>expenses</th>
		<th>profit</th>
		<th>taxes (enrolled)</th></tr>
	</thead><tbody>
<?php
		$totals = array(
			'revenue' => 0,
			'expenses' => 0,
			'suggested_paid' => 0,
			'suggested_enrolled' => 0,
			'suggested_capacity' => 0
		);
		foreach ($workshops_list as $wid => $wk) {
			echo "<tr><td>({$wk['id']}) <a href='admin_edit.php?wid={$wk['id']}'>{$wk['title']}</a> <small>({$wk['showstart']})</small><br><span class='text-secondary'>{$wk['place']}</span></td>
			<td>{$wk['paid']} / {$wk['enrolled']} / {$wk['capacity']}</td>
			<td>{$wk['cost']}</td>
			<td>".($wk['cost']*$wk['paid'])." / ".($wk['cost']*$wk['enrolled'])." / ".($wk['cost']*$wk['capacity'])."</td>
			<td>".Wbhkit\texty("revenue_{$wk['id']}", $wk['revenue'], 0)."</td>
			<td>".Wbhkit\texty("expenses_{$wk['id']}", $wk['expenses'], 0)."</td>
			<td>".($wk['revenue']-$wk['expenses'])."</td>
			<td>".number_format((($wk['cost']*$wk['enrolled']) / 3))."</td></tr>\n";
			
			$totals['revenue'] += $wk['revenue'];
			$totals['expenses'] += $wk['expenses'];
			$totals['suggested_paid'] += $wk['cost']*$wk['paid'];
			$totals['suggested_enrolled'] += $wk['cost']*$wk['enrolled'];
			$totals['suggested_capacity'] += $wk['cost']*$wk['capacity'];
		}
		echo "<tr><td>Totals:</td><td colspan=2>&nbsp;</td><td>{$totals['suggested_paid']} / {$totals['suggested_enrolled']} / {$totals['suggested_capacity']}</td><td>{$totals['revenue']}</td><td>{$totals['expenses']}</td><td>".($totals['revenue']-$totals['expenses'])."</td><td>".number_format(($totals['suggested_capacity'] / 3))."</td></tr>\n";
		echo "</tbody></table>".Wbhkit\submit('Also Update')."</form>\n";
		echo $weeknav; 
		
		
		echo "</div></div>\n";
		
?>