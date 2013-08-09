<?php
if (!defined('BORDERLESS'))	exit;
/**
 * General root class for all bcms objects.
 * @author ahe
 * @since 0.14 - 02.11.2006
 */
abstract class BcmsObject {
	
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
