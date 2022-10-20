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

$c_totals = $up_totals = 
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

	foreach ($classes as $key => $c) {
		if (!isset($c['dated_revenue'])) { $c['dated_revenue'] = 0; }
		if (!isset($c['undated_revenue'])) { $c['undated_revenue'] = 0; }
		if (!isset($c['cost'])) { $c['cost'] = 0; }
		
		$workshop_revenue = $c['dated_revenue'] + $c['undated_revenue'];
		$formatted_start = \Wbhkit\figure_year_minutes(strtotime($c['start']));
		echo "
			<div class='row'><br>\n".
			"<div class='col-md-6'>({$c['id']}) <a href='/admin-workshop/view/{$c['id']}'>{$c['title']}</a> ($formatted_start)</div>\n".
			"<div class='col-md-1'>{$c['cost']}</div>\n".
			"<div class='col-md-1'>$workshop_revenue</div>\n".
			"<div class='col-md-1'>{$c['undated_revenue']}</div>\n".
			"</div>\n";

		$c_totals['cost'] += $c['cost'];
		$c_totals['revenue'] += $workshop_revenue;
		$c_totals['dated'] += $c['dated_revenue'];
		$c_totals['undated'] += $c['undated_revenue'];
		
		$csv .= "{$c['id']}, 'class', ".date('M j Y g:ia', strtotime($c['start'])).", {$c['title']}, {$c['cost']}, $workshop_revenue\n";

	}
	echo totals_row($c_totals, 'class subtotals');
	$csv .= "\n"; // skip a row in downloadable data
}	

echo "<h3 class='mt-2'>Other Payments</h3>\n";
if (count($upayments) == 0) {
	echo "<p>No other payments in this time period!</p>\n";
} else {

	echo $headings;

	foreach ($upayments as $pid => $up) {
		if (!isset($up['cost'])) { $up['cost'] = 0; }
		
		$formatted_when = date('M j Y', strtotime($up['when_paid']));
		
		echo "
			<div class='row'>\n".
			"<div class='col-md-6'>{$up['title']}</a> ($formatted_when)</div>\n".
			"<div class='col-md-1'>{$up['cost']}</div>\n".
			"<div class='col-md-1'>&nbsp;</div>\n".
			"<div class='col-md-1'>&nbsp;</div>\n".
			"</div>\n";

		$up_totals['cost'] += $up['cost'];
		
		$csv .= "{$up['id']}, 'payment', {$up['title']}, ".date('M j Y', strtotime($up['when_paid'])).", {$up['cost']}, 0\n";

	}
	echo totals_row($up_totals, 'Payments subtotals');
}	

$g_totals = array(
	'cost' => $c_totals['cost']+$up_totals['cost'],
	'revenue' => $c_totals['revenue']+$up_totals['revenue'],
	'dated' => $c_totals['dated'],
	'undated' => $c_totals['undated']
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
