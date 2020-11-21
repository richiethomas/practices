<?php

if (isset($error) && $error) {
	echo "<div class='alert alert-danger' role='alert'>
		$error
	<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">
	    <span aria-hidden=\"true\">&times;</span>
	  </button></div>\n";
}

if (isset($message) && $message) {
	echo "<div class='alert alert-success' role='alert'>
		$message
	<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">
	    <span aria-hidden=\"true\">&times;</span>
	  </button></div>\n";
}
?>