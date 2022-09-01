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

$ip_html = '';
$ol_html = '';

$ip_text = '';
$ol_text = '';

foreach ($upcoming_workshops as $wk) {

	if ($wk->fields['hidden'] || !Wbhkit\is_future($wk->fields['start'])) {
		continue;
	}
	
	$wk = prep_wk($wk);
	
	$row_html = "<div class='row mt-4'>\n";
	$row_html .= "<div class='col-md-4'><a href='/workshop/view/{$wk->fields['id']}'>{$wk->fields['title']}</a>";
	
	if ($wk->fields['soldout']) {
		$row_html .= " - <span class='text-danger'>Sold Out</span>";
	}
	
	
	if ($u->check_user_level(2)) { 
		$row_html .= "<br><span class='text-muted'><small>({$wk->fields['enrolled']} / {$wk->fields['capacity']})</small></span>\n";
	}
	
	$row_html .= "</div>\n";
	$row_html .= "<div class='col-md-3'>{$wk->fields['teacher_name']}</div>\n";
	$row_html .= "<div class='col-md-2'>{$wk->fields['classpage_start']}</div>\n";

	$row_html .= "<div class='col-md-3'>{$wk->fields['total_sessions']} ".($wk->fields['total_sessions'] == 1 ? 'session': 'sessions').", {$wk->fields['costdisplay']}</div>\n";
	
	$row_html .= "</div>\n";
	
	
	// text view
	$text_html = "<p class='m-1 p-0 fs-6 lh-base'><a class='text-decoration-none' href='/workshop/view/{$wk->fields['id']}'>{$wk->fields['title']}</a>, {$wk->fields['teacher_name']}, {$wk->fields['classpage_start']}, {$wk->fields['total_sessions']} ".($wk->fields['total_sessions'] == 1 ? 'week': 'weeks').", {$wk->fields['costdisplay']}".($wk->fields['soldout'] ? " - <span class='text-danger'>Sold Out</span>" : '');
	
	if ($u->check_user_level(2)) { 
		$text_html .= " <span class='text-muted'><small>({$wk->fields['enrolled']} / {$wk->fields['capacity']})</small></span>\n";
	}
	
	$text_html .= "</p>\n";
	
	if (in_array('inperson', $wk->fields['tags_array'])) {
		$ip_html .= $row_html;
		$ip_text .= $text_html;
	} else {
		$ol_html .= $row_html;
		$ol_text .= $text_html;
	}
}	


$upc_html = '';

$current_date = null;
foreach ($unavailable_workshops as $wk) {
	
	if ($wk->fields['hidden'] || !Wbhkit\is_future($wk->fields['start'])) {
		continue;
	}
	$wk = prep_wk($wk);


	// update date?
	$next_date = Wbhkit\friendly_date($wk->fields['when_public']).' '.Wbhkit\friendly_time($wk->fields['when_public']);

	if ($next_date != $current_date) {
		
		if ($current_date) {
			$upc_html .= "</ul>\n";
		}
		$upc_html .= "<h6>Going live: $next_date</h6>\n<ul>";
		$current_date = $next_date;
	}
	
	// text view
	$upc_html .= "<p class='m-1 p-0 fs-6 lh-base'>{$wk->fields['classpage_start']} - <a class='text-decoration-none' href='/workshop/view/{$wk->fields['id']}'>{$wk->fields['title']}</a>, {$wk->fields['teacher_name']}, {$wk->fields['total_sessions']} ".($wk->fields['total_sessions'] == 1 ? 'week': 'weeks').", {$wk->fields['costdisplay']}";
	
	if (in_array('inperson', $wk->fields['tags_array'])) {
		$upc_html .= " <b>(in person, Los Angeles)</b>";
	}
	
	$upc_html .= "</p>";
	
	
} 

echo "<h2 class='my-3'>Online Classes</h2>\n";
echo $mode == 'text' ?
	($ol_text ? $ol_text : '<p>No multi-week courses coming up!</p>') :
	($ol_html ? $ol_html : '<p>No multi-week courses coming up!</p>');
	

echo "<h2 class='my-3'>In Person Los Angeles Classes</h2>\n";
echo $mode == 'text' ?
	($ip_text ? $ip_text : '<p>No multi-week courses coming up!</p>') :
	($ip_html ? $ip_html : '<p>No multi-week courses coming up!</p>');


if ($upc_html) {
	echo "<h2 class='my-3'>Classes Available Soon</h2>\n";
	echo $upc_html;
}



function prep_wk($wk) {
	global $u;
	$wk->fields['classpage_start'] = date("D M j", strtotime($wk->fields['start_tz'])).' '.Wbhkit\friendly_time($wk->fields['start_tz']).' ('.$u->fields['time_zone_friendly'].')';
	
	if ($wk->fields['costdisplay'] == 'Pay what you can') { $wk->fields['costdisplay'] = 'donation'; }
	
	$wk->fields['when_public'] = \Wbhkit\convert_tz($wk->fields['when_public'], $u->fields['time_zone']);
	
	return $wk;
}

?>
</div></div>