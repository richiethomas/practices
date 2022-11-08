<?php

class Workshop extends WBHObject {
	
	public array $teacher;
	public array $coteacher;
	public array $url;
	public array $location;
	public array $sessions;
	
	function __construct() {		
		parent::__construct(); // load logger, lookups
		
		$this->tablename = "workshops";

		$this->fields = array(
			'id' => null,
			'title' => null,
			'location_id' => null,
			'online_url' => null,
			'start' => null,
			'end' => null,
			'cost' => null,
			'capacity' => null,
			'notes' => null,
			'when_public' => null,
			'teacher_id' => 1,
			'co_teacher_id' => null,
			'reminder_sent' => 0,
			'application' => 0,
			'hidden' => 0,
			'tags' => null
		);
				
		$this->cols = $this->fields; // preserve db cols, we'll add more to fields after this
		$this->teacher = array();
		$this->coteacher = array();
		$this->url = array();
		$this->location = array();
		$this->sessions = array();

	}


	function finish_setup() {
		
		global $lookups;
				
		// replace references to this to use location directly
		$this->location = $lookups->locations[$this->fields['location_id']];
		
		if ($this->fields['when_public'] == 0 ) {
			$this->fields['when_public'] = '';
		}
		$this->fields['soldout'] = 0; // so many places in the code refer to this
	
	
		// create short title if it's more then 2 words
		$this->fields['short_title'] = $this->fields['title'];
	    if (str_word_count($this->fields['short_title'], 0) > 2) {
	        $words = str_word_count($this->fields['short_title'], 2);
	        $pos   = array_keys($words);
	        $this->fields['short_title']  = substr($this->fields['short_title'], 0, $pos[2]);
	    }
	
		$this->teacher = \Teachers\get_teacher_by_id($this->fields['teacher_id']);
		$this->coteacher = \Teachers\get_teacher_by_id($this->fields['co_teacher_id']);
		$this->fields['teacher_name'] = $this->get_teacher_name(); 
		$this->fields['costdisplay'] = $this->figure_costdisplay($this->fields['cost']);
		$this->url = $this->parse_online_url($this->fields['online_url']);

		// xtra session stuff
		$this->set_xtra_sessions();
		$this->format_times();
		
		$this->set_tags_array();

		// set teacher pay
		$this->set_costs();
		
		$this->fields['actual_revenue'] = $this->get_actual_revenue();
		$this->set_enrollment_stats();
	
		return true;
	}

	function figure_costdisplay(int $cost) {
		if ($cost == 1) {
			return 'Pay what you can';
		} elseif ($cost > 1) {
			return "\${$cost} USD";
		} else {
			return 'Free';
		}
	}


	public function parse_online_url(?string $online_url) {
		
		if (!$online_url) { 
			return array(
				'online_url_just_url' => null,
				'online_url_the_rest' => null,
				'online_url_display' => null); 
		}
	
		preg_match('/^(\S+)\s*([\S\s]*)/', $online_url, $url_parts);
		return array(
			'online_url_just_url' => ($url_parts[1] ?? ''),
			'online_url_the_rest' => preg_replace('/\n/', '<br>', $url_parts[2] ?? ''),
			'online_url_display' => preg_replace('/\n/', '<br>', $online_url)
		);
	
	}

	function set_xtra_sessions() {
		// xtra session info
		$this->sessions = \XtraSessions\get_xtra_sessions($this->fields['id']);	
		$this->fields['total_class_sessions'] = 1;
		$this->fields['total_show_sessions'] = 0;
		$this->fields['total_sessions'] = 1;
		foreach ($this->sessions as $sess) {
			if ($sess['class_show'] == 1) {
				$this->fields['total_show_sessions']++;
			} else {
				$this->fields['total_class_sessions']++;
			}
			$this->fields['total_sessions']++;
		}

		$this->fields['time_summary'] = $this->fields['total_class_sessions'].' class'.\Wbhkit\plural($this->fields['total_class_sessions'], '', 'es');

		if ($this->fields['total_show_sessions'] > 0) {
			$this->fields['time_summary'] .= ', '.$this->fields['total_show_sessions'].' show'.\Wbhkit\plural($this->fields['total_show_sessions']);
		}

		return true;		
	}


