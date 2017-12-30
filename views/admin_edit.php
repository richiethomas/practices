<?php
	
echo "<h2>{$wk['showtitle']}</h2>\n";
echo "<div class='row mt-md-3 admin-edit-workshop'>\n";

		// enrollment column
		echo "<div class='col-md-7'><h2>Enrollment Info <small><br>
			<a class='btn btn-primary' href='admin_messages.php?wid={$wk['id']}'><span class='oi oi-envelope-closed' title='envelope-closed' aria-hidden='true'></span> message</a> 
			<a class='btn btn-primary'  href='admin_attendance.php?wid={$wk['id']}'><span class='oi oi-clipboard' title='clipboard' aria-hidden='true'></span> attendance</a> 
			<a class='btn btn-primary'  href='$sc?ac=cw&wid={$wk['id']}'><span class='oi oi-clock' title='clock' aria-hidden='true'></span> check waiting</a>
			</small></h2>\n";
		
		//show enrollment totals at top
		echo  "<p>totals: (".implode(" / ", array_values($stats)).")<p>\n";
		
		// list students for each status
		foreach ($statuses as $stid => $status_name) {
			echo  "<h4>{$status_name} (".$stats[$stid].")</h4>\n";
			foreach ($lists[$stid] as $s) {
				echo "<div class='row'><div class='col-md-6'><a href='admin_student.php?uid={$s['id']}&wid={$wk['id']}'>{$s['nice_name']}</a> <small>".date('M j g:ia', strtotime($s['last_modified']))."</small></div>".
				"<div class='col-md-6'>
				<a class='btn btn-primary' href='admin.php?ac=cs&wid={$wk['id']}&uid={$s['id']}'>change status</a> <a class='btn btn-danger' href='admin.php?ac=conrem&uid={$s['id']}&wid={$wk['id']}'>remove</a></div>".
				"</div>\n";
			}
		}
		
		echo  $status_log; // from a snippet "admin_status"

		echo  "</div>"; // end of column
		
		//session column
		echo  \Wbhkit\form_validation_javascript('wk_edit');
		echo  "<div class='col-md-5'>
		<h2>Session Info</h2>
		<form id='wk_edit' action='$sc' method='post' novalidate>
		<fieldset name=\"session_edit\">".
		Workshops\workshop_fields($wk).
		Wbhkit\hidden('ac', 'up').
		Wbhkit\hidden('wid', $wk['id']).
		Wbhkit\submit('Update').
		"<a class='btn btn-outline-primary' href=\"{$sc}?wid={$wk['id']}&ac=cdel\">Delete This Practice</a>".
		"</fieldset></form>\n";
	
		
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