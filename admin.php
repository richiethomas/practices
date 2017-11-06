<?php
$sc = "admin.php";
include 'db.php';
include 'lib-master.php';
include 'validate.php';

if (!Validate\is_validated()) {
	include 'header.php';
	Validate\validate_user() or die();
	include 'footer.php';
	exit;
}

Wbhkit\set_vars(array('ac', 'wid', 'uid', 'email', 'title', 'notes', 'start', 'end', 'active', 'lid', 'lplace', 'lwhere', 'cost', 'capacity', 'notes', 'st', 'v', 'con', 'note', 'subject', 'workshops', 'revenue', 'expenses', 'searchstart', 'searchend', 'lmod', 'needle', 'newe', 'sms', 'phone', 'carrier_id', 'send_text', 'when_public', 'sort'));

if ($wid) {
	$wk = Workshops\get_workshop_info($wid);
} else {
	$wk = Workshops\empty_workshop();
}
if ($uid) {
	$u = Users\get_user_by_id($uid);
} elseif ($email) {
	$u = Users\get_user_by_email($email);
} else {
	$u = array();
}
$body = '';



switch ($ac) {
 
 
 	case 'changeemail':
		if ($uid) {
			$result = Users\change_email($uid, $newe);
			if ($result !== true) {
				$error = $result;
			} else {
				$message = "Email changed from '{$u['email']}' to '$newe'";
				$u = Users\get_user_by_email($newe);
			}
		} else {
			$error = "Can't change email because there's no value for the user.";
		}
		$v = 'astd';
		break;
 
 	case 'zero':
		$message = "Really remove students with zero workshops? <a class='btn btn-danger' href='$sc?ac=zeroconfirm'>yes remove</a> or <a class='btn btn-primary' href='$sc?v=search&needle=everyone'>cancel</a>";
		$v = 'search';
		break;
		
	case 'zeroconfirm':
	
		$stds = Users\find_students('everyone');
		$message = '';
		foreach ($stds as $s) {
			if ($s['classes'] == 0) {
				$message .= "deleting {$s['email']} {$s['id']} - ({$s['classes']})<br>\n";
				Users\delete_student($s['id']);
			}
		}
		if (!$message) {
			$message = "No zero registation students to delete.";
		}
		$v = 'search';
		$needle = 'everyone';
		break;	
		
 	case 'delstudent':
		$message = "Really delete '{$u['email']}'? <a class='btn btn-danger' href='$sc?ac=delstudentconfirm&uid={$u['id']}'>yes delete</a> or <a class='btn btn-primary' href='$sc?v=search&needle=everyone'>cancel</a>";
		break;
		
	case 'delstudentconfirm':
		Users\delete_student($uid);
		$v = 'search';
		$needle = 'everyone';
		break;

	case 'cr':
		$sql = 'update registrations set last_modified = \''.Database\mres(date('Y-m-d H:i:s', strtotime($lmod))).'\' where workshop_id = '.Database\mres($wk['id']).' and user_id = '.Database\mres($u['id']);
		Database\mysqli($sql) or Database\db_error();
		$v = 'cs';
		break; 
		
	case 'cw':
		$message = Enrollments\check_waiting($wk);
		$v = 'ed';
		break;

	case 'at':
		$v = 'at';
		$users = $_REQUEST['users'];
		foreach ($statuses as $sid => $sts) {
			$stds = Enrollments\get_students($wid, $sid);
			foreach ($stds as $as) {
				if (is_array($users) && in_array($as['id'], $users)) {
					update_attendance($wid, $as['id'], 1);
				} else {
					update_attendance($wid, $as['id'], 0);
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
		$stds = Enrollments\get_students($wk['id'], $st);
		$sent = '';
		$subject = preg_replace('/TITLE/', $wk['showtitle'], $subject);
		$note = preg_replace('/TITLE/', $wk['showtitle'], $note);
		$sms = preg_replace('/TITLE/', $wk['showtitle'], $sms);

		foreach ($stds as $std) {
			$key = Users\get_key($std['id']);
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
			
			Emails\send_text($std, $sms); // routine will check if they want texts and have proper info
			
		}
		$message = "Email '$subject' sent to $sent";
		$v = 'em';
		break;
		
	case 'lo':
		Validate\invalidate();
		header("Location: $sc");
		break;
		 
	case 'cdel':
		$error = "Are you sure you want to delete '{$wk['title']}'? <a class='btn btn-danger' href='$sc?ac=del&wid={$wid}'>delete</a>";
		break;
		
	case 'del':
		$sql = "delete from registrations where workshop_id = ".Database\mres($wid);
		Database\mysqli($sql) or Database\db_error();
		$sql = "delete from workshops where id = ".Database\mres($wid);
		Database\mysqli($sql) or Database\db_error();
		$message = "Deleted '{$wk['title']}'";
		break;
		
	
	case 'conrem':
		Enrollments\drop_session($wk, $u);
		$message = "Removed user ({$u['email']}) from practice '{$wk['showtitle']}'";
		$v = 'ed';
		break;
	
	case 'enroll':
		$message = Enrollments\handle_enroll($wk, $u, $email, $con);
		$v = 'ed';
		break;
		
	case 'cs':
		$message = Enrollments\change_status($wk, $u, $st, $con);
		$v = 'cs';
		break;

	case 'up':
	
		$sql = sprintf("update workshops
		set title = '%s', start = '%s', end = '%s', cost = %u, capacity = %u, location_id = %u, notes = '%s', revenue = %u, expenses = %u, when_public = '%s'
		where id = %u",
			Database\mres($title),
			Database\mres(date('Y-m-d H:i:s', strtotime($start))),
			Database\mres(date('Y-m-d H:i:s', strtotime($end))),
			Database\mres($cost),
			Database\mres($capacity),
			Database\mres($lid),
			Database\mres($notes),
			Database\mres($revenue),
			Database\mres($expenses),
			Database\mres(date('Y-m-d H:i:s', strtotime($when_public))),
			Database\mres($wid));
		Database\mysqli($sql) or Database\db_error();
		$wk = Workshops\get_workshop_info($wid);
		$message = "Updated practice ({$wid}) - {$wk['title']}";
		break;
		
	case 'ad':
		$sql = sprintf("insert into workshops (title, start, end, cost, capacity, location_id, notes, revenue, expenses, when_public)
		VALUES ('%s', '%s', '%s', '%u', '%u', '%u', '%s', %u, %u, '%s')",
			Database\mres($title),
			Database\mres(date('Y-m-d H:i:s', strtotime($start))),
			Database\mres(date('Y-m-d H:i:s', strtotime($end))),
			Database\mres($cost),
			Database\mres($capacity),
			Database\mres($lid),
			Database\mres($notes),
			Database\mres($revenue),
			Database\mres($expenses),
			Database\mres(date('Y-m-d H:i:s', strtotime($when_public))));
		Database\mysqli($sql) or Database\db_error();
		$wid = mysqli_insert_id($db);
		$wk = Workshops\get_workshop_info($wid);
		$message = "Added practice ({$title})";
		break;
		
		
	case 'remind':
	//{$wk['friendly_when']}
		$subject = "REMINDER: workshop {$wk['friendly_when']} at {$wk['place']}";
		$note = "Hey! You're enrolled in this workshop. ";
		if ($wk['type'] == 'past') {
			$note .= "Actually, it looks like this workshop is in the past, which means this reminder was probably sent in error. But since I'm just a computer, then maybe there's something going on that I don't quite grasp. At any rate, this is a reminder. ";
		} else {
			$note .= "It starts ".nicetime($wk['start']).".";
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
				Workshops\update_workshop_col($id, 'revenue', $value);
			}
			if (substr($key, 0, 9) == 'expenses_') {
				$id = substr($key, 9);
				Workshops\update_workshop_col($id, 'expenses', $value);
			}
		}
		$v = 'rev';
		break;
		
	case 'updateu':
		$u['carrier_id'] = $carrier_id;
		$u['phone'] = $phone;
		$u['send_text'] = $send_text;
		Users\update_text_preferences($u, $message, $error); // function will update all of those arguments
		$phone = $u['phone']; // sometimes gets updated
		$v = 'astd';
		break;		
	
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
						$body .= "<li><a href=\"{$sc}?v=astd&uid={$s['id']}&needle={$needle}\">{$s['email']}</a> ".($s['phone'] ? ", {$s['phone']}" : '')." ({$s['classes']}) ".($needle == 'everyone' ? date ('Y M j, g:ia', strtotime($s['joined'])) : '')."</li>\n";
					}
					$body .= "</ul>\n";
				}

			}

			$body .= "</div></div>\n";
			break;
			
			case 'allchange':
			$body .= "<h2>Change Log</h2>\n";
			$body .= "<a class='btn btn-primary' href='$sc'>back to front page</a>";
			$body .= Lookups\get_status_change_log();
			
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

$heading = "practices: admin";
include 'header.php';
 echo $body;
include 'footer.php';