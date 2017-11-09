<?php
namespace Workshops;
	


// workshops
function get_workshop_info($id) {
	$sql = "select w.*, l.place, l.lwhere from workshops w LEFT OUTER JOIN locations l on w.location_id = l.id where w.id = ".\Database\mres($id);
	$rows = \Database\mysqli( $sql) or \Database\db_error();
	while ($row = mysqli_fetch_assoc($rows)) {
		
		if ($row['when_public'] == 0 ) {
			$row['when_public'] = '';
		}
		$row = format_workshop_startend($row);		
		$row['enrolled'] = \Enrollments\get_enrollments($id);
		$row['invited'] = \Enrollments\get_enrollments($id, INVITED);
		$row['waiting'] = \Enrollments\get_enrollments($id, WAITING);
		$row['open'] = ($row['enrolled'] >= $row['capacity'] ? 0 : $row['capacity'] - $row['enrolled']);
		if (strtotime($row['start']) < strtotime('now')) { 
			$row['type'] = 'past'; 
		} elseif ($row['enrolled'] >= $row['capacity'] || $row['waiting'] > 0 || $row['invited'] > 0) { 
			$row['type'] = 'soldout'; 
		} else {
			$row['type'] = 'open';
		}
		
		$row = check_last_minuteness($row);
		
		return $row;
	}
	return false;
}

function check_last_minuteness($wk) {
	
	/* 
		there's two flags:
			1) workshops have "sold_out_late" meaning the workshop was sold out within $late_hours of the start. We update this to 1 or 0 everytime the web site selects the workshop info from the db.
			2) registrations have a "while_sold_out" flag. if it is set to 1, then you were enrolled in this workshop while it was sold_out_late (i.e. sold out within $late_hours of its start). we also check this every time we select the workshop info. but this never gets set back to zero. 
			If a "while sold out" person drops, that's not cool. They held a spot during a sold out time close to the start of the workshop.
	*/ 
			
	global $late_hours;
	$hours_left = (strtotime($wk['start']) - strtotime('now')) / 3600;
	if ($hours_left > 0 && $hours_left < $late_hours) {
		// have we never checked if it's sold out
		if ($wk['sold_out_late'] == -1) {
			if ($wk['type'] == 'soldout') {
				$sql = 'update workshops set sold_out_late = 1 where id = '.\Database\mres($wk['id']);
				\Database\mysqli( $sql) or \Database\db_error();
				
				$sql = "update registrations set while_soldout = 1 where workshop_id = ".\Database\mres($wk['id'])." and status_id = '".ENROLLED."'";
				\Database\mysqli( $sql) or \Database\db_error();
				
				$wk['sold_out_late'] = 1;
			} else {
				$sql = 'update workshops set sold_out_late = 0 where id = '.\Database\mres($wk['id']);
				\Database\mysqli( $sql) or \Database\db_error();
				$wk['sold_out_late'] = 0;
			}
		}
	}
	return $wk;
}


function get_workshop_info_tabled($wk) {
	
	$stds = \Enrollments\get_students($wk['id']);

	$snames = array();
	foreach ($stds as $s) {
		if ($s['display_name']) {
			$snames[] = $s['display_name'];
		}
	}
	$known = count($snames);
	
	$names_list = '';
	if ($known) {
		natcasesort($snames);
		$names_list = implode("<br>", $snames);
		if ($known < $wk['enrolled']) {
			$names_list .= "<br>plus ".($wk['enrolled']-$known)." more.";
		}
		$names_list = "<tr><td scope=\"row\">Currently Registered:</td><td>{$names_list}</td></tr>";
	}
	
	return "<table class=\"table table-striped\">
		<tbody>
		<tr><td scope=\"row\">Title:</td><td>{$wk['title']}</tr>
		<tr><td scope=\"row\">Description:</td><td>{$wk['notes']}</td></tr>
		<tr><td scope=\"row\">When:</td><td>{$wk['when']}</tr>
		<tr><td scope=\"row\">Where:</td><td>{$wk['place']} {$wk['lwhere']}</tr>
		<tr><td scope=\"row\">Cost:</td><td>{$wk['cost']}</td></tr>
		<tr><td scope=\"row\">Open Spots:</td><td>{$wk['open']} (of {$wk['capacity']})</td></tr>
		<tr><td scope=\"row\">Waiting:</td><td>".($wk['waiting']+$wk['invited'])."</td></tr>
		$names_list
		
		</tbody>
		</table>";
}

function get_workshops_dropdown($start = null, $end = null) {
	$sql = "select w.*, l.place, l.lwhere 
	from workshops w LEFT OUTER JOIN locations l on w.location_id = l.id order by start desc";
	$rows = \Database\mysqli( $sql) or \Database\db_error();
	$workshops = array();
	while ($row = mysqli_fetch_assoc($rows)) {
		$row = format_workshop_startend($row);
		$workshops[$row['id']] = $row['showtitle'];
	}
	return $workshops;
}

function friendly_time($time_string) {
	$ts = strtotime($time_string);
	$minutes = date('i', $ts);
	if ($minutes == 0) {
		return date('ga', $ts);
	} else {
		return date('g:ia', $ts);
	}
}

function friendly_date($time_string) {
	$now_doy = date('z'); // day of year
	$wk_doy = date('z', strtotime($time_string)); // workshop day of year
	
	if ($wk_doy - $now_doy < 7) {
		return date('l', strtotime($time_string)); // Monday, Tuesday, Wednesday
	} elseif (date('Y', strtotime($time_string)) != date('Y')) {  
		return date('D M j, Y', strtotime($time_string));
	} else {
		return date('D M j', strtotime($time_string));
	}
}	
	
	

