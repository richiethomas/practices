<?php

class Enrollment extends WBHObject {
	
	public User $u;
	public array $wk;
	
	
	function __construct() {		
		parent::__construct(); // load logger, lookups

		$this->fields = array(
				'id' => null,
				'user_id' => null,
				'workshop_id' => null,
				'status_id' => null,
				'paid' => null,
				'pay_amount' => null,
				'pay_when' => null,
				'pay_channel' => null,
				'registered' => null, 	
				'last_modified' => null,
				'while_soldout' => null);	
			
		$this->u = new User();
		$this->wk = array();			

	}

	function set_by_id(int $enrollment_id) {
	
		$stmt = \DB\pdo_query("select r.* from registrations r where r.id = :eid", array(':eid' => $enrollment_id));

		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			return $this->finish_fields($row);
		}
		$this->error = "No enrollment found for enrollment id '{$enrollment_id}'";
		return false;

	}

	function set_by_uid_wid(int $uid, int $wid) {
		$stmt = \DB\pdo_query("select r.* from registrations r where r.workshop_id = :wid and r.user_id = :uid", array(':wid' => $wid, ':uid' => $uid));

		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			return $this->finish_fields($row);
		}
		$this->error = "No enrollment found for uid/wid '$uid' / '$wid'";
		return false;
		
	}

	function set_by_u_wk(User $u, array $wk) {
		$this->u = $u;
		$this->wk =$wk;
		return $this->set_by_uid_wid($u->fields['id'], $wk['id']);
	}


	function finish_fields($row) {
		$this->replace_fields($row);
		$this->fields['status_name'] = $this->lookups->statuses[$row['status_id']];
		return $this->fields['id'];
	}
	
	function reset_user_and_workshop() {
		if (isset($this->fields['user_id']) && $this->fields['user_id']) {
			$this->u->set_by_id($this->fields['user_id']);
		}
		if (isset($this->fields['workshop_id']) && $this->fields['workshop_id']) {
			$this->wk = \Workshops\get_workshop_info($this->fields['workshop_id']);
		}
	}
		
	// assumes we have set u and wk
	function change_status(int $target_status = ENROLLED, bool $confirm = true) {
		
		// just to make the subsequent lines less bulky
		$wk = $this->wk;
		$u = $this->u;
		$statuses = $this->lookups->statuses;
		$before_status = $this->fields['status_id'];
		$last_insert_id = null;
		$datestring_now = date(MYSQL_FORMAT);

		$db = \DB\get_connection();

		// workshop full or nah?
		if ($target_status == SMARTENROLL) {
			
			if ($this->fields['status_id'] == ENROLLED) {
				$target_status = ENROLLED;
				return $this->message = "user {$u->fields['email']} ({$u->fields['id']}) was already status $target_status for {$wk['title']} ({$wk['id']})";
				
			} else {
				if ($wk['application']) {
					$target_status = APPLIED;
				} else {
					$target_status = ($wk['enrolled'] < $wk['capacity']) ? ENROLLED : WAITING;
				}
			}
		
		}
		
		// update or insert?
		if ($this->fields['id']) {
			
			if ($this->fields['status_id'] != $target_status) {
				$stmt = $db->prepare("update registrations set status_id = :status_id,  last_modified = '{$datestring_now}' where id = :eid");
				$stmt->execute(array(':status_id' => $target_status, ':eid' => $this->fields['id']));
								
			} else {
				return $this->message = "user {$u->fields['email']} ({$u->fields['id']}) was already status $target_status for {$wk['title']} ({$wk['id']})";
			}
					
		} else {
			$stmt = $db->prepare("INSERT INTO registrations (workshop_id, user_id, status_id, registered, last_modified) VALUES (:wid, :uid, :status_id, '{$datestring_now}', '{$datestring_now}')");
			$stmt->execute(array(':wid' => $wk['id'], ':uid' => $u->fields['id'], ':status_id' => $target_status));
			$last_insert_id = $db->lastInsertId();
			
		}

		$this->set_into_fields(
			array('user_id' => $u->fields['id'],	
			'workshop_id' => $wk['id'],
			'status_id' => $target_status,
			'status_name' => $this->lookups->statuses[$target_status],
			'last_modified' => $datestring_now,
			'while_soldout' => false));	

		if (!isset($this->fields['paid'])) { $this->fields['paid'] = false; }
			
		if ($last_insert_id) { $this->fields['id'] = $last_insert_id; }

		if ($before_status != $target_status) {
			$this->update_change_log($target_status);	
			if ($confirm) {
				\Emails\confirm_email($this, $target_status); 
			}			
		}


		return $this->message = "Updated user ({$this->u->fields['email']}) to '{$statuses[$target_status]}' for workshop {$this->wk['title']}.";

	}
	
	function update_change_log($status_id) {
	
		$statuses = $this->lookups->statuses;
	
		$stmt = \DB\pdo_query("insert into status_change_log (workshop_id, user_id, status_id, happened) VALUES (:wid, :uid, :status_id, '".date(MYSQL_FORMAT, time())."')", 
		array(':wid' => $this->fields['workshop_id'],
		':uid' => $this->fields['user_id'],
		':status_id' => $status_id));
	
		//$this->logger->info("{$this->u->fields['fullest_name']} is now '{$statuses[$status_id]}' for '{$this->wk['title']}'");
	
		return true;
	}


	function notify_waiting(array $wk) {
		
		
		if ($wk['enrolled'] >= $wk['capacity']) {
			return "No open spot available.";
		}
		
		// retrieve waiting list
		$eh = new EnrollmentsHelper();
		$stds1 = $eh->get_students($wk['id'], WAITING);
		$stds2 = $eh->get_students($wk['id'], APPLIED);
		
		$stds = $stds1 + $stds2;
		
		$total_notified = 0;
		// send them an email that says there's a spot open
		foreach ($stds as $s) {
			
				$body = "A spot has opened up in '{$wk['title']}', starting on {$wk['showstart']}.<br><br>
		
				Go here to enroll.<br>
				".URL."workshop/view/{$wk['id']}
				<br><br>
			
				Please note: everyone on the waiting list gets this email at the same time. If you want this spot, go there ASAP.";	


				\Emails\centralized_email($s['email'], "WGIS: Spot open in '{$wk['title']}'", $body);
				
				$total_notified++;

		}
		return "$total_notified students notified of open spot.";
		
	}
	
	function update_paid_by_enrollment_id(
		int $eid, 
		int $new_paid, 
		string $pay_amount = '0',
		?string $pay_when = null,
		?string $pay_channel = null,
		bool $block_email = false) {
		$this->set_by_id($eid);
		return $this->update_paid($new_paid, $pay_amount, $pay_when, $pay_channel, $block_email);
	}
	
	function update_paid_by_uid_wid(
		int $uid, 
		int $wid, 
		int $new_paid, 
		string $pay_amount = '0',
		?string $pay_when = null,
		?string $pay_channel = null,
		bool $block_email = false) {
		$this->set_by_uid_wid($uid, $wid);
		return $this->update_paid($new_paid, $pay_amount, $pay_when, $pay_channel, $block_email);
	}
	
	private function update_paid (
		int $new_paid, 
		string $pay_amount = '0',
		?string $pay_when = null,
		?string $pay_channel = null,
		bool $block_email = false) {
		
		
		if ($pay_amount == '') { $pay_amount = 0; }
		
		if ($pay_when) { $pay_when = date('Y-m-d', strtotime($pay_when)); }
		
		
		//echo "$new_paid, $pay_amount, $pay_when, $pay_channel<br>\n";
		//die;
		
		// update database even if there is no change -- sometimes pay_override changes even if "paid" does not
		$stmt = \DB\pdo_query("
			update registrations 
			set paid = :paid, 
			pay_amount = :pa,
			pay_when = :pw,
			pay_channel = :pc 
			where id = :rid", 
			array(
			':paid' => $new_paid, 
			':rid' => $this->fields['id'], 
			':pa' => $pay_amount,
			':pw' => $pay_when,
			':pc' => $pay_channel
		));


		// send message only if there was a change
		if ($this->fields['paid'] != $new_paid) {

			$this->reset_user_and_workshop();
			
			// send payment confirmation
			if ($new_paid == 1) {
		
				$body = "<p>This is automated email to confirm that I've received your payment for class.</p>";
				$body .= "<p>Class: {$this->wk['title']} {$this->wk['showstart']}</p>\n";
				$body .= "<p>Student: {$this->u->fields['nice_name']}</p>";
				$body .= "<p>Amount: $pay_amount</p>\n";
				
				if ($this->wk['online_url']) {
					$body .= "<p>Zoom link for this classs: {$this->wk['online_url']}</p>\n";
				}
				
				$body .= "<p>To see all other info on the class go here:<br>";
				$body .= URL."workshop/view/{$this->wk['id']}</p>\n";

				
				$body .= "<p>Thanks!<br>-WGIS confirmation email robot</p>\n";
				
				if (!$block_email) {
					\Emails\centralized_email($this->u->fields['email'], "Payment received for '{$this->wk['title']}' {$this->wk['showstart']}", $body); 
				}
			
				return $this->message = "Set user '{$this->u->fields['nice_name']}' to 'paid' for workshop '{$this->wk['title']}'";
			} else {
				return $this->message = "Set user '{$this->u->fields['nice_name']}' to 'unpaid' for workshop '{$this->wk['title']}'";
			}
		
		}
		return false; // no update needed
	
	}


	function drop_session() {	
		if (!isset($this->fields['id']) || !$this->fields['id']) {
			return false;
		}
		$this->update_change_log(DROPPED); // really should be a new status like "REMOVED" 
		$stmt = \DB\pdo_query('delete from registrations where id = :eid', array(':eid' => $this->fields['id']));	
		return true;
	}	

}
	
