<?php

$vars = array('id', 'slug', 'subject', 'body');
Wbhkit\set_vars($vars);

$reid = (int) ($params[2] ?? 0); 
$reh = new ReminderEmailsHelper();


switch ($action) {
	

	case 'edit':
		$re = new ReminderEmail();
		$re->set_by_id($reid);
		$id = $reid;
		foreach ($vars as $v) {
			if (isset($re->fields[$v])) {
				$$v = $re->fields[$v];
			}
		}
		break;

	case 'add':
	case 'update':
		
		if ($slug && $subject && $body) {
		
			$re = new ReminderEmail();
			$re->set_into_fields(
				array(
					'slug' => $slug,
					'subject' => $subject,
					'body' => $body)
			);
		
			if ($id) { $re->fields['id'] = $id; }
			
			if ($re->save_data()) {
				$message = $id ? "Reminder email '$slug' updated." : "Reminder email '$slug' added.";
			} else {
				$error = $id ? "Reminder email '$slug' failed to update." : "Reminder email '$slug' failed to add.";
			}
		}
		break;
		
	case 'delete':
	
		$re = new ReminderEmail();
		$re->set_by_id($reid);
		$message = "Deleted reminder email '{$re->fields['slug']}";
		$re->delete_row();
		break;
	
}

$view->add_globals($vars);
$view->data['heading'] = "reminder emails";
$view->data['reminder_emails'] = $reh->get_reminder_emails();
$view->renderPage('admin/reminder-emails');





