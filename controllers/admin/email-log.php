<?php
$view->data['heading'] = "email log";

$limit =  (int) ($params[2] ?? 100);


$rows = array();
$stmt = \DB\pdo_query("select * from email_log order by id desc limit :rows", array(":rows" => $limit)); 
while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
	$rows[] = $row; 
}

$view->data['rows'] = $rows;
$view->renderPage('admin/email-log');



