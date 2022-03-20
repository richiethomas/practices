<?php
	
class Lookups extends WBHObject {
	
	public array $statuses;
	public array $locations;
	public array $groups;
	
    function __construct() {
		
		$stmt = \DB\pdo_query("select * from statuses order by id");
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$this->statuses[$row['id']] = $row['status_name'];
		}
		
		$stmt = \DB\pdo_query("select * from locations order by id");
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$this->locations[$row['id']] = $row;
			$this->locations[$row['id']]['lwhere'] = $row['place'].' - '.$row['address'].' '.$row['city'].' '.$row['state'].' '.$row['zip'];
		}
		
		$stmt = \DB\pdo_query("select * from groups order by id");
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$this->groups[$row['id']] = $row['name'];
		}
		

    }
	
	public function find_status_by_name(string $stname) {
		foreach ($this->statuses as $status_id => $status_name) {
			if ($status_name == $stname) {
				return $status_id;
			}
		}
		return false;
	}

	public function find_status_by_value(int $stid) {
		foreach ($this->statuses as $status_id => $status_name) {
			if ($status_id == $stid) {
				return $status_name;
			}
		}
		return false;
	}
	
	public function locations_drop($lid = null) {
		$opts = array();
		foreach ($this->locations as $id => $info) {
			$opts[$id] = $info['place'];
		}
		return $opts;
	}

	
}	
	
?>