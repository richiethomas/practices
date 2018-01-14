<?php
	
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Create the logger
$logger = new Logger('my_logger');

// Now add some handlers
if (DEBUG_MODE) {
	$logger->pushHandler(new StreamHandler(ERROR_LOG, Logger::DEBUG));	
} else {
	$logger->pushHandler(new StreamHandler(ERROR_LOG, Logger::ERROR));	
}



