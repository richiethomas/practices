<?php 
if ($admin) {

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
	      let cost = document.querySelector(`#cost_\${userID}`).value;
	      let date = new Date();
	      // Get input elements for student
	      let whenInput = document.querySelector(`#when_\${userID}`);
	      let costInput = document.querySelector(`#amount_\${userID}`);
	      // Update input elements
	      if (whenInput.value === '') {
	        costInput.value = cost;
	      }
	      if (whenInput.value === '') {
	        whenInput.value = `\${date.getFullYear()}-\${date.getMonth()+1}-\${date.getDate()}`;
	      }
	    }
	  }
	});
	</script>\n";

	echo "<form action='/admin-users/at/$guest_id' method='post'>\n";
	echo Wbhkit\submit("update paid");
	echo Wbhkit\checkbox('hideconpay', 1, $label = 'no confirm payment', $hideconpay == 1);
}


if ($admin) {
	
	echo "
	<div class='row'>
		<div class='col'>Class</div>
		<div class='col'>When</div>
		<div class='col'>Teacher</div>
		<div class='col'>Where</div>
		<div class='col'>Status</div>
	</div>\n";
	
} else {
	echo "
	<div class='row'>
		<div class='col'>Class</div>
		<div class='col'>When</div>
		<div class='col'>Teacher</div>
		<div class='col'>Where</div>
	</div>\n";
}
		
	foreach ($rows as $t) {
		$cl = '';
		
		if (!$admin && $t['status_id'] != ENROLLED) {
			continue; // only enrolleds for public transcripts
		}
		
		if ($t['status_id'] == ENROLLED) {
			$cl .= 'success';
		} elseif (strtotime($t['start']) < strtotime("now") ) {
			$cl .= 'light';
		} else {
			$cl .= 'warning';
		}	
		
		echo "<div class='row workshop-row workshop-$cl mt-3 pt-3 border-top'>\n"; // workshop row start
				
			echo "	<div class='col-sm'>";
			if ($admin) {
				echo Wbhkit\checkbox('paids', $t['enrollment_id'], "<a href=\"/admin-workshop/view/{$t['workshop_id']}\">{$t['title']}</a>", $t['paid'], true, ' paids-check');
				
				echo Wbhkit\hidden("cost_{$t['enrollment_id']}", $t['cost'], true);
				
			} else {
				echo "<a href=\"/workshop/view/{$t['workshop_id']}\">{$t['title']}</a>";
			}
			echo "</div>\n";  // title col
			
		
			echo "	<div class='col-sm'>";
			if ($admin) {
				echo "{$t['showstart_cali']}<br>\n".$t['costdisplay'];
			} else {
				echo "{$t['showstart']}";
			}
			echo "</div>\n"; // when col
			
			echo "<div class='col-sm'><a href=\"/teachers/view/{$t['teacher_id']}\">{$t['teacher']['nice_name']}</a>";
			
			if ($t['co_teacher_id']) {
				echo ", <a href=\"/teachers/view/{$t['co_teacher_id']}\">{$t['co_teacher']['nice_name']}</a>";
			}
			echo "</div>\n"; // teacher col	

			echo "	<div class='col-sm my-2'>{$t['place']}</div>\n";  // where col

			if ($admin) { 
				echo "	<div class='col-sm'>{$statuses[$t['status_id']]}</div>\n"; // status col
			}
			echo "</div>\n\n"; // end of row
			
			if ($admin) {
				
				echo "<div class='row workshop-$cl'><div class='col-md-2'>".
					\Wbhkit\texty("amount_{$t['enrollment_id']}", $t['pay_amount'], 0, '$').
				"</div><div class='col-md-2'>".
					\Wbhkit\texty("when_{$t['enrollment_id']}", $t['pay_when'], 0, 'when').
				"</div><div class='col-md-2'>".
					\Wbhkit\texty("channel_{$t['enrollment_id']}", $t['pay_channel'], 0, 'how').
				"</div></div>";				
			}
			
		
	}
	
?>
<?php 
if ($admin) {
	echo "</form>\n";
}
?>
