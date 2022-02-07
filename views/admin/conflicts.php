
<h1>Schedule Conflicts</h1>

<?php
// conflicts
echo "<h2>Teacher Conflicts</h2>\n";
if (count($teacher_conflicts) > 0 ) {
	
	foreach ($teacher_conflicts as $c) {
		echo "<ul>\n";
		echo "<li><a href='/admin-workshop/view/{$c[0]['id']}'>{$c[0]['title']}</a> ({$c[0]['rank']}) ({$c[0]['start']}-{$c[0]['end']})</li>\n";
		echo "<li><a href='/admin-workshop/view/{$c[1]['id']}'>{$c[1]['title']}</a> ({$c[1]['rank']}) ({$c[1]['start']}-{$c[1]['end']})</li>\n";
		echo "</ul>\n";
	}
	
} else {
	echo "<p>None!</p>\n";
}

echo "<h2>Registration Conflicts</h2>\n";
if (count($registration_conflicts) > 0 ) {
	
	//print_r($registration_conflicts);
	
	foreach ($registration_conflicts as $c) {
		
		echo "<ul><li><a href='/admin-users/view/{$c[0][0]['user_id']}'>".($c[0][0]['display_name'] ? $c[0][0]['display_name'] : $c[0][0]['email'])."</a>";
		echo "<ul><li><a href='/admin-workshop/view/{$c[0][0]['workshop_id']}'>{$c[0][0]['title']}</a> ({$c[0][0]['rank']}) (".\Wbhkit\friendly_when($c[0][0]['start']).'-'.\Wbhkit\friendly_when($c[0][0]['end']).")</li>\n";
		echo "<li><a href='/admin-workshop/view/{$c[1][0]['workshop_id']}'>{$c[1][0]['title']}</a> ({$c[1][0]['rank']}) (".\Wbhkit\friendly_when($c[1][0]['start']).'-'.\Wbhkit\friendly_when($c[1][0]['end']).")</li>\n";
		echo "</ul></li></ul>\n";
	}
	
} else {
	echo "<p>None!</p>\n";
}


?>




