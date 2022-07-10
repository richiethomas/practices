<?php
	
echo "<h1>Profile: {$guest->fields['nice_name']}</h1>\n"; 
echo "<p><a href='/'>(return to front)</a></p>\n";

echo "<div class='row'><div class='col-md-2'>Email:</div><div class='col-md-4'>{$guest->fields['email']}</div></div>\n";
echo "<div class='row'><div class='col-md-2'>Display Name:</div><div class='col-md-4'>{$guest->fields['display_name']}</div></div>\n";
echo "<div class='row'><div class='col-md-2'>Time Zone:</div><div class='col-md-4'>{$guest->fields['time_zone']}</div></div>\n";
	
echo "<h2 class='mt-4'>Transcript</h2>\n";
echo $transcript; 

?>
