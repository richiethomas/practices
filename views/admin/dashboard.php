<script>		
function filterByTeacher() {
	window.location = '/admin/view/' + document.getElementById('filter_by').value;
	return true;
}

window.onload = function() {
	document.getElementById("filter_by").addEventListener('change', filterByTeacher);	
}
</script>

	<h2>Dashboard</h2>
		
	<div class="admin-box" id="admin-box-upcoming-workshops">

	<div class="admin-box-title">
		<h5>Upcoming Workshops</h5>
	</div>
	<div class="admin-box-content">

	<!-- Special Classes OffCanvas -->
	<button class="btn btn-primary float-end m-4" type="button" data-bs-toggle="offcanvas" data-bs-target="#special_classes" aria-controls="special_classes">See Special Classes</button>

	<div class="offcanvas offcanvas-end" tabindex="-1" id="special_classes" aria-labelledby="special_classes_label">
	  <div class="offcanvas-header">
	    <h4 class="offcanvas-title" id="special_classes_label">Special Classes</h4>
	    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
	  </div>
	  <div id="special_classes_body" class="offcanvas-body">

			<?php
			
			// unpaid students
			if (count($unpaid) > 0) {
				echo "<h5>Unpaid</h5>\n<ul>\n";
				$last_wk = null;
				foreach ($unpaid as $up) {
					if ($last_wk != $up['workshop_id']) {
						if ($last_wk) { echo "</ul></li>"; } // close last workshop list if there was one
						echo "<li><b><a href='/admin-workshop/view/{$up['workshop_id']}'>{$up['title']}</a> - ".date('D M j', strtotime($up['start']))." - {$up['cost']}</b><ul>\n";
						$last_wk = $up['workshop_id'];
					}
					echo "<li>{$up['nice_name']} - {$up['email']}</li>\n";
				}
				echo "</ul></li>"; // close last workshop list
				echo "</ul>\n"; // close this section
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
				echo "<h5>Not Full, 15 Days Out</h5>
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
				echo "<h5>Hidden</h5>\n
					<ul>$hiddenhtml</ul>";
			}
			 
			//bitness
			if (count($bitnesses) > 0) {
				echo "<h5>Recent Bitnesses</h5>\n<ul>\n";
				foreach ($bitnesses as $b) {
					echo "<li><a href='/admin-workshop/view/{$b->fields['id']}'>{$b->fields['title']}</a> ({$b->fields['enrolled']}/{$b->fields['capacity']})</li>\n";
				}
				echo "</ul>\n";
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
	
