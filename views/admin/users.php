<div class='row'><div class='col-md-12'><h2>Transcript for <a href='<?php echo "$sc?guest_id={$guest->fields['id']}&needle=".$needle; ?>'><?php echo $guest->fields['nice_name']; ?></a></h2>
	<?php echo "<p>Cut and pastable: {$guest->fields['display_name']} {$guest->fields['email']}</p>"; ?>


<?php 
if ($u->check_user_level(3)) {
	echo "<p><a href='".URL."home/k/$key'>Log in as {$guest->fields['email']}</a></p>\n";
} 


if ($needle) {
	echo "<p>Return to <a href='admin_search.php?needle=$needle'>search results</a></p>\n";
}
?>

<h3 class="my-3">Transcripts</h3>
<?php echo $transcripts; ?>

<div class='row'><div class='col-sm-6'>

<div class='card my-5 bg-light'><div class='card-body'>
<h3 class="my-3">Change Email</h3>
<?php echo $userhelper->edit_change_email($guest, $sc); ?>
</div></div> <!-- end of card -->

<?php
/*	
<div class='card my-5 bg-light'><div class='card-body'>
<h3 class="my-3">Text Preferences</h3>
<?php echo $userhelper->edit_text_preferences($guest, $sc, $lookups); ?>
</div></div> <!-- end of card -->
*/
?>

<div class='card my-5 bg-light'><div class='card-body'>
<h3 class="my-3">Display Name</h3>
<?php echo $userhelper->edit_display_name($guest, $sc); ?>
</div></div> <!-- end of card -->

<?php if ($u->check_user_level(3)) { ?>
<div class='card my-5 bg-light'><div class='card-body'>
<h3 class="my-3">Group Level</h3>
<?php echo $userhelper->edit_group_level($guest, $sc, $lookups); ?>
</div></div> <!-- end of card -->
<?php } ?>

<div class='card my-5 bg-light'><div class='card-body'>
<h3 class="my-3">Teacher Status</h3>
<?php 
$t = \Teachers\is_teacher($guest->fields['id']);
if ($t) {
	echo "<p>{$guest->fields['nice_name']} is a teacher. <a class=\"btn btn-primary text-light\" href='admin_teachers.php?ac=view&tid={$t['id']}'>Edit teacher info</a></p>";
} else {
	echo "<p>{$guest->fields['nice_name']} is NOT a teacher. <a class=\"btn btn-primary text-light\" href='admin_teachers.php?ac=make&guest_id={$guest->fields['id']}'>Make teacher</a></p>";	
}
?>
</div></div> <!-- end of card -->
</div></div> <!-- end of 6 col row -->


<p>or</p>

<p><a class='btn btn-danger text-light' href='<?php echo "$sc?ac=delstudent&guest_id={$guest->fields['id']}" ?>'>remove this student</a></p>

</div></div>
