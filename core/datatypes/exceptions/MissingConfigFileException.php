<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * The MissingConfigFileException is thrown when the config file cannot be found.
 * This is the case when the system has not been installed yet.
 *
 * @date 03.04.2007
 * @since 0.13.89
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @class MissingConfigFileException
 * @ingroup datatypes
 * @package datatypes
 */
class MissingConfigFileException extends BcmsException {

	/**
	 * default public constructor
	 *
	 * @param String $servername - name of the host for which a config file is missing
	 * @param integer $code - (optional) a severity constant of BcmsSystem
	 * @return MissingConfigFileException
	 * @author ahe
	 */
	public function __construct($servername, $code = 0){
		$message = 'Configuration file for server "'.$servername.'" could not be found!'; // @todo Use dictionary
		if($code<1) $code = BcmsSystem::SEVERITY_ERROR;
		parent::__construct($message, $code);
	}
}
?>
