<?php
	
	
// to do:
// get rid of "dropping last minute" logic? it's 24 hours or it's not
// get rid of "when is next start time?" shouldn't be done here	

class Workshop extends WBHObject {
	
	function __construct() {		
		parent::__construct(); // load logger, lookups

		$fields = array(
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
				'sold_out_late' => null,
				'cancelled' => null,
				'teacher_id' => 1,
				'reminder_sent' => 0
			);
		$this->set_into_fields($fields);

	}
	
	// workshops
	//formerly get_workshop_info
	function set_by_id(int $id) {

		$stmt = \DB\pdo_query("select w.* from workshops w where w.id = :id", array(':id' => $id));
		while ($row = $stmt->fetch()) {
			$this->set_into_fields($row);
			$this->fill_out_workshop();
			return true;
		}
		$this->error = "No workshop found for id '{$id}'";
		return false;
	}

	// formerly fill_out_workshop_row
	function fill_out_workshop(bool $get_enrollment_stats = true) {
		
		
		$this->fields[$loc_fields] = $this->lookups->locations[$loc_field];
		
		//foreach (array('address', 'city', 'state', 'zip', 'place', 'lwhere') as $loc_field) {
		//	$this->fields[$loc_field] = $this->lookups->locations[$this->fields['location_id']][$loc_field];
		//}
		
		if ($this->fields['when_public'] == 0 ) {
			$this->fields['when_public'] = '';
		}
		$this->fields['soldout'] = 0; // so many places in the code refer to this
		$this->fields = $this->format_workshop_startend();
	
	
	
		$this->fields['teacher'] = $trow = \Teachers\get_teacher_by_id($row['teacher_id']);

		//$row['teacher_email'] = $trow['email'];
		//$row['teacher_name'] = $trow['nice_name'];
		//$row['teacher_user_id'] = $trow['user_id'];
		//$row['teacher_id'] = $trow['id'];
		//$row['teacher_key'] = $trow['ukey'];
		
		$this->fields['costdisplay'] = $this->fields['cost'] ? "\${$this->fields['cost']} USD" : 'Free';
	
		// xtra session info
		$this->fields['sessions'] = \XtraSessions\get_xtra_sessions($row['id']);	
		$this->fields['total_class_sessions'] = 1;
		$this->fields['total_show_sessions'] = 0;
		foreach ($this->fields['sessions'] as $sess) {
			if ($sess['class_show']) {
				$this->fields['total_show_sessions']++;
			} else {
				$this->fields['total_class_sessions']++;
			}
		}
		$this->fields['total_sessions'] = $this->fields['total_class_sessions'] + $this->fields['total_show_sessions'];
	
		// when is the next starting session
		// if all are in past, set this to most recent one
		$this->fields['nextstart_raw'] = $this->fields['start'];
		$this->fields['nextend_raw'] = $this->fields['end'];
		$this->fields['nextstart_url'] = $this->fields['online_url'];
		$this->fields['nextsession_show'] = 0;
		$this->fields['nextsession_extra'] = 0;
		if (!\Wbhkit\is_future($this->fields['nextstart_raw'])) {
			foreach ($this->fields['sessions'] as $s) {
				if (\Wbhkit\is_future($s['start'])) {
					$this->fields['nextsesssion_extra'] = 1;
					$this->fields['nextstart_raw'] = $s['start'];
					$this->fields['nextend_raw'] = $s['end'];
					if ($s['online_url']) { $this->fields['nextstart_url'] = $s['online_url']; }
					if ($s['class_show'] == 1) { $this->fields['nextsession_show'] = 1; }
					break; // found the next start
				}
			}
		}
		// now that we've found it, format it nicely
		$this->fields['nextstart'] = \Wbhkit\friendly_when($row['nextstart_raw']);
		$this->fields['nextend'] = \Wbhkit\friendly_when($row['nextend_raw']);
		
		if (strtotime($this->fields['end']) >= strtotime('now')) { 
			$this->fields['upcoming'] = 1; 
		} else {
			$this->fields['upcoming'] = 0;
		}
		$this->check_last_minuteness();
	
		if ($get_enrollment_stats) {
			$this->set_enrollment_stats();
			$this->fields['paid'] = $this->how_many_paid();
			if ($this->fields['enrolled'] + $this->fields['waiting'] + $this->fields['invited'] >= $this->fields['capacity']) { 
				$this->fields['soldout'] = 1;
			} else {
				$this->fields['soldout'] = 0;
			}	
		}
		return $this;
	}

