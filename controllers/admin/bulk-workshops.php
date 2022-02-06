<?php
$view->data['heading'] = "bulk tags edit";

//$wk_vars = array('st', 'confirm');
//Wbhkit\set_vars($wk_vars);

switch ($ac) {

	case 'update':
		
		foreach ($_REQUEST as $k => $v) {
			// were tags changes
			if (substr($k, 0, 5) == 'tags_') {
				$ps = explode('_', $k);
				if ($v && $v != $_REQUEST["hiddentags_{$ps[1]}"]) {
					\Workshops\update_tags($ps[1], $v);
				}
			}

			// was hidden flag changed
			if (substr($k, 0, 13) == 'hiddenhidden_') {
				$ps = explode('_', $k);
				$hidden_flag_checkbox = isset($_REQUEST["hidden_{$ps[1]}"]) ? 1 : 0;
				if ($v != $hidden_flag_checkbox) {
					\Workshops\update_hidden($ps[1], $hidden_flag_checkbox);
				}
			}

			// was 'when public' changed
			if (substr($k, 0, 3) == 'wp_') {
				$ps = explode('_', $k);
				if (strtotime($v) != $_REQUEST["hiddenwp_{$ps[1]}"]) {
					\Workshops\update_wp($ps[1], $v);
				}
			}
			
			
		}
		break;
}



$view->data['workshops'] = \Workshops\get_recent_workshops_simple();
$view->renderPage('admin/bulk-workshops');