	// format times for a flat row of data -- workshop or xtra session
	// formerly format_workshop_startend
	function format_times_one_level(array $row, $tz = null) {
	
		if (!isset($row['start']) || !isset($row['end'])) {
			return $row; // do nothing without start and end 
		}
	
		if (!$tz) {
			global $u;
			$u->set_time_zone();
			$tz = $u->fields['time_zone'];
		}
		$tzadd = " (".\Wbhkit\get_time_zone_friendly($tz).")";
	
		// never touch 'start' or 'end', make new time zone versions
		$row['time_zone_used'] = $tz;
		$row['start_tz'] = \Wbhkit\convert_tz($row['start'], $tz);
		$row['end_tz'] = \Wbhkit\convert_tz($row['end'], $tz);
		$row['when_public_tz'] = ((isset($row['when_public']) && $row['when_public']) ? \Wbhkit\convert_tz($row['when_public'], $tz) : null);
	
		// nicer formatted starts and ends
		$row['showstart'] = \Wbhkit\friendly_date($row['start_tz']).' '.\Wbhkit\friendly_time($row['start_tz']);
		$row['showend'] = \Wbhkit\friendly_time($row['end_tz']);
		$row['when'] = "{$row['showstart']}-{$row['showend']}".$tzadd;
		$row['when_no_tz'] = "{$row['showstart']}-{$row['showend']}";
		$row['showstart'] .= $tzadd;
		$row['showend'] .= $tzadd;	

		// nicer formatted default time zones
		$tzcali = " (".TIME_ZONE.")";
		$row['showstart_cali'] = \Wbhkit\friendly_date($row['start']).' '.\Wbhkit\friendly_time($row['start']);
		$row['showend_cali'] = \Wbhkit\friendly_time($row['end']);
		$row['when_cali'] = "{$row['showstart_cali']}-{$row['showend_cali']}".$tzcali;
		$row['when_cali_no_tz'] = "{$row['showstart_cali']}-{$row['showend_cali']}";
		$row['showstart_cali'] .= $tzcali;
		$row['showend_cali'] .= $tzcali;
	
		// nicer formatted start, end, when_public (times not changed) 
		foreach (array('start', 'end', 'when_public') as $tv) {
			if (isset($row[$tv])) {
				$row[$tv] = \Wbhkit\present_ts($row[$tv]);
			}
		}
		return $row;
	}

	// make 2 lists of all dates for a workshop
	// one for a particular time zone and another for DEFAULT_TIME_ZONE

	function format_times($tz = null) {

		if (!$tz) {
			global $u;
			$u->set_time_zone();
			$tz = $u->fields['time_zone'];
		}
		$tzadd = " (".\Wbhkit\get_time_zone_friendly($tz).")";
	
		$this->fields = $this->format_times_one_level($this->fields, $tz);
		$this->fields['full_when'] = $this->fields['when'];
		$this->fields['full_when_cali'] = $this->fields['when_cali'];
	
		// drill down into xtra_sessions
		if (!empty($this->sessions)) {
			foreach ($this->sessions as $id => $s) {
				if (!isset($s['time_zone_used']) || $s['time_zone_used'] != $tz) {
					$this->sessions[$id] = $s = $this->format_times_one_level($s, $tz); // change row array
				}
				$this->fields['full_when'] .= "<br>\n".
					($s['class_show'] == 1 ? 'Show: ' : '').
					"{$s['when_no_tz']}\n";
			
				$this->fields['full_when_cali'] .= "<br>\n".
					($s['class_show'] == 1 ? 'Show: ' : '').
					"{$s['when_cali_no_tz']}\n";
			}
		}	
		return true;	
	}


