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
 * @date 26.09.2006
 * @since 0.12
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @class BcmsList
 * @ingroup datatypes
 * @package datatypes
 */
class BcmsList extends BcmsObject {
	
	private $internalList = array();
	
	/**
	 * NOT finished yet!
	 *
	 */
	public function __construct() {
		
	}
	
	public function getList() {
		return $this->internalList;
	}
}
?>
