<?php 
echo $links; 


if ($admin) {
	echo "<form action='$sc' method='post'>\n";
	echo Wbhkit\hidden('ac', 'at'); 	
	echo Wbhkit\hidden('uid', "$uid"); 	
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
		
		
		$sessions = '';
		if (!empty($t['sessions'])) {
			$sessions ="<p>\n";
			$sessions .= "{$t['when']}";
			foreach ($t['sessions'] as $s) {
				$sessions .= "<br>\n{$s['friendly_when']}";
			}
			$sessions .= "</p>\n";
			$t['when'] = $sessions; // replace the when variable 
		}
		
		
		echo "<div class='row workshop-row workshop-$cl my-3 py-3 border-top'>\n"; // workshop row start
				
			echo "	<div class='col-sm'>";
			if ($admin) {
				echo Wbhkit\checkbox('paids', $t['enrollment_id'], "<a href=\"admin.php?wid={$t['workshop_id']}&ac=ed\">{$t['title']}</a>", $t['attended'], true);
			} else {
				echo "<a href=\"index.php?wid={$t['workshop_id']}\">{$t['title']}</a>";
			}
			echo "</div>\n";  // title cell
			
		
				echo "	<div class='col-sm'>{$t['when']} (".TIMEZONE.")</div>\n"; // when col	
				if ($admin) { echo "<div class='col-sm my-2'>{$t['place']}</div>\n"; } // where col
				echo "	<div class='col-sm'>{$statuses[$t['status_id']]}";
				if ($t['status_id'] == WAITING) {
					echo " (spot {$t['rank']})";
				}
				echo "</div>\n"; // status and rank col
				echo "	<div class='col-sm'><a href='index.php?wid={$t['workshop_id']}'><span class=\"oi oi-info\" title=\"info\" aria-hidden=\"true\"></span> info</a></div>\n";			
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
