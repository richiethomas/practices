<?php
/*
* this is just so every object has an error and a message property
*/
class WBHObject
{
	public ?string $error = null;
	public ?string $message = null;
	
	public array $fields = array();
	public Monolog\Logger $logger;
	public Lookups $lookups;

	public function __construct() {
		global $logger, $lookups;
		$this->logger = $logger;
		$this->lookups = $lookups;
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
	}
	
	function replace_fields(array $row) {
		$this->fields = array();
		$this->set_into_fields($row);
	}

}
	
