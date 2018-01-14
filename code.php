<?php

if (!isset($_REQUEST['wid'])) { $_REQUEST['wid'] = 1; }
if (!isset($_REQUEST['key'])) { $_REQUEST['key'] = 'a'; }

$wid = preg_replace('/\D/', '', $_REQUEST['wid']);
$key = preg_replace('/[^\da-z]/i', '', $_REQUEST['key']);

$url = "http://www.willhines.net/practices/index.php?wid={$wid}&key={$key}&v=winfo";

include('phpqrcode/qrlib.php'); 
    
   // outputs image directly into browser, as PNG stream 
   QRcode::png($url);	
	
