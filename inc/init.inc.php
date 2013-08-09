<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * @file init.inc.php
 * This Include-file describes some steps of the init process.
 * Most things are done in BcmsSystem::init()
 *
 * @author ahe <aheusingfeld@borderlesscms.de>
 * @ingroup init
 */

// include path setzen
require 'inc/set_inc_path.inc.php';

//! @todo include classnames into BcmsSystem class
require 'classnames.php';
/**
 * the __autoload function is called whenever a class shall be instantiated but is not loaded yet
 * @ingroup sys
 */
function __autoload($classname) {
	if(isset($GLOBALS['bcms_classes'][$classname])) {
		require $GLOBALS['bcms_classes'][$classname];
	}
}
/**
 * the exception_handler function overwrites the default php exception handler
 * and preserves that exceptions are handled in a way Borderless CMS prefers.
 * E.g. logging to db if possible.
 *
 * @ingroup sys
 */
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

// set our exception_handler after basic system initialization is finished
//set_exception_handler('exception_handler');
?>