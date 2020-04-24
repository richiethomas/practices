<?php
	
	
	
$sessions = '';
if (!empty($wk['sessions'])) {
	$sessions .= "{$wk['when']}";
	foreach ($wk['sessions'] as $s) {
		$sessions .= "<br>\n{$s['friendly_when']}";
	}
	$wk['when'] = $sessions; // replace the when variable 
}	
	
echo "
<div class='row'><div class='col'>
<h2>{$wk['title']}</h2>
<p>{$wk['notes']}</p>
</div></div>

<div class='row'>
<div class='col'>
{$wk['when']} (".TIMEZONE.")<br><br>
".($admin ? "{$wk['place']} {$wk['lwhere']}<br><br>" : '').
"{$wk['costdisplay']}, {$wk['enrolled']} (of {$wk['capacity']}) enrolled, ".($wk['waiting']+$wk['invited'])." waiting
</div>

<div class='col'>$names_list</div>

</div>
";
		
?>