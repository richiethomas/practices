<?php
	
		echo  "<div class='row'><div class='col-md-6'><h2>emails for <a href='admin.php?ac=ed&wid={$wk['id']}'>{$wk['showtitle']}</a></h2>";
		echo  "<p>(Will replace TITLE in subject or note. Also, practice info is appended to message.)</p>\n";
		echo  "<div class='well'><h3>Send Message 
			<small><a href='$sc?ac=remind&wid={$wk['id']}'>load reminder</a> / 
		<a href='$sc?ac=feedback&wid={$wk['id']}'>load feedback</a></small>
		</h3>
			<form action ='$sc' method='post'>".
		Wbhkit\hidden('wid', $wk['id']).
		Wbhkit\hidden('ac', 'sendmsg').
		Wbhkit\texty('subject', $subject).
		Wbhkit\textarea('note', $note).
		Wbhkit\textarea('sms', $sms, 'SMS version (text)').
		Wbhkit\drop('st', $statuses, $st, 'To').
		Wbhkit\submit('send').
		"</form></div>\n";
		
		echo  "<div id='emaillists'>\n";
		foreach ($statuses as $stid => $status_name) {
			$stds = $students[$stid];
			$es = '';
			foreach ($stds as $as) {
				$es .= "{$as['email']}\n";
			}
			echo  "<h3>{$status_name} (".count($stds).")</h3>\n";
			echo  Wbhkit\textarea($status_name, $es, 0);
		}
		echo  "</div>\n";
		echo  "</div></div>\n";
		
?>
		