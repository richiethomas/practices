<div class='row'><div class='col-md-10'><h2>Revenues By Class</h2>
<form action='/admin-revbyclass/view/' method='get'>
<?php echo \Wbhkit\texty('searchstart', $searchstart, 'Search Start'); ?>
<?php echo \Wbhkit\texty('searchend', $searchend, 'Search End'); ?>
<?php echo \Wbhkit\radio('mode', array('0' => 'by teacher', '1' => 'by class'), $mode);  ?>
<?php echo \Wbhkit\submit('Update'); ?>
</form>

<?php

$nav = "<p><a href='/admin-revbyclass/view/?searchstart=$laststart&searchend=$lastend&mode=$mode'>last month</a> | <a href='/admin-revbyclass/view/&mode=$mode'>this month</a> | <a href='/admin-revbyclass/view/?searchstart=$nextstart&searchend=$nextend&mode=$mode'>next month</a></p>\n";
echo $nav;


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
			
			$teacher_name = get_teacher_name($wk);

			echo date('Y-m-d g:ia',strtotime($wk['start'])).', '.
				date('l',strtotime($wk['start'])).', '.
				date('g:ia',strtotime($wk['start'])).
				", {$wid}, {$wk['title']},  {$teacher_name}, {$wk['enrolled']}, {$wk['actual_revenue']}, {$wk['total_pay']}, {$wk['total_class_sessions']}, {$wk['total_show_sessions']}\n";
			$total_revenue += $wk['actual_revenue'];
			$total_pay += $wk['total_pay'];
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
		<th>teachers pay</th>
		<th>net (paid - teacher pay)</th>
		</tr>
	</thead><tbody>";


		// set totals to zero
		$empty_totals = array(
			'suggested_paid' => 0,
			'suggested_enrolled' => 0,
			'suggested_capacity' => 0,
			'total_pay' => 0
		);

		$totals = $teacher_totals = $empty_totals;
		$previous_teacher_key = null; 
		$previous_wk = null;
		
		foreach ($workshops_list as $wid => $wk) {
			
			$teacher_key = $wk['teacher_id'];
			if ($wk['co_teacher_id']) {
				$teacher_key .= "co".$wk['co_teacher_id'];
			}
			
			if (strcmp($teacher_key, $previous_teacher_key) !== 0) { // new teacher
				if ($previous_teacher_key) {
					show_teacher_totals($previous_wk, $teacher_totals);
					echo "</tbody></table>\n";
				}
				//start new teacher revenue
				$previous_teacher_key = $teacher_key;
				$teacher_totals = $empty_totals;
				$teacher_name = get_teacher_name($wk);
				
				echo "<h2 class='my-3'>{$teacher_name}</h2>\n";
				echo $table_open;
			}
						
			echo "<tr><td width='300'>({$wk['id']}) <a href='/admin-workshop/view/{$wk['id']}'>{$wk['title']}</a> <small>({$wk['showstart']})</small></td>
			<td>{$wk['paid']} / {$wk['enrolled']} / {$wk['capacity']}</td>
			<td>{$wk['cost']}</td>
			<td>{$wk['actual_revenue']}</td>
			<td>".number_format($wk['total_pay'])."</td>
			<td>".number_format($wk['actual_revenue'] - $wk['total_pay'])."</td>
			</tr>\n";
						
			$totals['suggested_paid'] += $wk['actual_revenue'];
			$totals['total_pay'] += $wk['total_pay'];
			
			$teacher_totals['suggested_paid'] += $wk['actual_revenue'];
			$teacher_totals['total_pay'] += $wk['total_pay'];

			$previous_wk = $wk;
			
		}
		show_teacher_totals($wk, $teacher_totals);
		
		echo "<tr><td colspan='6'></td></tr>\n";
		
		echo "<tr><td>Totals:</td>
		<td colspan=2>&nbsp;</td>
		<td>{$totals['suggested_paid']}</td>
		<td>".number_format($totals['total_pay'])."</td>
		<td>".number_format($totals['suggested_paid'] - $totals['total_pay'])."</td>
		</tr></table>\n";

		echo $nav; 
		
		
		echo "</div></div>\n";

	} // end of "mode" if then
		
} // end of "if count of workshops is zero" if/then
		
function show_teacher_totals($wk, $teacher_totals) {
	
	$teacher_name = get_teacher_name($wk);
	
	// wrap up previous teacher revenue
	echo "<tr class=\"table-info\">
		<td>{$teacher_name} sub totals:</td>
	<td colspan=2>&nbsp;</td>
	<td>{$teacher_totals['suggested_paid']}</td>
	<td>".number_format($teacher_totals['total_pay'])."</td>
	<td>".number_format($teacher_totals['suggested_paid'] - $teacher_totals['total_pay'])."</td>
	
	</tr>\n";	
}

function get_teacher_name($wk) {
	$teacher_name = $wk['teacher_info']['nice_name'];
	if ($wk['co_teacher_id']) {
		$teacher_name .= " and ".$wk['co_teacher_info']['nice_name'];
	}
	return $teacher_name;
}
		
?>