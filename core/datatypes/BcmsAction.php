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
 * @todo finish this class! 
 * @date 28.10.2006
 * @since 0.13
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @class BcmsAction
 * @ingroup datatypes
 * @package datatypes
 */
class BcmsAction extends BcmsObject {
	private $formvalue = null;
	private $defaultTrans = null;
	private $methodName = null;
	private $needed_right = null;
	
	/**
	 * 
	 *
	 * @param mixed $formvalue value that the form-tag attribute 'value' shall have
	 * @param String default_trans the default translation to get the translation from dictionary
	 * @author ahe
	 * @date 28.10.2006 22:23:24
	 */
	public function __construct($formvalue,$defaultTrans,$methodName,$neededRight=null){
		$this->formvalue = $formvalue;
		$this->defaultTrans = $defaultTrans;
		$this->methodName = $methodName;
		$this->neededRight = $neededRight;
	}
	
	public function getNeededRight() {
		return $this->neededRight; 
	}
	
	public function getLabelText() {
		return BcmsSystem::getDictionaryManager()->getTrans($this->defaultTrans); 
	}
	
	public function getFormValue() {
		return $this->formvalue; 
	}
	
}
?>
