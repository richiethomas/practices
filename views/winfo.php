<?php


$sessions = '';

	
if (!\Workshops\is_public($wk)) {
	$point = "This workshop is not available for signups yet. It will be available at <b>".date("l M j, g:ia", strtotime($wk['when_public']))."</b> California time (PDT)";
} elseif ($wk['upcoming'] == 0) {
	$point = "This workshop had started or is in the past.";
} elseif ($wk['cancelled'] == true) {
	$point = "This workshop is CANCELLED.";
} else {
	if ($u->logged_in()) {
		$enroll_link = "$sc?ac=enroll&wid={$wk['id']}";

		switch ($e->fields['status_id']) {
			case ENROLLED:
				$point = "You are ENROLLED in the practice listed below. Would you like to <a class='btn btn-primary' href='$sc?ac=drop&wid={$wk['id']}&v=winfo'>drop</a> it?";
				break;
			case WAITING:
				$point = "You are spot number {$e->fields['rank']} on the WAIT LIST for the practice listed below. Would you like to <a class='btn btn-primary' href='$sc?ac=drop&wid={$wk['id']}&v=winfo'>drop</a> it?";
				break;
			case INVITED:
				$point = "A spot opened up in the practice listed below. Would you like to <a class='btn btn-primary' href='$sc?ac=accept&wid={$wk['id']}&v=winfo'>accept</a> it, or <a class='btn btn-primary' href='$sc?ac=decline&wid={$wk['id']}&v=winfo'>decline</a> it?";
				break;
			case DROPPED:
				$point = "You have dropped out of the practice listed below. ".
					($wk['soldout'] == 1 ? "The class is full! Do you want to be on the <a class='btn btn-primary'  href='$enroll_link'>wait list</a>?" : "Would you like to <a class='btn btn-primary'  href='$enroll_link'>re-enroll</a>?").
						" Info will be sent to <b>{$u->fields['email']}</b>.";
				break;
			default:
	
				$point = 
					($wk['soldout'] == 1
					? "The class is full. Want to be on the <a class='btn btn-primary' href='$enroll_link'>wait list</a>?"
					: "Click here to <a class='btn btn-primary' href='$enroll_link'>enroll</a> in this class.  Info will be sent to <b>{$u->fields['email']}</b>.");
	
				break;
		}
	} else {
		$point = "You are not logged in. You must be logged in to enroll.<br><br>To log in, click the 'Login' button at the top-right corner of this page.<br><br>If you're on a phone, you'll see a square with three lines at the top of the page. Click that, then click 'Login'.";	
	}
}
?>
<div class='row'><div class='col'>


<?php
	
	
echo "	
<div class='row my-3 py-3'><div class='col-sm-6'>
<h2>{$wk['title']}</h2>
<p>{$wk['notes']}</p>
<p>{$wk['full_when']} (".TIMEZONE.")<br><br>
{$wk['costdisplay']}, {$wk['enrolled']} (of {$wk['capacity']}) enrolled, ".($wk['waiting']+$wk['invited'])." waiting</p>\n";

if ($show_other_action)  {
	echo "<p class='alert alert-info'>{$point}</p>\n";
}


if ($u->check_user_level(2)) { 
	$eh = new EnrollmentsHelper();
	$lists = $eh->get_students($wk['id'], ENROLLED);
	echo "<div class='m-3 p-3 bg-info'>\n";
	echo "<h3>Teacher/Admin Info</h3>\n";
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



if ($e->fields['status_id'] == ENROLLED && $wk['location_id'] == ONLINE_LOCATION_ID) {
	
	echo "<p class='alert alert-success'><b>Zoom link</b>:<br>{$wk['online_url']}</p>\n";
	
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
