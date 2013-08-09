<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

require_once 'core/plugins/user/Rights_DAL.php';

/**
 * @todo document this
 *
 * @since 0.10
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @date 2006-06-30
 * @class RightManager
 * @ingroup rights
 * @package rights
 */
class RightManager extends AbstractManager {

	// the versionstring is needed e.g. for getHashcode()
	protected $versionstring = '0.7';
	protected $modname = 'RightManager';
	protected $dalObj = null;

	/**
	 *
	 *
	 * @author ahe
	 * @date 22.11.2005 20:07:42
	 */
	public function __construct() {
		BcmsConfig::getInstance()->setTablename('rechte', 'plg_rights');
		$this->dalObj = Rights_DAL::getInstance();
//		$this->logicObj = new RightsLogic($this);
		$this->actions = array(
			0 => array('edit', BcmsSystem::getDictionaryManager()->getTrans('edit'), 'Edit'),
			1 => array('delete', BcmsSystem::getDictionaryManager()->getTrans('delete'), 'Delete')
		);
	}

	/**
	 *
	 *
	 * @author ahe
	 * @date 22.11.2005 20:07:42
	 */
	public function init($menuID) {
	}

	public function main($menuId) {
		return $this->printGeneralConfigForm();
	}

	public function getCss($menuId=0){
		return '';
	}

	/**
	 * returns the current menu's name to be added to teh page title
	 *
	 * @return string the current menu's name
	 * @author ahe
	 * @date 01.05.2006 00:20:33
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
        if( !BcmsSystem::getUserManager()->hasViewRight() )
		    return BcmsSystem::raiseNoAccessRightNotice(
				'printGeneralConfigForm()',__FILE__, __LINE__);

		$dialog = $this->performListAction('rights_table');
		if($dialog!=null) return $dialog;
		// ...else print general table overview

		// get dictionary table and surround it with a scrollpane div-tag
		$retStr = $this->createRightsTable();

		$form =& $this->dalObj->getForm('rights_form','go_right_action'
			,BcmsSystem::getDictionaryManager()->getTrans('save'));
		$retStr .= BcmsFactory::getInstanceOf('GuiUtility')->fillTemplate('fieldset_tpl'
					,array('id="rights"'
					,BcmsSystem::getDictionaryManager()->getTrans('rights.addRight')
					,$form->toHtml(),null));
		return $retStr;
	}

	private function createRightsTable($offset=null,$limit=null) {
		$tableObj = new HTMLTable('rights_table');
        $tableObj->setTranslationPrefix('rights.');
		$tableObj->setActions($this->actions);
		$tableObj->setBounds('page',null,$this->dalObj->getNumberOfEntries());
		$limit = $tableObj->getListLimit();
		$offset = $tableObj->getListOffset();
		$rights = $this->dalObj->getList($offset,$limit);
		$tableObj->setData($rights);
		unset($rights);
		return $tableObj->render(
			BcmsSystem::getDictionaryManager()->getTrans('rights.heading'),
			'right_id',true);
	}

	protected function createEditDialog(){
        if( !BcmsSystem::getUserManager()->hasEditRight() ) // @todo plg_cat_conf-edit_right should actually be checked here!
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createEditDialog()',__FILE__, __LINE__);

		$result = HTMLTable::getAffectedIds();
		$id=$result[0];
		$element = $this->dalObj->getObject($id);
		$this->dalObj->setLabels();
		$this->dalObj->setTypeSelect();
		$form = $this->dalObj->getForm('righteditform','editRightElement'
			,BcmsSystem::getDictionaryManager()->getTrans('save'),$element);
		return $form->toHTML();
	}

	protected function createDeleteDialog(){
        if( !BcmsSystem::getUserManager()->hasDeleteRight() ) // @todo plg_cat_conf-delete_right should actually be checked here!
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createDeleteDialog()',__FILE__, __LINE__);

		$heading = BcmsSystem::getDictionaryManager()->getTrans('rights.h.deleteEntries');
		return $this->createDeletionConfirmFormForHTML_TableForms($heading);
	}

	/**
	 *
	 *
	 * @author ahe
	 * @date 22.11.2005 20:07:42
	 */
	public function checkTransactions($id=0) {
		// check for edit
		if(array_key_exists('editRightElement', $_POST)){
			return $this->makeCheck($this->dalObj,'editRightElement',$_POST,
				'update','right_id='.intval($_POST['right_id']));
		}

		// check for insert
		if(array_key_exists('go_right_action', $_POST)){
			return $this->makeCheck($this->dalObj,'go_right_action');
		}

		return $this->checkForDeleteTransaction('right_id',__FILE__,__LINE__);;
	}

	public function getRightList() {
		$rights = $this->dalObj->select('list_everything');
		$retArray[0] = '&nbsp;'; // add empty row for "no additional right"
		for ($i = 0; $i < sizeof($rights); $i++) {
			$retArray[$rights[$i]['right_id']] = $rights[$i]['rightname'];
		}
		return $retArray;
	}

	public function getRightnameById($id) {
		$rights = $this->dalObj->select('list_everything','right_id='.$id);
		return $rights[0]['rightname'];
	}
}
?>
