<h2>Reminders</h2>


<p><a class="btn btn-primary" href="admin_reminders.php?ac=force">Force Reminder Check Now</a></p>

<h3>Recent Reminder Checks</h3>
<ul>
<?php
foreach ($reminders as $r) {
	echo "<li>".\Wbhkit\friendly_when($r['time_checked'])." - {$r['reminders_sent']}</li>\n";
}
?>
</ul>

