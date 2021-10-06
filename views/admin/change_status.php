<?php
		echo  "<div class='row'><div class='col-md-5'><h2><a href='admin_edit2.php?wid={$wk['id']}'>{$wk['title']}</a></h2>".
		"<p>Email: {$guest->fields['email']}</p>
		<p>Display Name: {$guest->fields['display_name']}</p>
		<p>Status: {$e->fields['status_name']}</p>";

		echo  "<form class='my-4' action ='$sc' method='post'>".
		Wbhkit\hidden('wid', $wk['id']).
		Wbhkit\hidden('guest_id', $guest->fields['id']).
		Wbhkit\hidden('ac', 'cs').
		Wbhkit\drop('st', $statuses, $e->fields['status_id'], 'to status').
		Wbhkit\drop('con', array('1' => 'confirm', '0' => 'don\'t'), 0, 'confirm').
		Wbhkit\submit('update').
		"<a class='btn btn-warning' href='admin_edit2.php?wid={$wk['id']}'>cancel</a>".
		"</form>\n";

		echo  "<form class='my-4' action ='$sc' method='post'>".
		Wbhkit\hidden('wid', $wk['id']).
		Wbhkit\hidden('guest_id', $guest->fields['id']).
		Wbhkit\hidden('ac', 'cr').
		Wbhkit\texty('lmod', $e->fields['last_modified'], 'Last modified').
		Wbhkit\submit('update').
		"<a class='btn btn-warning' href='admin_edit2.php?wid={$wk['id']}'>cancel</a>".
		"</form>\n";
		
		
		echo "<form class='my-4' action='$sc' method='post'>".
		\Wbhkit\drop('wid_new', \Workshops\get_recent_workshops_dropdown(), null, 'Xfer To Workshop').
		\Wbhkit\hidden('wid', $wk['id']).
		\Wbhkit\hidden('guest_id', $guest->fields['id']).
		\Wbhkit\hidden('ac', 'xfer').
		\Wbhkit\submit('transfer to').
		"</form></div></div>\n";		
		
		print_r($e->fields);
		
		
?>