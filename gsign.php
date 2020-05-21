<?php
require_once 'vendor/autoload.php';
header("Access-Control-Allow-Origin: https://willhinesimprov.com");

$id_token = isset($_POST['idtoken']) ? $_POST['idtoken'] : null;

$client = new Google_Client(['client_id' => "989168310652-2mk8v22d2vone6maq7jcumb9il0r9r2o.apps.googleusercontent.com"]);  // Specify the CLIENT_ID of the app that accesses the backend


if (isset($id_token) && $id_token) {
	$payload = $client->verifyIdToken($id_token);
	if ($payload) {
		$email = isset($payload['email']) ? $payload['email'] : null;

		include 'lib-master.php';

		if (isset($email) && $email && Users\validate_email($email)) {
			$u = Users\get_user_by_email($email);
			echo $u['ukey'];
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