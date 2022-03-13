<h1><a href="/admin-tasks/">Tasks</a></h1>
<p>edit the <a href="/admin-reminder-emails">reminder emails</a></p>
<?php

$re = new ReminderEmail();

// add new task

include 'ajax-jquery-search.php';


echo "<div class='row'><div class='col-md-5'>\n";

if ($id) {
	echo "<form id='add_task' action='/admin-tasks/update' method='post' novalidate>";
	echo "<input type='hidden' name='id' value='{$id}'>";
} else {
	echo "<form id='add_task' action='/admin-tasks/add' method='post' novalidate>";
}

echo \Wbhkit\form_validation_javascript('add_task');

echo "<fieldset name='task_add'><legend>".($id ? "Update" : "Add")." Task</legend>";

$event_when = \Wbhkit\friendly_when($event_when, true);

echo \Wbhkit\texty('event_when', $event_when, 'When', null, null, 'Required', ' required ').
\Wbhkit\texty('title', $title, 'Title of Task', null, null, 'Required', ' required ');

echo "<label for='search-box' class='form-label'>Who (email):</label>
<input type='text' class='form-control' id='search-box' name='email' autocomplete='off' value='$email'>
<div id='suggesstion-box'></div>";

echo \Wbhkit\texty('payment_amount', $payment_amount, null, null, null, 'Required', ' required ').
\Wbhkit\drop('reminder_email_id', $re->get_reminder_emails_dropdown(), $reminder_email_id, 'Reminder Email');
	
if ($id) {
	echo \Wbhkit\submit('Update'); 	
} else {
	echo \Wbhkit\submit('Add');
}


echo "</fieldset></form>
</div></div>



<h2 class='mt-3'>Existing Tasks ".($show == 'future' ? " - future" : " - including past")."</h2>
<p><a href='/admin-tasks/view/future'>just future</a> | <a href='/admin-tasks/view/past'>include past</a></p>";


// list tasks	
echo "<div class='row my-2 fw-bold'>		
	<div class='col'>When</div>
	<div class='col'>What</div>
	<div class='col'>Who</div>
	<div class='col'>Pay</div>
	<div class='col'><a href='/admin-reminder-emails'>Email To Send</a></div>
	<div class='col'>Actions</div>
</div>\n";

	

foreach ($tasks as $t) {
	
	$when = \Wbhkit\figure_year_minutes(strtotime($t->fields['event_when']));
	
	echo "
		<div class='row my-2'>		
			<div class='col'>{$when}</div>
			<div class='col'>{$t->fields['title']}</div>
			<div class='col'>{$t->user->fields['display_name']}</div>
			<div class='col'>{$t->fields['payment_amount']}</div>
			<div class='col'>{$t->reminder_email->fields['slug']}</div>
			<div class='col'><a href='/admin-tasks/edit/{$t->fields['id']}'>edit</a> | <a href='/admin-tasks/delete/{$t->fields['id']}'>delete</a></div>
		</div>		
";
	
}
	
	
?>

