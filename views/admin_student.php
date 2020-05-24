<div class='row'><div class='col-md-12'><h2>Transcript for <a href='<?php echo "$sc?guest_id={$guest['id']}&needle=".$needle; ?>'><?php echo $guest['email']; ?></a></h2>


<?php 
if (Users\check_user_level(3)) {
	echo "<p><a href='".URL."index.php?key=$key'>Log in as {$guest['email']}</a></p>\n";
} 


if ($needle) {
	echo "<p>Return to <a href='admin_search.php?needle=$needle'>search results</a></p>\n";
}
?>

<h3 class="my-3">Transcripts</h3>
<?php echo $transcripts; ?>

<div class='row justify-content-center'><div class='col-sm-6'>

<div class='card my-5 bg-light'><div class='card-body'>
<h3 class="my-3">Change Email</h3>
<?php echo $change_email_form; ?>
</div></div> <!-- end of card -->

<div class='card my-5 bg-light'><div class='card-body'>
<h3 class="my-3">Text Preferences</h3>
<?php echo $text_preferences; ?>
</div></div> <!-- end of card -->

<div class='card my-5 bg-light'><div class='card-body'>
<h3 class="my-3">Display Name</h3>
<?php echo $display_name_form; ?>
</div></div> <!-- end of card -->
</div></div> <!-- end of 6 col row -->

<p>or</p>

<p><a class='btn btn-danger' href='<?php echo "$sc?ac=delstudent&guest_id={$guest['id']}" ?>'>remove this student</a></p>

</div></div>