// pass in the workshop row as it comes from the database table
// add some columns with date / time stuff figured out
function format_workshop_startend($row) {	
	if (date('Y', strtotime($row['start'])) != date('Y')) {
		$row['showstart'] = date('D M j, Y - g:ia', strtotime($row['start']));
	} else {
		$row['showstart'] = date('D M j - g:ia', strtotime($row['start']));
	}
	$row['showend'] = friendly_time($row['end']);
	$row['friendly_when'] = friendly_date($row['start']).' '.friendly_time($row['start']);
	$row['showtitle'] = "{$row['title']} - {$row['showstart']}-{$row['showend']}";
	$row['when'] = "{$row['showstart']}-{$row['showend']}";
	
	return $row;
}

function get_workshops_list($admin = 0) {
	global $sc;
	$sql = 'select w.*, l.place, l.lwhere 
	from workshops w LEFT OUTER JOIN locations l on w.location_id = l.id ';
	$sql .= $admin ? " order by start desc" : " order by start asc";
	$rows = \Database\mysqli( $sql) or \Database\db_error();
	$body = "<table class='table table-striped'><thead><tr>
		<th width='500' scope=\"col\">Title</th>
		<th scope=\"col\">When</th>
		<th scope=\"col\">Where</th>
		<th scope=\"col\">Cost</th>
		<th scope=\"col\">Spots</th>
		<th scope=\"col\">Action</th>
		</tr></thead><tbody>\n";

	$i = 0;
	while($row = mysqli_fetch_assoc($rows)) {
		$wk = get_workshop_info($row['id']);

		if ($wk['type'] == 'past' && !$admin) { continue; }
		if (strtotime($wk['when_public']) > time() && !$admin) {
			continue;
		}
		
		$public = '';
		if ($admin && $wk['when_public']) {
			$public = "<br><small>Public: ".date('D M j - g:ia', strtotime($wk['when_public']))."</small>\n";
		}	
			
		$i++;
		
		if (date('z', strtotime($wk['start'])) == date('z')) {
			$cl = 'info';
		} elseif ($wk['type'] == 'soldout') {
			$cl = 'danger';
		} elseif ($wk['type'] == 'open') {
			$cl = 'success';
		} elseif ($wk['type'] == 'past') {
			$cl = 'light';
		} else  {
			$cl = '';
		}
		
		$body .= "<tr class='$cl'>";
		$titlelink = ($admin 
			? "<a href='$sc?wid={$row['id']}&v=ed'>{$wk['title']}</a>"
			: "<a href='$sc?wid={$row['id']}&v=winfo'>{$wk['title']}</a>");
			
		$body .= "<td>{$titlelink}".($wk['notes'] ? "<p class='small text-muted'>{$wk['notes']}</p>" : '')."</td>
		<td>{$wk['when']}{$public}</td>
		<td>{$wk['place']}</td>
		<td>{$wk['cost']}</td>
		<td>".number_format($wk['open'], 0)." of ".number_format($wk['capacity'], 0).",<br> ".number_format($wk['waiting']+$wk['invited'])." waiting</td>
";
		if ($admin) {
			$body .= "<td><a href=\"$sc?wid={$row['id']}\">Clone</a></td></tr>\n";
		} else {
			$call = ($wk['type'] == 'soldout' ? 'Join Wait List' : 'Enroll');
			$body .= "<td><a href=\"{$sc}?wid={$row['id']}&v=winfo\">{$call}</a></td></tr>\n";
		}
	}
	if (!$i) {
		return "<p>No upcoming workshops!</p>\n";
	}
	$body .= "</tbody></table>\n";
	return $body;
}


function get_workshops_list_raw($start = null, $end = null) {
	$sql = "select w.*, l.place, l.lwhere 
	from workshops w LEFT OUTER JOIN locations l on w.location_id = l.id WHERE 1 = 1 ";
	if ($start) {
		$sql .= " and w.start >= '".date('Y-m-d H:i:s', strtotime($start))."'";
	}
	if ($end) {
		$sql .= " and w.end <= '".date('Y-m-d H:i:s', strtotime($end))."'";
	}
	$sql .= " order by start desc";
	$rows = \Database\mysqli( $sql) or \Database\db_error();
	$workshops = array();
	while ($row = mysqli_fetch_assoc($rows)) {
		$row['showstart'] = date('D M j - g:ia', strtotime($row['start']));
		$row['showend'] = date('g:ia', strtotime($row['end']));		
		$row['showtitle'] = "{$row['title']} - {$row['showstart']}-{$row['showend']}";
		$workshops[$row['id']] = $row;
	}
	return $workshops;
}	

function empty_workshop() {
	return array(
		'title' => '',
		'location_id' => '',
		'start' => '',
		'end' => '',
		'cost' => '',
		'capacity' => '',
		'notes' => '',
		'revenue' => '',
		'expenses' => '',
		'when_public' => ''
	);
}

function workshop_fields($wk) {
	return \Wbhkit\texty('title', $wk['title']).
	\Lookups\locations_drop($wk['location_id']).
	\Wbhkit\texty('start', $wk['start']).
	\Wbhkit\texty('end', $wk['end']).
	\Wbhkit\texty('cost', $wk['cost']).
	\Wbhkit\texty('capacity', $wk['capacity']).
	\Wbhkit\textarea('notes', $wk['notes']).
	\Wbhkit\texty('revenue', $wk['revenue']).
	\Wbhkit\texty('expenses', $wk['expenses']).
	\Wbhkit\texty('when_public', $wk['when_public'], 'When Public');
}

function update_workshop_col($wid, $colname, $value) {
	$sql = "update workshops set $colname = ".\Database\mres($value)." where id = ".\Database\mres($wid);
	//echo $sql;
	\Database\mysqli($sql) or \Database\db_error();
	return true;
}
	
?>