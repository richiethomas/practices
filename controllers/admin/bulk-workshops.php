<?php
$view->data['heading'] = "bulk tags edit";

//$wk_vars = array('st', 'confirm');
//Wbhkit\set_vars($wk_vars);

switch ($action) {

	case 'update':
		
		foreach ($_REQUEST as $k => $v) {
			// were tags changes
			if (substr($k, 0, 5) == 'tags_') {
				$ps = explode('_', $k);
				if ($v && $v != $_REQUEST["hiddentags_{$ps[1]}"]) {
					update_tags($ps[1], $v);
				}
			}

			// was hidden flag changed
			if (substr($k, 0, 13) == 'hiddenhidden_') {
				$ps = explode('_', $k);
				$hidden_flag_checkbox = isset($_REQUEST["hidden_{$ps[1]}"]) ? 1 : 0;
				if ($v != $hidden_flag_checkbox) {
					update_hidden($ps[1], $hidden_flag_checkbox);
				}
			}

			// was 'when public' changed
			if (substr($k, 0, 3) == 'wp_') {
				$ps = explode('_', $k);
				if (strtotime($v) != $_REQUEST["hiddenwp_{$ps[1]}"]) {
					update_wp($ps[1], $v);
				}
			}
			
			
		}
		break;
}


$wh = new WorkshopsHelper();
$view->data['workshops'] = $wh->get_recent_workshops_simple();
$view->renderPage('admin/bulk-workshops');


function update_tags(int $id, string $tags) {
	$sql = "update workshops set tags = :tags where id = :id";			
	$stmt = \DB\pdo_query($sql, array(':id' => $id, ':tags' => $tags));
	return true;
}

function update_hidden(int $id, string $hidden) {
	$sql = "update workshops set hidden = :hidden where id = :id";			
	$stmt = \DB\pdo_query($sql, array(':id' => $id, ':hidden' => $hidden));
	return true;
}

function update_wp(int $id, string $wp) {
	
	$wp = date(MYSQL_FORMAT, strtotime($wp));
	$sql = "update workshops set when_public = :wp where id = :id";			
	$stmt = \DB\pdo_query($sql, array(':id' => $id, ':wp' => $wp));
	return true;
}


