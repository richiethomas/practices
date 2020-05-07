<div class='row'><div class='col-md-6'><h2>get emails</h2>
<div class='well'><form action ='<?php echo $sc; ?>' method='post'>
<?php echo	Wbhkit\hidden('ac', 'gemail') ?>
<?php echo 	Wbhkit\multi_drop('workshops', $all_workshops, $workshops, 'Workshops', 15); ?>
<?php echo  Wbhkit\submit('get emails'); ?>
</form></div>
<?php
	
		if ($results) {
			echo "<div id='emaillists'>\n";
			foreach ($results as $stid => $students) {
				$status_name = $statuses[$stid];
				$es = '';
				foreach ($students as $semail) {
					$es .= "{$semail}\n";
				}
				echo "<h3>{$status_name} (".count($students).")</h3>\n";
				echo Wbhkit\textarea($status_name, $es, 0);

			}
			
			echo "<h3>Enrolled and Unpaid</h3>\n";
			$unpaid_list = '';
			foreach ($unpaid as $unpaid_email) {
				$unpaid_list .= "{$unpaid_email}\n";
			}
			echo Wbhkit\textarea('unpaid', $unpaid_list, 0);
			
			
			echo "</div>\n";
		}
		
?>
</div></div>
		
		