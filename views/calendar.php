<h1 class="page-title mb-5">Calendar</h1>
	<div class="row justify-content-center">
		<div class="col-md-10">
			
<?php		
$current_date = null;
foreach ($workshops as $wk) {

	// update date?
	$next_date = date("l F j, Y", strtotime($wk['start']));
	
	if ($next_date != $current_date) {
				
		echo "<div class='row mt-3'><div class='col-md-12'><h4>$next_date</h4></div></div>";
		$current_date = $next_date;
	}
	
	$start = Wbhkit\friendly_time($wk['start']);
	$end = Wbhkit\friendly_time($wk['end']);
	
	echo "<div class='row mt-2'><div class='col-md-6'><a href='workshop.php?wid={$wk['id']}'>{$wk['title']}</a> (".($wk['class_show'] ? 'show' : $wk['rank']).")</div>
	<div class='col-md-2'>$start-$end</div>
	<div class='col-md-4'><a href='faculty.php?tid={$wk['teacher_id']}'>{$wk['teacher_name']}</a></div>
	</div>\n";
	
}	

echo "</ul>\n";
?>
</div></div>