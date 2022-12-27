<div class='row'><div class='col-md-12'><h2><a href='/admin-emails'>get emails</a></h2>
<div class='well'><form action ='/admin-emails/gemail' method='post'>
	
<?php echo 	Wbhkit\checkbox('opt_outs', 1, 'include opt outs (evil)'); ?>
<div class='row justify-content-center'>
	<div class='col-5'>
<?php echo 	Wbhkit\multi_drop('workshops', $all_workshops, $workshops, 'Workshops  (--- means in person)', 15); ?>
	<?php echo  Wbhkit\submit('get emails'); ?>
	</div>
	<div class='col-5'>
<?php echo 	Wbhkit\multi_drop('teams', $all_teams, $teams, 'Teams  (--- means in person)', 15); ?>
	</div>
</div>
</form></div>
<div class='row justify-content-center'>
	<div class='col-5'>
<?php
	
	$es = '';
	$nn = '';
	if (count($student_emails) > 0) {
		$es = implode(",\n", $student_emails);
	}
	if (count($student_names) > 0) {
		$nn = implode(",\n", $student_names);
	}
	
	echo "<div id='emaillists'>\n";
	if ($es) { echo Wbhkit\textarea('emails', $es, 0); }
	if ($nn) { echo Wbhkit\textarea('names', $nn, 0); }
	echo "</div>\n";
		
?>
</div></div>

</div></div>
		
		