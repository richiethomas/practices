<h2>Revenues By Class</h2>
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
	
		$form = '';
		$html = '';

		$html .= "<table class='table table-sm'><thead><tr>
				<th>start</th>
				<th>title</th>
				<th>teacher(s)</th>
				<th>students</th>
				<th>revenue</th>
				<th>costs</th>
			</tr></thead>";
			
			
			
		$form .= "<div class='row'><div class='col-md-12'><form id='dummy'><textarea class='form-control' rows='100' cols='200'>";
		$form .= "start date, day, time, workshop id, title, teacher, students, revenue, total costs,classes,class shows, in person\n";
		$total_revenue = 0;
		$total_costs = 0;
		$total_students = 0;
		$total_classes = 0;
		$total_shows = 0;
		foreach ($workshops_list as $wid => $wk) {
			
			
			$wk->fields['in_person'] = strpos($wk->fields['tags'], 'inperson') === false ? 0 : 1;
			
			$wk->fields['title'] = preg_replace('/,/', ' - ', $wk->fields['title']);
			
			if ($wk->fields['in_person'] && !strpos(strtolower($wk->fields['title']), 'in person')) {
				$wk->fields['title'] .= ' (in person)';
			}
			
			

		$html .= "
					<tr>
						<td>".\Wbhkit\figure_year_minutes(strtotime($wk->fields['start']))."</td>
						<td>({$wk->fields['id']}) {$wk->fields['title']}</td>
						<td>{$wk->fields['teacher_name']}</td>
						<td>{$wk->fields['enrolled']} / {$wk->fields['capacity']}</td>
						<td>{$wk->fields['actual_revenue']}</td>
						<td>{$wk->fields['total_costs']}</td>
					</tr>";
			
			
			$form .= date('Y-m-d g:ia',strtotime($wk->fields['start'])).', '.
				date('l',strtotime($wk->fields['start'])).', '.
				date('g:ia',strtotime($wk->fields['start'])).
				", {$wid}, {$wk->fields['title']},  {$wk->fields['teacher_name']}, {$wk->fields['enrolled']}, {$wk->fields['actual_revenue']}, {$wk->fields['total_costs']}, {$wk->fields['total_class_sessions']}, {$wk->fields['total_show_sessions']}, {$wk->fields['in_person']}\n";
			$total_revenue += $wk->fields['actual_revenue'];
			$total_costs += $wk->fields['total_costs'];
			$total_classes += $wk->fields['total_class_sessions'];
			$total_shows += $wk->fields['total_show_sessions'];

		}
		
		$html .= "<tr>
			<td colspan='4'>&nbsp;</td>
			<td>$total_revenue</td>
			<td>$total_costs</td>
			</tr></table>";
			
		$form .= ",,,,,,,$total_revenue,$total_costs,$total_classes,$total_shows\n";
		$form .= "</textarea></form></div></div>\n";
	
		echo $html;
		echo $form;
	
	} else {
	
$table_open = "<table class='table table-striped my-3'>
	<thead><tr>
		<th>workshop</th>
		<th>enrolled</th>
		<th>revenue</th>
		<th>teachers pay</th>
		<th>other costs</th>
		<th>net (paid - costs)</th>
		</tr>
	</thead><tbody>";


		// set totals to zero
		$empty_totals = array(
			'revenue' => 0,
			'enrolled' => 0,
			'capacity' => 0,
			'total_pay' => 0,
			'other_costs' => 0
		);

		$totals = $teacher_totals = $empty_totals;
		$previous_teacher_key = null; 
		$previous_wk = null;
		
		foreach ($workshops_list as $wid => $wk) {
			
			$wk->fields['other_costs'] = $wk->fields['total_costs'] - $wk->fields['total_pay'];
			if ($wk->fields['other_costs'] < 0) { $wk->fields['other_costs'] = 0; }
			
			$teacher_key = $wk->fields['teacher_id'];
			if ($wk->fields['co_teacher_id']) {
				$teacher_key .= "co".$wk->fields['co_teacher_id'];
			}
			
			if (strcmp($teacher_key, $previous_teacher_key) !== 0) { // new teacher
				if ($previous_teacher_key) {
					show_teacher_totals($previous_wk, $teacher_totals);
					echo "</tbody></table>\n";
				}
				//start new teacher revenue
				$previous_teacher_key = $teacher_key;
				$teacher_totals = $empty_totals;
				
				echo "<h2 class='my-3'>{$wk->fields['teacher_name']}</h2>\n";
				echo $table_open;
			}


			echo "<tr><td width='300'>({$wk->fields['id']}) <a href='/admin-workshop/view/{$wk->fields['id']}'>{$wk->fields['title']}</a> <small>({$wk->fields['showstart']})</small></td>
			<td>{$wk->fields['enrolled']} / {$wk->fields['capacity']}</td>
			<td>{$wk->fields['actual_revenue']}</td>
			<td>".number_format($wk->fields['total_pay'])."</td>
			<td>".number_format($wk->fields['other_costs'])."</td>
			<td>".number_format($wk->fields['actual_revenue'] - $wk->fields['total_costs'])."</td>
			</tr>\n";
						
			$totals['revenue'] += $wk->fields['actual_revenue'];
			$totals['total_pay'] += $wk->fields['total_pay'];
			$totals['other_costs'] += $wk->fields['other_costs'];
			
			$teacher_totals['revenue'] += $wk->fields['actual_revenue'];
			$teacher_totals['total_pay'] += $wk->fields['total_pay'];
			$teacher_totals['other_costs'] += $wk->fields['other_costs'];

			$previous_wk = $wk;
			
		}
		show_teacher_totals($wk, $teacher_totals);
		
		echo "<tr><td colspan='6'></td></tr>\n";
		
		echo "<tr><td>Totals:</td>
		<td>&nbsp;</td>
		<td>{$totals['revenue']}</td>
		<td>".number_format($totals['total_pay'])."</td>
		<td>".number_format($totals['other_costs'])."</td>
		<td>".number_format($totals['revenue'] - ($totals['total_pay']+$totals['other_costs']))."</td>
		</tr></table>\n";

		echo $nav; 
		
		
		echo "</div></div>\n";

	} // end of "mode" if then
		
} // end of "if count of workshops is zero" if/then
		
function show_teacher_totals($wk, $teacher_totals) {
	
	// wrap up previous teacher revenue
	echo "<tr class=\"table-info\">
	<td>{$wk->fields['teacher_name']} sub totals:</td>
	<td>&nbsp;</td>
	<td>{$teacher_totals['revenue']}</td>
	<td>".number_format($teacher_totals['total_pay'])."</td>
	<td>".number_format($teacher_totals['other_costs'])."</td>
	<td>".number_format($teacher_totals['revenue'] - ($teacher_totals['total_pay']+$teacher_totals['other_costs']))."</td>
	
	</tr>\n";	
}


		
?>