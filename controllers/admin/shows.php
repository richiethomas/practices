<?php

$vars = array('plaintext');
Wbhkit\set_vars($vars);


$view->data['heading'] = "class shows";
$view->data['plaintext'] = $plaintext;
$view->data['shows'] = get_class_shows();
$view->renderPage('admin/shows');


// get 'em all
function get_class_shows() {
	
	$stmt = \DB\pdo_query("
		select x.*, w.title, u.display_name, u.email
	from xtra_sessions x, workshops w, teachers t, users u
	where x.workshop_id = w.id
	and w.teacher_id = t.id
	and t.user_id = u.id
	and x.class_show = 1
	order by date(x.start) desc, time(x.start) asc
	limit 50");
	$cs = array();
	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
		$cs[$row['id']] = $row;
	}
	return $cs;
}	



