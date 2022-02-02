<?php
	
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Create the logger
$logger = new Logger('my_logger');

$logger->pushHandler(new StreamHandler(DEBUG_LOG, Logger::INFO));	
$logger->pushHandler(new StreamHandler(ERROR_LOG, Logger::ERROR));	




