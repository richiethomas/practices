<?php
$view->data['heading'] = "teams";

$team = new Team();

$vars_to_set = array('email');
$vars_to_set = \Wbhkit\add_empty_fields($vars_to_set, $team->fields);
Wbhkit\set_vars($vars_to_set);

$tid =  (int) ($params[2] ?? 0);
if ($tid) {
	$team->set_by_id($tid);
}

switch ($action) {
	
	case 'adup':
		foreach ($team->cols as $colname => $colvalue) {
			if ($colname == 'id') { continue; } // handle id special
			$team->fields[$colname] = $$colname;
		}
		$team->save_data();
		$team->set_by_id($team->fields['id']); // refresh data
		break;
		
	case 'addmember':
		$member = new User();
		$member->set_by_email($email);
		if ($member->fields['id']) {
			$team->add_member($member->fields['id']);
		}
		break;
	
	case 'removemember':
		$uid =  (int) ($params[3] ?? 0);
		$team->remove_member($uid);
		break;
		
	case 'delete':
		$error = "Are you sure you want to <a class='btn btn-danger' href='/admin-teams/condelete/{$team->fields['id']}'>delete team '{$team->fields['title']}'</a>?";
		break;
		
	case 'condelete':
		if ($team->fields['id']) {
			$team->delete_row();
		}
		break;

}

$teamsHelper = new TeamsHelper();
$view->data['team'] = $team;
$view->data['teams'] = $teamsHelper->get_teams();
$view->renderPage('admin/teams');



