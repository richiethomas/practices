<?php

if (count($unavailable_workshops) > 0 || count($application_workshops) > 0) {

echo "<div class=\"row justify-content-center my-3\">\n";
echo "<div class=\"col-md-6 border border-info\">\n";

	if (count($unavailable_workshops) > 0) {

		echo "<h2>Classes Going Live Soon</h2>\n";
		echo "<p>Times shown in (".$u->fields['time_zone_friendly'].")</p>\n";

		$current_date = null;
		foreach ($unavailable_workshops as $wk) {
			
			$wk['when_public'] = \Wbhkit\convert_tz($wk['when_public'], $u->fields['time_zone']);

			// update date?
			$next_date = Wbhkit\friendly_date($wk['when_public']).' '.Wbhkit\friendly_time($wk['when_public']);
	
			if ($next_date != $current_date) {
		
				if ($current_date) {
					echo "</ul>\n";
				}
		
				echo "<h6>Going live: $next_date</h6>\n<ul>";
				$current_date = $next_date;
			}
	
			$wkdate = date("l F j", strtotime($wk['start_tz']));
			$start = Wbhkit\friendly_time($wk['start_tz']);
			$end = Wbhkit\friendly_time($wk['end_tz']);	
			echo "<li class='mb-2'>$wkdate: <a href='/workshop/view/{$wk['id']}'>{$wk['title']}</a><br>
				<small>$start {$wk['costdisplay']}, Instructor: <a href='/teachers/view/{$wk['teacher_id']}'>{$wk['teacher_info']['nice_name']}</a><br>
			{$wk['time_summary']}<br></small></li>\n";	
			
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
	
			$wkdate = date("l F j", strtotime($wk['start_tz']));
			$start = Wbhkit\friendly_time($wk['start_tz']);
			$end = Wbhkit\friendly_time($wk['end_tz']);	
			echo "<li class='mb-2'>$wkdate: <a href='/workshop/view/{$wk['id']}'>{$wk['title']}</a><br>
				<small>$start {$wk['costdisplay']}, Instructor: <a href='/teachers/view/{$wk['teacher_id']}'>{$wk['teacher_info']['nice_name']}</a><br>
			{$wk['time_summary']}<br></small></li>\n";	
		}	
		echo "</ul>\n";
	}
	echo "</div></div>";

}

