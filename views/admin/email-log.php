<h2>Email Log</h2>
<p>See: <a href='/admin-email-log/view/100'>100</a> <a href='/admin-email-log/view/200'>200</a> <a href='/admin-email-log/view/500'>500</a> rows</p>
<div class='row fw-bold'>
<div class='col'>When</div>
<div class='col'>To</div>
<div class='col'>From</div>
<div class='col'>Subject</div>
</div>
<?php 



foreach ($rows as $r) {
	echo "
		<div class='row'>
		<div class='col'>".\Wbhkit\friendly_when($r['when_sent'])."</div>
		<div class='col'>{$r['to_email']}</div>
		<div class='col'>{$r['from_email']}</div>
		<div class='col'>{$r['subject']}</div>
		</div>";
}
	
	
	
?>
</pre>
