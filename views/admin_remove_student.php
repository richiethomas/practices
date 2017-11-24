<?php	
		echo "<h4>Remove user <b>'{$u['email']}'</b> from <b>'{$wk['showtitle']}'</b>?</h4>\n";
		echo "<p><a class='btn btn-danger' href='{$sc}?wid={$wk['id']}&uid={$u['id']}&ac=conrem'>Remove</a> <a class='btn btn-success' href='{$sc}?wid={$wk['id']}&ac=ed'>Keep</a></p>\n";

?>
