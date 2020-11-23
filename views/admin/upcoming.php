	<h2>Dashboard</h2>
	<div class="admin-box" id="admin-box-upcoming-workshops">
		<div class="admin-box-title">
			<h5>Upcoming Workshops</h5>
			
			<div class="admin-box-title-toolbar-items">
				<span id="admin-box-upcoming-workshops-teacher-filter-class-count"></span>
				<select id="admin-box-upcoming-workshops-teacher-filter">
					<option value="*">All Teachers</option>
					<?php
					foreach ($faculty as $teacher) {
						echo "<option value='{$teacher['id']}'>{$teacher['nice_name']}</option>";
					}
					?>
				</select>
			</div>
		</div>
		<div class="admin-box-content">
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
	
	echo "<li data-teacher=\"teacher-{$wk['teacher_id']}\" ".($wk['class_show'] ? ' class="show"' : '')."><a href='admin_edit.php?wid={$wk['id']}'>{$wk['title']}</a> ({$wk['rank']}".($wk['class_show'] ? ' - show' : '')."), $start-$end (".number_format($wk['paid'], 0)." / ".number_format($wk['enrolled'], 0)." /  ".number_format($wk['capacity'], 0)." / ".number_format($wk['waiting']+$wk['invited']).")";
	echo " - {$wk['teacher_name']}";
	if ($wk['override_url']) {
		echo "<a class='zoomlink' href='{$wk['override_url']}'>{$wk['override_url']}</a>";
	} else {
		echo "<a class='zoomlink' href='{$wk['online_url']}'>{$wk['online_url']}</a>";
	}
	echo "</li>\n";
	
}	

echo "</ul>\n";
?>
		</div>
	</div>
