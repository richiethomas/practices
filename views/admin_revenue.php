<div class='row'><div class='col-md-10'><h2>Revenues</h2>
<form action='<?php echo $sc; ?>' method='post'>
<?php echo \Wbhkit\texty('searchstart', $searchstart, 'Search Start'); ?>
<?php echo \Wbhkit\texty('searchend', $searchend, 'Search End'); ?>
<?php echo \Wbhkit\submit('Update'); ?>
<?php echo \Wbhkit\hidden('ac', 'rev'); ?>

<table class='table table-striped'>
	<thead><tr>
		<th>workshop</th>
		<th>enrolled / capacity</th>
		<th>cost</th>
		<th>suggested</th>
		<th>revenue</th>
		<th>expenses</th>
		<th>profit</th></tr>
	</thead><tbody>
<?php
		$totals = array(
			'revenue' => 0,
			'expenses' => 0
		);
		foreach ($workshops_list as $wid => $wk) {
			echo "<tr><td>({$wk['id']}) {$wk['showtitle']}<br><span class='text-secondary'>{$wk['place']}</span></td>
			<td>{$wk['enrolled']} / {$wk['capacity']}</td>
			<td>{$wk['cost']}</td>
			<td>".($wk['cost']*$wk['enrolled'])."</td>
			<td>".Wbhkit\texty("revenue_{$wk['id']}", $wk['revenue'], 0)."</td>
			<td>".Wbhkit\texty("expenses_{$wk['id']}", $wk['expenses'], 0)."</td>
			<td>".($wk['revenue']-$wk['expenses'])."</td></tr>\n";
			$totals['revenue'] += $wk['revenue'];
			$totals['expenses'] += $wk['expenses'];
		}
		echo "<tr><td>Totals:</td><td colspan=3>&nbsp;</td><td>{$totals['revenue']}</td><td>{$totals['expenses']}</td><td>".($totals['revenue']-$totals['expenses'])."</td></tr>\n";
		echo "</tbody></table>".Wbhkit\submit('Also Update')."</form>\n";
		echo "</div></div>\n";
		
?>