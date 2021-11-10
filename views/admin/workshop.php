<?php
echo "<h2><a href='/admin-workshop/view/{$wk['id']}'>{$wk['title']}</a></h2>\n
<p class='small'><a href='/admin-archives/clone/{$wk['id']}#addworkshop'>clone this workshop</a> - <a href='/workshop/view/{$wk['id']}'>student view</a></p>";
echo "<div class='row mt-md-3 admin-edit-workshop'>\n";

		// enrollment column
		echo "<div class='col-md-7'><h2>Enrollment Info <small><br>
			<a class='btn btn-primary' href='/admin-messages/view/{$wk['id']}'><span class='oi oi-envelope-closed' title='envelope-closed' aria-hidden='true'></span> message</a> 
			<a class='btn btn-primary'  href='/admin-workshop/nw/{$wk['id']}'><span class='oi oi-clock' title='clock' aria-hidden='true'></span> notify waiting</a>

			<a class='btn btn-primary'  href='/admin-workshop/sar/{$wk['id']}'><span class='oi oi-clock' title='clock' aria-hidden='true'></span> send all reminders</a>
			</small></h2>\n";

		//show enrollment totals at top
		echo  "<p>totals: (".implode(" / ", array_values($stats)).") - ({$wk['actual_revenue']})<p>\n";
		
		echo "<form action='/admin-workshop/at/{$wk['id']}' method='post'>\n";
		
		// list students for each status
		foreach ($statuses as $stid => $status_name) {
			echo  "<h4>{$status_name} (".$stats[$stid].")</h4>\n";
			foreach ($lists[$stid] as $s) {
				echo "<div class='row my-3'><div class='col-md-5'>".
					Wbhkit\checkbox('users', $s['id'], "<a href='/admin-users/view/{$s['id']}'>{$s['nice_name']}</a> <small>".
					date('M j g:ia', strtotime($s['last_modified']))."</small>", $s['paid'], true)."</div>".
					"<div class='col-md-2'>".
					\Wbhkit\texty("payoverride_{$s['id']}_{$wk['id']}", $s['pay_override'], 0).
					"</div>".
				"<div class='col-md-5'>
				<a class='btn btn-outline-secondary btn-sm' href='/admin-change-status/view/{$wk['id']}/{$s['id']}'>status</a>  <a  class='btn btn-outline-secondary btn-sm' href='/admin-workshop/conrem/{$wk['id']}/{$s['id']}'>remove</a></div>".
				"</div>\n";
			}
		}
		echo Wbhkit\checkbox('hideconpay', 1, $label = 'no confirm payment', $hideconpay == 1);
		echo Wbhkit\submit("update paid");
		echo "</form>\n";		
		
		
		$roster = Workshops\get_cut_and_paste_roster($wk, $lists[ENROLLED]);
		echo  "<h3>Cut-and-paste roster</h3>\n";
		echo  Wbhkit\textarea('roster', $roster, 0);			
		
		
		echo  "<h5>See <a href='/admin-status/view/{$wk['id']}'>status log</a> for this class?</h5>";

		echo  "</div>"; // end of column
		
		//main info column
		echo  \Wbhkit\form_validation_javascript('wk_edit');
		echo  "<div class='col-md-5'>
		<h2>Session Info</h2>
		<form id='wk_edit' action='/admin-workshop/up/{$wk['id']}' method='post' novalidate>
		<fieldset name=\"workshop_edit\">".
		Workshops\workshop_fields($wk).
		Wbhkit\submit('Update').
		"<a class='btn btn-outline-primary' href=\"/admin-workshop/cdel/{$wk['id']}\">Delete This Practice</a>".
		"</fieldset></form>\n";
	

		//xtra sessions 
		//echo  \Wbhkit\form_validation_javascript('xtra_edit');
		echo  "<h2>Xtra Sessions</h2>";
		echo "<ul>\n";
		if (!empty($wk['sessions'])) {
			foreach ($wk['sessions'] as $s) {
				echo "<li>({$s['rank']}) {$s['friendly_when']} <a href='/admin-workshop/delxtra/{$wk['id']}/{$s['id']}'>delete</a>".($s['reminder_sent'] ? ' <em>- reminder sent</em>' : '').
					($s['online_url'] ? "<ul><li><a href='{$s['online_url']}'>{$s['online_url']}</a></li></ul>" : '').
					"</li>\n";
			}
		}
		echo "<li><a href='/admin-workshop/week/{$wk['id']}'>Add a week</a></li>\n";
		echo "</ul>\n";
		
		echo "<form id='xtra_edit' action='/admin-workshop/adxtra/{$wk['id']}' method='post' novalidate>
		<fieldset name=\"sessions_edit\">".
		\XtraSessions\xtra_session_fields($wk).
		Wbhkit\submit('Add Session');
		echo "</fieldset></form>\n";
		
		// list class shows here
		if (count($wk['class_shows']) > 0) {
			echo "<h3>Class Shows</h3>\n";
			echo "<ul>\n";
			foreach ($wk['class_shows'] as $cs) {
				echo "<li><a href='/admin-shows/view/?show_id={$cs->fields['id']}'>{$cs->fields['friendly_when']}</a></li>\n";
			}
			echo "</ul>\n";
		}
		

	include 'ajax-jquery-search.php';
	echo  "<h2>Add Student</h2><form id='add_student' class='form-inline' action='/admin-workshop/enroll/{$wk['id']}' method='post' novalidate><fieldset name='new_student'>";
	echo "<div class='form-group'>
			<label for='search-box' class='form-label'>Email: </label>
			<input type='text' class='form-control' id='search-box' name='email' autocomplete='off'>
			<div id='suggesstion-box'></div>
			</div>\n";	
	echo Wbhkit\radio('con', array('1' => 'confirm', '0' => 'don\'t'), '0').
	Wbhkit\submit('Enroll').
	"</fieldset></form>\n";
		
		echo  "</div>"; // end of column
		echo  "</div>\n"; //end of row
		
?>