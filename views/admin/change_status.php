<?php
		echo  "<div class='row'><div class='col-md-5'><h2><a href='admin_edit.php?wid={$wk['id']}'>{$wk['title']}</a></h2>".
		"<p>Email: {$guest['email']}</p>
		<p>Display Name: {$guest['display_name']}</p>
		<p>Status: {$e['status_name']}</p>";

		echo  "<form action ='$sc' method='post'>".
		Wbhkit\hidden('wid', $wk['id']).
		Wbhkit\hidden('guest_id', $guest['id']).
		Wbhkit\hidden('ac', 'cs').
		Wbhkit\drop('st', $statuses, $e['status_id'], 'to status').
		Wbhkit\drop('con', array('1' => 'confirm', '0' => 'don\'t'), 0, 'confirm').
		Wbhkit\submit('update').
		"<a class='btn btn-warning' href='admin_edit.php?wid={$wk['id']}'>cancel</a>".
		"</form>\n";

		echo  "<form action ='$sc' method='post'>".
		Wbhkit\hidden('wid', $wk['id']).
		Wbhkit\hidden('guest_id', $guest['id']).
		Wbhkit\hidden('ac', 'cr').
		Wbhkit\texty('lmod', $e['last_modified'], 'Last modified').
		Wbhkit\submit('update').
		"<a class='btn btn-warning' href='admin_edit.php?wid={$wk['id']}'>cancel</a>".
		"</form></div></div>\n";
?>