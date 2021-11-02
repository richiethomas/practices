<?php
	
class Lookups extends WBHObject {
	
	public array $statuses;
	public array $carriers;
	public array $carriers_drop;
	public array $locations;
	public array $groups;
	
    function __construct() {
		
		$stmt = \DB\pdo_query("select * from statuses order by id");
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$this->statuses[$row['id']] = $row['status_name'];
		}
		$stmt = \DB\pdo_query("select * from carriers order by id");
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$this->carriers[$row['id']] = $row;
			$this->carriers_drop[$row['id']] = $row['network'];
		}
		
		$stmt = \DB\pdo_query("select * from locations order by id");
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$this->locations[$row['id']] = $row;
			$this->locations[$row['id']]['lwhere'] = $row['address'].' '.$row['city'].' '.$row['state'].' '.$row['zip'];
		}
		
		$stmt = \DB\pdo_query("select * from groups order by id");
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$this->groups[$row['id']] = $row['name'];
		}
		
    }
	
	public function find_status_by_value($stname) {
		foreach ($this->statuses as $status_id => $status_name) {
			if ($status_name == $stname) {
				return $status_id;
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