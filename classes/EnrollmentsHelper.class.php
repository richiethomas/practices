<?php
	
class EnrollmentsHelper extends WBHObject {
	
	public array $enrollments;
	
	function __construct() {		
		parent::__construct(); // load logger, lookups
		$this->enrollments = array();
	}
	
	// registrations
	function set_enrollments_for_workshop(int $workshop_id) {
	
		$stmt = \DB\pdo_query("select count(*) as total, status_id from registrations where workshop_id = :wid group by status_id", array(':wid' => $workshop_id));
		while ($row = $stmt->fetch()) {
			$this->enrollments[$row['status_id']] = $row['total'];
		}
		foreach ($this->lookups->statuses as $sid => $sname) {
			if (!isset($this->enrollments[$sid])) { $this->enrollments[$sid] = 0; }		
		}
		return $this->enrollments;
	}
	
	function get_enrollment_ids_for_user(int $uid) {
		$stmt = \DB\pdo_query("select * from registrations where user_id = :uid", array(':uid' => $uid));
		$es = array();
		while ($row = $stmt->fetch()) {
			$es[] = $row['id'];
		}
		return $es;	
	}	
	
	
	function get_status_change_log(array $wk = null) {

		$sql = "select s.*, u.email, u.display_name, st.status_name, wk.title, wk.start, wk.end, wk.cancelled from status_change_log s, users u, statuses st, workshops wk where WORKSHOPMAYBE  s.workshop_id = wk.id and s.user_id = u.id and s.status_id = st.id order by happened desc";
		if ($wk) { 
			$sql = preg_replace('/WORKSHOPMAYBE/', " workshop_id = :wid and ", $sql);
			$stmt = \DB\pdo_query($sql, array(':wid' => $wk['id']));
		} else {
			$sql = preg_replace('/WORKSHOPMAYBE/', '', $sql);
			$stmt = \DB\pdo_query($sql);
		}
		
		$log = array();

		$u = new \User(); // need its methods
		$e = new \Enrollment();
		while ($row = $stmt->fetch()) {
			$row = \Workshops\format_workshop_startend($row);
			$row = $u->set_nice_name_in_row($row);
			if (!$wk) {
				// skip old ones for the global change log
				if (strtotime($row['start']) < strtotime("24 hours ago")) {
					continue;
				}
			}
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
	
	function get_students(int $wid, int $status_id = ENROLLED) {
		$sql = "select u.*, r.status_id,  r.paid, r.registered, r.last_modified  from registrations r, users u where r.workshop_id = :wid and r.user_id = u.id ";
		if ($status_id) { 
			$sql .= " and status_id = :sid order by last_modified"; 
			$stmt = \DB\pdo_query($sql, array(':wid' => $wid, ':sid' => $status_id));
		} else {
			$sql .= " order by last_modified"; 
			$stmt = \DB\pdo_query($sql, array(':wid' => $wid));
		}
		$stds = array();
		$u = new \User(); // need its methods!
		while ($row = $stmt->fetch()) {
			$stds[$row['id']] = $u->set_nice_name_in_row($row);
		}
		return $stds;
	}
	
	function get_transcript_tabled(User $u, bool $admin = false, int $page = 1) {
		global $view;
		if (!$u->logged_in() || !isset($u->fields['id'])) {
			return "<p>Not logged in!</p>\n";
		}
		$mysqlnow = date("Y-m-d H:i:s");
	
		$sql = "select *, r.id as enrollment_id 
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
			return "<p>You have not taken any practices! Which is fine, but that's why this list is empty.</p>\n";
		}
	
		// prep data
		$links = $paginator->createLinks();
		$past_classes = array();
		$e = new Enrollment();
		foreach ($rows->data as $d) {
		
			// build a workshop array from data we have
			$wk_fields = \Workshops\get_empty_workshop();
			foreach ($wk_fields as $field => $fieldvalue) {
				$wk[$field] = $d[$field];
			}
			$wk['id'] = $d['workshop_id']; // make sure id is correct
			$wk = \Workshops\fill_out_workshop_row($wk);
			$d['soldout'] = $wk['soldout'];
			$d['upcoming'] = $wk['upcoming'];
			$d['when'] = $wk['when'];
			$d['sessions'] = $wk['sessions'];
			$d['teacher_name'] = $wk['teacher_name'];
			$d['teacher_id'] = $wk['teacher_id'];
			if ($d['status_id'] == WAITING) {
				$e->set_by_u_wk($u, $wk); 
				$d['rank'] = $e->fields['rank']; 
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
		return $view->renderSnippet('transcript');
	}	

}