<?php
	
if ($wk['type'] == 'past') {
	$point = "This workshop is IN THE PAST.";
} elseif ($wk['cancelled'] == true) {
	$point = "This workshop is CANCELLED.";
} else {
	if (isset($u['id'])) {
		$enroll_link = "$sc?ac=enroll&wid={$wk['id']}";
		$key = $u['ukey'];

		switch ($e['status_id']) {
			case ENROLLED:
				$point = "You are ENROLLED in the practice listed below. Would you like to <a class='btn btn-primary' href='$sc?ac=drop&wid={$wk['id']}&key={$key}&v=winfo'>drop</a> it?";
				break;
			case WAITING:
				$point = "You are spot number {$e['rank']} on the WAIT LIST for the practice listed below. Would you like to <a class='btn btn-primary' href='$sc?ac=drop&wid={$wk['id']}&key={$key}&v=winfo'>drop</a> it?";
				break;
			case INVITED:
				$point = "A spot opened up in the practice listed below. Would you like to <a class='btn btn-primary' href='$sc?ac=accept&wid={$wk['id']}&key={$key}&v=winfo'>accept</a> it, or <a class='btn btn-primary' href='$sc?ac=decline&wid={$wk['id']}&key={$key}&v=winfo'>decline</a> it?";
				break;
			case DROPPED:
				$point = "You have dropped out of the practice listed below. Would you like to <a class='btn btn-primary'  href='$enroll_link'>re-enroll</a>?";
				break;
			default:
	
				$point = "You are not currenty signed up for the practice listed below. ".
					($wk['type'] == 'soldout' 
					? "It is full. Want to <a class='btn btn-primary' href='$enroll_link'>join the wait list</a>?"
					: "Want to <a class='btn btn-primary' href='$enroll_link'>enroll</a>?");
	
				break;
		}
	} else {
		$point = "If you wish to enroll, you must first log in <a href='$sc'>on the front page</a>.";	
	}
}
?>
<div class='row'><div class='col'>
<p class='alert alert-info'><?php echo $point; ?></p>
<p>Click here to <a href='<?php echo $sc; ?>'> <span class="oi oi-home" title="home" aria-hidden="true"></span> return to the main page</a>.</p>
<hr>
<?php echo $workshop_tabled; ?>
</div></div> <!-- end of col and row -->	
