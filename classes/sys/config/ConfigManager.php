<?php
require 'sys/config/Config_DAL.php';
/**
 * Contains ConfigManager class
 *
 * @author ahe <aheusingfeld@borderlesscms.de>
 * @date 16.12.2006 09:53:17
 * @package htdocs/classes/sys/config
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
		BcmsConfig::getInstance()->setTablename('config', 'config');

		$this->dalObj = Config_DAL::getInstance();
		$this->actions = array(
			0 => array('edit', Factory::getObject('Dictionary')->getTrans('edit'), 'Edit'),
			1 => array('delete', Factory::getObject('Dictionary')->getTrans('delete'), 'Delete')
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

		// get dictionary table and surround it with a scrollpane div-tag
		$retStr = $this->createConfigTable();

		$form =& $this->dalObj->getForm('config_form','go_config_action'
			,Factory::getObject('Dictionary')->getTrans('save'));

		$retStr .= Factory::getObject('GuiUtility')->fillTemplate(
						'fieldset_tpl',
						array('id="add_config_form_fieldset"',
							Factory::getObject('Dictionary')->getTrans('config.h.addConfigVar'),
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
				Factory::getObject('Dictionary')->getTrans('config.heading'),
				'config_id',
				true);
	}

	protected function createEditDialog(){
        if( !PluginManager::getPlgInstance('UserManager')->hasRight('config.edit_var') ) // TODO build plugin_cat_config for ConfigManager
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createDeleteDialog()',__FILE__, __LINE__);

		$result = HTMLTable::getAffectedIds();
		$id=$result[0];
		$this->dalObj->addLabels('config.');
		$this->dalObj->setTypeSelect();
		$heading = Factory::getObject('Dictionary')->getTrans('config.h.edit_var');
		$heading = BcmsFactory::getInstanceOf('GuiUtility')->createHeading(3,$heading);
		$recordData= $this->dalObj->getObject($id);
		foreach ($recordData[0] as $key => $value) {
			$record[$key] = BcmsFactory::getInstanceOf('Parser')->filter($value);
		}
		$form = $this->dalObj->getForm('configEditform','editConfigElement'
			,Factory::getObject('Dictionary')->getTrans('submit'),$record);
		if($record['editable']==0) $form->freeze();
		return $heading.$form->toHTML();
	}

	protected function createDeleteDialog(){
        if( !PluginManager::getPlgInstance('UserManager')->hasRight('config.delete_var') )
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createDeleteDialog()',__FILE__, __LINE__);

		$heading = Factory::getObject('Dictionary')->getTrans('config.h.deleteEntries');
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
			return $this->checkForDeleteTransaction('config_id',__FILE__,__LINE__);


		// check for edit
		$retVal = $this->makeCheck($this->dalObj,
			'editConfigElement',
			$_POST,
			'update',
			'config_id='.intval($_POST['config_id']));

		if($retVal==null){
			$retVal = $this->makeCheck($this->dalObj,'go_config_action');
		}
		return $retVal;
	}

}
?>
