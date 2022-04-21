<?php
		echo  "<div class='row'><div class='col-md-8'><h2><a href='/admin-workshop/view/{$wk['id']}'>{$wk['title']}</a></h2>".
		"<h4 class='my-4'>Change status of: {$guest->fields['nice_name']} (<a href='/admin-users/view/{$guest->fields['id']}'>see user page</a>)</h4>
		<p>Email: {$guest->fields['email']}</p>
		<p>Display Name: {$guest->fields['display_name']}</p>
		<p>Status: {$e->fields['status_name']}</p>";

		echo  "<form class='my-4' action ='/admin-change-status/cs/{$wk['id']}/{$guest->fields['id']}' method='post'>".
		Wbhkit\drop('st', $statuses, $e->fields['status_id'], 'to status').
		Wbhkit\radio('con', array('1' => 'confirm', '0' => 'don\'t'), 0)."<br>\n".
		Wbhkit\submit('update').
		"<a class='btn btn-warning' href='/admin-workshops/view/{$wk['id']}'>cancel</a>".
		"</form>\n";

		echo  "<form class='my-4' action ='/admin-change-status/cr/{$wk['id']}/{$guest->fields['id']}' method='post'>".
		Wbhkit\texty('lmod', $e->fields['last_modified'], 'Last modified').
		Wbhkit\submit('update').
		"<a class='btn btn-warning' href='/admin-workshops/view/{$wk['id']}'>cancel</a>".
		"</form>\n";
		
		
		echo "<form class='my-4' action='/admin-change-status/xfer/{$wk['id']}/{$guest->fields['id']}' method='post'>".
		\Wbhkit\drop('wid_new', \Workshops\get_recent_workshops_dropdown(), null, 'Xfer To Workshop').
		\Wbhkit\submit('transfer to').
		"</form></div></div>\n";	
		
		
		echo "<p>Remove student from class entirely: <a class='btn btn-outline-secondary btn-sm' href='/admin-workshop/conrem/{$wk['id']}/{$guest->fields['id']}'>remove</a></p>";	
				
		
?>