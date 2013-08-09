<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * PearDbErrorException handles PEAR_Error objects. This exception class parses
 * the object into the exception object and - if not switched off by parameter -
 * raises a notice about the error which also logs the error to the database.
 *
 * @date 18.04.2007
 * @since 0.13.153
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @class PearDbErrorException
 * @ingroup datatypes
 * @package datatypes
 */
class PearDbErrorException extends BcmsException {

    public function __construct(PEAR_Error $error, $code = 0, $raiseNotice=true){

        if($code==0) {
        	$code = BcmsSystem::SEVERITY_ERROR;
        }
        $message = 'A database error occurred! '; // @todo Use dictionary
        $message = $message.' '.$error->getMessage();
        parent::__construct($message, $code);

        if($raiseNotice){
	        $debugInfo['displayMsg'] = $message;
	        $debugInfo['debugInfo'] = $error->getDebugInfo();
	        $debugInfo['backtrace'] = array();
	        foreach ($error->backtrace as $number => $traceArray) {
	            $debugInfo['backtrace'][$number]['file'] = $traceArray['file'];
	            $debugInfo['backtrace'][$number]['line'] = $traceArray['line'];
	        }
	        $notice = print_r($debugInfo,true);

	        BcmsSystem::raiseNotice($notice, BcmsSystem::LOGTYPE_EXCEPTION, $code, $methodname,
	            $this->getFile(), $this->getLine(), $message);
        }
    }
}
?>
