<?php


$sessions = '';

	
if (!\Workshops\is_public($wk)) {
	$point = "This workshop is not available for signups yet. It will be available at <b>".date("l M j, g:ia", strtotime($wk['when_public']))."</b> (".TIMEZONE.")";
} elseif ($wk['upcoming'] == 0) {
	$point = "This workshop had started or is in the past.";
} else {
	
	$enroll_link = "$sc?ac=enroll&wid={$wk['id']}";
	$drop_link = "$sc?ac=drop&wid={$wk['id']}";

	if ($e->fields['status_id'] == ENROLLED) {
		
		$point = "You are enrolled in this class!";
		if ($wk['location_id'] == ONLINE_LOCATION_ID) {
			$point .= "<br><br><b>Zoom link</b>: <a href='{$wk['online_url']}'>{$wk['online_url']}</a>";
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
				$point .= "You must be logged in to join the wait list. Click 'Login' in the menu at the top of the page.";	
			}

		} else {
			if ($u->logged_in()) {
				
				if ($wk['application']) {
					$point = "This class is taking applications. To request a spot in this class, click here: <a class='btn btn-primary' href='$enroll_link'>apply</a>. Your email will be added to the list and you'll be notified soon if you got in or not. We give preference to new students unless it says differently in the class description.";
				} else {
					$point = "Click here to <a class='btn btn-primary' href='$enroll_link'>enroll</a> in this class.  Info will be sent to <b>{$u->fields['email']}</b>.";
				}
				
				
			} else {
				$point .= "You must be logged in to join the class. Click 'Login' in the menu at the top of the page.";	
			}
		}		
	}

}
?>
<div class='row'><div class='col'>


<?php
	
	
echo "	
<div class='row my-3 py-3'><div class='col-sm-6'>
<h2>{$wk['title']}</h2>
<p>{$wk['notes']}</p>
<p>{$wk['full_when']}<br><br>
{$wk['costdisplay']}, {$wk['enrolled']} (of {$wk['capacity']}) enrolled</p>\n";

if ($wk['cost'] == 1) {
	echo "<p class='m-5'><em>This is a PAY WHAT YOU CAN workshop. Pay anything from zero to $40USD (the usual full price). There may be a suggested donation in the description.</em></p>";
}

if ($show_other_action)  {
	echo "<p class='alert alert-info'>{$point}</p>\n";
}


if ($u->check_user_level(2)) { 
	$eh = new EnrollmentsHelper();
	$lists = $eh->get_students($wk['id'], ENROLLED);
	echo "<div class='m-3 p-3 bg-info'>\n";
	echo "<h3>Teacher/Admin Info</h3>\n";
	
	echo "<h4>Zoom link</h4>\n";
	echo "<p><a href='{$wk['online_url']}'>{$wk['online_url']}</a></p>\n";
	
	echo "<h4>Enrolled Students</h4><ul>";
	foreach ($lists as $l) {
		echo "<li>".$l['nice_name']."</li>\n";
	}
	echo "</ul>\n";
	
	echo "<h4>Just emails</h4>\n";
	foreach ($lists as $l) {
		echo $l['email']."<br>\n";
	}
	echo "</div>\n";
}

echo "</div>\n";


echo "<div class='col-sm-6'>
<figure class=\"figure\">
<a href='teachers.php?tid={$wk['teacher_id']}'><img class='img-fluid border figure-img rounded' src='".\Teachers\get_teacher_photo_src($wk['teacher_info']['user_id'])."'></a>
  <figcaption class=\"figure-caption\"><b>Teacher: <a href='teachers.php?tid={$wk['teacher_id']}'>{$wk['teacher_info']['nice_name']}</a></b></figcaption>
</figure>\n";


if ($wk['co_teacher_id']) {
	
echo "<figure class=\"figure\">
<a href='teachers.php?tid={$wk['co_teacher_id']}'><img class='img-fluid border figure-img rounded' src='".\Teachers\get_teacher_photo_src($wk['co_teacher_info']['user_id'])."'></a>
  <figcaption class=\"figure-caption\"><b>Teacher: <a href='teachers.php?tid={$wk['co_teacher_id']}'>{$wk['co_teacher_info']['nice_name']}</a></b></figcaption>
</figure>\n";


}


echo "</div></div>
</div>

<div class=\"row m-3 p-3 justify-content-center\"><div class=\"col-md-8 border border-info\">
<h2>How This Works</h2>
<ul>
	<li>All times are California local time (PDT).</li>
	<li>Pay via Venmo (@wgimprovschool - a business) or Paypal (payments@wgimprovschool.com)</li>
	<li>Classes are held over <a href=\"http://www.zoom.us/\">Zoom</a></li>
	<li><b>LATE DROP POLICY: If drop less than ".LATE_HOURS." hours before, you still must pay! Unless we sell your spot in which case, it's cool.</b></li>
</ul>
</div>


</div></div> <!-- end of col and row -->
";	
