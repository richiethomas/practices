<?php
$sc = "admin.php";
include 'db.php';
include 'common.php';
include 'validate.php';

if (!is_validated()) {
	include 'header.php';
	validate_user() or die();
	include 'footer.php';
	exit;
}

wbh_set_vars(array('ac', 'wid', 'uid', 'email', 'title', 'notes', 'start', 'end', 'active', 'lid', 'lplace', 'lwhere', 'cost', 'capacity', 'notes', 'st', 'v', 'con', 'note', 'subject', 'workshops', 'revenue', 'expenses', 'searchstart', 'searchend', 'lmod', 'needle', 'newe', 'sms', 'phone', 'carrier_id', 'send_text', 'when_public', 'sort'));

if ($wid) {
	$wk = wbh_get_workshop_info($wid);
} else {
	$wk = wbh_empty_workshop();
}
if ($uid) {
	$u = wbh_get_user_by_id($uid);
} elseif ($email) {
	$u = wbh_get_user_by_email($email);
} else {
	$u = array();
}
$body = '';



switch ($ac) {
 
 
 	case 'changeemail':
		if ($uid) {
			$result = wbh_change_email($uid, $newe);
			if ($result !== true) {
				$error = $result;
			} else {
				$message = "Email changed from '{$u['email']}' to '$newe'";
				$u = wbh_get_user_by_email($newe);
			}
		} else {
			$error = "Can't change email because there's no value for the user.";
		}
		$v = 'astd';
		break;
 
 	case 'zero':
		$message = "Really remove students with zero workshops? <a class='btn btn-danger' href='$sc?ac=zeroconfirm'>yes remove</a> or <a class='btn btn-default' href='$sc?v=search&needle=everyone'>cancel</a>";
		$v = 'search';
		break;
		
	case 'zeroconfirm':
	
		$stds = wbh_find_students('everyone');
		$message = '';
		foreach ($stds as $s) {
			if ($s['classes'] == 0) {
				$message .= "deleting {$s['email']} {$s['id']} - ({$s['classes']})<br>\n";
				wbh_delete_student($s['id']);
			}
		}
		if (!$message) {
			$message = "No zero registation students to delete.";
		}
		$v = 'search';
		$needle = 'everyone';
		break;	
		
 	case 'delstudent':
		$message = "Really delete '{$u['email']}'? <a class='btn btn-danger' href='$sc?ac=delstudentconfirm&uid={$u['id']}'>yes delete</a> or <a class='btn btn-default' href='$sc?v=search&needle=everyone'>cancel</a>";
		break;
		
	case 'delstudentconfirm':
		wbh_delete_student($uid);
		$v = 'search';
		$needle = 'everyone';
		break;

	case 'cr':
		$sql = 'update registrations set last_modified = \''.mres(date('Y-m-d H:i:s', strtotime($lmod))).'\' where workshop_id = '.mres($wk['id']).' and user_id = '.mres($u['id']);
		wbh_mysqli($sql) or wbh_db_error();
		$v = 'cs';
		break; 
		
	case 'cw':
		$message = wbh_check_waiting($wk);
		$v = 'ed';
		break;

	case 'at':
		$v = 'at';
		$users = $_REQUEST['users'];
		foreach ($statuses as $sts) {
			$stds = wbh_get_students($wid, $sts);
			foreach ($stds as $as) {
				if (is_array($users) && in_array($as['id'], $users)) {
					wbh_update_attendance($wid, $as['id'], 1);
				} else {
					wbh_update_attendance($wid, $as['id'], 0);
				}
			}
		}		
		break;
	
	
	case 'sendmsg':
		if (!$st) {
			$error = 'No status chosen';
			break;
		}
		if (!$wk['id']) {
			$error = 'No workshop chosen';
			break;
		}
		$stds = wbh_get_students($wk['id'], $st);
		$sent = '';
		$subject = preg_replace('/TITLE/', $wk['showtitle'], $subject);
		$note = preg_replace('/TITLE/', $wk['showtitle'], $note);
		$sms = preg_replace('/TITLE/', $wk['showtitle'], $sms);

		foreach ($stds as $std) {
			$key = wbh_get_key($std['id']);
			$trans = URL."index.php?key=$key";
			$msg = $note;
			$msg .= "\n\nLog in or drop out here:\n$trans\n";
			$msg .= "
Regarding this practice:
Title: {$wk['showtitle']}
Where: {$wk['place']}
When: {$wk['when']}";
			mail($std['email'], $subject, $msg, 'From: '.WEBMASTER);
			$sent .= "{$std['email']}, ";
			
			wbh_send_text($std, $sms); // routine will check if they want texts and have proper info
			
		}
		$message = "Email '$subject' sent to $sent";
		$v = 'em';
		break;
		
	case 'lo':
		invalidate();
		header("Location: $sc");
		break;
		 
	case 'cdel':
		$error = "Are you sure you want to delete '{$wk['title']}'? <a class='btn btn-danger' href='$sc?ac=del&wid={$wid}'>delete</a>";
		break;
		
	case 'del':
		$sql = "delete from registrations where workshop_id = ".mres($wid);
		wbh_mysqli($sql) or wbh_db_error();
		$sql = "delete from workshops where id = ".mres($wid);
		wbh_mysqli($sql) or wbh_db_error();
		$message = "Deleted '{$wk['title']}'";
		break;
		
	
	case 'conrem':
		wbh_drop_session($wk, $u);
		$message = "Removed user ({$u['email']}) from practice '{$wk['showtitle']}'";
		$v = 'ed';
		break;
	
	case 'enroll':
		$message = wbh_handle_enroll($wk, $u, $email, $con);
		$v = 'ed';
		break;
		
	case 'cs':
		$message = wbh_change_status($wk, $u, $st, $con);
		$v = 'cs';
		break;

	case 'up':
	
		$sql = sprintf("update workshops
		set title = '%s', start = '%s', end = '%s', cost = %u, capacity = %u, location_id = %u, notes = '%s', revenue = %u, expenses = %u, when_public = '%s'
		where id = %u",
			mres($title),
			mres(date('Y-m-d H:i:s', strtotime($start))),
			mres(date('Y-m-d H:i:s', strtotime($end))),
			mres($cost),
			mres($capacity),
			mres($lid),
			mres($notes),
			mres($revenue),
			mres($expenses),
			mres(date('Y-m-d H:i:s', strtotime($when_public))),
			mres($wid));
		wbh_mysqli($sql) or wbh_db_error();
		$wk = wbh_get_workshop_info($wid);
		$message = "Updated practice ({$wid}) - {$wk['title']}";
		break;
		
	case 'ad':
		$sql = sprintf("insert into workshops (title, start, end, cost, capacity, location_id, notes, revenue, expenses, when_public)
		VALUES ('%s', '%s', '%s', '%u', '%u', '%u', '%s', %u, %u, '%s')",
			mres($title),
			mres(date('Y-m-d H:i:s', strtotime($start))),
			mres(date('Y-m-d H:i:s', strtotime($end))),
			mres($cost),
			mres($capacity),
			mres($lid),
			mres($notes),
			mres($revenue),
			mres($expenses),
			mres(date('Y-m-d H:i:s', strtotime($when_public))));
		wbh_mysqli($sql) or wbh_db_error();
		$wid = mysqli_insert_id($db);
		$wk = wbh_get_workshop_info($wid);
		$message = "Added practice ({$title})";
		break;
		
		
	case 'remind':
	//{$wk['friendly_when']}
		$subject = "REMINDER: workshop {$wk['friendly_when']} at {$wk['place']}";
		$note = "Hey! You're enrolled in this workshop. ";
		if ($wk['type'] == 'past') {
			$note .= "Actually, it looks like this workshop is in the past, which means this reminder was probably sent in error. But since I'm just a computer, then maybe there's something going on that I don't quite grasp. At any rate, this is a reminder. ";
		} else {
			$note .= "It starts ".wbh_nicetime($wk['start']).".";
		}
		$note .=" If you think you're not going to make it, that's fine but use the link below to drop out. ";
		if ($wk['waiting'] > 0) {
			$note .= "There are currently people on the waiting list who might want to go. ";
		}
		$note .= " Okay, see you soon!";
		$sms = "Reminder: workshop {$wk['friendly_when']} at {$wk['place']}";
		$st = ENROLLED; // pre-populating the status drop in 'send message' form
		break;

	case 'rev':
		foreach ($_REQUEST as $key => $value) {
			$exp = null;
			$rev = null;
			if (substr($key, 0, 8) == 'revenue_') {
				$id = substr($key, 8);
				wbh_update_workshop_col($id, 'revenue', $value);
			}
			if (substr($key, 0, 9) == 'expenses_') {
				$id = substr($key, 9);
				wbh_update_workshop_col($id, 'expenses', $value);
			}
		}
		$v = 'rev';
		break;
		
	case 'updateu':
		$u['carrier_id'] = $carrier_id;
		$u['phone'] = $phone;
		$u['send_text'] = $send_text;
		wbh_update_text_preferences($u, $message, $error); // function will update all of those arguments
		$phone = $u['phone']; // sometimes gets updated
		$v = 'astd';
		break;		
	
}

