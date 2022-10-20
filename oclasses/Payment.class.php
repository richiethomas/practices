<?php

class Payment extends WBHObject {
		
	public Workshop $wk;
	public User $u;
	
	function __construct() {		
		parent::__construct(); 
		
		$this->tablename = 'payments';

		$this->fields = array(
				'id' => null,
				'user_id' => null,
				'workshop_id' => null,
				'when_happened' => null,
				'when_paid' => null,
				'amount' => null,
				'title' => null);	
				
		$this->cols= $this->fields;
		$this->u = new User();
		$this->wk = new Workshop();
			
	}

	function finish_setup() {
		$this->set_mysql_date_field('when_paid', $this->fields['when_paid']);
		$this->set_mysql_date_field('when_happened', $this->fields['when_happened']);
		if (!$this->fields['amount']) { $this->fields['amount'] = 0; }
		if (!$this->fields['workshop_id']) { $this->fields['workshop_id'] = 0; }
		
		$this->fields['user_name'] = 'No Name';
		
		if ($this->fields['user_id']) {
			$this->u->set_by_id($this->fields['user_id']);
			$this->fields['user_name'] = $this->u->fields['nice_name'];
		}
		if ($this->fields['workshop_id']) {
			$this->wk->set_by_id($this->fields['workshop_id']);
		}

	}	
	
	
}
	
?>