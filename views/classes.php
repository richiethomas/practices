<h1 class="page-title">Classes</h1>
<h5 class='mb-5'>compact view
	<span class='text-muted'><small>(see <?php
if ($mode == 'text') {
	echo "<a href='/classes'>just regular compact</a>";
} else {
	echo "<a href='/classes/view/text'>very compact</a>";
}
?>
)</small></span></h5>

	<div class="row justify-content-center">
		<div class="col-md-12">
			
<?php		

echo "<div>Times shown in ({$u->fields['time_zone_friendly']})</div>";

$mw_html = '';
$wk_html = '';

$mw_text = '';
$wk_text = '';

foreach ($upcoming_workshops as $wk) {

	if ($wk['hidden'] || !Wbhkit\is_future($wk['start'])) {
		continue;
	}
	

	$start = date("D M j", strtotime($wk['start_tz'])).' '.Wbhkit\friendly_time($wk['start_tz']).' ('.$u->fields['time_zone_friendly'].')';
	if ($wk['costdisplay'] == 'Pay what you can') { $wk['costdisplay'] = 'donation'; }
	
	$row_html = "<div class='row mt-4'>\n";
	$row_html .= "<div class='col-md-2'>$start</div>\n";
	$row_html .= "<div class='col-md-4'><a href='/workshop/view/{$wk['id']}'>{$wk['title']}</a>";
	
	if ($wk['soldout']) {
		$row_html .= " - <span class='text-danger'>Sold Out</span>";
	}
	
	
	if ($u->check_user_level(2)) { 
		$row_html .= "<br><span class='text-muted'><small>({$wk['enrolled']} / {$wk['capacity']})</small></span>\n";
	}
	
	$row_html .= "</div>\n";
	$row_html .= "<div class='col-md-3'><a href='/teachers/view/{$wk['teacher_id']}'>{$wk['teacher_info']['nice_name']}</a>";
	
	if ($wk['co_teacher_id']) {
		$row_html .= ", <a href='/teachers/view/{$wk['co_teacher_id']}'>{$wk['co_teacher_info']['nice_name']}</a>";
	}

	$row_html .= "</div>\n";

	$row_html .= "<div class='col-md-3'>{$wk['total_sessions']} ".($wk['total_sessions'] == 1 ? 'session': 'sessions').", {$wk['costdisplay']}</div>\n";
	
	$row_html .= "</div>\n";
	
	
	// text view
	$text_html = "{$wk['title']}, {$wk['teacher_info']['nice_name']}, $start, {$wk['total_sessions']} weeks, {$wk['costdisplay']}".($wk['soldout'] ? " - <span class='text-danger'>Sold Out</span>" : '');
	
	if ($u->check_user_level(2)) { 
		$text_html .= " <span class='text-muted'><small>({$wk['enrolled']} / {$wk['capacity']})</small></span>\n";
	}
	
	$text_html .= "<br>\n";
	
	if ($wk['total_sessions'] == 1) {
		$wk_html .= $row_html;
		$wk_text .= $text_html;
	} else {
		$mw_html .= $row_html;
		$mw_text .= $text_html;
	}
}	

if ($mode == 'text') {
	
	echo "<h2 class='my-3'>Multi-Week Courses</h2>\n";
	echo $mw_text ? $mw_text : '<p>No multi-week courses coming up!</p>';

	echo "<h2 class='my-3'>One-Session Workshops</h2>\n";
	echo $wk_text ? $wk_text : '<p>No workshops coming up!</p>';
	
} else {
	echo "<h2 class='my-3'>Multi-Week Courses</h2>\n";
	echo $mw_html ? $mw_html : '<p>No multi-week courses coming up!</p>';

	echo "<h2 class='my-3'>One-Session Workshops</h2>\n";
	echo $wk_html ? $wk_html : '<p>No workshops coming up!</p>';
}




?>
</div></div>