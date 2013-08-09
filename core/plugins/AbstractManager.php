<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * Abstract father class for all plugin manager classes
 *
 * @since 0.8
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @class AbstractManager
 * @ingroup plugins
 * @package plugins
 */
abstract class AbstractManager {

	// the versionstring is needed e.g. for getHashcode()
	protected $versionstring = '0.5';
	protected $modname;
	protected static $uniqueInstance = null;

// \bug URGENT uncomment the following when ready for creating in all plugins
//	abstract public function install($menuId);
//	abstract public function deinstall($menuId);
//	abstract public function getRss();

	abstract public function init($menuId);
	abstract public function main($menuId);
	abstract public function getCss($menuId=0);
	abstract public function checkTransactions($menuId=0);
	abstract public function printGeneralConfigForm();
	abstract public function printCategoryConfigForm($menuId);
	abstract public function getPageTitle();
	abstract public function getMetaDescription();
	abstract public function getMetaKeywords();
	public function getView(){}

	/**
	 * checks whether the given object equals the current instance
	 *
	 * @return String name of the current plugin
	 * @since 0.8.???
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 * @deprecated use getManagerName() instead
	 */
	public function getModname() {
		return $this->getManagerName();
	}

	/**
	 * checks whether the given object equals the current instance
	 *
	 * @return String name of the current plugin
	 * @since 0.13.73
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 */
	public function getManagerName() {
		return $this->modname;
	}

	/**
	 * Returns a child instance of DataAbstractionLayer
	 *
	 * @return DataAbstractionLayer a child instance of DataAbstractionLayer
	 * @author ahe  <aheusingfeld@borderlesscms.de>
	 * @date 14.10.2006 23:06:12
	 * @since 0.9.2
	 */
	public function getModel(){
		return $this->getDalObj();
	}

	/**
	 * Returns the logic class of the current manager
	 *
	 * @return mixed   logic class
	 * @author ahe  <aheusingfeld@borderlesscms.de>
	 * @date 14.10.2006 23:06:12
	 * @since 0.9.2
	 */
	public function getLogic(){
		return $this->logicObj;
	}

	/**
	 * Returns a child instance of DataAbstractionLayer
	 *
	 * @return DataAbstractionLayer a child instance of DataAbstractionLayer
	 * @author ahe  <aheusingfeld@borderlesscms.de>
	 * @date 14.10.2006 11:41:12
	 * @since 0.9.2
	 */
	public function getDalObj() {

		if(!($this->dalObj instanceof DataAbstractionLayer))
			return BcmsSystem::raiseError('Manager class\' member variable is not of type DataAbstractionLayer', // @todo use dictionary
				BcmsSystem::LOGTYPE_CHECK,	BcmsSystem::SEVERITY_ERROR,
				'getDalObj()', __FILE__, __LINE__);
		return $this->dalObj;
	}

	/**
	 * checks whether the given object equals the current instance
	 *
	 * @param BcmsObject obj
	 * @return boolean
	 * @since 0.13.143
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 */
	public function getHashcode(){
		$parentHash = parent::getHashcode();
		return BcmsSystem::getHash($this->getModname().$parentHash);
	}

