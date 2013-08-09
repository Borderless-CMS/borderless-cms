<?php
require 'dictionary/Dictionary_DAL.php';
/**
 * Contains Dictionary class
 *
 * @module Dictionary.php
 * @author ahe <aheusingfeld@borderless-cms.de>
 * @package plugins
 * @version $Id$
 */

class Dictionary extends AbstractManager {

	// the versionstring is needed e.g. for getHashcode()
	protected $versionstring = '0.23';
	protected $modname = 'Dictionary';
	protected $dalObj = null;

	/**
	 *
	 *
	 * @author ahe
	 * @date 22.11.2005 20:07:42
	 * @package plugins
	 * @project bcms
	 */
	public function __construct() {
		$this->init($_SESSION['m_id']);
	}

	/**
	 *
	 *
	 * @author ahe
	 * @date 22.11.2005 20:07:42
	 */
	public function init($menuID) {
		BcmsConfig::getInstance()->setTablename('dict', 'plg_dict');

		$this->dalObj = Dictionary_DAL::getInstance();
		$this->actions = array(
			0 => array('edit', $this->dalObj->getTrans('edit'), 'Edit'),
			1 => array('delete', $this->dalObj->getTrans('delete'), 'Delete')
		);
	}

	public function main($menuId) {
		// ...else print general table overview
		return $this->printGeneralConfigForm();
	}

	public function getCss($menuId=0){
		if($_SESSION['mod']['name']=='dictionary')
			return '/* --- CSS for dictionary module --- */
';
	}

	/**
	 * returns the current menu's name to be added to teh page title
	 *
	 * @return string the current menu's name
	 * @author ahe
	 * @date 01.05.2006 00:20:33
	 * @package htdocs/plugins/classifications
	 */
	public function getPageTitle() {
		return null;
	}

	/**
	 * returns the MetaDescription of the current menu
	 *
	 * @return string
	 * @author ahe
	 * @date 01.05.2006 00:21:56
	 * @package htdocs/plugins/classifications
	 */
	public function getMetaDescription() {
		return null;
	}

	/**
	 * returns the MetaKeywords of the current menu
	 *
	 * @return string
	 * @author ahe
	 * @date 01.05.2006 00:23:41
	 * @package htdocs/plugins/classifications
	 */
	public function getMetaKeywords() {
		return null;
	}

	/**
	 * Ist in diesem Modul nicht vorhanden!
	 *
	 * @author ahe
	 * @date 22.11.2005 20:07:42
	 */
	public function printCategoryConfigForm($menuId) {
		return null;
	}

	/**
	 *
	 *
	 * @author ahe
	 * @date 22.11.2005 20:07:42
	 */
	public function printGeneralConfigForm() {
        if( !PluginManager::getPlgInstance('UserManager')->hasViewRight() )
		    return BcmsSystem::raiseNoAccessRightNotice(
				'printGeneralConfigForm()',__FILE__, __LINE__);

		$dialog = $this->performListAction('dict_table');
		if($dialog!=null) return $dialog;
		// ...else print general table overview

		// get dictionary table and surround it with a scrollpane div-tag
		$retStr = $this->createDictTable();

		$refGUI = Factory::getObject('GuiUtility');
		$form =& $this->dalObj->getForm('dict_form','go_dict_action'
			,$this->dalObj->getTrans('save'));
		$retStr .= $refGUI->fillTemplate('fieldset_tpl'
					,array('id="dictionary"',$this->dalObj->getTrans('dict.addTranslation')
					,$form->toHtml(),null));
		return $retStr;
	}

	public function getTrans($defaultTrans){
		return $this->dalObj->getTrans($defaultTrans);
	}

	public function getStatusTrans($status){
		return $this->dalObj->getTrans($status);
	}

	private function createDictTable() {
		$tableObj = new HTMLTable('dict_table');
        $tableObj->setTranslationPrefix('dict.');
		$tableObj->setActions($this->actions);
		$tableObj->setBounds('page',null,$this->dalObj->getNumberOfEntries());
		$limit = $tableObj->getListLimit();
		$offset = $tableObj->getListOffset();

		// prepare searching
		list($searchphrase,$offset,$limit) = $tableObj->setSearchBehaviour(true);
		$trans = $this->dalObj->getList($offset,$limit,null,$searchphrase);
		$tableObj->setData($trans);
		unset($trans);

		return $tableObj->render($this->dalObj->getTrans('dict.heading'),
				'dict_id',true);
	}

	protected function createEditDialog(){
        if( !PluginManager::getPlgInstance('UserManager')->hasEditRight() ) // TODO plg_cat_conf-edit_right should actually be checked here!
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createEditDialog()',__FILE__, __LINE__);

		$result = HTMLTable::getAffectedIds();
		$id=$result[0];
		$element = $this->dalObj->getObject($id);
		$this->dalObj->addLabels();
		$this->dalObj->setTypeSelect();
		$form = $this->dalObj->getForm('dicteditform','editDictElement'
			,Factory::getObject('Dictionary')->getTrans('submit'),$element[0]);
		return $form->toHTML();
	}

	protected function createDeleteDialog(){
        if( !PluginManager::getPlgInstance('UserManager')->hasDeleteRight() )
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createDeleteDialog()',__FILE__, __LINE__);

		$heading = Factory::getObject('Dictionary')->getTrans('dict.h.deleteEntries');
		return $this->createDeletionConfirmFormForHTML_TableForms($heading);
	}

	/**
	 *
	 *
	 * @author ahe
	 * @date 22.11.2005 20:07:42
	 */
	public function checkTransactions($id=0) {
		if(isset($_POST['submit_deletion']))
			return $this->checkForDeleteTransaction('dict_id',__FILE__,__LINE__);

		// check for edit
		$retVal = $this->makeCheck($this->dalObj, 'editDictElement', $_POST,
			'update', 'dict_id='.intval($_POST['dict_id']));

		if($retVal==null){
			$retVal = $this->makeCheck($this->dalObj,'go_dict_action');
		}
		return $retVal;
	}
}
?>
