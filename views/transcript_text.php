<?php 

	
if ($admin) {
	
	echo "
	<div class='row'>
		<div class='col'>Class</div>
		<div class='col'>When</div>
		<div class='col'>Teacher</div>
		<div class='col'>Where</div>
		<div class='col'>Status</div>
	</div>\n";
	
} else {
	echo "
	<div class='row'>
		<div class='col'>Class</div>
		<div class='col'>When</div>
		<div class='col'>Teacher</div>
	</div>\n";
}
	
	
	foreach ($rows as $t) {
		$cl = '';
		
		if (!$admin && $t['status_id'] != ENROLLED) {
			continue; // only enrolleds for public transcripts
		}
		echo "<div class='row'>
			<div class='col'>{$t['title']}</div>
			<div class='col'>{$t['showstart']}</div>
			<div class='col'>{$t['teacher']['nice_name']}";
			
			if ($t['co_teacher_id']) {
				echo ", {$t['co_teacher']['nice_name']}";
			}
			
		echo "</div>"; // end teacher col
		if ($admin) { 
			echo "	<div class='col'>{$t['place']}</div>\n";  // where col
			echo "	<div class='col'>{$statuses[$t['status_id']]}</div>\n"; // status col
		}
		echo "</div>\n"; // end of row
	}
	
?>
