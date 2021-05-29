<?php

$message_alert_yes = false;

if ((isset($error) && $error) || (isset($message) && $message)) {
	$message_alert_yes = true;
}

if ($page == 'home' && $message_alert_yes) {
	echo '<div class="container-lg container-fluid clearfix pt-5 mt-5" id="message-alert">'."\n";
}

if (isset($error) && $error) {
	echo "
<div class='alert alert-danger alert-dismissible' role='alert'>
		$error
	<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button></div>\n";
}

if (isset($message) && $message) {
	echo "
<div class='alert alert-success alert-dismissible' role='alert'>
		$message
	<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button></div>\n";
}

if ($page == 'home' && $message_alert_yes) {
	echo '</div>'."\n";
}


?>