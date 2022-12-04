<?php
$view->data['heading'] = "reminders";


if ($action == 'force') {
	Reminders\check_reminders(true); // force a new reminder check
	$message = "Reminder check FORCED";
}


$view->data['reminders'] = Reminders\get_reminders();
$view->renderPage('admin/reminders');