function wbh_update_workshop_col($wid, $colname, $value) {
	$sql = "update workshops set $colname = ".mres($value)." where id = ".mres($wid);
	//echo $sql;
	wbh_mysqli($sql) or wbh_db_error();
	return true;
}

switch ($v) {
	
	case 'rem':
		$body .= "<h4>Remove user <b>'{$u['email']}'</b> from <b>'{$wk['showtitle']}'</b>?</h4>\n";
		$body .= "<p><a class='btn btn-danger' href='{$sc}?wid={$wid}&uid={$uid}&ac=conrem'>Remove</a> <a class='btn btn-success' href='{$sc}?wid={$wid}&v=ed'>Keep</a></p>\n";
		$v = 'ed';
		break;
	
	
	case 'ed':
		$body .= "<h2>{$wk['showtitle']}</h2>
			<div class='row'>
";

		// enrollment column
		$body .= "<div class='col-md-7'><h2>Enrollment Info <small><br><a class='btn btn-default' href='$sc?v=em&wid={$wid}'>see emails</a> <a class='btn btn-default'  href='$sc?v=at&wid={$wid}'>attendance</a> <a class='btn btn-default'  href='$sc?v=ed&ac=cw&wid={$wid}'>check waiting</a></small></h2>\n";
		
		//show enrollment totals at top
		$stats = array();
		foreach ($statuses as $stid => $status_name) {
			$stats[$stid] = count(wbh_get_students($wid, $stid));
		}
		$body .= "<p>totals: (".implode(" / ", array_values($stats)).")<p>\n";
		
		// list students for each status
		foreach ($statuses as $stid => $status_name) {
			$body .= "<h4>{$status_name} (".$stats[$stid].")</h4>\n";
			$body .= wbh_list_students($wid, $stid);
		}
		
		$body .= "<h2>Change Log</h2>\n";
		$body .= wbh_get_status_change_log($wk);
		
		$body .= "</div>"; // end of column
		
		//session column
		$body .= "<div class='col-md-5'>
		<h2>Session Info</h2>
		<form action='$sc' method='post'>
		<fieldset name=\"session_edit\">".
		wbh_workshop_fields($wk).
		wbh_hidden('ac', 'up').
		wbh_hidden('v', 'ed').
		wbh_hidden('wid', $wid).
		wbh_submit('Update').
		"<a href=\"{$sc}?wid={$wid}&ac=cdel&v=ed\">Delete This Practice</a>".
		"</fieldset></form>\n";
		
	$body .= "<h2>Add Student</h2><form class='form-inline' action='$sc' method='post'><fieldset name='new_student'>".
	wbh_hidden('ac', 'enroll').
	wbh_texty('email', '', 0, 'email').
	wbh_radio('con', array('1' => 'confirm', '0' => 'don\'t'), '0').
	wbh_hidden('v', 'ed').
	wbh_hidden('wid', $wid).
	wbh_submit('Enroll').
	"</fieldset></form>\n";
		
		$body .= "</div>"; // end of column
		
		
		$body .= "</div>\n"; //end of row
		
		break;

	case 'em':
		$body .= "<div class='row'><div class='col-md-6'><h2>emails for <a href='$sc?v=ed&wid={$wid}'>{$wk['showtitle']}</a></h2>";
		$body .= "<p>(Will replace TITLE in subject or note. Also, practice info is appended to message.)</p>\n";
		$body .= "<div class='well'><h3>Send Message <small><a href='$sc?v=em&ac=remind&wid={$wk['id']}'>load reminder</a></small></h3><form action ='$sc' method='post'>".
		wbh_hidden('wid', $wk['id']).
		wbh_hidden('ac', 'sendmsg').
		wbh_texty('subject', $subject).
		wbh_textarea('note', $note).
		wbh_textarea('sms', $sms, 'SMS version (text)').
		wbh_drop('st', $statuses, $st, 'To').
		wbh_submit('send').
		"</form></div>\n";
		
		$body .= "<div id='emaillists'>\n";
		foreach ($statuses as $stid => $status_name) {
			$stds = wbh_get_students($wid, $stid);
			$es = '';
			foreach ($stds as $as) {
				$es .= "{$as['email']}\n";
			}
			$body .= "<h3>{$status_name} (".count($stds).")</h3>\n";
			$body .= wbh_textarea($status_name, $es, 0);
		}
		$body .= "</div>\n";
		$body .= "</div></div>\n";
		break;

	case 'gemail':
		$all_workshops = wbh_get_workshops_dropdown();
		$body .= "<div class='row'><div class='col-md-6'><h2>get emails</h2>";
		$body .= "<div class='well'><form action ='$sc' method='post'>".
		wbh_hidden('v', 'gemail').
		wbh_multi_drop('workshops', $all_workshops, $workshops, 'Workshops', 15).
		wbh_submit('get emails').
		"</form></div>\n";
	
		if (is_array($workshops)) {
			$body .= "<div id='emaillists'>\n";
			$statuses[0] = 'all';
			foreach ($statuses as $stid => $status_name) {
				$students = array();
				foreach ($workshops as $workshop_id) {
					$stds = wbh_get_students($workshop_id, $stid);
					foreach ($stds as $as) {
						$students[] = $as['email'];
					}
				}
				$students = array_unique($students);
				natcasesort($students);
				$es = '';
				foreach ($students as $semail) {
					$es .= "{$semail}\n";
				}
				$body .= "<h3>{$status_name} (".count($students).")</h3>\n";
				$body .= wbh_textarea($status_name, $es, 0);
			}
			$body .= "</div>\n";
		}
		$body .= "</div></div>\n";
		break;

	
	case 'cs':
		$e = wbh_get_an_enrollment($wk, $u);
		$body .= "<div class='row'><div class='col-md-4'><h2><a href='$sc?v=ed&wid={$wid}'>{$wk['showtitle']}</a></h2>".
		"<p>Email: {$u['email']}</p>
		<p>Status: {$e['status_name']}</p>";
		$body .= "<form action ='$sc' method='post'>".
		wbh_hidden('wid', $wk['id']).
		wbh_hidden('uid', $u['id']).
		wbh_hidden('ac', 'cs').
		wbh_drop('st', $statuses, $e['status_id'], 'to status').
		wbh_drop('con', array('1' => 'confirm', '0' => 'don\'t'), 0, 'confirm').
		wbh_submit('update').
		"<a class='btn btn-warning' href='$sc?v=ed&wid={$wid}'>cancel</a>".
		"</form>\n";

		$body .= "<form action ='$sc' method='post'>".
		wbh_hidden('wid', $wk['id']).
		wbh_hidden('uid', $u['id']).
		wbh_hidden('ac', 'cr').
		wbh_texty('lmod', $e['last_modified'], 'Last modified').
		wbh_submit('update').
		"<a class='btn btn-warning' href='$sc?v=ed&wid={$wid}'>cancel</a>".
		"</form></div>\n";
		
		
		break;
		
	case 'at':
		$body .= "<div class='row'><div class='col-md-9'><h2>attendance for <a href='$sc?v=ed&wid={$wid}'>{$wk['showtitle']}</a></h2>";
		$body .= "<div id='emaillists'>\n";
		$body .= "<form action='$sc' method='post'>".
		wbh_hidden('wid', $wk['id']).
		wbh_hidden('ac', 'at');
		foreach ($statuses as $stid => $status_name) {
			$body .= "<h3>{$status_name}</h3>\n";
			$stds = wbh_get_students($wid, $stid);
			foreach ($stds as $as) {
				$body .= "<p>".wbh_checkbox('users', $as['id'], $as['email'], $as['attended'], true).'</p>';
			}
		}
		$body .= wbh_submit("update attendance");
		$body .= "</form>\n";
		$body .= "</div>\n";
		$body .= "</div></div>\n";
		break;
	
	case 'rev':
		$workshops = wbh_get_workshops_list_raw($searchstart, $searchend);
		if ($searchstart) {
			$searchstart = date('Y-m-d H:i:s', strtotime($searchstart));
		}
		if ($searchend) {
			$searchend = date('Y-m-d H:i:s', strtotime($searchend));
		}
		$body .= "<div class='row'><div class='col-md-10'><h2>Revenues</h2>";
		$body .= "<form action='$sc' method='post'>".
		wbh_texty('searchstart', $searchstart, 'Search Start').
		wbh_texty('searchend', $searchend, 'Search End').
		wbh_submit('Update').
		wbh_hidden('ac', 'rev');
		$body .= "<table class='table table-striped'><thead><tr><th>workshop</th><th>enrolled / capacity</th><th>cost</th><th>suggested</th><th>revenue</th><th>expenses</th><th>profit</th></tr></thead><tbody>\n";
		$totals = array();
		foreach ($workshops as $wid => $w) {
			$wk = wbh_get_workshop_info($w['id']);
			$body .= "<tr><td>({$w['id']}) {$w['showtitle']}</td>
			<td>{$wk['enrolled']} / {$w['capacity']}</td>
			<td>{$w['cost']}</td>
			<td>".($w['cost']*$wk['enrolled'])."</td>
			<td>".wbh_texty("revenue_{$w['id']}", $w['revenue'], 0)."</td>
			<td>".wbh_texty("expenses_{$w['id']}", $w['expenses'], 0)."</td>
			<td>".($w['revenue']-$w['expenses'])."</td></tr>\n";
			$totals['revenue'] += $w['revenue'];
			$totals['expenses'] += $w['expenses'];
		}
		$body .= "<tr><td>Totals:</td><td colspan=3>&nbsp;</td><td>{$totals['revenue']}</td><td>{$totals['expenses']}</td><td>".($totals['revenue']-$totals['expenses'])."</td></tr>\n";
		$body .= "</tbody></table>".wbh_submit('Also Update')."</form>\n";
		$body .= "</div></div>\n";
	
		
		break;
		
		case 'search':
			$body .= "<div class='row'><div class='col-md-12'><h2>Find Students</h2>\n"; 
			

			$search_opts = array('n' => 'by name', 't' => 'by total classes', 'd' => 'by date registered');
			if ($sort != 'n' && $sort != 't' && $sort != 'd') {
				$sort = 'n';
			}
			
			$body .= "<form action ='$sc' method='post'>".
			wbh_hidden('v', 'search').
			wbh_texty('needle', $needle, 'Enter an email or part of an email:').
			wbh_radio('sort', $search_opts, $sort).
			'<div class="clearfix">'.wbh_submit('search').'</div>'.
			"</form>\n";
			$body .= "<p>Or click this button to list <a class='btn btn-default' href='$sc?v=search&needle=everyone'>all students</a> ";
			if ($needle == 'everyone') {
				$body .= "<a class='btn btn-default' href='$sc?v=search&ac=zero'>remove the zeroes</a>";
			}
			$body .= "</p>\n";
						
			if ($needle) {
				$body .= "<h3>Matches for '$needle'</h3>\n";
				$all = wbh_find_students($needle, $sort);
				if (count($all) == 0) {
					$body .= "<p>No matches!</p>";
				} else {
					$body .= "<ul>\n";
					foreach ($all as $s) {
						$body .= "<li><a href=\"{$sc}?v=astd&uid={$s['id']}&needle={$needle}\">{$s['email']}</a> ({$s['classes']}) ".($needle == 'everyone' ? date ('Y M j, g:ia', strtotime($s['joined'])) : '')."</li>\n";
					}
					$body .= "</ul>\n";
				}

			}

			$body .= "</div></div>\n";
			break;
			
			case 'allchange':
			$body .= "<h2>Change Log</h2>\n";
			$body .= "<a class='btn btn-default' href='$sc'>back to front page</a>";
			$body .= wbh_get_status_change_log();
			
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
			
			$key = wbh_get_key($u['id']);
			$trans = URL."index.php?key=$key";
			
			$body .= "<p><a href='$trans'>Log in as {$u['email']}</a></p>\n";
			
			$body .= "<h3>Transcripts</h3>\n";
			$body .= wbh_get_transcript_tabled($u, true);	
			
			$body .= "<h3>Change Email</h3>\n";
			$body .= "<form action='$sc' method='post'>\n".
				wbh_texty('newe', $newe, 'change email to:').
				wbh_hidden('ac', 'changeemail').
				wbh_hidden('uid', $u['id']).
				wbh_submit('change email').
				"</form>\n";
			
			$body .= "<h3>Text Preferences</h3>\n";
			$body .= wbh_edit_text_preferences($u);

			$body .= "<p>or</p><p><a class='btn btn-danger' href='$sc?ac=delstudent&uid={$u['id']}&v=astd{$breadcrumb}'>remove this student</a></p>\n";

			$body .= "</div></div>\n";
			break;
	default:

	
		$body .= "<p>
			<a class='btn btn-default' href='#add'>add a workshop</a> 
			<a class='btn btn-default' href='$sc?v=gemail'>get emails</a> 
			<a class='btn btn-default' href='$sc?v=rev'>revenues</a>
			<a class='btn btn-default' href='$sc?v=search'>find students</a>
			<a class='btn btn-default' href='$sc?v=allchange'>change log</a>

			</p>\n";
		$body .= "<h2>All Practices</h2>";
		$body .= wbh_get_workshops_list(1);
		
		$body .= "<a name='add'></a><div class='row'><div class='col-md-3'><form action='$sc' method='post'>
			<fieldset name=\"session_add\"><legend>Add Session</legend>".
			wbh_hidden('ac', 'ad').
			wbh_workshop_fields($wk).
			wbh_submit('Add').
			"</fieldset></form></div></div>\n";
		break;

}

function wbh_empty_workshop() {
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

function wbh_workshop_fields($wk) {
	return wbh_texty('title', $wk['title']).
	wbh_locations_drop($wk['location_id']).
	wbh_texty('start', $wk['start']).
	wbh_texty('end', $wk['end']).
	wbh_texty('cost', $wk['cost']).
	wbh_texty('capacity', $wk['capacity']).
	wbh_textarea('notes', $wk['notes']).
	wbh_texty('revenue', $wk['revenue']).
	wbh_texty('expenses', $wk['expenses']).
	wbh_texty('when_public', $wk['when_public'], 'When Public');
}

$heading = "practices: admin";
include 'header.php';
 echo $body;
include 'footer.php';