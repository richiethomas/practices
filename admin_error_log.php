<?php
$heading = "debug";
include 'lib-master.php';

Wbhkit\set_vars(array('ac', 'deldate'));

$log = file(ERROR_LOG);

switch ($ac) {
	case 'condel':
		file_put_contents(ERROR_LOG, '');
		$log = file(ERROR_LOG);
		break;
	
	case 'deldate':
		if ($deldate) {
			$newlog = array();
			foreach ($log as $l) {
				foreach ($deldate as $d) {
					if (preg_match("/^\[$d/", $l)) {
						continue 2;
					}
				}
				$newlog[] = $l;
			}
			file_put_contents(ERROR_LOG, implode($newlog));
			$log = $newlog;
		}
		break;
}



//get days
$dates = array();
foreach ($log as $l) {
	if (preg_match('/^\[(.+?)(T| )/', $l, $matches)) {
		if (empty($dates[$matches[1]])) {
			$dates[$matches[1]] = 1;
		} else {
			$dates[$matches[1]]++;
		}
	}
}
$dates_opts = array();
foreach ($dates as $id => $value) {
	$dates_opts[$id] = $id;
}

if (empty($log)) {
	$log = 'No log activity!';
}

$view->data['dates_opts'] = $dates_opts; 
$view->data['log'] = $log;
$view->data['ac'] = $ac;
$view->renderPage('admin/error_log');



