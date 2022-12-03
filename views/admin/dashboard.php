<script>		
function filterByTeacher() {
	window.location = '/admin/view/' + document.getElementById('filter_by').value;
	return true;
}

window.addEventListener('load', function() {
	document.getElementById("filter_by").addEventListener('change', filterByTeacher);	
});
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
		  <?php include 'dash_special.php'; ?>
	  </div>
	</div>


	<div class='row'>
		<div class='col-3'>
			<?php echo \Wbhkit\drop('filter_by', \Teachers\teachers_dropdown_array(true), $filter_by, 'Teacher');?>
		</div>
		<div class='col-5'>
			<?php include 'search_form.php'; 
			?>
		</div>
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
	
