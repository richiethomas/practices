<?php
require_once 'vendor/autoload.php';



header("Access-Control-Allow-Origin: *");

$id_token = isset($_POST['idtoken']) ? $_POST['idtoken'] : null;

$client = new Google_Client(['client_id' => "989168310652-al6inpe49ep29r9i2ppb0t8j58k1pt22.apps.googleusercontent.com"]);  // Specify the CLIENT_ID of the app that accesses the backend


if (isset($id_token) && $id_token) {
	$payload = $client->verifyIdToken($id_token);
	if ($payload) {
		$email = isset($payload['email']) ? $payload['email'] : null;

		include 'lib-master.php';

		if (isset($email) && $email && $u->validate_email($email)) {
			$u ->set_by_email($email);
			echo $u->fields['ukey'];
			exit;
		} else {
			echo false;
		}

	} else {
		// Invalid ID token
		echo "invalid token";
	}
} else {
	echo "No ID token at all";
}

	
?>