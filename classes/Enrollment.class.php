<?php

class Enrollment extends WBHObject {
	
	public array $fields;	
	public User $u;
	public array $wk;
	
	
	function __construct() {		
		parent::__construct(); // load logger, lookups

		$this->fields = array(
				'id' => false,
				'user_id' => false,
				'workshop_id' => false,
				'status_id' => false,
				'paid' => false,
				'registered' => false, 	
				'last_modified' => false,
				'while_soldout' => false);				

	}
	
	function set_an_enrollment(int $workshop_id, int $user_id, bool $set_user_workshop = false) {
	
		$stmt = \DB\pdo_query("select r.* from registrations r where r.workshop_id = :wid and user_id = :uid", array(':wid' => $workshop_id, ':uid' => $user_id));

		while ($row = $stmt->fetch()) {
			return $this->fill_out_fields($row, $set_user_workshop);
		}
		$this->error = "No enrollment found for user '{$user_id}' and workshop '{$workshop_id}'";
		return false;

	}

	function set_an_enrollment_by_id(int $enrollment_id, bool $set_user_workshop = false) {
	
		$stmt = \DB\pdo_query("select r.* from registrations r where r.id = :eid", array(':eid' => $enrollment_id));

		while ($row = $stmt->fetch()) {
			return $this->fill_out_fields($row, $set_user_workshop);
		}
		$this->error = "No enrollment found for enrollment id '{$enrollment_id}'";
		return false;

	}
	
	function fill_out_fields(array $row, bool $set_user_workshop = false) {
				
		$this->replace_fields($row);
				
		$this->fields['status_name'] = $this->lookups->statuses[$row['status_id']];
		if ($set_user_workshop) {
			$this->set_user_workshop();
		}		
		
		// check rank
		if ($this->fields['status_id'] == WAITING) {
			$stmt2 = \DB\pdo_query("select r.* from registrations r where r.workshop_id = :wid and r.status_id = :sid order by last_modified", array(':wid' => $this->fields['workshop_id'], ':sid' => WAITING));
			$i = 1;
			while ($row2 = $stmt2->fetch()) {
				if ($row2['id'] == $row['id']) {
					break;
				}
				$i++;
			}
			$this->fields['rank'] = $i;
		} else {
			$this->fields['rank'] = null;
		}
		
		return $this->fields['id'];
	}

	function set_user_workshop() {
		if (!is_set($this->u) || !is_set($this->u->fields['id']) || $this->u->fields['id'] != $this->fields['user_id']) {
			$this->u = new \User();
			$this->u->set_user_by_id($this->fields['id']);
		}
		if (!is_set($this->wk) || !is_set($wk['id']) || $this->wk['id'] != $this->fields['workshop_id']) {
			$this->wk = \Workshops\get_workshop_info($this->fields['workshop_id']);
		}
	}

