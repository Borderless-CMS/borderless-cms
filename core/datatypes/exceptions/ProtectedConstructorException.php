<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * This Exception is actually just a temporary workaround...
 *
 * @date 14.10.2006
 * @since 0.13
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @class ProtectedConstructorException
 * @ingroup datatypes
 * @package datatypes
 */
class ProtectedConstructorException extends BcmsException {

	/**
	 * public default contructor
	 *
	 * @param String $classname - class which should be instantiated
	 * @param integer $code - (optional) a severity constant of BcmsSystem
	 */
	public function __construct($classname, $code = 0){
		$message = 'The constructor of the class "'.$classname.'" is actually protected!' .
		' Use "'.$classname.'::getInstance()" to retrieve an instance!'; // @todo Use dictionary
		if($code<1) $code = BcmsSystem::SEVERITY_ERROR;
		parent::__construct($message, $code);
	}
}
?>
