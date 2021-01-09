<div class='row'><div class='col-md-10'><h2>Revenues</h2>
<form action='<?php echo $sc; ?>' method='get'>
<?php echo \Wbhkit\texty('searchstart', $searchstart, 'Search Start'); ?>
<?php echo \Wbhkit\texty('searchend', $searchend, 'Search End'); ?>
<?php echo \Wbhkit\submit('Update'); ?>
</form>

<?php
$weeknav = "<p><a href='admin_revenue.php?searchstart=$lastweekstart&searchend=$lastweekend'>last week</a> | <a href='admin_revenue.php'>this week</a> | <a href='admin_revenue.php?searchstart=$nextweekstart&searchend=$nextweekend'>next week</a></p>\n";
echo $weeknav;

if (count($workshops_list) == 0) {
	echo "<h2>No workshops offered in this time period!</h2>\n";
} else {
	

$table_open = "<table class='table table-striped my-3'>
	<thead><tr>
		<th>workshop</th>
		<th>paid / enrolled / capacity</th>
		<th>cost</th>
		<th>revenue<br>paid / enrolled / capacity</th>
		<th>teacher pay</th>
		<th>net (paid - teacher pay)</th>
		</tr>
	</thead><tbody>";


		// set totals to zero
		$empty_totals = array(
			'suggested_paid' => 0,
			'suggested_enrolled' => 0,
			'suggested_capacity' => 0,
			'teacher_pay' => 0
		);

		$totals = $teacher_totals = $empty_totals;
		$teacher_id = 0;
		$previous_wk = null;
		
		foreach ($workshops_list as $wid => $wk) {
			
			if ($wk['teacher_id'] != $teacher_id) { // new teacher
				if ($teacher_id != 0) {
					show_teacher_totals($previous_wk, $teacher_totals);
					echo "</tbody></table>\n";
				}
				//start new teacher revenue
				$teacher_id = $wk['teacher_id'];
				$teacher_totals = $empty_totals;
				
				echo "<h2 class='my-3'>{$wk['teacher_name']}</h2>\n";
				echo $table_open;
				$teacher_id = $wk['teacher_id'];
			}
			
			$teacher_pay = \Workshops\get_teacher_pay($wk['id']);
			
			echo "<tr><td width='300'>({$wk['id']}-{$wk['teacher_id']}) <a href='admin_edit2.php?wid={$wk['id']}'>{$wk['title']}</a> <small>({$wk['showstart']})</small></td>
			<td>{$wk['paid']} / {$wk['enrolled']} / {$wk['capacity']}</td>
			<td>{$wk['cost']}</td>
			<td>".($wk['cost']*$wk['paid'])." / ".($wk['cost']*$wk['enrolled'])." / ".($wk['cost']*$wk['capacity'])."</td>
			<td>".number_format($teacher_pay)."</td>
			<td>".number_format($wk['cost']*$wk['paid'] - $teacher_pay)."</td>
			</tr>\n";
						
			$totals['suggested_paid'] += $wk['cost']*$wk['paid'];
			$totals['suggested_enrolled'] += $wk['cost']*$wk['enrolled'];
			$totals['suggested_capacity'] += $wk['cost']*$wk['capacity'];
			$totals['teacher_pay'] += $teacher_pay;
			
			$teacher_totals['suggested_paid'] += $wk['cost']*$wk['paid'];
			$teacher_totals['suggested_enrolled'] += $wk['cost']*$wk['enrolled'];
			$teacher_totals['suggested_capacity'] += $wk['cost']*$wk['capacity'];
			$teacher_totals['teacher_pay'] += $teacher_pay;

			$previous_wk = $wk;
			
		}
		show_teacher_totals($wk, $teacher_totals);
		
		echo "<tr><td colspan='6'></td></tr>\n";
		
		echo "<tr><td>Totals:</td>
		<td colspan=2>&nbsp;</td>
		<td>{$totals['suggested_paid']} / {$totals['suggested_enrolled']} / {$totals['suggested_capacity']}
		</td>
		<td>".number_format($totals['teacher_pay'])."</td>
		<td>".number_format($totals['suggested_paid'] - $totals['teacher_pay'])."</td>
		</tr></table>\n";

		echo $weeknav; 
		
		
		echo "</div></div>\n";
		
} // end of "if count of workshops is zero" if/then
		
function show_teacher_totals($wk, $teacher_totals) {
	// wrap up previous teacher revenue
	echo "<tr class=\"table-info\">
		<td>{$wk['teacher_name']} sub totals:</td>
	<td colspan=2>&nbsp;</td>
	<td>{$teacher_totals['suggested_paid']} / {$teacher_totals['suggested_enrolled']} / {$teacher_totals['suggested_capacity']}</td>
	<td>".number_format($teacher_totals['teacher_pay'])."</td>
	<td>".number_format($teacher_totals['suggested_paid'] - $teacher_totals['teacher_pay'])."</td>
	
	</tr>\n";	
}
		
?>