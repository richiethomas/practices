<div class="row justify-content-center"><div class="col-sm-6">
<h1>Shows</h1>


<?php


if (\Users\logged_in()) {
?>

<form action="shows.php" method="post">
<p>list of existing teams</p>
<p><b>OR</b></p>
<?php 
echo "<p>Captain: <b>{$u['nice_name']}</b></p>\n";
echo \Shows\get_teams_radio_fields($u['id']);
echo \Shows\get_team_form_fields($u['id']);
echo \Shows\get_show_fields(); 
echo \Wbhkit\submit('Apply');
?>	

</form>
	
	
<?php	
}


/*	
// actions: add application, delete application


//if logged in
//show form to apply
individual or team?
team name / individual name
available performances


//if have applications, show them

// show existing applications
each one. if it's in the future, have a delete link
*/	


?>
</div></div>