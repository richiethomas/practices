<?php
/*
* this is just so every object has an error and a message property
*/
class WBHObject
{
	public ?string $error = null;
	public ?string $message = null;
	
	public array $fields = array();
	public array $cols = array();
	
	public Monolog\Logger $logger;
	public Lookups $lookups;
	
	public ?string $tablename = null;

	public function __construct() {
		global $logger, $lookups;
		$this->logger = &$logger;
		$this->lookups = &$lookups;
	}
	
	public function setError($error) {
		$this->error .= $error;
		return $this->error;
	}
	
	public function setMessage($message) {
		$this->message .= $message;
		return $this->message;
	}

	function set_into_fields(array $row) {
		foreach ($row as $n => $v) {
			$this->fields[$n] = $v;
		}
		return true;
	}
	
	function replace_fields(array $row) {
		$this->fields = array();
		$this->set_into_fields($row);
	}
	
	function set_mysql_datetime_field(string $fn, ?string $ts = null) {
		if ($ts) {
			$this->fields[$fn] = date('Y-m-d H:i:s', strtotime($ts));
		} else {
			$this->fields[$fn] = null;
		}
	}
	
	function set_by_id(int $id) {
		
		if (!$id) {
			$this->error = "No {$this->tablename} found for id '{$id}'";
			return false;
		}
	
		$stmt = \DB\pdo_query("select * from {$this->tablename} where id = :id", array(':id' => $id));

		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$this->set_into_fields($row);
			$this->format_row();
			return true;
		}
		$this->error = "No {$this->tablename} found for id '{$id}'";
		return false;

	}
	
	function format_row() {
		return true;
	}


	function save_data() {
		// make sure datetime fields are formatted for mysql
		$this->format_row();
		
		$params = $this->make_params();
		
		//insert or update
		if ($this->fields['id']) {
			$params[':id'] = $this->fields['id'];
			//echo "update {$this->tablename} set ".$this->get_update_sql($this->cols)." where id = :id";
			$stmt = \DB\pdo_query("update {$this->tablename} set ".$this->get_update_sql($this->cols)." where id = :id", $params);
			return true;
		} else {
			$query = "insert into {$this->tablename} ".$this->get_insert_sql($this->cols);
			//echo $query;
			
			$db = \DB\get_connection();
			$stmt = $db->prepare($query);
			
			foreach ($this->cols as $f => $v) {
				if (is_numeric($f) || $f == 'id') { continue; }
				
				if ($this->fields[$f] !== null) {
					$stmt->bindParam(":{$f}", $this->fields[$f]);
				} else {
					$stmt->bindValue(":{$f}", null, PDO::PARAM_INT);
				}
			}
			$stmt->execute();
			$this->fields['id'] = $db->lastInsertId();
			return true;	
		}
	}

	function make_params() {
		$params = array();
		foreach ($this->cols as $f => $v) {
			$params[":{$f}"] = $this->fields[$f];
		}
		return $params;
	}

	function get_update_sql($params) {
		
		$sql = null;
		foreach ($params as $p => $v) {
			if ($p == 'id' || is_numeric($p)) { continue; }
			if ($sql) { $sql .= ', '; }
			$sql .= "$p = :$p";
		}
		return $sql;
	}
	
	function get_insert_sql($params) {
		$sql1 = null;
		$sql2 = null;
		
		foreach ($params as $p => $v) {
			if ($p == 'id' || is_numeric($p)) { continue; }
			if ($sql1) {   $sql1 .= ', ';  }
			$sql1 .= "$p";
			if ($sql2) { $sql2 .= ', '; }
			$sql2 .= ":$p";
		}
		return "($sql1) VALUES ($sql2)";	
	}
	
	function delete_row() {
		if (!$this->fields['id']) {
			$this->error = "No id set!";
			return false;
		}
		$params = array(':id' => $this->fields['id']);
		$stmt = \DB\pdo_query("delete from {$this->tablename} where id = :id", $params);
		$this->message = "Deleted from {$this->tablename} {$this->fields['id']}";
		$this->fields = array();
		return true;
	}
	

}
	
