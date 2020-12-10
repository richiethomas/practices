<?php
if (isset($error_message)) {
	echo $error_message;
} else {
?>
<h2>Error</h2>
<p>Hi! This is a generic error page. We didn't have the data we needed to make this page. Probably a bad link or something. Almost definitely the coder's fault! Sorry!</p>

<?php } ?>

<p>Return to the <a href='index.php'>front</a>.</p>
