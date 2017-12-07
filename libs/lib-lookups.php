<?php
namespace Lookups;	


function get_statuses() {
	global $statuses;
	if (is_array($statuses) && count($statuses) > 0) {
		return $statuses;
	}
	$statuses = array();
	$sql = "select * from statuses order by id";
	$rows = \Database\mysqli($sql) or \Database\db_error();
	while ($row = mysqli_fetch_assoc($rows)) {
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
	$sql = "select * from carriers order by id";
	$rows = \Database\mysqli($sql) or \Database\db_error();
	while ($row = mysqli_fetch_assoc($rows)) {
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
	$sql = "select * from locations";
	$rows = \Database\mysqli( $sql) or \Database\db_error();
	$locations = array();
	while ($row = mysqli_fetch_assoc($rows)) {
		$row['lwhere'] = $row['address'].' '.$row['city'].' '.$row['state'].' '.$row['zip'];
		$locations[$row['id']]['place'] = $row['place'];
		$locations[$row['id']]['lwhere'] = $row['lwhere'];
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