	/**
	 * checks whether submit button is set and action could be performed
	 *
	 * @param $dalObj current DAL object e.g. ModEntries_DAL
	 * @param string $submit name of submit button
	 * @param array $dataArray array of data to be submitted to DB
	 * @return string success or error message
	 * @author ahe
	 * @date 28.01.2006 22:58:06
	 */
	protected function makeCheck(&$dalObj,$submit,$dataArray=null,$func='insert',$where=null) {
		if(empty($dataArray)) $dataArray = $_POST;

		if(!array_key_exists($submit, $_POST)) {	return null;}

		$returnValue = $dalObj->checkForAction($submit,$dataArray,$func,$where);
		if(!empty($returnValue) && is_numeric($returnValue)){
			BcmsSystem::raiseDictionaryNotice('dataInsertSuccess',
					BcmsSystem::LOGTYPE_CHECK, BcmsSystem::SEVERITY_INFO,
					'makeCheck()', __FILE__, __LINE__);
	        return $returnValue;
		}

		if($returnValue==true){
  			return BcmsSystem::raiseDictionaryNotice('dataInsertSuccess',
					BcmsSystem::LOGTYPE_CHECK, BcmsSystem::SEVERITY_INFO,
					'makeCheck()', __FILE__, __LINE__);
		}

		return BcmsSystem::raiseDictionaryNotice('dataInsertFailed', BcmsSystem::LOGTYPE_CHECK,
				BcmsSystem::SEVERITY_FAILURE, 'makeCheck()',
				__FILE__, __LINE__);
	}

	/**
	 *
	 *
	 * @param String $heading The heading to put above the form
	 * @return String the whole form
	 * @author ahe
	 * @date 05.07.2006 23:37:36
	 */
	protected function createDeletionConfirmFormForHTML_TableForms($heading, $additionalInfo=null){

		require_once 'pear/HTML/QuickForm.php';
		$action_url = BcmsSystem::getParser()->getServerParameter('REDIRECT_URL');
		$result = HTMLTable::getAffectedIds();
		$heading = BcmsFactory::getInstanceOf('GuiUtility')->createHeading(3,$heading);

		$form = new HTML_QuickForm('delete_elements','post',$action_url);
		$idStr = '';
		for ($i = 0; $i < sizeof($result); $i++) {
			$id=$result[$i];
				$element = $form->addElement('hidden', 'elemid_'.$id,$i);
				if($idStr!='') $idStr .= ', ';
				$idStr .= $id;
		}
		$element = $form->addElement('submit','submit_deletion',
			BcmsSystem::getDictionaryManager()->getTrans('submit'), 'id="submit_deletion"');
		$element->setLabel('&nbsp;');
		$element = $form->addElement('submit', 'abort_action',
			BcmsSystem::getDictionaryManager()->getTrans('cancel'), 'id="abort_action"');
		$element->setLabel('&nbsp;');

		$question =	BcmsSystem::getDictionaryManager()->getTrans('really_delete');
		$retString = $heading.'<p>'.$question."<br/>\n".$idStr."</p>\n";
		if(!empty($additionalInfo)) $retString .= $additionalInfo;
		$retString .= $form->toHTML();
		return $retString;
	}

	protected function checkForDeleteTransaction($id_column, $file=__FILE__, $line=__LINE__){
		if(isset($_POST['submit_deletion'])) {
			$deleteIds = HTMLTable::getAffectedIds();
			if(count($deleteIds)<1) return false;
			$where = '';
			foreach ($deleteIds as $value) {
				if($where != '') $where .= ' OR ';
				$where .=$id_column.'='.intval($value);
			}
			return $this->dalObj->delete($where);
		}
		return null;
	}

	/**
	 * if action is performed this will call the according method and return the
	 * appropriate html.
	 * @todo <b>ATTENTION: This is only public because of TWO calls from cContent class!!!</b>
	 *
	 * @param string tablename - name/ id of the html table
	 * @return string - null or the html code for the dialog
	 * @author ahe
	 * @date 19.01.2007 00:35:36
	 */
	public function performListAction($tablename) {
		if(isset($_POST['table_action_select_'.$tablename])
			&& count(HTMLTable::getAffectedIds(true))>0
		){
			$i = -1;
			$stop=false;
			while($i < count($this->actions) && !$stop){
				$i++;
				if($_POST['table_action_select_'.$tablename] == $this->actions[$i][0])
				{
					$stop=true;
				}
			}
			if(!$stop) return null;
			$methodName = 'create'.$this->actions[$i][2].'Dialog';
			if(method_exists($this,$methodName)){
				return $this->$methodName();
			}
		}
		return null;
	}
}
?>
