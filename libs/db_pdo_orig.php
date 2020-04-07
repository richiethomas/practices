<?php
namespace DB;

define('EMAIL_PASSWORD', EMAIL PASSWORD HERE);


$db = null;

function get_admin_password() {
	return ""; // put admin password here  
}

function pdo_query($sql, $params = null) {
	global $last_insert_id, $logger;
	$db = get_connection();
	//echo "$sql<br>\n";
	$stmt = $db->prepare($sql);
	if ($params) {
		foreach ($params as $key => &$value) {
			if (is_array($value)) {
				$stmt->bindParam($key, $value[0], $value[1]); // explicit data type
			} else {
				$stmt->bindParam($key, $value);
			}
		}
	}
	try {
		$stmt->execute();
	} catch (\PDOException $e) {
	    $logger->critical('SQL failed: ' .$e->getMessage());
	}
	
	if (preg_match('/insert/i', $sql)) {
		$last_insert_id = $db->lastInsertId();
	}
	
	return $stmt;
}

function get_connection() {	
	global $db, $logger;
	if ($db) {
		return $db;
	}
	$dsn = 'mysql:host=localhost;dbname=DBNAME';
	$username = // username here
	$password =   // password here
	$options = array(
	    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
	); 

	try {
		$db = new \PDO($dsn, $username, $password, $options);	
	} catch (\PDOException $e) {
	    $logger->critical('DB Connection failed: ' .$e->getMessage());
	}
	
	$db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
	$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	return $db;
}

	
?>