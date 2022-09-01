<?php 
// controller

$view->data['heading'] = "conflicts";

$wh = new WorkshopsHelper();
$view->data['workshops'] = $wh->get_sessions_to_come(true); // get enrollments, show hidden
$view->data['teacher_conflicts'] = find_conflicts($view->data['workshops']);
$view->data['registration_conflicts'] = find_registration_conflicts();

$view->renderPage('admin/conflicts');

function find_conflicts(array $sessions) {

	$conflicts = array();
	
	while ($s1 = array_pop($sessions)) {
		
		$start1 = strtotime($s1['start']);
		$end1 = strtotime($s1['end']);
		
		$counter = 0;
		$other_teachers_courses = array();
			
		foreach ($sessions as $s2) {
			
			if (in_array($s2['id'], $other_teachers_courses)) {
				continue;
			}
									
			$start2 = strtotime($s2['start']);
			$end2 = strtotime($s2['end']);
			
			// teacher / co-teacher overlap?
			if (
				($s1['teacher_id'] == $s2['teacher_id'] || $s1['teacher_id'] == $s2['co_teacher_id']) ||
				(($s1['co_teacher_id']) && ($s1['co_teacher_id'] == $s2['co_teacher_id'] || $s1['co_teacher_id'] == $s2['teacher_id']))
				) {
				
					// time overlap?
					if ($start1 >= $start2 && $start1 <= $end2 || 
					$end1 >= $start2 && $end1 <= $end2 || 
					$start1 <= $start2 && $end1 >= $end2) {
				
						$conflicts[] = array($s1, $s2);
					}
				
			} else {
				$other_teachers_courses[] = $s2['id'];
			}
			$counter++;
				
		}
				
	}	
	return $conflicts;
	
}

function find_registration_conflicts() {
	
	$sql = "select r.id, r.user_id, w.start, w.end, w.title, u.email, u.display_name, r.workshop_id, 1 as rank
	from registrations r, workshops w, users u
	where r.workshop_id = w.id
	and r.user_id = u.id
	and r.status_id = 1
	and w.start > now()
	UNION
	select r.id, r.user_id, x.start, x.end, w.title, u.email, u.display_name, r.workshop_id, x.rank
	from registrations r, workshops w, users u, xtra_sessions x
	where r.workshop_id = w.id
	and x.workshop_id = w.id
	and r.user_id = u.id
	and r.status_id = 1
	and x.start > now()
	order by start";
	
	$stmt = \DB\pdo_query($sql);
	$all_regs = array();
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		$all_regs[$row['user_id']][] = array($row); // groups by user
	}
	
	$conflicts = array();
	
	foreach ($all_regs as $uid => $r) { // get all regs for one user
		
		while ($r1 = array_pop($r)) { // go through regs of just that user 
		
			$start1 = strtotime($r1[0]['start']);
			$end1 = strtotime($r1[0]['end']);
			
			foreach ($r as $r2) {
								
				if ($r2[0]['workshop_id'] == $r1[0]['workshop_id']) { 
					continue; 
				}
				
				$start2 = strtotime($r2[0]['start']);
				$end2 = strtotime($r2[0]['end']);
				
				if ($start1 > $start2 && $start1 < $end2 || 
				$end1 > $start2 && $end1 < $end2 || 
				$start1 < $start2 && $end1 > $end2) {
					
					$conflicts[] = array($r1, $r2);
				}
			}
		}
	}
	
	return $conflicts;

}

