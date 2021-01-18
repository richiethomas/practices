<?php

if (isset($error) && $error) {
	echo "<div class='alert alert-danger alert-dismissible' role='alert'>
		$error
	<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button></div>\n";
}

if (isset($message) && $message) {
	echo "<div class='alert alert-success alert-dismissible' role='alert'>
		$message
	<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button></div>\n";
}
?>