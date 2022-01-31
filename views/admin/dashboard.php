	<h2>Dashboard</h2>
	<div class="admin-box" id="admin-box-upcoming-workshops">
		<div class="admin-box-title">
			<h5>Upcoming Workshops</h5>
		</div>
		<div class="admin-box-content">
		
			<div class="issues float-end border border-4 p-2 m-2">				
				<h4>Unpaid</h4>
				<ul>
				<?php
				$last_wk = null;
				foreach ($unpaid as $up) {
					if ($last_wk != $up['workshop_id']) {
						echo "<p class='m-0'><b><a href='/admin-workshop/view/{$up['workshop_id']}'>{$up['title']}</a> - ".date('D M j', strtotime($up['start']))." - {$up['cost']}</b></p>\n";
						$last_wk = $up['workshop_id'];
					}
					echo "<p class='m-0 ps-4'>{$up['nice_name']} - {$up['email']}</p>\n";
				}
				?>
				</ul>
				
				<h4>Not Sold Out</h4>
				<ul>
				<?php
				$ids = array();
				$ts_now = strtotime('now');
				foreach ($workshops as $wk) {
					if (!in_array($wk['id'], $ids)) {
						if ($wk['enrolled'] < $wk['capacity']) {
							$ts = strtotime($wk['course_start']);
							
							if ($ts >= $ts_now) { 			
							
								echo "<li>".\Wbhkit\figure_year_minutes($ts).": <a href='/admin-workshop/view/{$wk['id']}'>{$wk['title']}</a> ({$wk['enrolled']}/{$wk['capacity']})";
								if ($wk['applied']) { echo " <span class='text-primary'>- {$wk['applied']}</span>"; }
								echo "</li>\n";
							}
						}
						$ids[] = $wk['id'];
					}
				}
					
					
				?>
				</ul>
			</div>
			
			
			<p><i>(enrolled / capacity)</i></p>

<?php		
$current_date = null;
foreach ($workshops as $wk) {

	if ($filter_by != 'all' && $filter_by > 0) {
		if ($wk['teacher_id'] != $filter_by) {
			continue; // skip this loop
		}
	}

	// update date?
	$next_date = date("D M j", strtotime($wk['start']));
	
	if ($next_date != $current_date) {
		
		if ($current_date) {
			echo "</ul>\n";
		}
		
		echo "<h4>$next_date</h4>\n<ul>";
		$current_date = $next_date;
	}
	
	$start = Wbhkit\friendly_time($wk['start']);
	$end = Wbhkit\friendly_time($wk['end']);
	
	$xtra = $wk['class_show'] ? ' show' : '';
		
	echo "<li class='mt-3 $xtra' data-teacher=\"teacher-{$wk['teacher_id']}\"><a   href='/admin-workshop/view/{$wk['id']}'>{$wk['title']}</a> ({$wk['rank']}/{$wk['total_sessions']}".($wk['class_show'] ? ' - show' : '')."), $start-$end";
	
	echo " - {$wk['teacher_name']}";
	if ($wk['co_teacher_id']) {
		echo ", {$wk['co_teacher_name']}";
	}
		
	echo "<small>";
	if ($wk['location_id'] == ONLINE_LOCATION_ID) {
		if ($wk['override_url']) {
			echo "<div class='zoomlink'>{$wk['override_url']}</div>";
		} else {
			echo "<div class='zoomlink'>{$wk['online_url']}</div>";
		}
	} else {
		echo "<div class='zoomlink'>{$wk['lwhere']}</div>";
	}
	echo "</small>\n";
	echo "</li>\n";
	
}	

echo "</ul>\n";
?>

		</div>
	</div>
