<?php 
echo $links; 


if ($admin) {
	echo "<form action='$sc' method='post'>\n";
	echo Wbhkit\hidden('ac', 'at'); 	
	echo Wbhkit\hidden('guest_id', "$guest_id"); 	
}

?>

<?php
		
	foreach ($rows as $t) {
		$cl = '';
		if ($t['upcoming'] == 0) {
			$cl .= 'light';
		} elseif ($t['status_id'] == ENROLLED) {
			$cl .= 'success';
		} else {
			$cl .= 'warning';
		}
		
		
		$t['when'] = \XtraSessions\add_sessions_to_when($t['when'], $t['sessions']);
				
		echo "<div class='row workshop-row workshop-$cl my-3 py-3 border-top'>\n"; // workshop row start
				
			echo "	<div class='col-sm'>";
			if ($admin) {
				echo Wbhkit\checkbox('paids', $t['enrollment_id'], "<a href=\"admin_edit.php?wid={$t['workshop_id']}\">{$t['title']}</a>", $t['paid'], true);
			} else {
				echo "<a href=\"workshop.php?wid={$t['workshop_id']}\">{$t['title']}</a>";
			}
			echo "</div>\n";  // title cell
			
		
			echo "	<div class='col-sm'>{$t['when']} (".TIMEZONE.")<br>
				Instructor: <a href=\"teachers.php?tid={$t['teacher_id']}\">{$t['teacher_name']}</a></div>\n"; // when col	
			if ($admin) { echo "<div class='col-sm my-2'>{$t['place']}</div>\n"; } // where col
			echo "	<div class='col-sm'>{$statuses[$t['status_id']]}";
			if ($t['status_id'] == WAITING) {
				echo " (spot {$t['rank']})";
			}
			echo "</div>\n"; // status and rank col
			echo "	<div class='col-sm'><a href='workshop.php?wid={$t['workshop_id']}'><span class=\"oi oi-info\" title=\"info\" aria-hidden=\"true\"></span> info</a></div>\n";			
			echo "</div>\n\n"; // end of row
		
	}
	
?>
<?php 
if ($admin) {
	echo Wbhkit\submit("update paid");
	echo "</form>\n";
}
echo $links; 
?>
