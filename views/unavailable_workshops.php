<?php

if (count($unavailable_workshops) > 0 || count($application_workshops) > 0) {

echo "<div class=\"row justify-content-center my-3\">\n";
echo "<div class=\"col-md-6 border border-info\">\n";

	if (count($unavailable_workshops) > 0) {

		echo "<h2>Classes Going Live Soon</h2>\n";
		echo "<p>Times shown in (".$u->fields['time_zone_friendly'].")</p>\n";

		$current_date = null;
		foreach ($unavailable_workshops as $wk) {
			
			$wk->fields['when_public'] = \Wbhkit\convert_tz($wk->fields['when_public'], $u->fields['time_zone']);

			// update date?
			$next_date = Wbhkit\friendly_date($wk->fields['when_public']).' '.Wbhkit\friendly_time($wk->fields['when_public']);
	
			if ($next_date != $current_date) {
		
				if ($current_date) {
					echo "</ul>\n";
				}
		
				echo "<h6>Going live: $next_date</h6>\n<ul>";
				$current_date = $next_date;
			}
			echo upcoming_class_item($wk);
			
		}
		echo "</ul>\n";
		

	}

	if (count($application_workshops) > 0) {

		echo "<h2>Classes Taking Requests</h2>\n";
		//echo "<h4>Enrollments Announced December 20</h4>\n";

		echo "<p class=\"font-weight-light\">(All times ".$u->fields['time_zone_friendly'].")</p>\n";

		$current_date = null;
		echo "<ul>\n";
		foreach ($application_workshops as $wk) {
			echo upcoming_class_item($wk);
		}	
		echo "</ul>\n";
	}
	echo "</div></div>";

}

function upcoming_class_item($wk) {
	$wkdate = date("l F j", strtotime($wk->fields['start_tz']));
	$start = Wbhkit\friendly_time($wk->fields['start_tz']);
	$end = Wbhkit\friendly_time($wk->fields['end_tz']);	
	return "<li class='mb-2'>$wkdate: <a href='/workshop/view/{$wk->fields['id']}'>{$wk->fields['title']}</a><br>
		<small>$start {$wk->fields['costdisplay']}, Instructor: <a href='/teachers/view/{$wk->fields['teacher_id']}'>{$wk->teacher['nice_name']}</a><br>
	{$wk->fields['time_summary']}<br></small></li>\n";	
	
}


