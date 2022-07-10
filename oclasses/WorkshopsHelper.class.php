<?php

class WorkshopsHelper extends WBHObject {


	public array $workshops;
	public Workshop $wk;
	
	function __construct() {		
		parent::__construct(); // load logger, lookups
		$this->workshops = array();
		$this->wk = new Workshop();
	}

	// for calendar
	function get_sessions_to_come(bool $get_enrollments = true) {
	
		global $lookups;
	
		// get IDs of workshops
		$mysqlnow = date(MYSQL_FORMAT, strtotime("-3 hours"));
	
		$stmt = \DB\pdo_query("
	(select w.id, title, start, end, capacity, cost, 0 as xtra, 0 as class_show, notes, teacher_id, co_teacher_id, 1 as rank, '' as override_url, online_url, application, w.location_id, w.start as course_start, w.hidden
	from workshops w
	where start >= date('$mysqlnow') ) 
	union
	(select x.workshop_id as id, w.title, x.start, x.end, w.capacity, w.cost, 1 as xtra,  x.class_show, w.notes, w.teacher_id, w.co_teacher_id, x.rank, x.online_url as override_url, w.online_url, w.application, w.location_id, w.start as course_start, w.hidden
	from xtra_sessions x, workshops w 
	where w.id = x.workshop_id and x.start >= date('$mysqlnow') ) 
	order by start asc"); 
	
		$teachers = \Teachers\get_all_teachers(); // avoid getting same teacher multiple times	
		$enrollments = array();
		$total_sessions = array();
		$wk = new \Workshop();
		
		$sessions = array();
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
				
		
			$row['costdisplay'] = $this->wk->figure_costdisplay($row['cost']);
			$row['lwhere'] = $lookups->locations[$row['location_id']]['lwhere'];
			$row = $wk->format_times_one_level($row);

			$row['total_sessions'] = $this->check_total_sessions($row['id'], $total_sessions);
			$row['teacher'] = $this->find_teacher_in_teacher_array($row['teacher_id'], $teachers);
			$row['co_teacher'] = $this->find_teacher_in_teacher_array($row['co_teacher_id'], $teachers);
			
			$row['teacher_name'] = $row['teacher']['nice_name'];
			if (isset($row['co_teacher']['nice_name'])) {
				$row['teacher_name'] .= " & ".$row['co_teacher']['nice_name'];
			}
			
			if ($get_enrollments) {
				$row['enrollments'] = $this->check_enrollments($row['id'], $enrollments);
			}
			
			$sessions[] = $row;
			
		}
		return $sessions;
	}

	// used in "get sessions to come"
	private function find_teacher_in_teacher_array(?int $id = 0, array &$teachers) {
		foreach ($teachers as $tid => $teach) {
			if ($id == $teach['id']) {
				return $teach;
			}
		}
		return array();
	}