	function smart_enroll(array $wk, User $u, bool $confirm = true) {

		// what status are we going to?
		if (($wk['enrolled']+$wk['invited']+$wk['waiting']) < $wk['capacity']) {
			$target_status = ENROLLED;
		} else {
			$target_status = WAITING;
		} 

		// existing enrollment?
		if ($this->set_an_enrollment($wk['id'], $u->fields['id'])) {
			$before_status = $this->fields['status_id'];
			$this->u = $u;
			$this->wk = $wk;
			$this->change_status($target_status, false); // don't send confirm message here
		} else { // new enrollment
			$before_status = null;
			$stmt = \DB\pdo_query("INSERT INTO registrations (workshop_id, user_id, status_id, registered, last_modified) VALUES (:wid, :uid, :status_id, '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."')", array(':wid' => $wl['id'], ':uid' => $u->fields['id'], ':status_id' => $target_status));	
			$this->update_change_log($target_status); 
		}
	
		// finicky confirmation message
		$keyword = '';
		if ($before_status == $target_status) {
			$keyword = 'is still';
		} else {
			$keyword = 'is now';
		}
		
		if ($target_status == ENROLLED) {
			$this->message = "'{$u->fields['nice_name']}' $keyword enrolled in '{$wk['title']}'!  Info emailed to <b>{$u->fields['email']}</b>.";
		} elseif ($target_status == WAITING) {
			$this->message = "This practice is full. '{$u->fields['nice_name']}' $keyword on the waiting list.";
		} 
			
		if ($confirm) { \Emails\confirm_email($wk, $u, $status_id); }
		return $this->message;
	}

	function change_status(int $status_id = ENROLLED, bool $confirm = true) {
		
		$this->set_user_and_workshop();
		$statuses = $this->lookups->statuses;

		// are we changing a status or nah
		if ($this->fields['status_id'] != $status_id) {
		
			$stmt = \DB\pdo_query("update registrations set status_id = :status_id,  last_modified = '".date("Y-m-d H:i:s")."' where workshop_id = :wid and user_id = :uid", array(':status_id' => $status_id, ':wid' => $this->fields['workshop_id'], ':uid' => $this->fields['user_id']));
		
			$this->update_change_log($status_id);	
			
			if ($confirm) { 
				\Emails\confirm_email($this->wk, $this->u, $status_id); 
			}
			return $this->message = "Updated user ({$this->u->fields['email']}) to status '{$statuses[$status_id]}' for {$this->wk['title']}.";
		}
		return $this->message = "User ({$this->u->fields['email']}) was already status '{$statuses[$status_id]}' for {$this->wk['title']}.";
	}

	function update_change_log($status_id) {
	
		$statuses = $this->lookups->statuses;
	
		$stmt = \DB\pdo_query("insert into status_change_log (workshop_id, user_id, status_id, happened) VALUES (:wid, :uid, :status_id, '".date('Y-m-d H:i:s', time())."')", 
		array(':wid' => $this->fields['workshop_id'],
		':uid' => $this->fields['user_id'],
		':status_id' => $status_id));
	
		$this->set_user_and_workshop();
		$this->logger->info("{$this->u->fields['fullest_name']} is now '{$statuses[$status_id]}' for '{$this->wk['title']}'");
	
		return true;
	}


	// this checks for open spots, and makes sure invites have gone out to anyone on waiting list
	// i call this in places just to make sure i haven't neglected the waiting list
	function check_waiting(array $wk) {
		$wk = \Workshops\fill_out_workshop_row($wk); // make sure it's up to date
		$msg = '';
		if ($wk['upcoming'] == 0) {
			return 'Workshop is in the past';
		}
		while (($wk['enrolled']+$wk['invited']) < $wk['capacity'] && $wk['waiting'] > 0) {
		
			$stmt = \DB\pdo_query("select * from registrations where workshop_id = :wid and status_id = '".WAITING."' order by last_modified limit 1", array(':wid' => $wk['id']));
		
			while ($row = $stmt->fetch()) {
				$this->set_an_enrollment($wk['id'], $row['user_id']);
				$msg .= $this->change_status(INVITED, true);
			}
		}
		if ($msg) { 
			return $this->message = $msg;
		}
		return $this->message = "No invites sent.";
	}

	function update_paid(int $wid = null, int $uid = null, int $paid = 1, int $eid = null) {
		
		// get enrollment ID if need be	
		if ($eid) {
			$this->set_an_enrollment_by_id($eid);
		} else {
			$this->set_an_enrollment($wid, $uid);
		}
	
		// get paid status before
		$paid_before = $this->fields['paid'];
	
		if ($paid != $paid_before) {
			$stmt = \DB\pdo_query("update registrations set paid = :paid where id = :rid", array(':paid' => $paid, ':rid' => $this->fields['id']));
			// send payment confirmation
			$this->set_user_and_workshop();
			if ($paid == 1) {
		
				$body = "<p>This is automated email to confirm that I've received your payment for class.</p>";
				$body .= "<p>Class: {$this->wk['title']} {$this->wk['showstart']} (California time, PST)</p>\n";
				$body .= "<p>Student: {$this->u->fields['nice_name']}</p>";
				$body .= "<p>Amount: \${$this->wk['cost']} (USD)</p>\n";
				$body .= "<p>Thanks!<br>-Will</p>\n";
				
				\Emails\centralized_email($this->u->fields['email'], "Payment received for {$this->wk['title']} {$this->wk['showstart']} (PDT)", $body); 
			
				return "Set user '{$this->u->fields['nice_name']}' to 'paid' for workshop '{$this->wk['title']}'";
			} else {
				return "Set user '{$this->u->fields['nice_name']}' to 'unpaid' for workshop '{$this->wk['title']}'";
			}
		
		}
		return false; // no update needed
	
	}

	function get_last_enrolled(int $wid = 0, int $uid = 0, string $before = null) {
		if (!$before) { 
			$before = "now()"; 
		}
		$stmt = \DB\pdo_query("select * from  status_change_log scl where workshop_id = :wid and user_id = :uid and happened <= :before order by happened desc", array(':wid' => $wid, ':uid' => $uid, ':before' => $before));	
		while ($row = $stmt->fetch()) {
			if ($row['status_id'] == ENROLLED) {
				return $row['happened'];
			}
		}
		return false;
	}


	function drop_session() {	
		if (!is_set($this->fields['id']) || !$this->fields['id']) {
			return false;
		}
		$this->update_change_log(DROPPED); // really should be a new status like "REMOVED" 
		$stmt = \DB\pdo_query('delete from registrations where id = :eid', array(':eid' => $this->fields['id']));	
		$this->check_waiting($wk);
		return true;
	}	

}
	
