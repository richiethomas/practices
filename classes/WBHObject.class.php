<?php
/*
* this is just so every object has an error and a message property
*/
class WBHObject
{
	public $error = null;
	public $message = null;
	
	public function setError($error) {
		$this->error .= $error;
		return $this->error;
	}
	
	public function setMessage($message) {
		$this->message .= $message;
		return $this->message;
	}

}
	
