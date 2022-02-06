<div class='row'><div class='col-md-10'><h2>Revenues</h2>
<form action='/admin-revenue/view/' method='get'>
<?php echo \Wbhkit\texty('searchstart', $searchstart, 'Search Start'); ?>
<?php echo \Wbhkit\texty('searchend', $searchend, 'Search End'); ?>
<?php echo \Wbhkit\radio('mode', array('0' => 'by teacher', '1' => 'by class'), $mode);  ?>
<?php echo \Wbhkit\submit('Update'); ?>
</form>

<?php
$weeknav = "<p><a href='/admin-revenue/view/?searchstart=$lastweekstart&searchend=$lastweekend&mode=$mode'>last week</a> | <a href='/admin-revenue/view/'>this week</a> | <a href='/admin-revenue/view/?searchstart=$nextweekstart&searchend=$nextweekend&mode=$mode'>next week</a></p>\n";
echo $weeknav;

if (count($workshops_list) == 0) {
	echo "<h2>No workshops offered in this time period!</h2>\n";
} else {
	
	
	if ($mode) {
	
		echo "<form id='dummy'><textarea rows='100' cols='300'>";
		echo "start date, day, time, workshop id, title, teacher, students, revenue, teacher pay,classes,class shows\n";
		$total_revenue = 0;
		$total_pay = 0;
		$total_students = 0;
		$total_classes = 0;
		$total_shows = 0;
		foreach ($workshops_list as $wid => $wk) {
			
			$wk['title'] = preg_replace('/,/', ' - ', $wk['title']);
						
			echo date('Y-m-d g:ia',strtotime($wk['start'])).', '.
				date('l',strtotime($wk['start'])).', '.
				date('g:ia',strtotime($wk['start'])).
				", {$wid}, {$wk['title']},  {$wk['teacher_info']['nice_name']}, {$wk['enrolled']}, {$wk['actual_revenue']}, {$wk['teacher_pay']}, {$wk['total_class_sessions']}, {$wk['total_show_sessions']}\n";
			$total_revenue += $wk['actual_revenue'];
			$total_pay += $wk['teacher_pay'];
			$total_classes += $wk['total_class_sessions'];
			$total_shows += $wk['total_show_sessions'];
		}
		echo ",,,,,,,$total_revenue,$total_pay,$total_classes,$total_shows\n";
		echo "</textarea></form>\n";
	
	} else {
	
$table_open = "<table class='table table-striped my-3'>
	<thead><tr>
		<th>workshop</th>
		<th>paid / enrolled / capacity</th>
		<th>cost</th>
		<th>revenue</th>
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
				
				echo "<h2 class='my-3'>{$wk['teacher_info']['nice_name']}</h2>\n";
				echo $table_open;
				$teacher_id = $wk['teacher_id'];
			}
						
			echo "<tr><td width='300'>({$wk['id']}) <a href='/admin-workshop/view/{$wk['id']}'>{$wk['title']}</a> <small>({$wk['showstart']})</small></td>
			<td>{$wk['paid']} / {$wk['enrolled']} / {$wk['capacity']}</td>
			<td>{$wk['cost']}</td>
			<td>{$wk['actual_revenue']}</td>
			<td>".number_format($wk['teacher_pay'])."</td>
			<td>".number_format($wk['actual_revenue'] - $wk['teacher_pay'])."</td>
			</tr>\n";
						
			$totals['suggested_paid'] += $wk['actual_revenue'];
			$totals['teacher_pay'] += $wk['teacher_pay'];
			
			$teacher_totals['suggested_paid'] += $wk['actual_revenue'];
			$teacher_totals['teacher_pay'] += $wk['teacher_pay'];

			$previous_wk = $wk;
			
		}
		show_teacher_totals($wk, $teacher_totals);
		
		echo "<tr><td colspan='6'></td></tr>\n";
		
		echo "<tr><td>Totals:</td>
		<td colspan=2>&nbsp;</td>
		<td>{$totals['suggested_paid']}</td>
		<td>".number_format($totals['teacher_pay'])."</td>
		<td>".number_format($totals['suggested_paid'] - $totals['teacher_pay'])."</td>
		</tr></table>\n";

		echo $weeknav; 
		
		
		echo "</div></div>\n";

	} // end of "mode" if then
		
} // end of "if count of workshops is zero" if/then
		
function show_teacher_totals($wk, $teacher_totals) {
	// wrap up previous teacher revenue
	echo "<tr class=\"table-info\">
		<td>{$wk['teacher_info']['nice_name']} sub totals:</td>
	<td colspan=2>&nbsp;</td>
	<td>{$teacher_totals['suggested_paid']}</td>
	<td>".number_format($teacher_totals['teacher_pay'])."</td>
	<td>".number_format($teacher_totals['suggested_paid'] - $teacher_totals['teacher_pay'])."</td>
	
	</tr>\n";	
}
		
?>