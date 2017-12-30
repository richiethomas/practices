<?php
namespace Lookups;	


function get_statuses() {
	global $statuses;
	if (is_array($statuses) && count($statuses) > 0) {
		return $statuses;
	}
	$statuses = array();
	$stmt = \DB\pdo_query("select * from statuses order by id");
	while ($row = $stmt->fetch()) {
		$statuses[$row['id']] = $row['status_name'];
	}
	return $statuses;
	
}

function find_status_by_value($stname) {
	$statuses = get_statuses();
	foreach ($statuses as $status_id => $status_name) {
		if ($status_name == $stname) {
			return $status_id;
		}
	}
	return false;
}

function get_carriers($update = 0) {
	global $carriers;
	if (is_array($carriers) && count($carriers) > 0 && !$update) {
		return $carriers;
	}
	$carriers = array();
	$stmt = \DB\pdo_query("select * from carriers order by id");
	while ($row = $stmt->fetch()) {
		$carriers[$row['id']] = $row;
	}
	return $carriers;
	
}

function get_carriers_drop() {
	$carriers = get_carriers();
	$cardrop = '';
	foreach ($carriers as $c) {
		$cardrop[$c['id']] = $c['network'];
	}
	return $cardrop;
}

// locations
function get_locations() {
	global $locations;
	if (!$locations) {
		$stmt = \DB\pdo_query("select * from locations order by id");
		while ($row = $stmt->fetch()) {
			$locations[$row['id']] = $row;
			$locations[$row['id']]['lwhere'] = $row['address'].' '.$row['city'].' '.$row['state'].' '.$row['zip'];
		}
	}
	return $locations;
}

function locations_drop($lid = null) {
	$l = get_locations();
	$opts = array();
	foreach ($l as $id => $info) {
		$opts[$id] = $info['place'];
	}
	return \Wbhkit\drop('lid', $opts, $lid, 'Location', null, 'Required', ' required ');
}

	
	
?>