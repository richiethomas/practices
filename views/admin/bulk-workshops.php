<?php
echo "<h1>Bulk Edit Workshop</h1>\n";
echo "<p>hidden flag / tags</p>\n";
echo "<div class='row'><div class='col-md-8'>";

echo "<form action='/admin-tags/update/' method='post'>\n";
echo \Wbhkit\submit('Update');
		
foreach ($workshops as $wk) {
	
	echo "<div class='row my-2 py-2 border-top'><div class='col'><p><a href='/admin-workshop/view/{$wk['id']}'>{$wk['title']}</a> - ".\Wbhkit\figure_year_minutes(strtotime($wk['start']))."</p>\n";
	echo "<div class='row'>";
	echo "<div class='col-sm-1'>".\Wbhkit\checkbox("hidden_{$wk['id']}", 1, 0, $wk['hidden'])."</div>\n";
	echo "<div class='col-sm-11'>".\Wbhkit\texty("tags_{$wk['id']}", $wk['tags'], 0)."</div>\n";
	echo "</div>\n";
	echo \Wbhkit\hidden("hiddentags_{$wk['id']}", $wk['tags']);
	echo \Wbhkit\hidden("hiddenhidden_{$wk['id']}", $wk['tags']);
	echo "</div></div>\n";
	
}
		
echo "</form>\n";
echo "</div></div>\n";	
		
?>