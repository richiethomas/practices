<?php

$vars = array('id', 'title', 'event_when', 'email', 'payment_amount', 'reminder_email_id');
Wbhkit\set_vars($vars);

$tid = (int) ($params[2] ?? 0); 
$show = 'future';

$th = new TasksHelper();


switch ($ac) {
	
	case 'clone':
		$t = new Task();
		$t->set_by_id($tid);
		foreach ($vars as $v) {
			$$v = isset($t->fields[$v]) ? $t->fields[$v] : null;
		}
		$guest = new User();
		$guest->set_by_id($t->fields['user_id']);
		$email = $guest->fields['email'];
		$id = null;
		break;

	case 'edit':
		$t = new Task();
		$t->set_by_id($tid);
		$id = $tid;
		foreach ($vars as $v) {
			if (isset($t->fields[$v])) {
				$$v = $t->fields[$v];
			}
		}
		$guest = new User();
		$guest->set_by_id($t->fields['user_id']);
		$email = $guest->fields['email'];
		break;

	case 'add':
	case 'update':
		
		if ($title && $event_when && $email && $reminder_email_id) {
		
			$guest = new User();
			if ($guest->set_by_email($email)) {
			
				$t = new Task();
				$t->set_into_fields(
					array(
						'title' => $title,
						'event_when' => $event_when,
						'user_id' => $guest->fields['id'],
						'payment_amount' => $payment_amount,
						'reminder_email_id' => $reminder_email_id
					)
				);
			
				if ($id) { $t->fields['id'] = $id; }
				
				if ($t->save_data()) {
					$message = $id ? "Task '$title' updated." : "Take '$title' added.";
				} else {
					$error = $id ? "Task '$title' failed to update." : "Task '$title' failed to add.";
				}
			}
		}
		break;
		
	case 'delete':
	
		$t = new Task();
		$t->set_by_id($tid);
		$message = "Deleted task '{$t->fields['title']}'";
		$t->delete_row();
		break;
		
	case 'view':
		$show = (string) ($params[2] ?? 'future');
		break; 
		
	
}

$view->add_globals($vars);
$view->data['heading'] = "tasks";
$view->data['show'] = $show;
$view->data['tasks'] = $th->get_tasks(
	$show == 'future' ? true : false
);
$view->renderPage('admin/tasks');





