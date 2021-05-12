	<h2>Dashboard</h2>
	<div class="admin-box" id="admin-box-upcoming-workshops">
		<div class="admin-box-title">
			<h5>Upcoming Workshops</h5>
		</div>
		<div class="admin-box-content">
			
			
			<div class="issues float-end border border-4 p-2 m-2">
				<h4>Outstanding Invites</h4>
				<ul>
				<?php
				$last_wk = null;
				foreach ($invites as $in) {
					if ($last_wk != $in['title']) {
						echo "<p class='m-0'><b><a href='admin_edit2.php?wid={$in['workshop_id']}'>{$in['title']}</a> - ".date('D M j', strtotime($in['start']))."</b></p>\n";
						$last_wk = $in['title'];
					}
					echo "<p class='m-0 ps-4'>{$in['nice_name']} <small>(".date('M j g:ia', strtotime($in['last_modified'])).")</small></p>\n";
				}
				?>
				</ul>
				
				<h4>Unpaid</h4>
				<ul>
				<?php
				$last_wk = null;
				foreach ($unpaid as $up) {
					if ($last_wk != $up['title']) {
						echo "<p class='m-0'><b><a href='admin_edit2.php?wid={$up['workshop_id']}'>{$up['title']}</a> - ".date('D M j', strtotime($up['start']))."</b></p>\n";
						$last_wk = $up['title'];
					}
					echo "<p class='m-0 ps-4'>{$up['nice_name']}</p>\n";
				}
				?>
				</ul>
			</div>
			
			
			<p><i>(paid / enrolled / capacity / waiting)</i></p>

<?php		
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
		
		echo "<h4>$next_date</h4>\n<ul>";
		$current_date = $next_date;
	}
	
	$start = Wbhkit\friendly_time($wk['start']);
	$end = Wbhkit\friendly_time($wk['end']);
	
	echo "<li class='mt-3' data-teacher=\"teacher-{$wk['teacher_id']}\" ".($wk['class_show'] ? ' class="show"' : '')."><a href='admin_edit2.php?wid={$wk['id']}'>{$wk['title']}</a> ({$wk['rank']}".($wk['class_show'] ? ' - show' : '')."), $start-$end (".number_format($wk['paid'], 0)." / ".number_format($wk['enrolled'], 0)." /  ".number_format($wk['capacity'], 0)." / ".number_format($wk['waiting']+$wk['invited']).")";
	echo " - {$wk['teacher_name']}";
	if ($wk['co_teacher_id']) {
		echo ", {$wk['co_teacher_name']}";
	}
	echo "<small>";
	if ($wk['override_url']) {
		echo "<a class='zoomlink' href='{$wk['override_url']}'>{$wk['override_url']}</a>";
	} else {
		echo "<a class='zoomlink' href='{$wk['online_url']}'>{$wk['online_url']}</a>";
	}
	echo "</small>\n";
	echo "</li>\n";
	
}	

echo "</ul>\n";
?>

		</div>
	</div>
