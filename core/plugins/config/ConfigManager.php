<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

require 'core/plugins/config/Config_DAL.php';
/**
 * Contains ConfigManager class
 *
 * @author ahe <aheusingfeld@borderlesscms.de>
 * @date 16.12.2006 09:53:17
 * @since 0.13
 * @class ConfigManager
 * @ingroup config
 * @package config
 */
class ConfigManager extends AbstractManager {

	// the versionstring is needed e.g. for getHashcode()
	protected $versionstring = '0.1';
	protected $modname = 'ConfigManager';
	protected $dalObj = null;

	/**
	 *
	 *
	 * @author ahe
	 * @date 22.11.2005 20:07:42
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
		BcmsConfig::getInstance()->setTablename('config', 'config');

		$this->dalObj = Config_DAL::getInstance();
		$this->actions = array(
			0 => array('edit', BcmsSystem::getDictionaryManager()->getTrans('edit'), 'Edit'),
			1 => array('delete', BcmsSystem::getDictionaryManager()->getTrans('delete'), 'Delete')
		);
	}

	public function main($menuId) {
		if(isset($_POST['table_action_select_config_table'])){
			$i = -1;
			$actionFound = false;
			while($i < sizeof($this->actions) && !$actionFound){
				$i++;
				if($_POST['table_action_select_config_table'] == $this->actions[$i][0])
					$actionFound=true;
			}
			$methodName = 'create'.$this->actions[$i][2].'Dialog';
			return $this->$methodName();
		}
		// ...else print general table overview
		return $this->printGeneralConfigForm();
	}

	public function getCss($menuId=0){
			return null;
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

		// get dictionary table and surround it with a scrollpane div-tag
		$retStr = $this->createConfigTable();

		$form =& $this->dalObj->getForm('config_form','go_config_action'
			,BcmsSystem::getDictionaryManager()->getTrans('save'));

		$retStr .= BcmsFactory::getInstanceOf('GuiUtility')->fillTemplate(
						'fieldset_tpl',
						array('id="add_config_form_fieldset"',
							BcmsSystem::getDictionaryManager()->getTrans('config.h.addConfigVar'),
							$form->toHtml(),null)
						);
		return $retStr;
	}

	private function createConfigTable() {
		$tableObj = new HTMLTable('config_table');
        $tableObj->setTranslationPrefix('config.');
		$tableObj->setActions($this->actions);
		$tableObj->setBounds('page',null,$this->dalObj->getNumberOfEntries());
		$limit = $tableObj->getListLimit();
		$offset = $tableObj->getListOffset();

		// prepare searching
		list($searchphrase,$offset,$limit) = $tableObj->setSearchBehaviour(true);
		$config_vars = $this->dalObj->getList($offset,$limit,null,$searchphrase);
		$tableObj->setData($config_vars);
		unset($config_vars);
		return $tableObj->render(
				BcmsSystem::getDictionaryManager()->getTrans('config.heading'),
				'config_id',
				true);
	}

	protected function createEditDialog(){
        if( !BcmsSystem::getUserManager()->hasRight('config.edit_var') ) // @todo build plugin_cat_config for ConfigManager
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createDeleteDialog()',__FILE__, __LINE__);

		$result = HTMLTable::getAffectedIds();
		$id=$result[0];
		$this->dalObj->addLabels('config.');
		$this->dalObj->setTypeSelect();
		$heading = BcmsSystem::getDictionaryManager()->getTrans('config.h.edit_var');
		$heading = BcmsFactory::getInstanceOf('GuiUtility')->createHeading(3,$heading);
		$recordData= $this->dalObj->getObject($id);
		foreach ($recordData[0] as $key => $value) {
			$record[$key] = BcmsSystem::getParser()->filter($value);
		}
		$form = $this->dalObj->getForm('configEditform','editConfigElement'
			,BcmsSystem::getDictionaryManager()->getTrans('submit'),$record);
		if($record['editable']==0) $form->freeze();
		return $heading.$form->toHTML();
	}

	protected function createDeleteDialog(){
        if( !BcmsSystem::getUserManager()->hasRight('config.delete_var') )
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createDeleteDialog()',__FILE__, __LINE__);

		$heading = BcmsSystem::getDictionaryManager()->getTrans('config.h.deleteEntries');
		return $this->createDeletionConfirmFormForHTML_TableForms($heading);
	}

	/**
	 *
	 *
	 * @author ahe
	 * @date 22.11.2005 20:07:42
	 */
	public function checkTransactions($id=0) {
		if(array_key_exists('submit_deletion', $_POST)){
			return $this->checkForDeleteTransaction('config_id',__FILE__,__LINE__);
		}

		// check for edit
		if(array_key_exists('config_id', $_POST)){
			return $this->makeCheck($this->dalObj,
			'editConfigElement',
			$_POST,
			'update',
			'config_id='.intval($_POST['config_id']));
		}

		return $this->makeCheck($this->dalObj,'go_config_action');
	}

}
?>
