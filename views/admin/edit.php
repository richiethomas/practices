<?php
echo "<h2><a href='$sc?wid={$wk['id']}'>{$wk['title']}</a></h2><p class='small'><a href='admin_listall.php?wid={$wk['id']}#addworkshop'>(Clone this workshop)</a></p>\n";
echo "<div class='row mt-md-3 admin-edit-workshop'>\n";

		// enrollment column
		echo "<div class='col-md-7'><h2>Enrollment Info <small><br>
			<a class='btn btn-primary' href='admin_messages.php?wid={$wk['id']}'><span class='oi oi-envelope-closed' title='envelope-closed' aria-hidden='true'></span> message</a> 
			<a class='btn btn-primary'  href='$sc?ac=cw&wid={$wk['id']}'><span class='oi oi-clock' title='clock' aria-hidden='true'></span> check waiting</a>

			<a class='btn btn-primary'  href='$sc?ac=sar&wid={$wk['id']}'><span class='oi oi-clock' title='clock' aria-hidden='true'></span> send all reminders</a>
			</small></h2>\n";

		//show enrollment totals at top
		echo  "<p>totals: (".implode(" / ", array_values($stats)).")<p>\n";
		
		echo "<form action='$sc' method='post'>\n";
		echo Wbhkit\hidden('wid', $wk['id']);
		echo Wbhkit\hidden('ac', 'at'); 
		
		// list students for each status
		foreach ($statuses as $stid => $status_name) {
			echo  "<h4>{$status_name} (".$stats[$stid].")</h4>\n";
			foreach ($lists[$stid] as $s) {
				echo "<div class='row my-3'><div class='col-md-6'>".Wbhkit\checkbox('users', $s['id'], "<a href='admin_users.php?guest_id={$s['id']}&wid={$wk['id']}'>{$s['nice_name']}</a> <small>".date('M j g:ia', strtotime($s['last_modified']))."</small>", $s['paid'], true)."</div>".
				"<div class='col-md-6'>
				<a class='btn btn-outline-secondary btn-sm' href='admin_change_status.php?wid={$wk['id']}&guest_id={$s['id']}'>change status</a>  <a  class='btn btn-outline-secondary btn-sm' href='admin_edit.php?ac=conrem&guest_id={$s['id']}&wid={$wk['id']}'>remove</a></div>".
				"</div>\n";
			}
		}
		echo Wbhkit\submit("update paid");
		echo "</form>\n";		
		
		
		//cut-and-paste roster
		$names = array();
		$just_emails = array();
		foreach ($lists[ENROLLED] as $s) {
			$names[] = "{$s['nice_name']} {$s['email']}";
			$just_emails[] = "{$s['email']}";
		}
		sort($names);
		sort($just_emails);
		
		$class_dates = $wk['when']."\n";
		if (!empty($wk['sessions'])) {
			foreach ($wk['sessions'] as $s) {
				$class_dates .= "{$s['friendly_when']}".($s['class_show'] ? ' (show)': '').
				($s['online_url'] ? " - {$s['online_url']}" : '')."\n";
			}
		}
		if ($class_dates) {
			$class_dates = "\n\nClass Dates:\n{$class_dates}";
		}
		
		echo  "<h3>Cut-and-paste roster</h3>\n";
		echo  Wbhkit\textarea('roster',
			"{$wk['title']} - {$wk['showstart']}\n".
			($wk['location_id'] == ONLINE_LOCATION_ID ? "{$wk['online_url']}\n" : '').
			"\n".
			implode("\n", $names).
			"\n\n".implode(",\n", $just_emails).$class_dates, 
		0);			
		
		
		echo  $status_log; // from a snippet "admin_status"

		echo  "</div>"; // end of column
		
		//main info column
		echo  \Wbhkit\form_validation_javascript('wk_edit');
		echo  "<div class='col-md-5'>
		<h2>Session Info</h2>
		<form id='wk_edit' action='$sc' method='post' novalidate>
		<fieldset name=\"workshop_edit\">".
		Workshops\workshop_fields($wk).
		Wbhkit\hidden('ac', 'up').
		Wbhkit\hidden('wid', $wk['id']).
		Wbhkit\submit('Update').
		"<a class='btn btn-outline-primary' href=\"admin_edit.php?wid={$wk['id']}&ac=cdel\">Delete This Practice</a>".
		"</fieldset></form>\n";
	

		//xtra sessions 
		echo  \Wbhkit\form_validation_javascript('xtra_edit');
		echo  "<h2>Xtra Sessions</h2>";
		if (!empty($wk['sessions'])) {
			echo "<ul>\n";
			foreach ($wk['sessions'] as $s) {
				echo "<li>({$s['rank']}) {$s['friendly_when']}".($s['class_show'] ? ' <b>(show)</b> ': '')." <a href='$sc?ac=delxtra&xtraid={$s['id']}&wid={$wk['id']}'>delete</a>".($s['reminder_sent'] ? ' <em>- reminder sent</em>' : '').
					($s['online_url'] ? "<ul><li><a href='{$s['online_url']}'>{$s['online_url']}</a></li></ul>" : '').
					"</li>\n";
			}
			echo "</ul>\n";
		}
		
		echo "<form id='xtra_edit' action='$sc' method='post' novalidate>
		<fieldset name=\"sessions_edit\">".
		\XtraSessions\xtra_session_fields($wk).
		Wbhkit\hidden('ac', 'adxtra').
		Wbhkit\submit('Add Session');
		echo "</fieldset></form>\n";
		

	echo  \Wbhkit\form_validation_javascript('add_student');
	echo  "<h2>Add Student</h2><form id='add_student' class='form-inline' action='$sc' method='post' novalidate><fieldset name='new_student'>".
	Wbhkit\hidden('ac', 'enroll').
	Wbhkit\texty('email', '', 0, 'email', null, 'Must be an email', 'required', 'email').
	Wbhkit\radio('con', array('1' => 'confirm', '0' => 'don\'t'), '0').
	Wbhkit\hidden('wid', $wk['id']).
	Wbhkit\submit('Enroll').
	"</fieldset></form>\n";
		
		echo  "</div>"; // end of column
		
		
		echo  "</div>\n"; //end of row
		
?>