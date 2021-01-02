<script
  src="https://code.jquery.com/jquery-3.5.1.min.js"
  integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
  crossorigin="anonymous"></script>
  
<script>
window.onload = function() {

	$( "#todays_date" ).click(function(e) {
		var d = new Date();
		var td = (d.getMonth()+1)+'/'+d.getDate()+'/'+d.getFullYear();
		$("input[name*='whenpaid_']").val(td);
		e.preventDefault();
	});
	
	
	$( "#clear_dates" ).click(function(e) {
		$("input[name*='whenpaid_']").val('');
		e.preventDefault();
	});
	
	
	$( "#copy_default" ).click(function(e) {
		e.preventDefault();
		$("input[name*='actual_']").each(function() {
			var dr = $(this).parents('td').prev('td').find('input').val();
			$(this).val(dr);
		});

	});
};	
	
</script>

<div class='row'><div class='col-md-10'><h2>Payroll</h2>
<form action='admin_payroll.php' method='post'>
<?php echo \Wbhkit\texty('searchstart', $searchstart, 'Search Start'); ?>
<?php echo \Wbhkit\texty('searchend', $searchend, 'Search End'); ?>
<?php echo \Wbhkit\submit('Search'); ?>
</form>

<?php

// update data in database


$weeknav = "<p><a href='admin_payroll.php?searchstart=$lastweekstart&searchend=$lastweekend'>last week</a> | <a href='admin_payroll.php'>this week</a> | <a href='admin_payroll.php?searchstart=$nextweekstart&searchend=$nextweekend'>next week</a></p>\n";
echo $weeknav;

echo "<form action='admin_payroll.php' method='post'>\n";
echo \Wbhkit\hidden('ac', 'up');
echo \Wbhkit\hidden('searchstart', $searchstart);
echo \Wbhkit\hidden('searchend', $searchend);


$table_open = "<table class='table table-striped my-3'>
	<thead><tr>
		<th>workshop</th>
		<th>session #</th>
		<th>default pay</th>
		<th>actual pay</th>
		<th>when paid</th>
	</thead><tbody>";

		$teacher_id = 0;
		$previous_wk = array();
		$total_pay = $teacher_pay = 0;
		
		foreach ($workshops_list as $wk) {
			
			if ($wk['teacher_id'] != $teacher_id) { // new teacher
				if ($teacher_id != 0) {
					show_teacher_totals($previous_wk, $teacher_pay);
					echo "</tbody></table>\n";
								}
				//start new teacher section
				$total_pay += $teacher_pay;
				$teacher_pay = 0;
				$teacher_id = $wk['teacher_id'];
				
				echo "<h2 class='my-3'>{$wk['teacher_name']}</h2>\n";
				echo $table_open;
				$teacher_id = $wk['teacher_id'];
			}
			
			// class shows show up in every associated teacher's feed
			// but only the show teacher gets paid for it
			if ($wk['show_teacher_id'] == 0 || $teacher_id == $wk['show_teacher_id']) {
				echo "<tr>
				<td width='300'><a href='admin_edit.php?wid={$wk['id']}'>{$wk['title']}</a> <small>({$wk['start']})</small></td>
				<td>".($wk['class_show'] ? "<b>show</b>" : "{$wk['rank']}")."</td>
				<td class='tdr'>".\Wbhkit\texty(
						"tdr_{$wk['id']}_{$wk['xtra_id']}_{$wk['show_id']}", 
						$wk['teacher_default_rate'],
						0)."</td>
				<td class='or'>".\Wbhkit\texty(
						"actual_{$wk['id']}_{$wk['xtra_id']}_{$wk['show_id']}", 
						$wk['actual_pay'],
						0)."</td>
				<td>".\Wbhkit\texty(
						"whenpaid_{$wk['id']}_{$wk['xtra_id']}_{$wk['show_id']}", 					set_when_paid_date($wk['when_teacher_paid']),
						0)."</td>
				</tr>\n";
			
				$teacher_pay += $wk['actual_pay'];
			}
						

			$previous_wk = $wk; // remember this workshop during next loop
		}
		
		show_teacher_totals($wk, $teacher_pay);
		$total_pay += $teacher_pay;
		
		echo "<tr><td colspan='6'></td></tr>\n";
		
		echo "<tr><td>Total Pay:</td>
		<td colspan=2>&nbsp;</td>
		<td>{$total_pay}</td>
		<td>&nbsp;</td>		
		</tr>\n";
		
		echo "</tbody></table>\n";
		
		echo "<button id=\"todays_date\" class=\"btn btn-success m-1\"  role=\"button\">Make Paid Dates Today</a>\n";
		echo "<button id=\"clear_dates\" class=\"btn btn-success m-1\"  role=\"button\">Clear All Paid Dates</a>\n";
		echo "<button id=\"copy_default\" class=\"btn btn-success m-1\"  role=\"button\">Copy Default as Override</a>\n";
		echo \Wbhkit\submit('Update');

		
		echo "</form>\n";
		echo "</div></div>\n";


function show_teacher_totals($wk, $teacher_pay) {
	// wrap up previous teacher revenue
	echo "<tr class=\"table-info\">
		<td>{$wk['teacher_name']} pay:</td>
	<td colspan=2>&nbsp;</td>
	<td>{$teacher_pay}</td>
	<td>&nbsp;</td>
	</tr>\n";	
}
	

function set_when_paid_date($ts) {
	if ($ts === null || $ts == '') {
		return null;
	}
	return date('M d Y', strtotime($ts));
}
	
?>