<div class='row'><div class='col-md-12'><h2>Transcript for <?php echo $u['email']; ?></h2>

<p><a href='<?php echo URL."index.php?key=$key"; ?>'>Log in as <?php echo $u['email']; ?></a></p>

<?php
if ($needle) {
	echo "<p>Return to <a href='admin_search.php?needle=$needle'>search results</a></p>\n";
}
?>

<h3 class="my-3">Transcripts</h3>
<?php echo $transcripts; ?>

<h3 class="my-3">Change Email</h3>
<?php echo $change_email_form; ?>

<h3 class="my-3">Text Preferences</h3>
<?php echo $text_preferences; ?>

<h3 class="my-3">Display Name</h3>
<?php echo $display_name_form; ?>

<p>or</p>

<p><a class='btn btn-danger' href='<?php echo "$sc?ac=delstudent&uid={$u['id']}" ?>'>remove this student</a></p>

</div></div>
