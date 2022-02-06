<?php
echo "<h1>Bulk Edit Workshop</h1>\n";
echo "<div class='row'><div class='col-md-8'>";

echo "<form action='/admin-bulk-workshops/update/' method='post'>\n";
echo \Wbhkit\submit('Update');
echo "<p class='mt-3'>(hidden flag / tags / when public)</p>\n";
		
foreach ($workshops as $wk) {
	
	$ts = strtotime($wk['when_public']);
	if (date('Y', $ts) == '1969') {
		$wk['when_public'] = '';
	} else {
		$wk['when_public'] = date('M j Y'.\Wbhkit\figure_minutes_df($ts), $ts);
	}
	
	echo "<div class='row my-2 py-2 border-top'><div class='col'>";
	echo "<div class='row'><div class='col-sm-1'>&nbsp;</div><div class='col-sm-11'>({$wk['id']}) <a href='/admin-workshop/view/{$wk['id']}'>{$wk['title']}</a>, {$wk['teacher_name']},  ".\Wbhkit\figure_year_minutes(strtotime($wk['start']))."</div></div>\n";
	echo "<div class='row'>";
	echo "<div class='col-sm-1'>".\Wbhkit\checkbox("hidden_{$wk['id']}", 1, 0, $wk['hidden'])."</div>\n";
	echo "<div class='col-sm-5'>".\Wbhkit\texty("tags_{$wk['id']}", $wk['tags'], 0)."</div>";
	echo "<div class='col-sm-5'>".\Wbhkit\texty("wp_{$wk['id']}", $wk['when_public'], 0)."</div>\n";
	echo "</div>\n";
	echo \Wbhkit\hidden("hiddentags_{$wk['id']}", $wk['tags']);
	echo \Wbhkit\hidden("hiddenhidden_{$wk['id']}", $wk['hidden']);
	echo \Wbhkit\hidden("hiddenwp_{$wk['id']}", strtotime($wk['when_public']));
	echo "</div></div>\n";
	
}
		
echo "</form>\n";
echo "</div></div>\n";	
		
?>
