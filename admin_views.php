<?php
	
switch ($v) {
	
	case 'rem':
		$body .= "<h4>Remove user <b>'{$u['email']}'</b> from <b>'{$wk['showtitle']}'</b>?</h4>\n";
		$body .= "<p><a class='btn btn-danger' href='{$sc}?wid={$wid}&uid={$uid}&ac=conrem'>Remove</a> <a class='btn btn-success' href='{$sc}?wid={$wid}&v=ed'>Keep</a></p>\n";
		$v = 'ed';
		break;
	
	
	case 'ed':
		$body .= "<h2>{$wk['showtitle']}</h2>
			<div class='row'>";

		// enrollment column
		$body .= "<div class='col-md-7'><h2>Enrollment Info <small><br><a class='btn btn-primary' href='$sc?v=em&wid={$wid}'>see emails</a> <a class='btn btn-primary'  href='$sc?v=at&wid={$wid}'>attendance</a> <a class='btn btn-primary'  href='$sc?v=ed&ac=cw&wid={$wid}'>check waiting</a></small></h2>\n";
		
		//show enrollment totals at top
		$stats = array();
		foreach ($statuses as $stid => $status_name) {
			$stats[$stid] = count(Enrollments\get_students($wid, $stid));
		}
		$body .= "<p>totals: (".implode(" / ", array_values($stats)).")<p>\n";
		
		// list students for each status
		foreach ($statuses as $stid => $status_name) {
			$body .= "<h4>{$status_name} (".$stats[$stid].")</h4>\n";
			$body .= Enrollments\list_students($wid, $stid);
		}
		
		$body .= "<h2>Change Log</h2>\n";
		$body .= Enrollments\get_status_change_log($wk);
		
		$body .= "</div>"; // end of column
		
		//session column
		$body .= "<div class='col-md-5'>
		<h2>Session Info</h2>
		<form action='$sc' method='post'>
		<fieldset name=\"session_edit\">".
		Workshops\workshop_fields($wk).
		Wbhkit\hidden('ac', 'up').
		Wbhkit\hidden('v', 'ed').
		Wbhkit\hidden('wid', $wid).
		Wbhkit\submit('Update').
		"<a href=\"{$sc}?wid={$wid}&ac=cdel&v=ed\">Delete This Practice</a>".
		"</fieldset></form>\n";
		
	$body .= "<h2>Add Student</h2><form class='form-inline' action='$sc' method='post'><fieldset name='new_student'>".
	Wbhkit\hidden('ac', 'enroll').
	Wbhkit\texty('email', '', 0, 'email').
	Wbhkit\radio('con', array('1' => 'confirm', '0' => 'don\'t'), '0').
	Wbhkit\hidden('v', 'ed').
	Wbhkit\hidden('wid', $wid).
	Wbhkit\submit('Enroll').
	"</fieldset></form>\n";
		
		$body .= "</div>"; // end of column
		
		
		$body .= "</div>\n"; //end of row
		
		break;

	case 'em':
		$body .= "<div class='row'><div class='col-md-6'><h2>emails for <a href='$sc?v=ed&wid={$wid}'>{$wk['showtitle']}</a></h2>";
		$body .= "<p>(Will replace TITLE in subject or note. Also, practice info is appended to message.)</p>\n";
		$body .= "<div class='well'><h3>Send Message <small><a href='$sc?v=em&ac=remind&wid={$wk['id']}'>load reminder</a></small></h3><form action ='$sc' method='post'>".
		Wbhkit\hidden('wid', $wk['id']).
		Wbhkit\hidden('ac', 'sendmsg').
		Wbhkit\texty('subject', $subject).
		Wbhkit\textarea('note', $note).
		Wbhkit\textarea('sms', $sms, 'SMS version (text)').
		Wbhkit\drop('st', $statuses, $st, 'To').
		Wbhkit\submit('send').
		"</form></div>\n";
		
		$body .= "<div id='emaillists'>\n";
		foreach ($statuses as $stid => $status_name) {
			$stds = Enrollments\get_students($wid, $stid);
			$es = '';
			foreach ($stds as $as) {
				$es .= "{$as['email']}\n";
			}
			$body .= "<h3>{$status_name} (".count($stds).")</h3>\n";
			$body .= Wbhkit\textarea($status_name, $es, 0);
		}
		$body .= "</div>\n";
		$body .= "</div></div>\n";
		break;

	case 'gemail':
		$all_workshops = Workshops\get_workshops_dropdown();
		$body .= "<div class='row'><div class='col-md-6'><h2>get emails</h2>";
		$body .= "<div class='well'><form action ='$sc' method='post'>".
		Wbhkit\hidden('v', 'gemail').
		Wbhkit\multi_drop('workshops', $all_workshops, $workshops, 'Workshops', 15).
		Wbhkit\submit('get emails').
		"</form></div>\n";
	
		if (is_array($workshops)) {
			$body .= "<div id='emaillists'>\n";
			$statuses[0] = 'all';
			foreach ($statuses as $stid => $status_name) {
				$students = array();
				foreach ($workshops as $workshop_id) {
					if ($workshop_id) {
						$stds = Enrollments\get_students($workshop_id, $stid);
						foreach ($stds as $as) {
							$students[] = $as['email'];
						}
					}
				}
				$students = array_unique($students);
				natcasesort($students);
				$es = '';
				foreach ($students as $semail) {
					$es .= "{$semail}\n";
				}
				$body .= "<h3>{$status_name} (".count($students).")</h3>\n";
				$body .= Wbhkit\textarea($status_name, $es, 0);
			}
			$body .= "</div>\n";
		}
		$body .= "</div></div>\n";
		break;

	
	case 'cs':
		$e = Enrollments\get_an_enrollment($wk, $u);
		$body .= "<div class='row'><div class='col-md-4'><h2><a href='$sc?v=ed&wid={$wid}'>{$wk['showtitle']}</a></h2>".
		"<p>Email: {$u['email']}</p>
		<p>Display Name: {$u['display_name']}</p>
		<p>Status: {$e['status_name']}</p>";
		$body .= "<form action ='$sc' method='post'>".
		Wbhkit\hidden('wid', $wk['id']).
		Wbhkit\hidden('uid', $u['id']).
		Wbhkit\hidden('ac', 'cs').
		Wbhkit\drop('st', $statuses, $e['status_id'], 'to status').
		Wbhkit\drop('con', array('1' => 'confirm', '0' => 'don\'t'), 0, 'confirm').
		Wbhkit\submit('update').
		"<a class='btn btn-warning' href='$sc?v=ed&wid={$wid}'>cancel</a>".
		"</form>\n";

		$body .= "<form action ='$sc' method='post'>".
		Wbhkit\hidden('wid', $wk['id']).
		Wbhkit\hidden('uid', $u['id']).
		Wbhkit\hidden('ac', 'cr').
		Wbhkit\texty('lmod', $e['last_modified'], 'Last modified').
		Wbhkit\submit('update').
		"<a class='btn btn-warning' href='$sc?v=ed&wid={$wid}'>cancel</a>".
		"</form></div>\n";
		
		
		break;
		
	case 'at':
		$body .= "<div class='row'><div class='col-md-9'><h2>attendance for <a href='$sc?v=ed&wid={$wid}'>{$wk['showtitle']}</a></h2>";
		$body .= "<div id='emaillists'>\n";
		$body .= "<form action='$sc' method='post'>".
		Wbhkit\hidden('wid', $wk['id']).
		Wbhkit\hidden('ac', 'at');
		foreach ($statuses as $stid => $status_name) {
			$body .= "<h3>{$status_name}</h3>\n";
			$stds = Enrollments\get_students($wid, $stid);
			foreach ($stds as $as) {
				$body .= "<p>".Wbhkit\checkbox('users', $as['id'], $as['email'], $as['attended'], true).'</p>';
			}
		}
		$body .= Wbhkit\submit("update attendance");
		$body .= "</form>\n";
		$body .= "</div>\n";
		$body .= "</div></div>\n";
		break;
	
	case 'rev':
		$workshops = Workshops\get_workshops_list_raw($searchstart, $searchend);
		if ($searchstart) {
			$searchstart = date('Y-m-d H:i:s', strtotime($searchstart));
		}
		if ($searchend) {
			$searchend = date('Y-m-d H:i:s', strtotime($searchend));
		}
		$body .= "<div class='row'><div class='col-md-10'><h2>Revenues</h2>";
		$body .= "<form action='$sc' method='post'>".
		Wbhkit\texty('searchstart', $searchstart, 'Search Start').
		Wbhkit\texty('searchend', $searchend, 'Search End').
		Wbhkit\submit('Update').
		Wbhkit\hidden('ac', 'rev');
		$body .= "<table class='table table-striped'><thead><tr><th>workshop</th><th>enrolled / capacity</th><th>cost</th><th>suggested</th><th>revenue</th><th>expenses</th><th>profit</th></tr></thead><tbody>\n";
		$totals = array(
			'revenue' => 0,
			'expenses' => 0
		);
		foreach ($workshops as $wid => $w) {
			$wk = Workshops\get_workshop_info($w['id']);
			$body .= "<tr><td>({$w['id']}) {$w['showtitle']}</td>
			<td>{$wk['enrolled']} / {$w['capacity']}</td>
			<td>{$w['cost']}</td>
			<td>".($w['cost']*$wk['enrolled'])."</td>
			<td>".Wbhkit\texty("revenue_{$w['id']}", $w['revenue'], 0)."</td>
			<td>".Wbhkit\texty("expenses_{$w['id']}", $w['expenses'], 0)."</td>
			<td>".($w['revenue']-$w['expenses'])."</td></tr>\n";
			$totals['revenue'] += $w['revenue'];
			$totals['expenses'] += $w['expenses'];
		}
		$body .= "<tr><td>Totals:</td><td colspan=3>&nbsp;</td><td>{$totals['revenue']}</td><td>{$totals['expenses']}</td><td>".($totals['revenue']-$totals['expenses'])."</td></tr>\n";
		$body .= "</tbody></table>".Wbhkit\submit('Also Update')."</form>\n";
		$body .= "</div></div>\n";
	
		
		break;
		
		case 'search':
			$body .= "<div class='row'><div class='col-md-12'><h2>Find Students</h2>\n"; 
			

			$search_opts = array('n' => 'by name', 't' => 'by total classes', 'd' => 'by date registered');
			if ($sort != 'n' && $sort != 't' && $sort != 'd') {
				$sort = 'n';
			}
			
			$body .= "<form action ='$sc' method='post'>".
			Wbhkit\hidden('v', 'search').
			Wbhkit\texty('needle', $needle, 'Enter an email or part of an email:').
			Wbhkit\radio('sort', $search_opts, $sort).
			'<div class="clearfix">'.Wbhkit\submit('search').'</div>'.
			"</form>\n";
			$body .= "<p>Or click this button to list <a class='btn btn-primary' href='$sc?v=search&needle=everyone'>all students</a> ";
			if ($needle == 'everyone') {
				$body .= "<a class='btn btn-primary' href='$sc?v=search&ac=zero'>remove the zeroes</a>";
			}
			$body .= "</p>\n";
						
			if ($needle) {
				$body .= "<h3>Matches for '$needle'</h3>\n";
				$all = Users\find_students($needle, $sort);
				if (count($all) == 0) {
					$body .= "<p>No matches!</p>";
				} else {
					$body .= "<ul>\n";
					foreach ($all as $s) {
						$body .= "<li><a href=\"{$sc}?v=astd&uid={$s['id']}&needle={$needle}\">{$s['nice_name']}</a> ".($s['phone'] ? ", {$s['phone']}" : '')." ({$s['classes']}) ".($needle == 'everyone' ? date ('Y M j, g:ia', strtotime($s['joined'])) : '')."</li>\n";
					}
					$body .= "</ul>\n";
				}

			}

			$body .= "</div></div>\n";
			break;
			
		case 'allchange':
			$body .= "<h2>Change Log</h2>\n";
			$body .= "<a class='btn btn-primary' href='$sc'>back to front page</a>";
			$body .= Enrollments\get_status_change_log();
			
			break;
			
		case 'astd':
			$body .= "<div class='row'><div class='col-md-12'><h2>Transcript for {$u['email']}</h2>\n"; 
			$breadcrumb = '';
			if ($needle) {
				$body .= "<p>Back to <a href='$sc?v=search&needle={$needle}'>search for '$needle'</a></p>\n";
				$breadcrumb .= "&needle={$needle}";
			}
			if ($wid) {
				$body .= "<p>Back to <a href='$sc?v=ed&wid={$wid}'>{$wk['showtitle']}</a></p>\n";
				$breadcrumb .= "&wid={$wid}";
			}
			
			$key = Users\get_key($u['id']);
			$trans = URL."index.php?key=$key";
			
			$body .= "<p><a href='$trans'>Log in as {$u['email']}</a></p>\n";
			
			$body .= "<h3>Transcripts</h3>\n";
			$body .= Enrollments\get_transcript_tabled($u, true);	
			
			$body .= "<h3>Change Email</h3>\n";
			$body .= "<form action='$sc' method='post'>\n".
				Wbhkit\texty('newe', $newe, 'change email to:').
				Wbhkit\hidden('ac', 'changeemail').
				Wbhkit\hidden('uid', $u['id']).
				Wbhkit\submit('change email').
				"</form>\n";
			
			$body .= "<h3>Text Preferences</h3>\n";
			$body .= Users\edit_text_preferences($u);

			$body .= "<h3>Display Name</h3>\n";
			$body .= Users\edit_display_name($u);

			$body .= "<p>or</p><p><a class='btn btn-danger' href='$sc?ac=delstudent&uid={$u['id']}&v=astd{$breadcrumb}'>remove this student</a></p>\n";

			$body .= "</div></div>\n";
			break;
	default:

	
		$body .= "<p>
			<a class='btn btn-primary' href='#add'>add a workshop</a> 
			<a class='btn btn-primary' href='$sc?v=gemail'>get emails</a> 
			<a class='btn btn-primary' href='$sc?v=rev'>revenues</a>
			<a class='btn btn-primary' href='$sc?v=search'>find students</a>
			<a class='btn btn-primary' href='$sc?v=allchange'>change log</a>

			</p>\n";
		$body .= "<h2>All Practices</h2>";
		$body .= Workshops\get_workshops_list(1);
		
		$body .= "<a name='add'></a><div class='row'><div class='col-md-3'><form action='$sc' method='post'>
			<fieldset name=\"session_add\"><legend>Add Session</legend>".
			Wbhkit\hidden('ac', 'ad').
			Workshops\workshop_fields($wk).
			Wbhkit\submit('Add').
			"</fieldset></form></div></div>\n";
		break;

}
	
	
?>