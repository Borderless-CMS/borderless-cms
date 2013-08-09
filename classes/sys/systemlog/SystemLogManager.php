<?php
require 'sys/systemlog/SystemLog_DAL.php';
/**
 * Contains SystemLogManager class
 *
 * @author ahe <aheusingfeld@borderlesscms.de>
 * @date 16.12.2006 09:53:17
 * @package htdocs/classes/sys/requestlog
 */
class SystemLogManager extends AbstractManager {

	// the versionstring is needed e.g. for getHashcode()
	protected $versionstring = '0.1';
	protected $modname = 'SystemLogManager';
	protected $dalObj = null;
	protected $categoryName = '';


	/**
	 *
	 *
	 * @author ahe
	 * @date 22.11.2005 20:07:42
	 */
	public function __construct() {
		BcmsConfig::getInstance()->setTablename('syslog', 'syslog');

		$this->dalObj = SystemLog_DAL::getInstance();
		$this->categoryName = $this->dalObj->getPluginsCatName();
	}

	/**
	 *
	 *
	 * @author ahe
	 * @date 22.11.2005 20:07:42
	 */
	public function init($menuID) {
		$this->actions = array(
			0 => array('view', Factory::getObject('Dictionary')->getTrans('view'), 'View'),
			1 => array('delete', Factory::getObject('Dictionary')->getTrans('delete'), 'Delete')
		);
	}

	public function main($menuId){
		$myModArray = PluginManager::getInstance()->getCurrentMainPlugin();
		switch ($myModArray['func']) {
			case 'list':
			default:
				if(isset($_POST['table_action_select_syslog_table'])){
					$i = -1;
					$actionFound = false;
					while($i < sizeof($this->actions) && !$actionFound){
						$i++;
						if($_POST['table_action_select_syslog_table'] == $this->actions[$i][0])
							$actionFound=true;
					}
					$methodName = 'create'.$this->actions[$i][2].'Dialog';
					return $this->$methodName();
				}
				// ...else print general table overview
				return $this->printGeneralConfigForm();
		}
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

		// get table and surround it with a scrollpane div-tag
		$retStr = $this->createSystemLogTable();
		return $retStr;
	}

	private function createSystemLogTable() {
		$tableObj = new HTMLTable('syslog_table');
        $tableObj->setTranslationPrefix('syslog.');
		$tableObj->setActions($this->actions);
		$tableObj->setBounds('page',null,$this->dalObj->getNumberOfEntries());
		$limit = $tableObj->getListLimit();
		$offset = $tableObj->getListOffset();

		// prepare searching
		list($searchphrase,$offset,$limit) = $tableObj->setSearchBehaviour(true);
		$syslog_vars = $this->dalObj->getList($offset,$limit,null,$searchphrase);
		$tableObj->setData($syslog_vars);
		unset($syslog_vars);
		return $tableObj->render(
				Factory::getObject('Dictionary')->getTrans('syslog.heading'),
				'syslog_id',
				true);
	}

	protected function createViewDialog(){
        if( !PluginManager::getPlgInstance('UserManager')->hasRight('syslog_view_entry') ) // TODO build plugin_cat_config for ConfigManager
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createViewDialog()',__FILE__, __LINE__);

		$result = HTMLTable::getAffectedIds();
		$id=$result[0];
		$this->dalObj->addLabels('syslog.');
		$heading = Factory::getObject('Dictionary')->getTrans('syslog.h.view_entry'); // TODO this should check plg_cat_config!!!
		$heading = BcmsFactory::getInstanceOf('GuiUtility')->createHeading(3,$heading);
		$element = $this->dalObj->getObject($id);
		$form = $this->dalObj->getForm('syslogEditform','editSystemLogElement'
			,Factory::getObject('Dictionary')->getTrans('submit'),$element[0]);
		$form->freeze();
		return $heading.$form->toHTML();
	}

	protected function createDeleteDialog(){
        if( !PluginManager::getPlgInstance('UserManager')->hasDeleteRight() )// TODO this should check plg_cat_config!!!
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createDeleteDialog()',__FILE__, __LINE__);

		$heading = Factory::getObject('Dictionary')->getTrans('syslog.h.deleteEntries');
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
			return $this->checkForDeleteTransaction('syslog_id',__FILE__,__LINE__);

		// check for edit
		$retVal = $this->makeCheck($this->dalObj,
			'editSystemLogElement',
			$_POST,
			'update',
			'syslog_id='.intval($_POST['syslog_id']));

		if($retVal==null){
			$retVal = $this->makeCheck($this->dalObj,'go_syslog_action');
		}
		return $retVal;
	}

}
?>
