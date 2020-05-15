<?php
	
	
	
$sessions = '';
if (!empty($wk['sessions'])) {
	$sessions .= "{$wk['when']}";
	foreach ($wk['sessions'] as $s) {
		$sessions .= "<br>\n{$s['friendly_when']}";
	}
	$wk['when'] = $sessions; // replace the when variable 
}	
?>
	<div class="row justify-content-center"><div class="col-md-6 border border-info">
	<h2>How This Works</h2>
	<ul>
		<li>All class times are California local time.</li>
		<li>You log in with your email, a link gets emailed to you, you click it. Then you can enroll in classes, drop out, join waiting lists</li>
		<li>Pay with Venmo or Paypal</li>
		<li>Classes are held over <a href="http://www.zoom.us/">Zoom</a></li>
		<li><b>LATE DROP POLICY: If you drop within <?php echo LATE_HOURS; ?> hours of the start of the workshop, you still must pay. Before that, full refund available.</b></li>
	</ul>
	</div>
	</div>
<?php	
	
	
echo "	
<div class='row'><div class='col'>
<h2>{$wk['title']}</h2>
<p>{$wk['notes']}</p>
</div></div>

<div class='row'>
<div class='col'>
{$wk['when']} (".TIMEZONE.")<br><br>
{$wk['costdisplay']}, {$wk['enrolled']} (of {$wk['capacity']}) enrolled, ".
($wk['waiting']+$wk['invited'])." waiting
</div>

<div class='col'>$names_list</div>

</div>
";
		
?>