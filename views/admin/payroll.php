<div class='row'><div class='col-md-10'><h2>Payroll</h2>
<form action='<?php echo $sc; ?>' method='post'>
<?php echo \Wbhkit\texty('searchstart', $searchstart, 'Search Start'); ?>
<?php echo \Wbhkit\texty('searchend', $searchend, 'Search End'); ?>
<?php echo \Wbhkit\submit('Update'); ?>
</form>

<?php
$weeknav = "<p><a href='admin_payroll.php?searchstart=$lastweekstart&searchend=$lastweekend'>last week</a> | <a href='admin_payroll.php'>this week</a> | <a href='admin_payroll.php?searchstart=$nextweekstart&searchend=$nextweekend'>next week</a></p>\n";
echo $weeknav;



$table_open = "<table class='table table-striped my-3'>
	<thead><tr>
		<th>workshop</th>
		<th>session #</th>
	</thead><tbody>";

		$teacher_id = 0;
		
		foreach ($workshops_list as $wk) {
			
			if ($wk['teacher_id'] != $teacher_id) { // new teacher
				if ($teacher_id != 0) {
					echo "</tbody></table>\n";
				}
				//start new teacher revenue
				$teacher_id = $wk['teacher_id'];
				
				echo "<h2 class='my-3'>{$wk['teacher_name']}</h2>\n";
				echo $table_open;
				$teacher_id = $wk['teacher_id'];
			}
			
			echo "<tr><td>({$wk['id']}-{$wk['teacher_id']}) <a href='admin_edit.php?wid={$wk['id']}'>{$wk['title']}</a> <small>({$wk['start']})</small></td>
			<td>{$wk['rank']} </td></tr>\n";
			
		}
		
		echo "</tbody></table>\n";

		echo $weeknav; 
		
		
		echo "</div></div>\n";
		
		
?>