<?php
	
class EnrollmentsHelper extends WBHObject {
	
	public array $enrollments;
	
	function __construct() {		
		parent::__construct(); // load logger, lookups
		$this->enrollments = array();
	}
	
	// registrations
	function set_enrollments_for_workshop(int $workshop_id) {
	
		// set enrollment data to zero
		$this->enrollments = array();
		foreach ($this->lookups->statuses as $sid => $sname) {
			$this->enrollments[$sname] = 0;
		}
		$this->enrollments['paid'] = 0;
	
		$stmt = \DB\pdo_query("select r.status_id, r.paid from registrations r where r.workshop_id = :wid", array(':wid' => $workshop_id));
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$this->enrollments[$this->lookups->statuses[$row['status_id']]]++;
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
	
	
	function get_status_change_log(array $wk = null) {

		$sql = "select s.*, u.email, u.display_name, st.status_name, wk.title, wk.start, wk.end  from status_change_log s, users u, statuses st, workshops wk where WORKSHOPMAYBE  s.workshop_id = wk.id and s.user_id = u.id and s.status_id = st.id order by happened desc";
		if (isset($wk['id']) && $wk['id']) { 
			// if we got a workshop, only show changes for that
			$sql = preg_replace('/WORKSHOPMAYBE/', " workshop_id = :wid and ", $sql);
			$stmt = \DB\pdo_query($sql, array(':wid' => $wk['id']));
		} else {
			// if we are showing all, only show last 3 days
			$sql = preg_replace('/WORKSHOPMAYBE/', 'wk.start >= "'.date("Y-m-d H:i:s", strtotime("7 days ago")).'" and ', $sql);
			$stmt = \DB\pdo_query($sql);
		}
		
		$log = array();

		$u = new \User(); // need its methods
		$e = new \Enrollment();
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$row = \Workshops\format_workshop_startend($row);
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
		$sql = "select u.*, r.id as enrollment_id, r.status_id,  r.paid, r.registered, r.last_modified, r.pay_override from registrations r, users u where r.workshop_id = :wid and r.user_id = u.id";
		if ($status_id) { 
			$sql .= " and status_id = :sid order by last_modified"; 
			$stmt = \DB\pdo_query($sql, array(':wid' => $wid, ':sid' => $status_id));
		} else {
			$sql .= " order by last_modified"; 
			$stmt = \DB\pdo_query($sql, array(':wid' => $wid));
		}
		$stds = array();
		$u = new \User(); // need its methods!
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$stds[$row['id']] = $u->set_nice_name_in_row($row);
		}
		return $stds;
	}

	function get_transcript_tabled(User $u, bool $admin = false, int $page = 1, $hideconpay = 0) {
		global $view;
		if (!$u->logged_in() || !isset($u->fields['id'])) {
			return "<p>Not logged in!</p>\n";
		}
		$mysqlnow = date("Y-m-d H:i:s");
	
		$sql = "select *, r.id as enrollment_id, r.paid, r.pay_override 
		from registrations r, workshops w, locations l
		where r.workshop_id = w.id 
		and w.location_id = l.id 
		and r.user_id = :uid 
		and ( (w.start >= :now) || (r.status_id = ".ENROLLED.") ) 
		order by w.start desc";
		$params = array(':uid' => $u->fields['id'], ':now' => $mysqlnow);
	
		// rank
		$paginator  = new \Paginator( $sql, $params );
		$rows = $paginator->getData($page);	
		if (count($rows->data) == 0) {
			return "<p>".($admin ? 'This student has '  : 'You have')." not taken any classes! Which is fine, but that's why this list is empty.</p>\n";
		}
	
		// prep data
		$links = $paginator->createLinks();
		$past_classes = array();
		$e = new Enrollment();
		foreach ($rows->data as $d) {
			$d = \Workshops\fill_out_workshop_row($d, false);
			
//			print_r($d);
			
			if ($d['status_id'] == WAITING) {
				$e->fields['id'] = $d['enrollment_id'];
				$e->fields['workshops_id'] = $d['id']; 
				$d['rank'] = null;
			} else {
				$d['rank'] = null;
			}
			$past_classes[] = $d;
		}
	
		$view->data['guest_id'] = $u->fields['id'];
		$view->data['statuses'] = $this->lookups->statuses;
		$view->data['admin'] = $admin;
		$view->data['links'] = $links;
		$view->data['rows'] = $past_classes;
		$view->data['hideconpay'] = $hideconpay;
		return $view->renderSnippet('transcript');
	}	

}