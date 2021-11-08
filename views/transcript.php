<?php 
echo $links; 

if ($admin) {
	echo "<form action='/admin-users/at/$guest_id' method='post'>\n";
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
		
		echo "<div class='row workshop-row workshop-$cl my-3 py-3 border-top'>\n"; // workshop row start
				
			echo "	<div class='col-sm'>";
			if ($admin) {
				echo Wbhkit\checkbox('paids', $t['enrollment_id'], "<a href=\"/admin-workshop/view/{$t['workshop_id']}\">{$t['title']}</a>", $t['paid'], true);
				echo \Wbhkit\texty("payoverride_{$t['enrollment_id']}", $t['pay_override'], 0);
				
			} else {
				echo "<a href=\"/workshop/view/{$t['workshop_id']}\">{$t['title']}</a>";
			}
			echo "</div>\n";  // title cell
			
		
			echo "	<div class='col-sm'>{$t['full_when']} (".TIMEZONE.")<br>
				<small>Instructor: <a href=\"/teachers/view/{$t['teacher_id']}\">{$t['teacher_info']['nice_name']}</a>";
			
			if ($t['co_teacher_id']) {
				echo ", <a href=\"/teachers/view/{$t['co_teacher_id']}\">{$t['co_teacher_info']['nice_name']}</a>";
			}
			
			echo "</small></div>\n"; // when col	
			if ($admin) { echo "<div class='col-sm my-2'>{$t['place']}</div>\n"; } // where col
			echo "	<div class='col-sm'>{$statuses[$t['status_id']]}";
			echo "</div>\n"; // status col
			echo "	<div class='col-sm'><a href='/workshop/view/{$t['workshop_id']}'><span class=\"oi oi-info\" title=\"info\" aria-hidden=\"true\"></span> info</a></div>\n";			
			echo "</div>\n\n"; // end of row
		
	}
	
?>
<?php 
if ($admin) {
	
	echo Wbhkit\checkbox('hideconpay', 1, $label = 'no confirm payment', $hideconpay == 1);
	echo Wbhkit\submit("update paid");
	echo "</form>\n";
}
echo $links; 
?>
