<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * General root class for all bcms objects.
 * 
 * @author ahe
 * @since 0.14 
 * @date 02.11.2006
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @class BcmsObject
 * @ingroup datatypes
 * @package datatypes
 */
abstract class BcmsObject extends SingleObjectPattern {

	protected $version = 0;

	/**
	 * checks whether the given object equals the current instance
	 *
	 * @param BcmsObject obj
	 * @return boolean
	 * @since 0.13.143
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 */
	public function equals(BcmsObject $obj){
//		return ($this===$obj);
		return ($this->hashcode() == $obj->getHashcode());
	}

	/**
	 * returns the version string of the current object
	 *
	 * @return String version info string
	 * @since 0.13.143
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 */
	public function getVersion(){
		return $this->version;
	}

	/**
	 * checks whether the given object equals the current instance
	 *
	 * @param BcmsObject obj
	 * @return boolean
	 * @since 0.13.143
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 */
	public function hashcode(){
		return BcmsSystem::getHash($this.'ZH,.Sr?:;L�KUZ/&%3z6�%$&�U�!fbCI"9&r'.$this->getVersion());
	}
}
?>
