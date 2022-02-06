<h1 class="page-title">Classes</h1>
<h5 class='mb-5'>(compact view)</h5>
	<div class="row justify-content-center">
		<div class="col-md-12">
			
<?php		

if ($u->logged_in()) {
	echo "<div>Times shown in ({$u->fields['time_zone_friendly']})</div>";
}

$mw_html = '';
$wk_html = '';

foreach ($upcoming_workshops as $wk) {

	if ($wk['hidden'] || !Wbhkit\is_future($wk['start'])) {
		continue;
	}
	

	$start = Wbhkit\friendly_time($wk['start_tz']);
	if ($wk['costdisplay'] == 'Pay what you can') { $wk['costdisplay'] = 'donation'; }
	
	$row_html = "<div class='row mt-4'>\n";
	$row_html .= "<div class='col-md-2'>".date("D M j", strtotime($wk['start_tz']))." $start</div>\n";
	$row_html .= "<div class='col-md-4'><a href='/workshop/view/{$wk['id']}'>{$wk['title']}</a>";
	
	if ($wk['soldout']) {
		$row_html .= " - <span class='text-danger'>Sold Out</span>";
	}
	
	$row_html .= "</div>\n";
	$row_html .= "<div class='col-md-3'><a href='/teachers/view/{$wk['teacher_id']}'>{$wk['teacher_info']['nice_name']}</a>";
	
	if ($wk['co_teacher_id']) {
		$row_html .= ", <a href='/teachers/view/{$wk['co_teacher_id']}'>{$wk['co_teacher_info']['nice_name']}</a>";
	}

	$row_html .= "</div>\n";

	$row_html .= "<div class='col-md-3'>{$wk['total_sessions']} ".($wk['total_sessions'] == 1 ? 'session': 'sessions').", {$wk['costdisplay']}</div>\n";
	
	$row_html .= "</div>\n";
	
	if ($wk['total_sessions'] == 1) {
		$wk_html .= $row_html;
	} else {
		$mw_html .= $row_html;
	}
}	

echo "<h2 class='my-3'>Multi-Week Courses</h2>\n";
echo $mw_html ? $mw_html : '<p>No multi-week courses coming up!</p>';

echo "<h2 class='my-3'>One-Session Workshops</h2>\n";
echo $wk_html ? $wk_html : '<p>No workshops coming up!</p>';




?>
</div></div>