<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * @todo document this
 *
 * @date 14.10.2006
 * @since 0.13
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @class UnknownClassException
 * @ingroup datatypes
 * @package datatypes
 */
class UnknownClassException extends BcmsException {

	public function __construct($classname, $code = 0){
		$message = 'Unknown class: Class file is not registered in this system! ' .
				'Requested class: '.$classname; // @todo Use dictionary
		parent::__construct($message, $code);
	}
}
?>
