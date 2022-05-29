<div class='row'><div class='col-md-12'><h2>Revenues By Date</h2>
<form action='/admin-revbydate/view/' method='get'>
<?php echo \Wbhkit\texty('searchstart', $searchstart, 'Search Start'); ?>
<?php echo \Wbhkit\texty('searchend', $searchend, 'Search End'); ?>
<?php echo \Wbhkit\submit('Update'); ?>
</form>

<?php
$nav = "<p><a href='/admin-revbydate/view/?searchstart=$laststart&searchend=$lastend'>last month</a> | <a href='/admin-revbydate/view/'>this month</a> | <a href='/admin-revbydate/view/?searchstart=$nextstart&searchend=$nextend'>next month</a></p>\n";
echo $nav;



$csv = "id, type, when, title, cost, revenue\n";

$c_totals = $t_totals = 
	array('cost' => 0, 'revenue' => 0, 'dated' => 0, 'undated' => 0);

$headings = "
		<div class='row fw-bold'>
		<div class='col-md-6'>Title</div>
		<div class='col-md-1'>Cost</div>
		<div class='col-md-1'>Revenue</div>
		<div class='col-md-1'>Undated</div>
		</div>\n";	

echo "<h3 class='mt-2'>Classes</h3>\n";
if (count($classes) == 0) {
	echo "<p>No classes data in this time period!</p>\n";
} else {
	

	echo $headings;

	foreach ($classes as $wid => $c) {
		if (!isset($c['dated'])) { $c['dated'] = 0; }
		if (!isset($c['undated'])) { $c['undated'] = 0; }
		if (!isset($c['total_pay'])) { $c['total_pay'] = 0; }
		
		$workshop_revenue = $c['dated'] + $c['undated'];
		$formatted_start = \Wbhkit\figure_year_minutes(strtotime($c['start']));
		echo "
			<div class='row'><br>\n".
			"<div class='col-md-6'>({$c['workshop_id']}) <a href='/admin-workshop/view/{$c['workshop_id']}'>{$c['title']}</a> ($formatted_start)</div>\n".
			"<div class='col-md-1'>{$c['total_pay']}</div>\n".
			"<div class='col-md-1'>$workshop_revenue</div>\n".
			"<div class='col-md-1'>{$c['undated']}</div>\n".
			"</div>\n";

		$c_totals['cost'] += $c['total_pay'];
		$c_totals['revenue'] += $workshop_revenue;
		$c_totals['dated'] += $c['dated'];
		$c_totals['undated'] += $c['undated'];
		
		$csv .= "{$c['workshop_id']}, 'class', ".date('M j Y g:ia', strtotime($c['start'])).", {$c['title']}, {$c['total_pay']}, $workshop_revenue\n";

	}
	echo totals_row($c_totals, 'class subtotals');
	$csv .= "\n"; // skip a row in downloadable data
}	

echo "<h3 class='mt-2'>Tasks</h3>\n";
if (count($tasks) == 0) {
	echo "<p>No task data in this time period!</p>\n";
} else {

	echo $headings;

	foreach ($tasks as $tid => $t) {
		if (!isset($t['cost'])) { $t['cost'] = 0; }
		
		$formatted_when = date('M j Y', strtotime($t['when_paid']));
		
		echo "
			<div class='row'>\n".
			"<div class='col-md-6'><a href='/admin-tasks/edit/{$t['task_id']}'>{$t['title']}</a> ($formatted_when)</div>\n".
			"<div class='col-md-1'>{$t['cost']}</div>\n".
			"<div class='col-md-1'>&nbsp;</div>\n".
			"<div class='col-md-1'>&nbsp;</div>\n".
			"</div>\n";

		$t_totals['cost'] += $t['cost'];
		
		$csv .= "{$t['task_id']}, 'task', {$t['title']}, ".date('M j Y', strtotime($t['when_paid'])).", {$t['cost']}, 0\n";

	}
	echo totals_row($t_totals, 'Task subtotals');
}	

$g_totals = array(
	'cost' => $c_totals['cost']+$t_totals['cost'],
	'revenue' => $c_totals['revenue']+$t_totals['revenue'],
	'dated' => $c_totals['dated']+$t_totals['dated'],
	'undated' => $c_totals['undated']+$t_totals['undated']
);
echo totals_row($g_totals, 'Grand Total');
$csv .= ",,,,{$g_totals['cost']}, {$g_totals['revenue']}\n";


function totals_row(array $totals, ?string $label = 'totals') {
	return "
		<div class='row fw-bold'>
		<div class='col-md-6'>{$label}</div>
		<div class='col-md-1'>{$totals['cost']}</div>
		<div class='col-md-1'>{$totals['revenue']}</div>
		<div class='col-md-1'>{$totals['undated']}</div>
		</div>
";	
}
		
?>
</div></div>

<div class='row'><div class='col-md-6'>
<h3 class='mt-2'>CSV Version</h3>
<form id='dummy'><textarea style='box-sizing: border-box; width: 700px' rows='15' cols='300'>
<?php echo $csv; ?>
</textarea></form>
</div></div>
