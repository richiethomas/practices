<?php
	
	
$current_date = null;
foreach ($workshops as $wk) {

	// update date?
	$next_date = date("l F j, Y", strtotime($wk['start']));
	
	if ($next_date != $current_date) {
		
		if ($current_date) {
			echo "</ul>\n";
		}
		
		echo "<h3>$next_date</h3>\n<ul>";
		$current_date = $next_date;
	}
	
	$start = Workshops\friendly_time($wk['start']);
	$end = Workshops\friendly_time($wk['end']);
	
	echo "<li><a href='admin.php?wid={$wk['id']}&ac=ed'>{$wk['title']}</a>, $start-$end (".number_format($wk['enrolled'], 0)." /  ".number_format($wk['capacity'], 0)." / ".number_format($wk['waiting']+$wk['invited']).")";
	if ($wk['online_url']) {
		echo " <a href='{$wk['online_url']}'>online link</a>";
	}
	echo "</li>\n";
	
}	
echo "</ul>\n";
?>