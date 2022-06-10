<?php
	
class EnrollmentsHelper extends WBHObject {
	
	public array $enrollments;
	
	function __construct() {		
		parent::__construct(); // load logger, lookups
		$this->enrollments = array();
	}
	
	// registrations
	function set_enrollments_for_workshop(int $workshop_id) {
	
		global $lookups;
	
		// set enrollment data to zero
		$this->enrollments = array();
		foreach ($lookups->statuses as $sid => $sname) {
			$this->enrollments[$sname] = 0;
		}
		$this->enrollments['paid'] = 0;
	
		$stmt = \DB\pdo_query("select r.status_id, r.paid from registrations r where r.workshop_id = :wid", array(':wid' => $workshop_id));
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$this->enrollments[$lookups->statuses[$row['status_id']]]++;
			if ($row['paid']) { $this->enrollments['paid']++; }
		}
		return $this->enrollments;
	}
	
	function get_enrollment_ids_for_user(int $uid) {
		$stmt = \DB\pdo_query("select * from registrations where user_id = :uid", array(':uid' => $uid));
		$es = array();
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$es[] = $row['id'];
		}
		return $es;	
	}	
	
	
	function get_status_change_log(?int $wid = null) {

		$sql = "select s.*, u.email, u.display_name, st.status_name, wk.title, wk.start, wk.end  from status_change_log s, users u, statuses st, workshops wk where WORKSHOPMAYBE  s.workshop_id = wk.id and s.user_id = u.id and s.status_id = st.id order by happened desc";
		if ($wid) { 
			// if we got a workshop, only show changes for that
			$sql = preg_replace('/WORKSHOPMAYBE/', " workshop_id = :wid and ", $sql);
			$stmt = \DB\pdo_query($sql, array(':wid' => $wid));
		} else {
			// if we are showing all, only show last 7 days
			$sql = preg_replace('/WORKSHOPMAYBE/', 'wk.start >= "'.date(MYSQL_FORMAT, strtotime("7 days ago")).'" and ', $sql);
			$stmt = \DB\pdo_query($sql);
		}
		
		$log = array();

		$wk = new \Workshop();
		$u = new \User(); // need its methods
		$e = new \Enrollment();
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$row = $wk->format_times_one_level($row);
			$row = $u->set_nice_name_in_row($row);
			$log[] = $row;
		}
		foreach ($log as $id => $l) {
			if ($l['status_id'] == DROPPED) {
				$log[$id]['last_enrolled'] = $this->get_last_enrolled($log, $l['workshop_id'], $l['user_id'], $l['happened']);
			}	
		}
		
		return $log;
	}


	// search through status log results to find most recent enrollment if any
	// for a user in a workshop
	// used to see how long someone was in before they dropped
	function get_last_enrolled(array $log, int $wid = 0, int $uid = 0, string $before = null) {
		if (!$before) {  $before = "now()"; }
		foreach ($log as $l) {
			if ($l['user_id'] == $uid
			&& $l['workshop_id'] == $wid
			&& $l['status_id'] == ENROLLED 
			&& strtotime($l['happened']) < strtotime($before)) {
				return $l['happened'];
			}
		}
		return null;
	}
	
	// returns student basic registration info
	function get_students(int $wid, int $status_id = ENROLLED) {
		$sql = "
		select u.*, r.id as enrollment_id, r.status_id,  r.paid, r.registered, r.last_modified, r.pay_amount, r.pay_when, r.pay_channel, r.pay_override, u.id as user_id
		from registrations r, users u 
		where r.workshop_id = :wid and r.user_id = u.id";
		
		if ($status_id) { 
			$sql .= " and status_id = :sid "; 
			$params = array(':wid' => $wid, ':sid' => $status_id);
		} else {
			$params = array(':wid' => $wid);
		}
		$sql .= " order by last_modified";
		$stmt = \DB\pdo_query($sql, $params);
		
		$stds = array();
		$u = new \User(); // need its methods!
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			if ($row['pay_when'] == '0000-00-00') { $row['pay_when'] = null; }
			$stds[$row['id']] = $u->set_nice_name_in_row($row);
		}
		return $stds;
	}

	function get_transcript_tabled(User $u, bool $admin = false, $hideconpay = 0) {
		global $view, $lookups;
		
		if (!$u->logged_in() || !isset($u->fields['id'])) {
			return "<p>Not logged in!</p>\n";
		}
		$mysqlnow = date(MYSQL_FORMAT);
	
		$sql = "select *, r.id as enrollment_id, r.paid, r.pay_amount, r.pay_when, r.pay_channel 
		from registrations r, workshops w, locations l
		where r.workshop_id = w.id 
		and w.location_id = l.id 
		and r.user_id = :uid ";
		if (!$admin) { 
			$sql .= " and ( (w.start >= :now) || (r.status_id = ".ENROLLED."))";
		} 
		$sql .= " order by w.start desc";
		$params = array(':uid' => $u->fields['id']);
		if (!$admin) { $params[':now'] = $mysqlnow;  }
		
		$stmt = \DB\pdo_query($sql, $params);
	
		// prep data
		$past_classes = array();
		$wk = new \Workshop();
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$row = $wk->format_times_one_level($row);
			$row['costdisplay'] = $wk->figure_costdisplay($row['cost']);
			if ($row['pay_when'] == '0000-00-00') { $row['pay_when'] = null; }
			
			if ($row['teacher_id']) { $row['teacher'] = \Teachers\get_teacher_by_id($row['teacher_id']); }
			if ($row['co_teacher_id']) { $row['co_teacher'] = \Teachers\get_teacher_by_id($row['co_teacher_id']); }
			
			$past_classes[] = $row;
		}
	
		if (count($past_classes) == 0) {
			return "<p>".($admin ? 'This student has '  : 'You have')." not taken any classes! Which is fine, but that's why this list is empty.</p>\n";
		}
	
		$view->data['guest_id'] = $u->fields['id'];
		$view->data['statuses'] = $lookups->statuses;
		$view->data['admin'] = $admin;
		$view->data['rows'] = $past_classes;
		$view->data['hideconpay'] = $hideconpay;
		return $view->renderSnippet('transcript');
	}	

}