<h1 class="page-title mb-5">Calendar</h1>
	<div class="row justify-content-center">
		<div class="col-md-10">
			
<?php		

if ($u->logged_in()) {
	echo "<div>Times shown in ({$u->fields['time_zone_friendly']})</div>";
}

$current_date = null;
foreach ($workshops as $wk) {

	// update date?
	$next_date = date("l F j, Y", strtotime($wk['start_tz']));
	
	if ($next_date != $current_date) {
				
		echo "<div class='row mt-3'><div class='col-md-12'><h4>$next_date</h4></div></div>";
		$current_date = $next_date;
	}
	
	$start = Wbhkit\friendly_time($wk['start_tz']);
	$end = Wbhkit\friendly_time($wk['end_tz']);
	
	echo "<div class='row mt-2'><div class='col-md-6'><a href='/workshop/view/{$wk['id']}'>{$wk['title']}</a> ({$wk['rank']}/{$wk['total_sessions']}) ".($wk['class_show'] ? ' - show' : '')."</div>
	<div class='col-md-2'>$start-$end</div>
	<div class='col-md-4'><a href='/teachers/view/{$wk['teacher_id']}'>{$wk['teacher_name']}</a>";
	
	if ($wk['co_teacher_id']) {
		echo ", <a href='/teachers/view/{$wk['co_teacher_id']}'>{$wk['co_teacher_name']}</a>";
	}
	
	echo "</div>
	</div>\n";
	
}	

echo "</ul>\n";
?>
</div></div>