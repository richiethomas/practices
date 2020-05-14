<?php
	
echo "<h2>{$wk['title']}</h2>\n";
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
				echo "<div class='row my-3'><div class='col-md-6'>".Wbhkit\checkbox('users', $s['id'], "<a href='admin_student.php?guest_id={$s['id']}&wid={$wk['id']}'>{$s['nice_name']}</a> <small>".date('M j g:ia', strtotime($s['last_modified']))."</small>", $s['paid'], true)."</div>".
				"<div class='col-md-6'>
				<a class='btn btn-outline-secondary btn-sm' href='admin_change_status.php?wid={$wk['id']}&guest_id={$s['id']}'>change status</a>  <a  class='btn btn-outline-secondary btn-sm' href='admin_edit.php?ac=conrem&guest_id={$s['id']}&wid={$wk['id']}'>remove</a></div>".
				"</div>\n";
			}
		}
		echo Wbhkit\submit("update paid");
		echo "</form>\n";
		
		
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
		"<a class='btn btn-outline-primary' href=\"{$sc}?wid={$wk['id']}&ac=cdel\">Delete This Practice</a>".
		"</fieldset></form>\n";
	

		//xtra sessions 
		echo  \Wbhkit\form_validation_javascript('xtra_edit');
		echo  "<h2>Xtra Sessions</h2>";
		if (!empty($wk['sessions'])) {
			echo "<ul>\n";
			foreach ($wk['sessions'] as $s) {
				echo "<li>{$s['friendly_when']}".($s['class_show'] ? ' <b>(show)</b> ': '')." <a href='$sc?ac=delxtra&xtraid={$s['id']}&wid={$wk['id']}'>delete</a>".($s['reminder_sent'] ? ' <em>- reminder sent</em>' : '')."</li>\n";
			}
			echo "</ul>\n";
		}
		
		echo "<form id='xtra_edit' action='$sc' method='post' novalidate>
		<fieldset name=\"sessions_edit\">".
		Wbhkit\texty('start', null, null, null, null, 'Required', ' required ').
		Wbhkit\texty('end', null, null, null, null, 'Required', ' required ').
		Wbhkit\checkbox('class_show', 1, 'Class Show?', 0).				
		Wbhkit\hidden('ac', 'adxtra').
		Wbhkit\hidden('wid', $wk['id']).
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