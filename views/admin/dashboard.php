	<h2>Dashboard</h2>
		
	<div class="admin-box" id="admin-box-upcoming-workshops">
		<div class="admin-box-title">
			<h5>Upcoming Workshops</h5>
		</div>
		<div class="admin-box-content">


<script>
$( document ).ready(function() {
	$( "#dashalertsbutton" ).click(
		function() { $("#dashalerts").toggle(200); }
	);
});	



</script>
		

			<div class="issues float-end border border-4 p-2 m-2">				
				<button id="dashalertsbutton" type="button" class="btn-close float-end" aria-label="Close"></button>
				<div id="dashalerts">
				<?php
				// unpaid students
				$last_wk = null;
				$uphtml = '';
				foreach ($unpaid as $up) {
					if ($last_wk != $up['workshop_id']) {
						$uphtml .= "<p class='m-0'><b><a href='/admin-workshop/view/{$up['workshop_id']}'>{$up['title']}</a> - ".date('D M j', strtotime($up['start']))." - {$up['cost']}</b></p>\n";
						$last_wk = $up['workshop_id'];
					}
					$uphtml .= "<p class='m-0 ps-4'>{$up['nice_name']} - {$up['email']}</p>\n";
				}
				
				if ($uphtml) {
					echo "<h4>Unpaid</h4>
				<ul>
					$uphtml
				</ul>\n";
				}

				// not full, 15 days out
				$ts_now = strtotime('now');
				$ts_then = strtotime('+15 days');
				$nsohtml = '';
				foreach ($workshops as $wk) {
					if (!$wk['hidden'] && $wk['xtra'] == 0) {
						if ($wk['enrolled'] < $wk['capacity']) {
							$ts = strtotime($wk['course_start']);
							
							if ($ts >= $ts_now && $ts <= $ts_then) { 			
							
								$nsohtml .= "<li>".\Wbhkit\figure_year_minutes($ts).": <a href='/admin-workshop/view/{$wk['id']}'>{$wk['title']}</a> ({$wk['enrolled']}/{$wk['capacity']})";
								if ($wk['applied']) { $nso .= " <span class='text-primary'>- {$wk['applied']}</span>"; }
								$nsohtml .= "</li>\n";
							}
						}
					}
				}
				if ($nsohtml) {
					echo "<h4>Not Full, 15 Days Out</h4>
				<ul>
					$nsohtml
				</ul>\n";
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
						<ul>
							$hiddenhtml
						</ul>";
				}
				
				
				
				
				?>
			</div>
			</div>			
			<p><i>(class # / total classes)</i></p>


<script type="text/javascript">
$(function(){
  $("#filter_by").change(function(){
    window.location='/admin/view/' + this.value
  });
});
</script>


	<div class='col-3'>
	<?php
	echo \Wbhkit\drop('filter_by', \Teachers\teachers_dropdown_array(true), $filter_by, 'Teacher');
	?>
	</div>


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
	
	$xtra = $wk['class_show'] ? ' show' : '';
	if ($wk['hidden'] == 1) { $xtra = 'text-muted'; }
		
	echo "<li class='mt-1 $xtra' data-teacher=\"teacher-{$wk['teacher_id']}\"><a   href='/admin-workshop/view/{$wk['id']}' class='$xtra'>{$wk['title']}</a> ({$wk['rank']}/{$wk['total_sessions']}".($wk['class_show'] ? ' - show' : '')."), $start";
	
	echo " - {$wk['teacher_name']}";
	if ($wk['co_teacher_id']) {
		echo ", {$wk['co_teacher_name']}";
	}
	echo "</li>\n";
	
}	

echo "</ul>\n";
?>

		</div>
	</div>
