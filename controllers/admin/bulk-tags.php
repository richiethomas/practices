<?php
$view->data['heading'] = "bulk tags edit";

//$wk_vars = array('st', 'confirm');
//Wbhkit\set_vars($wk_vars);

switch ($ac) {

	case 'update':
		
		foreach ($_REQUEST as $k => $v) {
			if (substr($k, 0, 5) == 'tags_') {
				$ps = explode('_', $k);
				if ($v && $v != $_REQUEST["hidden_{$ps[1]}"]) {
					\Workshops\update_tags($ps[1], $v);
				}
			}
		}
		break;
		
}



$view->data['workshops'] = \Workshops\get_recent_workshops_simple();
$view->renderPage('admin/bulk-tags');




