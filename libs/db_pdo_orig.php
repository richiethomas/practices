<?php
namespace DB;

define('EMAIL_PASSWORD', HIDDEN);
define('EMAIL_PASSWORD_LOCAL', HIDDEN);
define('EMAIL_PASSWORD_PRODUCTION', HIDDEN);


$db = null;

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

		$password = HIDDEN;
		if (LOCAL) { // laptop
			$dsn = 'mysql:host=localhost;dbname=NOPE';
			$username = 'NOPE';
		} else { // wgimprovschool.com
			$dsn = 'mysql:host=localhost;dbname=wNOPE';
			$username = NOPE;
		}

		$options = array(
	    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
		\PDO::ATTR_PERSISTENT => true
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


function interpolateQuery($query, $params) {
    $keys = array();


	foreach ($params as $k => $p) {
		$params[$k] = "'$p'";
	}

    # build a regular expression for each parameter
    foreach ($params as $key => $value) {
        if (is_string($key)) {
            $keys[] = '/'.$key.'/';
        } else {
            $keys[] = '/[?]/';
        }
    }

    $query = preg_replace($keys, $params, $query, 1, $count);

    #trigger_error('replaced '.$count.' keys');

    return $query;
}

?>