	function set_tags_array() {
	
		$tags = array();
		if ($this->fields['tags']) {
			$tags = explode(',', $this->fields['tags']);
			foreach ($tags as $k =>$v) {
				$tags[$k] = strtolower(trim($v));
			}
		}

		if ($this->fields['total_sessions'] == 1 && !strpos($this->fields['title'], 'Bitness')) {
			$tags[] ='workshop';
		} else {
			$tags[] ='multiweek';
		}

		sort($tags);
		$this->fields['tags_array'] = $tags;
	}


	function set_costs() {
	
		$this->fields['teacher_pay'] = $this->get_teacher_pay_by_index($this->teacher);
		$this->fields['co_teacher_pay'] = $this->get_teacher_pay_by_index($this->coteacher);
		$this->fields['total_pay'] = $this->fields['teacher_pay'] + $this->fields['co_teacher_pay'];

		if (!$this->fields['total_pay']) {
			$this->fields['total_pay'] = ($this->fields['total_class_sessions'] * $this->teacher['default_rate']) +
			($this->fields['total_show_sessions'] * ($this->teacher['default_rate'] / 2));
		}

		$ph = new PaymentsHelper();
		$this->fields['total_costs'] = $ph->get_class_costs_total($this->fields['id']);
	
		return true;

	}

	private function get_teacher_pay_by_index(array $teacher) {
		if (isset($teacher['user_id'])) {
			return $this->get_recorded_teacher_pay($teacher['user_id']);
		} else {
			return 0;
		}
	}

	// teacher's user id
	private function get_recorded_teacher_pay(?int $uid = 0) {
	
		if (!$this->fields['id'] || !$uid) {
			return 0;
		}
		$wid = $this->fields['id'];
	
		$sql = "select p.* 
			from payments p
			where p.title = '".TEACHERPAY."' and p.workshop_id = :id and p.user_id = :uid";

		$stmt = \DB\pdo_query($sql, array(':id' => $wid, ':uid' => $uid));
	
		$pay = 0;
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$pay += $row['amount'];
		}
	
