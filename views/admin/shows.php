<h1>Class Shows</h1>

<p><a href='/admin-shows/view?plaintext=0'>linked</a> | <a href='/admin-shows/view?plaintext=1'>plaintext</a></p> 




<?php
$oldday = null;
foreach ($shows as $cs) {
	
	$heading = date('M d Y', strtotime($cs['start']));
	
	if ($heading != $oldday) {
		
		if ($oldday) { echo "</ul>\n"; }
		
		echo "<h4>$heading</h4><ul>";
	}
	
	echo "<div class='row my-1'>";
	
	if ($plaintext) {
		$workshop = $cs['title'];
	} else {
		$workshop = "<a href='/admin-workshop/view/{$cs['workshop_id']}'>{$cs['title']}</a>";
	}
	
	echo "<div class='col-md-12'><li>".\Wbhkit\friendly_time($cs['start'])."-".\Wbhkit\friendly_time($cs['end']).":  $workshop</li></div>";
	
	echo "</div>";
	
	$oldday = $heading;
}
?>
</ul>
</div></div>




