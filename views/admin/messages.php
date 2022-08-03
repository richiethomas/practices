<?php
	
		echo  "<div class='row'><div class='col-md-6'><h2>emails for <a href='/admin-workshop/view/{$wk->fields['id']}'>{$wk->fields['title']}</a></h2>";
		echo  "<p>(Will replace TITLE in subject or note. Also, practice info is appended to message.)</p>\n";
		echo  "<div class='well'><h3>Send Message 
			<small><a href='/admin-messages/roster/{$wk->fields['id']}'>roster info</a> / 
		<a href='/admin-messages/feedback/{$wk->fields['id']}'>feedback</a></small>
		</h3>
			<form action ='/admin-messages/sendmsg/{$wk->fields['id']}' method='post'>".
		Wbhkit\texty('subject', $subject).
		Wbhkit\textarea('note', $note).
		Wbhkit\drop('st', $statuses, $st, 'To').
		Wbhkit\submit('send').
		"</form></div>\n";
		
		echo  "<div id='emaillists'>\n";
		$whole_list = array();
		$total_number = 0;
		foreach ($statuses as $stid => $status_name) {
			$stds = $students[$stid];
			uasort($stds, 'cmp');
			$total_number += count($stds);
			$es = '';
			$names = '';
			foreach ($stds as $as) {
				$es .= "{$as['email']},\n"; // the comma is for better cutting-and-pasting into gmail
				$whole_list[] = $as['email'];
			}
			echo  "<h3>{$status_name} (".count($stds).")</h3>\n";
			echo  Wbhkit\textarea($status_name, $es, 0);
		}
		echo "<h3>all emails ({$total_number})</h3>\n";
		sort($whole_list, SORT_NATURAL | SORT_FLAG_CASE);
		$whole_list_textarea = '';
		foreach($whole_list as $wl) {
			$whole_list_textarea .= "$wl,\n";
		}
		echo Wbhkit\textarea("all", $whole_list_textarea, 0);			
		echo  "</div>\n";
		echo  "</div></div>\n";

function cmp($a, $b) {
	return strcasecmp($a['email'], $b['email']);
}
		
?>


		