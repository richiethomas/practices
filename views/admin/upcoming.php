<?php
	

echo "<h2>Upcoming Workshops</h2>\n";
echo "<p>Numbers in parenthesis are: <i>(paid / enrolled / capacity / waiting)</i></p>\n";

echo "<p class='alert alert-primary'>Hi! You are logged in as <b>{$u['nice_name']}</b>.</p>\n";

if ($your_teacher_id > 0) {
	echo "<p>
		See: 
	<a class=\"".($filter_by == $your_teacher_id ? 'text-muted' : 'font-weight-bold')."\" href='$sc?filter_by=$your_teacher_id'>just your classes</a> | 
	<a class=\"".($filter_by == 'all' ? 'text-muted' : 'font-weight-bold')."\" href='$sc?filter_by=all'>all of them</a>
	</p>";
}

$current_date = null;
foreach ($workshops as $wk) {

	if ($filter_by != 'all' && $filter_by > 0) {
		if ($wk['teacher_id'] != $filter_by) {
			continue; // skip this loop
		}
	}

	// update date?
	$next_date = date("l F j, Y", strtotime($wk['start']));
	
	if ($next_date != $current_date) {
		
		if ($current_date) {
			echo "</ul>\n";
		}
		
		echo "<h3>$next_date</h3>\n<ul>";
		$current_date = $next_date;
	}
	
	$start = Wbhkit\friendly_time($wk['start']);
	$end = Wbhkit\friendly_time($wk['end']);
	
	echo "<li".($wk['class_show'] ? ' class="show"' : '')."><a href='admin_edit.php?wid={$wk['id']}'>{$wk['title']}</a> ({$wk['rank']}".($wk['class_show'] ? ' - show' : '')."), $start-$end (".number_format($wk['paid'], 0)." / ".number_format($wk['enrolled'], 0)." /  ".number_format($wk['capacity'], 0)." / ".number_format($wk['waiting']+$wk['invited']).")";
	echo " - {$wk['teacher_name']}";
	if ($wk['override_url']) {
		echo "<ul><li><a href='{$wk['override_url']}'>{$wk['override_url']}</a></li></ul>";
	} else {
		echo "<ul><li><a href='{$wk['online_url']}'>{$wk['online_url']}</a></li></ul>";
	}
	echo "</li>\n";
	
}	

echo "</ul>\n";

echo "<h2>Upcoming Classes</h2>\n";
echo "<h5>All times PDT - California time</h5>\n";
echo "<ul>\n";
foreach ($workshops as $wk) {
	if ($wk['xtra']) { continue; } // first sessoins only
	$wkdate = date("l F j", strtotime($wk['start']));
	$start = Wbhkit\friendly_time($wk['start']);
	$end = Wbhkit\friendly_time($wk['end']);	
	echo "<li>$wkdate: {$wk['title']}, $start-$end \${$wk['cost']} (USD)</li>\n";
}	
echo "</ul>\n";
echo "<p>All times PDT - California time</p>\n";

echo "<h2>Descriptions</h2>\n";
echo "<ul>\n";
foreach ($workshops as $wk) {
	if ($wk['xtra']) { continue; } // first sessoins only
	echo "<li><b>{$wk['title']}</b> - {$wk['notes']}</li>\n";
}	
echo "</ul>\n";




?>