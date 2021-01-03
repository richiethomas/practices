<?php

define('LOCAL', ($_SERVER['SERVER_NAME'] == 'localhost') ? true : false);
define('DEBUG_LOG', 'info.txt');
define('ERROR_LOG', 'error_log.txt');

require "vendor/autoload.php"; // i barely understand this; might not have enough classes to justify it
include 'libs/lib-logger.php';
include 'libs/db_pdo.php';

$db = \DB\get_connection();

if(!empty($_POST["keyword"])) {
$query ="SELECT * FROM users WHERE email like '" . $_POST["keyword"] . "%' ORDER BY email LIMIT 0,6";
$result = $db->query($query);

if(!empty($result)) {
?>
<ul id="user-list">
<?php
foreach($result as $user) {
?>
<li class='border-top p-2' onClick="selectUser('<?php echo $user["email"]; ?>');"><?php echo $user["email"]; ?></li>
<?php } ?>
</ul>
<?php } } ?>