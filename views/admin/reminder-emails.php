<h1><a href="/admin-reminder-emails">Reminder Emails</a></h1>
<p>Return to <a href='/admin-tasks/'>tasks</a></p>

<?php

$re = new ReminderEmail();

echo "<div class='row'><div class='col-md-5'>\n";

if ($id) {
	echo "<form id='add_reminder_email' action='/admin-reminder-emails/update' method='post' novalidate>";
	echo "<input type='hidden' name='id' value='{$id}'>";
} else {
	echo "<form id='add_reminder_email' action='/admin-reminder-emails/add' method='post' novalidate>";
}

echo \Wbhkit\form_validation_javascript('add_reminder_email');

echo "<fieldset name='task_add'><legend>".($id ? "Update" : "Add")." Reminder Email</legend>";

echo \Wbhkit\texty('slug', $slug, null, null, null, 'Required', ' required ').
\Wbhkit\texty('subject', $subject, null, null, null, 'Required', ' required ');

echo "<p class='my-2'>These uppercase phrases get replaced:<br>USERNAME, USEREMAIL, EVENTWHEN, TASKTITLE</p>";

echo \Wbhkit\textarea('body', $body);
	
if ($id) {
	echo \Wbhkit\submit('Update'); 	
} else {
	echo \Wbhkit\submit('Add');
}


echo "</fieldset></form>
</div></div>



<h2 class='mt-3'>Existing Reminder Emails</h2>
";


// list reminder emails	
echo "<div class='row my-2 fw-bold'>		
	<div class='col'>Slug</div>
	<div class='col'>Subject</div>
	<div class='col'>Actions</div>
</div>\n";

	

foreach ($reminder_emails as $re) {
	
	echo "
		<div class='row my-2'>		
			<div class='col'>{$re->fields['slug']}</div>
			<div class='col'>{$re->fields['subject']}</div>
			<div class='col'><a href='/admin-reminder-emails/edit/{$re->fields['id']}'>edit</a> | <a href='/admin-reminder-emails/delete/{$re->fields['id']}'>delete</a></div>
		</div>		
";
	
}
	
	
?>