	private function check_total_sessions(int $wid, array &$total_sessions) {
		
		if (isset($total_sessions[$wid])) {
			return $total_sessions[$wid];
		}
		$stmt = \DB\pdo_query("select count(*) as total from xtra_sessions where workshop_id = :wid", array(':wid' => $wid));
		
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$total_sessions[$wid] = $row['total']+1;
		}
		return $total_sessions[$wid];
	}

	private function check_enrollments(int $wid, array &$enrollments) {
		if (isset($enrollments[$wid])) {
			return $enrollments[$wid];
		}
		$eh = new EnrollmentsHelper();
		$enrollments[$wid] = $eh->set_enrollments_for_workshop($wid);
		return $enrollments[$wid];
	}


	function get_unpaid_students() {
		// get IDs of workshops
		$mysql_lastmonth = date(MYSQL_FORMAT, strtotime("-8 weeks"));
		$mysqlnow = date(MYSQL_FORMAT, strtotime("now"));

		$stmt = \DB\pdo_query("
	select r.workshop_id, w.title, u.email, u.display_name, r.user_id, w.start, w.cost
	from workshops w, registrations r, users u
	where 
	(w.start >= date('$mysql_lastmonth') and w.start <= date('$mysqlnow'))
	and r.workshop_id = w.id
	and r.user_id = u.id
	and r.status_id = ".ENROLLED."
	and r.paid = 0
	and w.cost > 0
	order by w.start");
	
		$unpaid = array();	
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$row['nice_name'] = ($row['display_name'] ? $row['display_name'] : $row['email']);
			$unpaid[] = $row;
		}
		return $unpaid;
	}	

	function get_workshops_list_no_html() {
	
		// get IDs of workshops
		$mysqlnow = date(MYSQL_FORMAT);

		$sql = "select w.* from workshops w where when_public < '$mysqlnow' and start >= '$mysqlnow' and w.hidden = 0 or (CURRENT_DATE() < '2022-06-27' and w.title like '%glendale%') order by start asc";  
	
		$stmt = \DB\pdo_query($sql);
		$workshops = array();
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$wk = new Workshop();
			$wk->set_by_id($row['id']);
			$workshops[] = $wk;
		}

		return $workshops;
	}
	
	function get_unavailable_workshops() {
	
		$mysqlnow = date(MYSQL_FORMAT);
	
		$stmt = \DB\pdo_query("
	select * from workshops where date(start) >= :when1 and when_public >= :when2 and hidden = 0 order by when_public asc, start asc", array(":when1" => $mysqlnow, ":when2" => $mysqlnow)); 
		
		$sessions = array();
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$wk = new Workshop();
			$sessions[] = $wk->set_by_id($row['id']);
		}
		return $sessions;
	
	}

	function get_application_workshops() {
	
		$mysqlnow = date(MYSQL_FORMAT);
	
		$stmt = \DB\pdo_query("select * from workshops where date(start) >= :when1 and when_public < :when2 and application = 1 and hidden = 0 order by start asc", array(":when1" => $mysqlnow, ":when2" => $mysqlnow)); 
		
		$sessions = array();
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$wk = new Workshop();
			$sessions[] = $wk->set_by_id($row['id']);
		}
		return $sessions;
	
	}


	// for "revenues" page
	function get_workshops_list_bydate(?string $start = null, ?string $end = null, bool $byclass = true) {
		if (!$start) { $start = "Jan 1 1000"; }
		if (!$end) { $end = "Dec 31 3000"; }
		
		$start = date("M d Y 00:00", strtotime($start));
		$end = date("M d Y 23:59", strtotime($end));
		
	
		$stmt = \DB\pdo_query("
	select w.* 
	from workshops w 
	where w.start >= :start and w.start <= :end
	order by ".($byclass ? '' : ' teacher_id, co_teacher_id, ')." start desc", 
		array(':start' => date(MYSQL_FORMAT, strtotime($start)), ':end' => date(MYSQL_FORMAT, strtotime($end))));
	
		$workshops = array();
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$wk = new Workshop();
			$wk->set_by_id($row['id']);
			$workshops[$row['id']] = $wk;
		}
		return $workshops;
	}


	// for admins eyes only
	function get_search_results(string $page = "1", ?string $needle = null) {
		global $view;
	
		// get IDs of workshops
		$sql = "select distinct w.* from workshops w ";
		if ($needle) { 
			$sql .= " , teachers t, users u 
			where (w.teacher_id = t.id or w.co_teacher_id = t.id) 
			and t.user_id = u.id
			and (w.title like '%$needle%' or u.display_name like '%$needle%')"; 
		}
		$sql .= " order by start desc"; // get all
		
		// prep paginator
		$paginator  = new \Paginator( $sql );
		$rows = $paginator->getData($page);
		$links = $paginator->createLinks(7, 'search results', $needle ? "&needle=".urlencode($needle) : null);

		// calculate enrollments, etc
		if ($rows->total > 0) {
			$workshops = array();
			foreach ($rows->data as $row ) {
				$wk = new Workshop();
				$workshops[] = $wk->set_by_id($row['id']);
			}
		} else {
			return "<p>No upcoming workshops!</p>\n";	// this skips $body variable contents	
		}
		
		// prep view
		$view->data['links'] = $links;
		$view->data['rows'] = $workshops;
		return $view->renderSnippet('admin/search_workshops');	
	}

	function get_workshops_dropdown(?string $start = null, ?string $end = null) {
	
		$stmt = \DB\pdo_query("select w.* from workshops w order by start desc");
		$workshops = array();
		$wk = new Workshop();
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$row = $wk->format_times_one_level($row);
			$workshops[$row['id']] = $row['title'].' ('.date('Y-M-d', strtotime($row['start'])).')';
		}
		return $workshops;
	}


	function get_recent_workshops_simple(?int $limit = 50) {
	
		$stmt = \DB\pdo_query("
			select w.*, u.display_name as teacher_name
			from workshops w, users u, teachers t
			where w.teacher_id = t.id
			and t.user_id = u.id
			order by w.id desc limit $limit");
		$all = array();
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$all[$row['id']] = $row;
		}
		return $all;
	}


	function get_recent_workshops_dropdown(int $limit = 40) {
		$stmt = \DB\pdo_query("select * from workshops order by id desc limit $limit");
		$all = array();
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$all[$row['id']] = $row['title']. ' ('.	\Wbhkit\friendly_date($row['start']).' '.\Wbhkit\friendly_time($row['start']).')';
		}
		return $all;
	}


	function get_recent_bitness(int $limit = 6) {
		$stmt = \DB\pdo_query("select * from workshops where title like '%bitness%' order by id desc limit $limit");
		$all = array();
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$wk = new Workshop();
			$wk->set_by_id($row['id']);
			$all[$row['id']] = $wk;
		}
		return $all;
	}

}
	
?>