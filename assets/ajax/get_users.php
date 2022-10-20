<?php

define('LOCAL', ($_SERVER['SERVER_NAME'] == 'localhost') ? true : false);
define('DEBUG_LOG', 'info.txt');
define('ERROR_LOG', 'error_log.txt');

require "../../vendor/autoload.php";
include '../../libs/lib-logger.php';
include '../../libs/db_pdo.php';

$db = \DB\get_connection();

if(!empty($_POST["keyword"])) {
	$query ="SELECT * FROM users WHERE email like '" . $_POST["keyword"] . "%' ORDER BY email LIMIT 0,6";
	$result = $db->query($query);

	if(!empty($result)) {
		echo "<ul id=\"user-list\">\n";
		foreach($result as $user) {
			if (!empty($_POST['search']) && $_POST['search'] == 1) {
				echo "<li class='border-top p-2'><a href='/admin-users/view/{$user['id']}/{$_POST['keyword']}'>{$user["email"]}</a></li>";
			} else {
				echo "<li class='border-top p-2' onClick=\"selectUser('{$user['email']}')\">{$user["email"]}</li>";		
			}
		}
		echo "</ul>\n";
	}
}
?>
