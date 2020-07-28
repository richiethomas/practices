<div class='row'><div class='col-md-10'><h2>Revenues</h2>
<form action='<?php echo $sc; ?>' method='post'>
<?php echo \Wbhkit\texty('searchstart', $searchstart, 'Search Start'); ?>
<?php echo \Wbhkit\texty('searchend', $searchend, 'Search End'); ?>
<?php echo \Wbhkit\submit('Update'); ?>
</form>

<?php
$weeknav = "<p><a href='admin_revenue.php?searchstart=$lastweekstart&searchend=$lastweekend'>last week</a> | <a href='admin_revenue.php'>this week</a> | <a href='admin_revenue.php?searchstart=$nextweekstart&searchend=$nextweekend'>next week</a></p>\n";
echo $weeknav;



$table_open = "<table class='table table-striped my-3'>
	<thead><tr>
		<th>workshop</th>
		<th>paid / enrolled / capacity</th>
		<th>cost</th>
		<th>house fee</th>
		<th>est totals:<br>paid / enrolled / capacity</th>
		<th>suggested taxes</th></tr>
	</thead><tbody>";


		// set totals to zero
		$empty_totals = array(
			'suggested_paid' => 0,
			'suggested_enrolled' => 0,
			'suggested_capacity' => 0,
			'school_fee' => 0
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
			
			echo "<tr><td>({$wk['id']}-{$wk['teacher_id']}) <a href='admin_edit.php?wid={$wk['id']}'>{$wk['title']}</a> <small>({$wk['showstart']})</small><br><span class='text-secondary'>{$wk['place']}</span></td>
			<td>{$wk['paid']} / {$wk['enrolled']} / {$wk['capacity']}</td>
			<td>{$wk['cost']}</td>
			<td>{$wk['school_fee']}</td>
			<td>".($wk['cost']*$wk['paid'] - $wk['school_fee'])." / ".($wk['cost']*$wk['enrolled'] - $wk['school_fee'])." / ".($wk['cost']*$wk['capacity'] - $wk['school_fee'])."</td>
			<td>".number_format((($wk['cost']*$wk['enrolled'] - $wk['school_fee']) / 3))."</td></tr>\n";
						
			$totals['suggested_paid'] += $wk['cost']*$wk['paid'] - $wk['school_fee'];
			$totals['suggested_enrolled'] += $wk['cost']*$wk['enrolled'] - $wk['school_fee'];
			$totals['suggested_capacity'] += $wk['cost']*$wk['capacity'] - $wk['school_fee'];
			$totals['school_fee'] += $wk['school_fee'];
			
			$teacher_totals['suggested_paid'] += $wk['cost']*$wk['paid'] - $wk['school_fee'];
			$teacher_totals['suggested_enrolled'] += $wk['cost']*$wk['enrolled'] - $wk['school_fee'];
			$teacher_totals['suggested_capacity'] += $wk['cost']*$wk['capacity'] - $wk['school_fee'];
			$teacher_totals['school_fee'] += $wk['school_fee'];

			$previous_wk = $wk;
			
		}
		show_teacher_totals($wk, $teacher_totals);
		
		echo "<tr><td colspan='6'></td></tr>\n";
		
		echo "<tr><td>Totals:</td>
		<td colspan=2>&nbsp;</td>
		<td>{$totals['school_fee']}</td>
		<td>{$totals['suggested_paid']} / {$totals['suggested_enrolled']} / {$totals['suggested_capacity']}
		</td>
		<td>".number_format(($totals['suggested_paid'] / 3))."</td>
		</tr></table>\n";

		echo $weeknav; 
		
		
		echo "</div></div>\n";
		
function show_teacher_totals($wk, $teacher_totals) {
	// wrap up previous teacher revenue
	echo "<tr class=\"table-info\">
		<td>{$wk['teacher_name']} sub totals:</td>
	<td colspan=2>&nbsp;</td>
	<td>{$teacher_totals['school_fee']}
	<td>{$teacher_totals['suggested_paid']} / {$teacher_totals['suggested_enrolled']} / {$teacher_totals['suggested_capacity']}</td>
	<td>".number_format(($teacher_totals['suggested_paid'] / 3))."</td>
	</tr>\n";	
}
		
?>