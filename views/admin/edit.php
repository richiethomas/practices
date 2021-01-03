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
		
		
		$roster = Workshops\get_cut_and_paste_roster($wk, $lists[ENROLLED]);
		echo  "<h3>Cut-and-paste roster</h3>\n";
		echo  Wbhkit\textarea('roster', $roster, 0);			
		
		
		echo  "<h5>See <a href='admin_status_log.php?wid={$wk['id']}'>status log</a> for this class?</h5>";

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
				echo "<li>({$s['rank']}) {$s['friendly_when']} <a href='$sc?ac=delxtra&xtraid={$s['id']}&wid={$wk['id']}'>delete</a>".($s['reminder_sent'] ? ' <em>- reminder sent</em>' : '').
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
		
		// list class shows here
		if (count($wk['class_shows']) > 0) {
			echo "<h3>Class Shows</h3>\n";
			echo "<ul>\n";
			foreach ($wk['class_shows'] as $cs) {
				echo "<li><a href='admin_shows.php?ac=ed&show_id={$cs->fields['id']}'>{$cs->fields['friendly_when']}</a></li>\n";
			}
			echo "</ul>\n";
		}
		

	include 'ajax-jquery-search.php';
	echo  "<h2>Add Student</h2><form id='add_student' class='form-inline' action='$sc' method='post' novalidate><fieldset name='new_student'>".
	Wbhkit\hidden('ac', 'enroll');
	echo "<div class='form-group'>
			<label for='search-box' class='form-label'>Email: </label>
			<input type='text' class='form-control' id='search-box' name='email' autocomplete='off'>
			<div id='suggesstion-box'></div>
			</div>\n";	
	echo Wbhkit\radio('con', array('1' => 'confirm', '0' => 'don\'t'), '0').
	Wbhkit\hidden('wid', $wk['id']).
	Wbhkit\submit('Enroll').
	"</fieldset></form>\n";
		
		echo  "</div>"; // end of column
		echo  "</div>\n"; //end of row
		
?>