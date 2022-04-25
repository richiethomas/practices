<?php 
echo $links; 

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
	        whenInput.value = `\${date.getFullYear()}-\${date.getMonth()}-\${date.getDate()}`;
	      }
	      if (costInput.value === '') {
	        costInput.value = cost;
	      }
	    }
	  }
	});
	</script>\n";

	echo "<form action='/admin-users/at/$guest_id' method='post'>\n";
	echo Wbhkit\submit("update paid");
	echo Wbhkit\checkbox('hideconpay', 1, $label = 'no confirm payment', $hideconpay == 1);
}

		
	foreach ($rows as $t) {
		$cl = '';
		if ($t['upcoming'] == 0) {
			$cl .= 'light';
		} elseif ($t['status_id'] == ENROLLED) {
			$cl .= 'success';
		} else {
			$cl .= 'warning';
		}	
		
		echo "<div class='row workshop-row workshop-$cl mt-3 mx-1 pt-3 border-top'>\n"; // workshop row start
				
			echo "	<div class='col-sm'>";
			if ($admin) {
				echo Wbhkit\checkbox('paids', $t['enrollment_id'], "<a href=\"/admin-workshop/view/{$t['workshop_id']}\">{$t['title']}</a>", $t['paid'], true, ' paids-check');
				
				echo Wbhkit\hidden("cost_{$t['enrollment_id']}", $t['cost'], true);
				
			} else {
				echo "<a href=\"/workshop/view/{$t['workshop_id']}\">{$t['title']}</a>";
			}
			echo "</div>\n";  // title cell
			
		
			echo "	<div class='col-sm'>";
			echo $admin ? $t['full_when_cali'] : $t['full_when'];
			
			echo "<br>
				<small>Instructor: <a href=\"/teachers/view/{$t['teacher_id']}\">{$t['teacher_info']['nice_name']}</a>";
			
			if ($t['co_teacher_id']) {
				echo ", <a href=\"/teachers/view/{$t['co_teacher_id']}\">{$t['co_teacher_info']['nice_name']}</a>";
			}
			
			if ($admin) {
				echo "<br>\n".$t['costdisplay'];
			}
			
			echo "</small></div>\n"; // when col	
			if ($admin) { echo "<div class='col-sm my-2'>{$t['place']}</div>\n"; } // where col
			echo "	<div class='col-sm'>{$statuses[$t['status_id']]}";
			echo "</div>\n"; // status col
			echo "</div>\n\n"; // end of row
			
			if ($admin) {
				
				echo "<div class='row workshop-$cl mx-1'><div class='col-md-2'>".
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
echo $links; 
?>
