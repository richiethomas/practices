	<h2>Dashboard</h2>
		
	<div class="admin-box" id="admin-box-upcoming-workshops">
		<div class="admin-box-title">
			<h5>Upcoming Workshops</h5>
		</div>
		<div class="admin-box-content">

<script>		
function changeHighlightBox() {
    var box = document.getElementById('dashalerts');
    box.style.display = (box.style.display == 'none') ? 'block' : 'none';
}
function filterByTeacher() {
	window.location = '/admin/view/' + document.getElementById('filter_by').value;
	return true;
}

window.onload = function() {
	document.getElementById('dashalertsbutton').addEventListener('click', changeHighlightBox);
	document.getElementById("filter_by").addEventListener('change', filterByTeacher);	
}
</script>
			<div class="issues float-end border border-4 p-2 m-2">				
				<button id="dashalertsbutton" type="button" class="btn-close float-end" aria-label="Close"></button>
				<div id="dashalerts" <?php if ($filter_by) { echo "style='display:none'"; } ?>>
				<?php
				
				// unpaid students
				if (count($unpaid) > 0) {
					echo "<h4>Unpaid</h4>\n<ul>\n";
					$last_wk = null;
					foreach ($unpaid as $up) {
						if ($last_wk != $up['workshop_id']) {
							echo "<p class='m-0'><b><a href='/admin-workshop/view/{$up['workshop_id']}'>{$up['title']}</a> - ".date('D M j', strtotime($up['start']))." - {$up['cost']}</b></p>\n";
							$last_wk = $up['workshop_id'];
						}
						echo "<p class='m-0 ps-4'>{$up['nice_name']} - {$up['email']}</p>\n";
					}
					echo "</ul>\n";
				}


				// not full, 15 days out
				$ts_now = strtotime('now');
				$ts_then = strtotime('+15 days');
				$nsohtml = '';
				foreach ($workshops as $wk) {
					if (!$wk['hidden'] && $wk['xtra'] == 0) {
						if ($wk['enrollments']['enrolled'] < $wk['capacity']) {
							$ts = strtotime($wk['course_start']);
							
							if ($ts >= $ts_now && $ts <= $ts_then) {
							
								$nsohtml .= "<li>".\Wbhkit\figure_year_minutes($ts).": <a href='/admin-workshop/view/{$wk['id']}'>{$wk['title']}</a> ({$wk['enrollments']['enrolled']}/{$wk['capacity']})";
								if ($wk['enrollments']['applied']) { $nsohtml .= " <span class='text-primary'>- {$wk['enrollments']['applied']}</span>"; }
								$nsohtml .= "</li>\n";
							}
						}
					}
				}
				if ($nsohtml) {
					echo "<h4>Not Full, 15 Days Out</h4>
				<ul>$nsohtml</ul>\n";
				}	
				
				
				// hidden classes
				$hiddenhtml = '';
				foreach ($workshops as $wk) {
					if ($wk['hidden'] == 1 && $wk['xtra'] == 0) {
						$ts = strtotime($wk['course_start']);
						
						$hiddenhtml .= "<li>".\Wbhkit\figure_year_minutes($ts).": <a href='/admin-workshop/view/{$wk['id']}'>{$wk['title']}</a>, {$wk['teacher_name']}</li>\n";
						
					}
				}
				if ($hiddenhtml) {
					echo "<h4>Hidden</h4>\n
						<ul>$hiddenhtml</ul>";
				}
				 
				//bitness
				if (count($bitnesses) > 0) {
					echo "<h4>Recent Bitnesses</h4>\n<ul>\n";
					foreach ($bitnesses as $b) {
						echo "<li><a href='/admin-workshop/view/{$b->fields['id']}'>{$b->fields['title']}</a> ({$b->fields['enrolled']}/{$b->fields['capacity']})</li>\n";
					}
				}
				
				
				?>
			</div>
			</div>			

	<div class='col-3'>
	<?php
	echo \Wbhkit\drop('filter_by', \Teachers\teachers_dropdown_array(true), $filter_by, 'Teacher');
	?>
	</div>


<?php		
$current_date = null;
foreach ($workshops as $wk) {

	if ($filter_by != 'all' && $filter_by > 0) {
		if ($wk['teacher_id'] != $filter_by && $wk['co_teacher_id'] != $filter_by) {
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
	
	$xtra = $wk['class_show'] ? ' show' : '';
	if ($wk['hidden'] == 1) { $xtra = 'text-muted'; }
		
	echo "<li class='mt-1 $xtra class-session' data-teacher=\"teacher-{$wk['teacher_id']}\"><a   href='/admin-workshop/view/{$wk['id']}' class='$xtra'>{$wk['title']}</a> {$wk['rank']} of {$wk['total_sessions']} <span class='text-muted'>({$wk['enrollments']['enrolled']}/{$wk['capacity']})</span>".($wk['class_show'] ? ' - show' : '').", $start - {$wk['teacher_name']}</li>\n";
	
}	

echo "</ul>\n";
?>

		</div>
	</div>
	
