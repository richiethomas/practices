<?php
$view->data['heading'] = "list all";

$vars = array('needle', 'page');
Wbhkit\set_vars($vars);

switch ($action) {
	case 'clone':
		$page = '1';
		$needle = null;
		$wid = (int) ($params[2] ?? null); 
		$wk->set_by_id($wid);
		$wk->fields['reminder_sent'] = 0; // don't want to clone that part
		break;		
		
}

$wh = new WorkshopsHelper();

$view->data['page'] = $page;
$view->data['needle'] = $needle;
$view->data['workshops_list'] = $wh->get_search_results($page, $needle);
$view->data['wk'] = $wk; // for add workshop form

$view->renderPage('admin/archives');





