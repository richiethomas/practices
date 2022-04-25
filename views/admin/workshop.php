<?php
echo "<h2><a href='/admin-workshop/view/{$wk['id']}'>{$wk['title']}</a></h2>\n
<p class='small'><a href='/admin-archives/clone/{$wk['id']}#addworkshop'>clone this workshop</a> - <a href='/workshop/view/{$wk['id']}'>student view</a> - <a href='/admin-bulk/view/{$wk['id']}'>bulk edit</a></p>";
echo "<div class='row mt-md-3 admin-edit-workshop'>\n";

		// enrollment column
		echo "<div class='col-md-7'><h2>Enrollment Info <small><br>
			<a class='btn btn-primary' href='/admin-messages/view/{$wk['id']}'><i class='bi-envelope'></i> message</a> 
			<a class='btn btn-primary'  href='/admin-workshop/nw/{$wk['id']}'><i class='bi-clock'></i> notify waiting</a>

			<a class='btn btn-primary'  href='/admin-workshop/sar/{$wk['id']}'><i class='bi-exclamation-circle'></i> send all reminders</a>
			</small></h2>\n";

		//show enrollment totals at top
		echo  "<p>totals: (".implode(" / ", array_values($stats)).") - ({$wk['actual_revenue']})</p>\n";


		echo "
		<script type='text/javascript'>
		document.addEventListener('DOMContentLoaded', function () {
		  // Get all checkbox elements and add event listeners on click
		  let paidCheckboxes = document.querySelectorAll('.paids-check');
		  paidCheckboxes.forEach((checkbox) => {
		    checkbox.addEventListener('click', checkboxClick);
		  });

		  function checkboxClick(event) {
		    if (event.target.checked) {
		      let userID = event.target.value;
		      // Get base values
		      let cost = document.querySelectorAll('#cost')[0].value;
		      let date = new Date();
		      // Get input elements for student
		      let whenInput = document.querySelector(`#when_\${userID}`);
		      let costInput = document.querySelector(`#amount_\${userID}`);
		      // Update input elements
		      if (whenInput.value === '') {
		        whenInput.value = `\${date.getFullYear()}-\${date.getMonth()}-\${date.getDate()}`;
		      }
		      if (costInput.value === '') {
		        costInput.value = cost;
		      }
		    }
		  }
		});
		</script>\n";


		
		echo "<form action='/admin-workshop/at/{$wk['id']}' method='post'>\n";
		
		// list students for each status
		foreach ($statuses as $stid => $status_name) {
			echo  "<h4>{$status_name} (".$stats[$stid].")</h4>\n";
			foreach ($lists[$stid] as $s) {
				echo "<div class='row my-3'>
						<div class='col-md-5'>".
					Wbhkit\checkbox('paids', $s['id'], "<a href='/admin-change-status/view/{$wk['id']}/{$s['id']}'>{$s['nice_name']}</a> <small>".
					date('M j g:ia', strtotime($s['last_modified']))."</small>", $s['paid'], true, ' paids-check')."</div>".
						"<div class='col-md-2'>".
							\Wbhkit\texty("amount_{$s['id']}", $s['pay_amount'], 0, '$').
						" <!--({$s['pay_override']})--></div><div class='col-md-3'>".
							\Wbhkit\texty("when_{$s['id']}", $s['pay_when'], 0, 'when').
						"</div><div class='col-md-2'>".
							\Wbhkit\texty("channel_{$s['id']}", $s['pay_channel'], 0, 'how').
						"</div></div>";
			}
		}
		echo Wbhkit\submit("update paid");
		echo Wbhkit\checkbox('hideconpay', 1, $label = 'no confirm payment', $hideconpay == 1);
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
				echo "<li>({$s['rank']}) ".
					($s['class_show'] ? '<b>Show:</b> ' : '');
				
				if (date('M j Y', strtotime($s['start'])) != date('M j Y', strtotime($s['end']))) {
					echo date('D M j Y g:ia', strtotime($s['start']))."-<span class='text-danger'><b>".date('D M j Y g:ia', strtotime($s['end']))."</b></span>";
				} else {
					echo	"{$s['when_cali']}";
				}
				
				echo " <a href='/admin-workshop/delxtra/{$wk['id']}/{$s['id']}'>delete</a>".
				($s['reminder_sent'] ? ' <em>- reminder sent</em>' : '');
				if ($s['online_url']) {
					echo "<ul><li><a href='{$s['online_url_just_url']}'>{$s['online_url_just_url']}</a>";
					if ($s['online_url_the_rest']) {
						echo "<br>{$s['online_url_the_rest']}";
					}
					echo "</li></ul>\n";
				}
				echo "</li>\n";
			}
		}
		echo "<li><a href='/admin-workshop/week/{$wk['id']}'>Add a week</a></li>\n";
		echo "</ul>\n";
		
		echo "<form id='xtra_edit' action='/admin-workshop/adxtra/{$wk['id']}' method='post' novalidate>
		<fieldset name=\"sessions_edit\">".
		\XtraSessions\xtra_session_fields($wk).
		Wbhkit\submit('Add Session');
		echo "</fieldset></form>\n";		

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