	// pass in the workshop row as it comes from the database table
	// add some columns with date / time stuff figured out
	function format_workshop_startend() {
		$this->fields['showstart'] = \Wbhkit\friendly_date($this->fields['start']).' '.\Wbhkit\friendly_time($this->fields['start']);
		$this->fields['showend'] = \Wbhkit\friendly_time($row['end']);
		$this->fields['when'] = "{$this->fields['showstart']}-{$this->fields['showend']}";
		return true;
	}

	// used in fill_out_workshop_row and also get_sessions_to_come
	// expects 'id' and 'capacity' to be set
	function set_enrollment_stats() {
	
		$eh = new \EnrollmentsHelper();
	
		$enrollments = $eh->set_enrollments_for_workshop($this->fields['id']);
		foreach ($lookups->statuses as $sid => $sname) {
			$this->fields[$sname] = $enrollments[$sid];
		}	
		$this->fields['open'] = ($this->fields['enrolled'] >= $this->fields['capacity'] ? 0 : $this->fields['capacity'] - $this->fields['enrolled']);
		return true;
	}


	function check_last_minuteness() {
	
		/* 
			there's two flags:
				1) workshops have "sold_out_late" meaning the workshop was sold out within LATE_HOURS of the start. We update this to 1 or 0 everytime the web site selects the workshop info from the db.
				2) registrations have a "while_sold_out" flag. if it is set to 1, then you were enrolled in this workshop while it was sold_out_late (i.e. sold out within $late_hours of its start). we also check this every time we select the workshop info. but this never gets set back to zero. 
				If a "while sold out" person drops, that's not cool. They held a spot during a sold out time close to the start of the workshop.
		*/ 
			
		$hours_left = (strtotime($this->fields['start']) - strtotime('now')) / 3600;
		if ($hours_left > 0 && $hours_left < LATE_HOURS) {
			// have we never checked if it's sold out
			if ($this->fields['sold_out_late'] == -1) {
				if ($this->fields['soldout'] == 1) {
				
					$stmt = \DB\pdo_query("update workshops set sold_out_late = 1 where id = :wid", array(':wid' => $this->fields['id']));				

					$stmt = \DB\pdo_query("update registrations set while_soldout = 1 where workshop_id = :wid and status_id = '".ENROLLED."'", array(':wid' => $this->fields['id']));
				
					$this->fields['sold_out_late'] = 1;
				} else {

					$stmt = \DB\pdo_query("update workshops set sold_out_late = 0 where id = :wid", array(':wid' => $this->fields['id']));

					$this->fields['sold_out_late'] = 0;
				}
			}
		}
		return true;
	}


	function how_many_paid() {
		$stmt = \DB\pdo_query("select count(*) as total_paid from registrations where workshop_id = :wid and paid = 1", array(':wid' => $this->fields['id']));
		while ($row = $stmt->fetch()) {
			return $row['total_paid'];
		}
		return 0;
	}




	function add_workshop_form() {
		return "<form id='add_wk' action='admin_edit.php' method='post' novalidate>".
		\Wbhkit\form_validation_javascript('add_wk').
		"<fieldset name='session_add'><legend>Add Workshop</legend>".
		\Wbhkit\hidden('ac', 'ad').
		$this->workshop_fields().
		\Wbhkit\submit('Add').
		"</fieldset></form>";
	
	}

	function workshop_fields() {
		
		return \Wbhkit\texty('title', $this->fields['title'], null, null, null, 'Required', ' required ').
		\Wbhkit\drop('lid', $this->lookups->locations_drop(), $this->fields['location_id'], 'Location', null, 'Required', ' required ').
		\Wbhkit\texty('online_url', $this->fields['online_url'], 'Online URL').	
		\Wbhkit\texty('start', $this->fields['start'], null, null, null, 'Required', ' required ').
		\Wbhkit\texty('end', $this->fields['end'], null, null, null, 'Required', ' required ').
		\Wbhkit\texty('cost', $this->fields['cost']).
		\Wbhkit\texty('capacity', $this->fields['capacity']).
		\Wbhkit\textarea('notes', $this->fields['notes']).
		\Wbhkit\drop('teacher_id', \Teachers\teachers_dropdown_array(), $this->fields['teacher_id'], 'Teacher', null, 'Required', ' required ').
		\Wbhkit\checkbox('cancelled', 1, null, $this->fields['cancelled']).	
		\Wbhkit\texty('when_public', $this->fields['when_public'], 'When Public').
		\Wbhkit\checkbox('reminder_sent', 1, 'Reminder sent?', $this->fields['reminder_sent']);
	
	}

