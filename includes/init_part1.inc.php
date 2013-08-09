<?php
/**
  * Diese Include-Datei beschreibt den ersten Teil des Initprozesses.
  */
if(!defined('BORDERLESS')) exit;

// the __autoload function is called whenever a class shall be instantiated but
//  is not loaded yet
require 'classnames.php'; // TODO include classnames into BcmsSystem class
function __autoload($classname) {
	if(isset($GLOBALS['bcms_classes'][$classname])) {
		require $GLOBALS['bcms_classes'][$classname];
	}
}
function exception_handler($exception) {
	$displayMsg = $exception->getMessage();
  	$msg = 'Exception occured: '. $exception->getMessage()
  		."\n Trace: ".$exception->getTraceAsString();
  	BcmsSystem::raiseError(
		$msg,
  		BcmsSystem::LOGTYPE_EXCEPTION,
  		$exception->getCode(),
  		null,
  		$exception->getFile(),
  		$exception->getLine(),
  		$displayMsg);
  	// ATTENTION: Execution of script will stop here! This is PHP default!
}
BcmsSystem::init();
// set our exception_handler after basic system initialization is finished
set_exception_handler('exception_handler');
?>