<?php
	
	
	
$sessions = '';
if (!empty($wk['sessions'])) {
	$wk['when'] = \XtraSessions\add_sessions_to_when($wk['when'], $wk['sessions']);
}	
?>
	<div class="row m-3 p-3"><div class="col-md-6 border border-info">
	<h2>How This Works</h2>
	<ul>
		<li>All listed times are California local time.</li>
		<li>Pay with Venmo (@willhines) or Paypal (@whines@gmail.com)</li>
		<li>Classes are held over <a href="http://www.zoom.us/">Zoom</a></li>
		<li><b>LATE DROP POLICY: If you are enrolled, and you drop within <?php echo LATE_HOURS; ?> hours of the start of the workshop, you still must pay. Before that, full refund available.</b></li>
	</ul>
	</div>
	</div>
<?php	
	
	
echo "	
<div class='row my-3 py-3'><div class='col-sm-6'>
<h2>{$wk['title']}</h2>
<p>{$wk['notes']}</p>
{$wk['when']} (".TIMEZONE.")<br><br>
{$wk['costdisplay']}, {$wk['enrolled']} (of {$wk['capacity']}) enrolled, ".($wk['waiting']+$wk['invited'])." waiting<br><br>

$names_list<br><br>\n";

echo \Teachers\teacher_photo($wk['teacher_user_id']);

echo "<p><b>Teacher: <a href='teachers.php?tid={$wk['teacher_id']}'>{$wk['teacher_name']}</a></b></p>\n";


echo "</div></div>\n";
		
?>