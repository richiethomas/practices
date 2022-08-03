<?php
$view->data['heading'] = "catalog";


$sql = "select * 
from workshops w 
where start > '2020-03-01'
order by start desc";

$stmt = \DB\pdo_query($sql);
$classes = array();
while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
	$classes[$row['id']] = $row;
}

$view->data['classes'] = $classes;
$view->data['fb_description'] = "All past courses.";

$view->renderPage('about/catalog');

