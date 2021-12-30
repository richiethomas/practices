<?php


$sessions = '';
	
if (!\Workshops\is_public($wk)) {
	$point = "This workshop is not available for signups yet. It will be available at <b>".date("l M j, g:ia", strtotime($wk['when_public_tz']))."</b> ({$u->fields['time_zone']})";
} elseif ($wk['upcoming'] == 0) {
	$point = "This workshop had started or is in the past.";
} else {
	
	$enroll_link = "/workshop/enroll/{$wk['id']}";
	$drop_link = "/workshop/drop/{$wk['id']}";

	if ($e->fields['status_id'] == ENROLLED) {
		
		$point = "You are enrolled in this class!";
		if ($wk['location_id'] == ONLINE_LOCATION_ID) {
			$point .= "<br><br><b>Zoom link</b>: <a href='{$wk['online_url_just_url']}'>{$wk['online_url_just_url']}</a>";
			
			if ($wk['online_url_the_rest']) {
				$point .= "<br>{$wk['online_url_the_rest']}";
			}
			
		}
		
		$point .= "<br><br>Need to drop this class? <a class='btn btn-primary' href='$drop_link'>click here to drop</a>.";
		
		
	} elseif ($e->fields['status_id'] == APPLIED) {
		$point = "You have applied for this class. You'll be notified soon if you got in. If you change your mind, <a class='btn btn-primary' href='$drop_link'>click here to drop</a>.";
		
	} else {
		if ($wk['soldout']) {
			$point = "The class is full.<br><br>";

			if ($u->logged_in()) {
				
				if ($e->fields['status_id'] == WAITING) {
					$point .= "You are on the waiting list. You'll get an email if a spot opens up, so you can come here an enroll. If you no longer want to be notified of open spots, <a class='btn btn-primary' href='$drop_link'>click here to drop</a>.";
				} else {
					$point .= "If you want an email when a spot opens, you can <a class='btn btn-primary' href='$enroll_link'>join the wait list</a>";
				}
				
			} else {
				$point = "You must be logged in to join the wait list. Click 'Login' in the menu at the top of the page.";	
			}

		} else {
			if ($u->logged_in()) {
				
				if ($wk['application']) {
					$point = "To request a spot in this class, click here: <a class='btn btn-primary' href='$enroll_link'>request a spot</a>. Your email will be added to the list and you'll be notified soon if you got in or not. We give preference to new students unless it says differently in the class description.";
				} else {
					$point = "Click here to <a class='btn btn-primary' href='$enroll_link'>enroll</a> in this class.  Info will be sent to <b>{$u->fields['email']}</b>.";
				}
				
				
			} else {
				$point = "You must be logged in to join the class. Click 'Login' in the menu at the top of the page.";	
			}
		}		
	}

}
	
echo "<div class='row my-3 py-3'><div class='col-sm-6'>\n";

// start of main row, and class info col
echo "<h2>{$wk['title']}</h2>
<p>{$wk['notes']}</p>
<p>{$wk['full_when']}<br><br>
{$wk['costdisplay']}, {$wk['enrolled']} (of {$wk['capacity']}) enrolled</p>\n";

if ($wk['cost'] == 1) {
	echo "<p class='m-5'><em>This is a PAY WHAT YOU CAN workshop. Pay anything from zero to $40USD (the usual full price). There may be a suggested donation in the description.</em></p>";
}

if ($show_other_action)  {
	echo "<p class='alert alert-info'>{$point}</p>\n";
}

echo "</div>\n";



function teacher_section($tinfo) {
	echo "<figure class=\"figure\">
	<a href='/teachers/view/{$tinfo['id']}'><img class='img-fluid border figure-img rounded' src='".\Teachers\get_teacher_photo_src($tinfo['user_id'])."'></a>
	  <figcaption class=\"figure-caption\"><b>Teacher: <a href='/teachers/view/{$tinfo['id']}'>{$tinfo['nice_name']}</a></b></figcaption>
	</figure>\n";

	echo "<p><small><b>About {$tinfo['nice_name']}:</b> {$tinfo['bio']}</small></p>\n";
	
}

// teacher col
echo "<div class='col-sm-6 p-5 border bg-light'>";
echo teacher_section($wk['teacher_info']);
if ($wk['co_teacher_id']) {
	echo teacher_section($wk['co_teacher_info']);
}
echo "</div></div>\n"; // end of main row



// how it works row
echo "<div class=\"row m-3 p-3 justify-content-center\"><div class=\"col-md-8 border border-info\">
<h2>How This Works</h2>
<ul>
	<li>Pay via Venmo (@wgimprovschool - a business) or Paypal (payments@wgimprovschool.com)</li>
	<li>Classes are online, on <a href=\"http://www.zoom.us/\">Zoom</a> unless the title includes IN PERSON</li>
	<li><b>LATE DROP POLICY: If drop less than ".LATE_HOURS." hours before and it was sold out, we will ask you to still pay!</b></li>
</ul>
</div></div>\n";


function list_names($lists, $title = 'Enrolled') {
	
	if (count($lists) > 0) {
		echo "<h4 class='mt-2'>{$title}</h4>\n";
		echo "<div class='mx-4'>\n";
		echo "<h5>Names</h5>\n";
		foreach ($lists as $l) {
			echo "{$l['nice_name']}<br>\n";
		}
		echo "<h5>Emails</h5>\n";
		foreach ($lists as $l) {
			echo "{$l['email']}<br>\n";
		}
		echo "</div>\n";
	}
}



// teacher / admin info
if ($u->check_user_level(2)) { 
	$eh = new EnrollmentsHelper();
	
	$lists = $eh->get_students($wk['id'], ENROLLED);
	$alists = $eh->get_students($wk['id'], APPLIED);
	$wlists = $eh->get_students($wk['id'], WAITING);
	
	echo "<div class='m-3 p-3 bg-info'>\n";
	echo "<h3>Teacher/Admin Info</h3>\n";
	
	if ($wk['location_id'] == ONLINE_LOCATION_ID) {
		echo "<h4>Zoom link</h4>\n";
		echo "<p><a href='{$wk['online_url_just_url']}'>{$wk['online_url_just_url']}</a>\n";
		if ($wk['online_url_the_rest']) {
			echo "<br>{$wk['online_url_the_rest']}";
		}
		echo "</p>";
	}
	
	list_names($lists, 'Enrolled');
	list_names($alists, 'Requested A Spot');
	list_names($wlists, 'Wait List');
	
	echo "</div>\n";
}

