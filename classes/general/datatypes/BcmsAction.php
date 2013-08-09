<?php
/**
 * Created on 28.10.2006
 *
 */
class BcmsAction extends BcmsObject {
	private $formvalue = null;
	private $defaultTrans = null;
	private $methodName = null;
	private $needed_right = null;
	
	/**
	 * 
	 *
	 * @param mixed $formvalue value that the form-tag attribute 'value' shall
	 * have
	 * @param String default_trans the default translation to get the
	 * translation from dictionary
	 * @author ahe
	 * @date 28.10.2006 22:23:24
	 * @package htdocs/classes/datatypes
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
		return Factory::getObject('Dictionary')->getTrans($this->defaultTrans); 
	}
	
	public function getFormValue() {
		return $this->formvalue; 
	}
	
}
?>
