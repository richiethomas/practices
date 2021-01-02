<?php
	
echo "<div class=\"row justify-content-center my-3\">\n";
echo "<div class=\"col-md-6 border border-info\">\n";
echo "<h2>Classes Going Live Soon</h2>\n";

$current_date = null;
foreach ($unavailable_workshops as $wk) {

	// update date?
	$next_date = Wbhkit\friendly_date($wk['when_public']).' '.Wbhkit\friendly_time($wk['when_public']);
	
	if ($next_date != $current_date) {
		
		if ($current_date) {
			echo "</ul>\n";
		}
		
		echo "<h6>Going live: $next_date</h6>\n<ul>";
		$current_date = $next_date;
	}
	
	$wkdate = date("l F j", strtotime($wk['start']));
	$start = Wbhkit\friendly_time($wk['start']);
	$end = Wbhkit\friendly_time($wk['end']);	
	echo "<li class='mb-2'>$wkdate: <a href='workshop.php?wid={$wk['id']}'>{$wk['title']}</a><br><small>$start \${$wk['cost']} (USD), Instructor: {$wk['teacher_name']}</small></li>\n";	
}	

echo "</ul>\n";
echo "<p class=\"font-weight-light\">(All times ".TIMEZONE." - California time)</p></div></div>\n";

