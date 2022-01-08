<?php
echo "<h1>Bulk Edit Tags</h1>\n";
echo "<div class='row'><div class='col-md-8'>";

echo "<form action='/admin-tags/update/' method='post'>\n";
echo \Wbhkit\submit('Update');
		
foreach ($workshops as $wk) {
	
	echo "<div class='row my-2 py-2 border-top'><div class='col'><p>{$wk['title']} - ".date('M j Y, g:ia', strtotime($wk['start']))."</p>\n";
	echo \Wbhkit\texty("tags_{$wk['id']}", $wk['tags'], 0);
	echo \Wbhkit\hidden("hidden_{$wk['id']}", $wk['tags']);
	echo "</div></div>\n";
	
}
		
echo "</form>\n";
echo "</div></div>\n";	
		
?>