<?php

// code that works with groups of workshops

class WorkshopsHelper extends WBHObject {
	
	function __construct() {		
		parent::__construct(); // load logger, lookups
	}

	function get_unavailable_workshops() {
	
		$wk = new Workshop();
		$mysqlnow = date("Y-m-d H:i:s");
	
		$stmt = \DB\pdo_query("
	select * from workshops where date(start) >= :when1 and when_public >= :when2 order by when_public asc, start asc", array(":when1" => $mysqlnow, ":when2" => $mysqlnow)); 
		
		$sessions = array();
		while ($row = $stmt->fetch()) {
			$wk->set_into_fields($row);
			$wk->fill_out_workshop();
			$sessions[] = $wk;
		}
		return $sessions;
	}
	
	function get_workshops_dropdown(string $start = null, string $end = null) {
	
		$stmt = \DB\pdo_query("select w.* from workshops w order by start desc");
		$workshops = array();
		while ($row = $stmt->fetch()) {
			$wk = new Workshop();
			$wk->set_into_fields($row);
			$wk->format_workshop_startend($row);
			$workshops[$row['id']] = $wk->fields['title'];
		}
		return $workshops;
	}

	// for admins eyes only
	function get_search_results(int $page = 1, string $needle = null) {
		global $view;
	
		// get IDs of workshops
		$sql = "select w.* from workshops w ";
		if ($needle) { $sql .= " where w.title like '%$needle%' "; }
		$sql .= " order by start desc"; // get all
		
		// prep paginator
		$paginator  = new \Paginator( $sql );
		$rows = $paginator->getData($page);
		$links = $paginator->createLinks(7, 'search results', "&needle=".urlencode($needle));

		// calculate enrollments, ranks, etc
		if ($rows->total > 0) {
			$workshops = array();
			foreach ($rows->data as $row ) {
				$wk = new Workshop();
				$wk->set_into_fields($row);
				$workshops[] = $wk->fill_out_workshop();
			}
		} else {
			return "<p>No upcoming workshops!</p>\n";	// this skips $body variable contents	
		}
		
		// prep view
		$view->data['links'] = $links;
		$view->data['rows'] = $workshops;
		return $view->renderSnippet('admin/search_workshops');	
	}	


	function get_workshops_list_no_html(int $admin = 0, int $page = 1) {
	
		$sql = $this->build_workshop_list_sql($admin);
	
		$stmt = \DB\pdo_query($sql);
		$workshops = array();
		while ($row = $stmt->fetch()) {
			$wk = new Workshop();
			$wk->set_into_fields($row);
			$workshops[] = $wk->fill_out_workshop();
		}

		return $workshops;
	}


	private function build_workshop_list_sql(int $admin) {
	
		// get IDs of workshops
		$mysqlnow = date("Y-m-d H:i:s");

		$sql = '(select w.* from workshops w ';
		if (!$admin) {
			$sql .= "where when_public < '$mysqlnow' and date(start) >= date('$mysqlnow')"; // get public ones to come
		}
		$sql .= ")"; // end first select statement of UNION
	
		// second select: search xtra sessions. 
		// UNION should automatically remove duplicate rows,
		// so multiple workshop/xtra sessions should only show up once in results?
		$sql .=
			"UNION
			(select w.* from workshops w, xtra_sessions x where x.workshop_id = w.id
		and w.when_public < '$mysqlnow' and date(x.start) >= date('$mysqlnow'))";  

		if ($admin) {
			$sql .= " order by start desc"; // get all
		} else {
			$sql .= " order by start asc";  // temporary, should be asc
		}

		return $sql;
	
	
	}


	// data only, for admin_calendar
	function get_sessions_to_come() {
	
		// get IDs of workshops
		$mysqlnow = date("Y-m-d H:i:s", strtotime("-3 hours"));
	
		$stmt = \DB\pdo_query("
	(select w.id, title, start, end, capacity, cost, 0 as xtra, 0 as class_show, notes, teacher_id, 1 as rank, '' as override_url, online_url 
	from workshops w
	where start >= date('$mysqlnow'))
	union
	(select x.workshop_id, w.title, x.start, x.end, w.capacity, w.cost, 1 as xtra, x.class_show, w.notes, w.teacher_id, x.rank, x.online_url as override_url, w.online_url
	from xtra_sessions x, workshops w 
	where w.id = x.workshop_id and x.start >= date('$mysqlnow'))
	order by start asc"); 
		
		
		$teachers = \Teachers\get_all_teachers(); // avoid getting same teacher multiple times	
		$sessions = array();
		while ($row = $stmt->fetch()) {
			$teach = \Teachers\find_teacher_in_teacher_array($row['teacher_id'], $teachers);
			if ($teach) {
				$row['teacher_name'] = $teach['nice_name'];
				$row['teacher_user_id'] = $teach['user_id'];
			}
			$wk = new Workshop()l
			$wk->set_into_fields($row);
			$wk->fields['paid'] = $wk->how_many_paid($row);
			$wk->set_enrollment_stats();
			$sessions[] = $wk;
		}
		return $sessions;
	}

	// for "revenues" page
	function get_workshops_list_bydate(string $start = null, string $end = null) {
		if (!$start) { $start = "Jan 1 1000"; }
		if (!$end) { $end = "Dec 31 3000"; }
	
		//echo "select w.* from workshops w WHERE w.start >= '".date('Y-m-d H:i:s', strtotime($start))."' and w.end <= '".date('Y-m-d H:i:s', strtotime($end))."' order by start desc";
	
		$stmt = \DB\pdo_query("select w.* from workshops w WHERE w.start >= :start and w.end <= :end order by teacher_id, start desc", array(':start' => date('Y-m-d H:i:s', strtotime($start)), ':end' => date('Y-m-d H:i:s', strtotime($end))));
	
		$workshops = array();
		while ($row = $stmt->fetch()) {
			$wk = new Workshop();
			$wk->set_into_fields($row);
			$workshops[$row['id']] = $wk->fill_out_workshop();
		}
		return $workshops;
	}	

	// for "payroll" page
	function get_sessions_bydate(string $start = null, string $end = null) {
		if (!$start) { $start = "Jan 1 1000"; }
		if (!$end) { $end = "Dec 31 3000"; }
	
		//echo "select w.* from workshops w WHERE w.start >= '".date('Y-m-d H:i:s', strtotime($start))."' and w.end <= '".date('Y-m-d H:i:s', strtotime($end))."' order by start desc";
	
		// get IDs of workshops
		$mysqlstart = date("Y-m-d H:i:s", strtotime($start));
		$mysqlend = date("Y-m-d H:i:s", strtotime($end));
		$mysqlnow = date("Y-m-d H:i:s", strtotime("-3 hours"));
	
		$stmt = \DB\pdo_query("
	(select w.id, 0 as xtra_id, title, start, end, capacity, cost, 0 as xtra, 0 as class_show, notes, teacher_id, 1 as rank, '' as override_url, online_url, when_teacher_paid, actual_pay
		from workshops w 
		where w.start >= :start1 and w.end <= :end1)
	union
	(select x.workshop_id, x.id as xtra_id, w.title, x.start, x.end, w.capacity, w.cost, 1 as xtra, x.class_show, w.notes, w.teacher_id, x.rank, x.online_url as override_url, w.online_url, x.when_teacher_paid, x.actual_pay from xtra_sessions x, workshops w, users u where w.id = x.workshop_id and x.start >= :start2 and x.end <= :end2)
	order by teacher_id, start asc",
	array(':start1' => $mysqlstart,
	':end1' => $mysqlend,
	'start2' => $mysqlstart,
	'end2' => $mysqlend)); 	
	
	//	$stmt = \DB\pdo_query("select w.* from workshops w WHERE w.start >= :start and w.end <= :end order by teacher_id, start desc", array(':start' => date('Y-m-d H:i:s', strtotime($start)), ':end' => date('Y-m-d H:i:s', strtotime($end))));
	
		$sessions = array();
		while ($row = $stmt->fetch()) {
			$t = \Teachers\get_teacher_by_id($row['teacher_id']);
			$row['teacher_name'] = $t['nice_name']; 
			$row['teacher_default_rate'] = $t['default_rate'];
			$sessions[] = $row;
		}
		return $sessions;
	}	

	
}	



	
?>