	// $ac can be 'up' or 'ad'
	function add_update_workshop(string $ac = 'up') {
	
		global $last_insert_id;
	
		// fussy MySQL 5.7
		if (!$this->fields['cancelled']) { $this->fields['cancelled'] = 0; }
		if (!$this->fields['cost']) { $this->fields['cost'] = 0; }
		if (!$this->fields['capacity']) { $this->fields['capacity'] = 0; }
		if (!$this->fields['when_public']) { $this->fields['when_public'] = NULL; }
		if (!$this->fields['start']) { $this->fields['start'] = NULL; }
		if (!$this->fields['end']) { $this->fields['end'] = NULL; }
		if (!$this->fields['teacher_id']) { $this->fields['teacher_id'] = 1; }
		if (!$this->fields['reminder_sent']) { $this->fields['reminder_sent'] = 0; }
	
		
		$params = array(':title' => $this->fields['title'],
			':start' => date('Y-m-d H:i:s', strtotime($this->fields['start'])),
			':end' => date('Y-m-d H:i:s', strtotime($this->fields['end'])),
			':cost' => $this->fields['cost'],
			':capacity' => $this->fields['capacity'],
			':lid' => $this->fields['location_id'],
			':online_url' => $this->fields['online_url'],
			':notes' => $this->fields['notes'],
			':public' => date('Y-m-d H:i:s', strtotime($this->fields['when_public'])),
			':cancelled' => $this->fields['cancelled'],
			':tid' => $this->fields['teacher_id'],
			':reminder_sent' => $this->fields['reminder_sent']);
		
			if ($ac == 'up') {
				$params[':wid'] = $this->fields['id'];
				$sql = "update workshops set title = :title, start = :start, end = :end, cost = :cost, capacity = :capacity, location_id = :lid, online_url = :online_url, notes = :notes, when_public = :public, cancelled = :cancelled, reminder_sent = :reminder_sent, teacher_id = :tid where id = :wid";			
				$stmt = \DB\pdo_query($sql, $params);
				return $this->fields['id'];
			} elseif ($ac = 'ad') {
				$stmt = \DB\pdo_query("insert into workshops (title, start, end, cost, capacity, location_id, online_url, notes, when_public, cancelled, reminder_sent, teacher_id)
				VALUES (:title, :start, :end, :cost, :capacity, :lid, :online_url, :notes,  :public, :cancelled, :reminder_sent, :tid)",
				$params);
				return $last_insert_id; // set as a global by my dbo routines
			}
	
	}

	function delete_workshop() {
		$stmt = \DB\pdo_query("delete from registrations where workshop_id = :wid", array(':wid' => $this->fields['id']));
		$stmt = \DB\pdo_query("delete from xtra_sessions where workshop_id = :wid", array(':wid' => $this->fields['id']));
		$stmt = \DB\pdo_query("delete from workshops where id = :wid", array(':wid' => $this->fields['id']));

	}

	function is_public() {
		if (isset($this->fields['when_public']) && $this->fields['when_public'] && strtotime($this->fields['when_public']) > time()) {
			return false;
		}
		return true;
	}

	function is_complete_workshop() {
		if (is_array($this->fields) && isset($this->fields['id']) && $this->fields['id'] > 0) {
			return true;
		}
		return false;
	}

	function get_cut_and_paste_roster(bool $enrolled = null) {
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
		if (!empty($this->fields['sessions'])) {
			foreach ($this->fields['sessions'] as $s) {
				$class_dates .= "{$s['friendly_when']}".($s['class_show'] ? ' (show)': '').
				($s['online_url'] ? " - {$s['online_url']}" : '')."\n";
			}
		}
		if ($class_dates) {
			$class_dates = "\n\nClass Sessions:\n(some sessions may their own zoom links)\n------------\n{$class_dates}";
		}
	
		return 
			preg_replace("/\n\n+/", 
						"\n\n", 
						"{$this->fields['title']} - {$this->fields['showstart']}\n\n".
						"Main zoom link:\n".($this->fields['location_id'] == ONLINE_LOCATION_ID ? "{$wk['online_url']}\n" : '').
							$class_dates.
						"\nNames and Emails\n---------------\n".implode("\n", $names)."\n\nJust the emails\n---------------\n".implode(",\n", $just_emails));
	
	}

}