		return $pay;
	
	}

	function get_actual_revenue() {
	
		$stmt = \DB\pdo_query("select paid, pay_amount, pay_channel, pay_when from registrations r where r.workshop_id = :wid", array(':wid' => $this->fields['id']));
		$total = 0;
		while ($reg = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			if ($reg['paid'] && $reg['pay_amount']) {
				$total += $reg['pay_amount'];
			}
		}
		return $total;
	}


	// used in fill_out_workshop_row and also get_sessions_to_come
	// expects 'id' and 'capacity' to be set
	function set_enrollment_stats() {

		$eh = new \EnrollmentsHelper();
	
		$enrollments = $eh->set_enrollments_for_workshop($this->fields['id']);
		foreach ($enrollments as $sname => $svalue) {
			$this->fields[$sname] = $svalue;
		}	
	
		$this->fields['open'] = $this->fields['capacity'] - $this->fields['enrolled'];
		if ($this->fields['open'] < 0) { $this->fields['open'] = 0; }
	
		if ($this->fields['enrolled'] >= $this->fields['capacity']) { 
			$this->fields['soldout'] = 1;
		} else {
			$this->fields['soldout'] = 0;
		}	
		
		return true;
	}

	function get_workshop_fields() {
	
		global $lookups;
	
		return \Wbhkit\texty('title', $this->fields['title'], null, null, null, 'Required', ' required ').
		\Wbhkit\texty('tags', $this->fields['tags']).
		\Wbhkit\drop('lid', $lookups->locations_drop(), $this->fields['location_id'], 'Location', null, 'Required', ' required ').
		\Wbhkit\textarea('online_url', $this->fields['online_url'], 'Online URL').	
		\Wbhkit\texty('start', $this->fields['start'], null, null, null, 'Required', ' required ').
		\Wbhkit\texty('end', $this->fields['end'], null, null, null, 'Required', ' required ').
		\Wbhkit\texty('cost', $this->fields['cost']).
		\Wbhkit\texty('capacity', $this->fields['capacity']).
		\Wbhkit\checkbox('application', 1, 'Taking applications', $this->fields['application']).
		\Wbhkit\checkbox('hidden', 1, 'Hidden', $this->fields['hidden']).
		\Wbhkit\textarea('notes', $this->fields['notes']).
		\Wbhkit\drop('teacher_id', \Teachers\teachers_dropdown_array(), $this->fields['teacher_id'], 'Teacher', null, 'Required', ' required ').
		\Wbhkit\drop('co_teacher_id', \Teachers\teachers_dropdown_array(), $this->fields['co_teacher_id'], 'Co-teacher', null).
		\Wbhkit\texty('when_public', $this->fields['when_public'], 'When Public').
		\Wbhkit\checkbox('reminder_sent', 1, 'Reminder sent?', $this->fields['reminder_sent']);
	
	}


	function get_cut_and_paste_roster(?array $enrolled = null) {
		$names = array();
		$just_emails = array();
		$eh = new \EnrollmentsHelper();
	
		if (!isset($enrolled)) {
			$enrolled = $eh->get_students($this->fields['id'], ENROLLED);
		}

		foreach ($enrolled as $s) {
			$names[] = "{$s['nice_name']} {$s['email']}";
			$just_emails[] = "{$s['email']}";
		}
		sort($names);
		sort($just_emails);

	
		$class_dates = $this->fields['when']."\n";
		if (!empty($this->sessions)) {
			foreach ($this->sessions as $s) {
				$class_dates .= 
				($s['class_show'] ? 'Show: ' : '').	
				"{$s['when']}".
				($s['online_url'] ? " - {$s['online_url']}" : '')."\n";
			}
		}
		if ($class_dates) {
			$class_dates = "\n\nClass Sessions:\n------------\n{$class_dates}";
		}


	
		return 
			preg_replace("/\n\n+/", 
						"\n\n", 
						"{$this->fields['title']} - {$this->fields['showstart']}\n\n".
					
							($this->fields['location_id'] == ONLINE_LOCATION_ID ? "Main zoom link: {$this->fields['online_url']}" :
								"Location: {$this->location['lwhere']}").
							$class_dates.
						"\nNames and Emails\n---------------\n".implode("\n", $names)."\n\nJust the emails\n---------------\n".implode(",\n", $just_emails));
	
	}


	function add_workshop_form() {
		return "<form id='add_wk' action='/admin-workshop/ad' method='post' novalidate>".
		\Wbhkit\form_validation_javascript('add_wk').
		"<fieldset name='session_add'><legend>Add Workshop</legend>".
		$this->get_workshop_fields().
		\Wbhkit\submit('Add').
		"</fieldset></form>";
	
	}



	// $ac can be 'up' or 'ad'
	function add_update_workshop(string $ac = 'up') {
	
		global $last_insert_id;
	
		// fussy MySQL 5.7
		if (!$this->fields['cost']) { $this->fields['cost'] = 0; }
		if (!$this->fields['capacity']) { $this->fields['capacity'] = 0; }
		if (!$this->fields['when_public']) { $this->fields['when_public'] = NULL; }
		if (!$this->fields['start']) { $this->fields['start'] = NULL; }
		if (!$this->fields['end']) { $this->fields['end'] = NULL; }
		if (!$this->fields['teacher_id']) { $this->fields['teacher_id'] = 1; }
		if (!$this->fields['co_teacher_id']) { $this->fields['co_teacher_id'] = NULL; }
		if (!$this->fields['reminder_sent']) { $this->fields['reminder_sent'] = 0; }
		if (!$this->fields['application']) { $this->fields['application'] = 0; }
		if (!$this->fields['hidden']) { $this->fields['hidden'] = 0; }
		if (!$this->fields['tags']) { $this->fields['tags'] = null; }
	
		
		$params = array(':title' => $this->fields['title'],
			':start' => date(MYSQL_FORMAT, strtotime($this->fields['start'])),
			':end' => date(MYSQL_FORMAT, strtotime($this->fields['end'])),
			':cost' => $this->fields['cost'],
			':capacity' => $this->fields['capacity'],
			':lid' => $this->fields['location_id'],
			':online_url' => $this->fields['online_url'],
			':notes' => $this->fields['notes'],
			':public' => date(MYSQL_FORMAT, strtotime($this->fields['when_public'])),
			':tid' => $this->fields['teacher_id'],
			':ctid' => $this->fields['co_teacher_id'],
			':reminder_sent' => $this->fields['reminder_sent'],
			':application' => $this->fields['application'],
			':hidden' => $this->fields['hidden'],
			':tags' => $this->fields['tags']
		);
		
			if ($ac == 'up') {
				$params[':wid'] = $this->fields['id'];
				$sql = "update workshops set title = :title, start = :start, end = :end, cost = :cost, capacity = :capacity, location_id = :lid, online_url = :online_url,  notes = :notes, when_public = :public, reminder_sent = :reminder_sent, teacher_id = :tid, co_teacher_id = :ctid, application = :application, hidden = :hidden, tags = :tags where id = :wid";			
				$stmt = \DB\pdo_query($sql, $params);
				return $this->fields['id'];
			} elseif ($ac = 'ad') {
				$stmt = \DB\pdo_query("insert into workshops (title, start, end, cost, capacity, location_id, online_url, notes, when_public, reminder_sent, teacher_id, co_teacher_id, application, hidden, tags)
				VALUES (:title, :start, :end, :cost, :capacity, :lid, :online_url,  :notes,  :public, :reminder_sent, :tid, :ctid, :application, :hidden, :tags)",
				$params);
				return $last_insert_id; // set as a global in db_pdo.php 
			}
	
	}

	function delete_workshop() {
		$workshop_id = $this->fields['id'];

		$stmt = \DB\pdo_query("delete from payments where workshop_id = :wid", array(':wid' => $workshop_id));

		$stmt = \DB\pdo_query("delete from registrations where workshop_id = :wid", array(':wid' => $workshop_id));
		$stmt = \DB\pdo_query("delete from xtra_sessions where workshop_id = :wid", array(':wid' => $workshop_id));
		$stmt = \DB\pdo_query("delete from workshops where id = :wid", array(':wid' => $workshop_id));

	}	


	function print_tags() {
	
		$output = null;	
		foreach ($this->fields['tags_array'] as $tag) {
			$output .= $this->print_a_tag($tag, $tag);
		}

		if ($this->fields['open'] <= 2 && $this->fields['open'] > 0) {
			$output .= $this->print_a_tag("{$this->fields['open']} SPOT".\Wbhkit\plural($this->fields['open'])." LEFT", 'few spots left', 'dangerlight');
		}
	
		return $output;
	}

	function print_a_tag(string $label, string $value, ?string $xtra = "bg-light text-muted") {

		return "<span data-tag='{$value}' class='classtag badge rounded-pill me-1 border $xtra'>".strtoupper($label)."</span>";
	
	}

	function is_public() {
		if (isset($this->fields['when_public']) && $this->fields['when_public'] && strtotime($this->fields['when_public']) > time()) {
			return false;
		}
		return true;
	}

	function is_complete_workshop() {
		if (isset($this->fields['id']) && $this->fields['id']) {
			return true;
		}
		return false;
	}


	function email_teacher_info() {
		$output = null;	
	
		if ($this->teacher['student_email'] || ($this->fields['co_teacher_id'] && $this->coteacher['student_email'])) {
			$output .= "TEACHER CONTACT<br>\n---------------<br>\n";
		}
	
		if ($this->teacher['student_email']) {
			$output .= "If you wish to contact your teacher, their contact email is: {$this->teacher['student_email']}.<br>\n";
		}
		if ($this->fields['co_teacher_id'] && $this->coteacher['student_email']) {
			$output .= "If you wish to contact your co-teacher, their contact email is: {$this->coteacher['student_email']}.<br>\n";
		}
	
		return $output;
	}


	function get_teacher_name() {
		$teacher_name = $this->teacher['nice_name'];
		if ($this->fields['co_teacher_id']) {
			$teacher_name .= " and ".$this->coteacher['nice_name'];
		}
		return $teacher_name;
	}



}



	
?>