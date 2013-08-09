<?php
/**
 * Contains GroupManager class
 *
 * @author ahe <aheusingfeld@borderless-cms.de>
 * @package plugins
 * @version $Id$
 */
require_once 'user/Groups_DAL.php';

class GroupManager extends AbstractManager {

	// the versionstring is needed e.g. for getHashcode()
	protected $versionstring = '0.6';
	protected $modname = 'GroupManager';
	protected $dalObj = null;
	protected $configInstance = null;

	/**
	 *
	 *
	 * @author ahe
	 * @date 22.11.2005 20:07:42
	 * @package plugins
	 * @project bcms
	 */
	public function __construct() {
		$this->configInstance = BcmsConfig::getInstance();
		$this->configInstance->setTablename('user', 'plg_users');
		$this->configInstance->setTablename('groups', 'plg_groups');
		$this->configInstance->setTablename('user_group_assoc', 'user_group_assoc');
		$this->configInstance->setTablename('rechte', 'plg_rights');
		$this->configInstance->setTablename('groups_rechte_zo', 'grouprightassoc');

		$this->dalObj = Groups_DAL::getInstance();
		$this->actions = array(
			0 => array('edit', Factory::getObject('Dictionary')->getTrans('edit'), 'Edit'),
			1 => array('delete', Factory::getObject('Dictionary')->getTrans('delete'), 'Delete'),
			2 => array('user_group',
				Factory::getObject('Dictionary')->getTrans('groups.editUserGroupAssoc'),
				'EditUserGroupAssoc'),
			3 => array('group_right',
				Factory::getObject('Dictionary')->getTrans('groups.editGroupRightAssoc'),
				'EditGroupRightAssoc')
		);
	}

	/**
	 *
	 *
	 * @author ahe
	 * @date 22.11.2005 20:07:42
	 */
	public function init($menuId) {
	}

	public function main($menuId) {
		return $this->printGeneralConfigForm();
	}

	public function getCss($menuId=0){
		return '';
	}

	/**
	 * returns the current menu's name to be added to the page title
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

		$dialog = $this->performListAction('groups_table');
		if($dialog!=null) return $dialog;
		// ...else print general table overview

		// get dictionary table and surround it with a scrollpane div-tag
		$retStr = $this->createGroupsTable();

		$form =& $this->dalObj->getForm('groups_form','go_group_action'
			,Factory::getObject('Dictionary')->getTrans('save'));
		$retStr .= Factory::getObject('GuiUtility')->fillTemplate('fieldset_tpl'
					,array('id="groups"'
					,Factory::getObject('Dictionary')->getTrans('groups.addGroup')
					,$form->toHtml(),null));
		return $retStr;
	}

	private function createGroupsTable() {
		$tableObj = new HTMLTable('groups_table');
        $tableObj->setTranslationPrefix('groups.');
		$tableObj->setActions($this->actions);
		$tableObj->setBounds('page',null,$this->dalObj->getNumberOfEntries());
		$limit = $tableObj->getListLimit();
		$offset = $tableObj->getListOffset();
		$groups = $this->dalObj->getList($offset,$limit);
		$tableObj->setData($groups);
		unset($groups);
		return $tableObj->render(
			Factory::getObject('Dictionary')->getTrans('groups.heading'),
			'group_id',true);
	}

	protected function createEditDialog(){
        if( !PluginManager::getPlgInstance('UserManager')->hasEditRight() ) // TODO plg_cat_conf-edit_right should actually be checked here!
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createEditDialog()',__FILE__, __LINE__);

		$result = HTMLTable::getAffectedIds();
		$id=$result[0];
		$element = $this->dalObj->getObject($id);
		$this->dalObj->setLabels();
		$this->dalObj->setTypeSelect();
		$form = $this->dalObj->getForm('groupeditform','editGroupElement'
			,Factory::getObject('Dictionary')->getTrans('save'),$element);
		return $form->toHTML();
	}

	protected function createEditUserGroupAssocDialog(){
		require_once 'HTML/QuickForm.php';
		require_once 'HTML/QuickForm/advmultiselect.php';

		$result = HTMLTable::getAffectedIds();
		$groupId=$result[0];

		$queryAll = 'SELECT user_id, username '
		          . 'FROM '.$this->configInstance->getTablename('user')
		          . ' ORDER BY username ASC';

		$querySel = 'SELECT fk_user FROM '
			.$this->configInstance->getTablename('user_group_assoc')
			.' WHERE fk_rolle='.$groupId;
		$associated_users =&$GLOBALS['db']->getCol($querySel);

		$form = new HTML_QuickForm('ug_assoc','post',$_SERVER['REQUEST_URI']);
		$form->removeAttribute('name');  // XHTML compliance

		$form->addElement('header', null, 'Benutzer der Gruppe "'.
			$this->getGroupnameById($groupId).'" zuordnen');

		$ams =& $form->addElement('advmultiselect', 'user', // TODO use dictionary
		    array('Benutzer:', 'Verfügbar', 'Zugeordnet'), // labels
		    null,                                             // datas
		    array('style' => 'width:200px;')                  // custom layout
		);

		// load QFAMS values (unselected and selected)
		$ams->load($GLOBALS['db'], $queryAll, 'username', 'user_id', $associated_users);

		$form->addElement('hidden', 'group_id', $groupId);
		$form->addElement('submit', 'send_user_group', 'Speichern');
	    return $form->toHtml();
	}

	protected function createEditGroupRightAssocDialog(){
		require_once 'HTML/QuickForm.php';
		require_once 'HTML/QuickForm/advmultiselect.php';

		$result = HTMLTable::getAffectedIds();
		$groupId=$result[0];

		$queryAll = 'SELECT right_id, rightname '
		          . 'FROM '.$this->configInstance->getTablename('rechte')
		          . ' ORDER BY rightname ASC';

		$querySel = 'SELECT fk_recht FROM '
			.$this->configInstance->getTablename('groups_rechte_zo')
			.' WHERE fk_rolle='.$groupId;
		$associated_rights =&$GLOBALS['db']->getCol($querySel);

		$form = new HTML_QuickForm('gr_assoc','post',$_SERVER['REQUEST_URI']);
		$form->removeAttribute('name');  // XHTML compliance

		$form->addElement('header', null, 'Systemrechte der Gruppe "'.
			$this->getGroupnameById($groupId).'" zuordnen');

		$ams =& $form->addElement('advmultiselect', 'user', // TODO use dictionary
		    array('Rechte:', 'Verfügbar', 'Zugeordnet'), // labels
		    null,                                             // datas
		    array('style' => 'width:200px;')                  // custom layout
		);

		// load QFAMS values (unselected and selected)
		$ams->load($GLOBALS['db'], $queryAll, 'rightname', 'right_id', $associated_rights);

		$form->addElement('hidden', 'group_id', $groupId);
		$form->addElement('submit', 'send_group_right', Factory::getObject('Dictionary')->getTrans('save'));
	    return $form->toHtml();
	}

	protected function createDeleteDialog(){
        if( !PluginManager::getPlgInstance('UserManager')->hasDeleteRight() )
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createDeleteDialog()',__FILE__, __LINE__);

		$heading = Factory::getObject('Dictionary')->getTrans('groups.h.deleteEntries');
		return $this->createDeletionConfirmFormForHTML_TableForms($heading);
	}

	/**
	 *
	 *
	 * @author ahe
	 * @date 22.11.2005 20:07:42
	 */
	public function checkTransactions($id=0) {
		$retVal = $this->checkForDeleteTransaction('group_id',__FILE__,__LINE__);

		$this->checkEditUserGroupAssoc();

		$this->checkEditGroupRightsAssoc();

		// check for edit
		$this->makeCheck($this->dalObj,'editGroupElement',$_POST,
				'update','group_id='.intval($_POST['group_id']));

		// check for insert
		$this->makeCheck($this->dalObj,'go_group_action');

	}

	private function checkEditUserGroupAssoc() {
		require_once 'HTML/QuickForm.php';
		require_once 'HTML/QuickForm/advmultiselect.php';
		$form = new HTML_QuickForm('ug_assoc');
	    if(isset($_POST['send_user_group'])) {
		    $groupId = intval($_POST['group_id']);
			if (!$form->validate())
			    return BcmsSystem::raiseNotice('Forminhalt ist ungültig!', // TODO use dictionary
			 		BcmsSystem::LOGTYPE_CHECK, BcmsSystem::SEVERITY_INFO,
					'checkEditUserGroupAssoc()',__FILE__, __LINE__);

		    $retVal = $this->deleteUserGroupAssoc($groupId);
		    if(!$retVal) return false;
		    $clean = $form->getSubmitValues();
		    if(isset($clean['user'])) {
			    foreach ($clean['user'] as $userId) {
					$retVal = $this->addUserGroupAssoc($groupId,$userId);
				    if(!$retVal) return false;
				}
		    }
		    return BcmsSystem::raiseDictionaryNotice('dataInsertSuccess',
		 		BcmsSystem::LOGTYPE_CHECK, BcmsSystem::SEVERITY_INFO,
				'checkEditUserGroupAssoc()',__FILE__, __LINE__);
		}
		return true;
	}

	private function deleteUserGroupAssoc($groupId){
		$sql = 'DELETE FROM '.$this->configInstance->getTablename('user_group_assoc')
			.' WHERE fk_rolle='.$groupId;

	 	$result = $GLOBALS['db']->query($sql);
		if ($result instanceof PEAR_ERROR)	{
			return BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_DELETE,
				BcmsSystem::SEVERITY_ERROR, 'deleteUserGroupAssoc()',
				__FILE__,__LINE__);
		}
	  	return $result;
	}

	private function addUserGroupAssoc($groupId,$userId){
		$sql = 'INSERT INTO '.$this->configInstance->getTablename('user_group_assoc')
			.' (fk_rolle, fk_user) VALUES ('.$groupId.', '.$userId.')';
	 	$result = $GLOBALS['db']->query($sql);
		if ($result instanceof PEAR_ERROR)	{
			return BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_INSERT,
				BcmsSystem::SEVERITY_ERROR, 'addUserGroupAssoc()',
				__FILE__,__LINE__);
		}
	  	return $result;
	}

	private function checkEditGroupRightsAssoc() {
		require_once 'HTML/QuickForm.php';
		require_once 'HTML/QuickForm/advmultiselect.php';
		$form = new HTML_QuickForm('ug_assoc');
	    if(isset($_POST['send_group_right'])) {
		    $groupId = intval($_POST['group_id']);
			if (!$form->validate())
			    return BcmsSystem::raiseNotice('Forminhalt ist ungültig!', // TODO use dictionary
			 		BcmsSystem::LOGTYPE_CHECK, BcmsSystem::SEVERITY_INFO,
					'checkEditGroupRightsAssoc()',__FILE__, __LINE__);

		    $retVal = $this->deleteGroupRightAssoc($groupId);
		    if(!$retVal) return false;
		    $clean = $form->getSubmitValues();
		    if(isset($clean['user'])) {
			    foreach ($clean['user'] as $userId) {
					$retVal = $this->addGroupRightAssoc($groupId,$userId);
				    if(!$retVal) return false;
				}
		    }
		    return BcmsSystem::raiseDictionaryNotice('dataInsertSuccess',
		 		BcmsSystem::LOGTYPE_CHECK, BcmsSystem::SEVERITY_INFO,
				'checkEditGroupRightsAssoc()',__FILE__, __LINE__);
	    }
		return true;
	}

	private function deleteGroupRightAssoc($groupId){
		$sql = 'DELETE FROM '.$this->configInstance->getTablename('groups_rechte_zo')
			.' WHERE fk_rolle='.$groupId;

	 	$result = $GLOBALS['db']->query($sql);
		if ($result instanceof PEAR_ERROR)	{
			return BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_DELETE,
				BcmsSystem::SEVERITY_ERROR, 'deleteGroupRightAssoc()',
				__FILE__,__LINE__);
		}
	  	return $result;
	}

	private function addGroupRightAssoc($groupId,$rightId){
		$sql = 'INSERT INTO '.$this->configInstance->getTablename('groups_rechte_zo')
			.' (fk_rolle, fk_recht) VALUES ('.$groupId.', '.$rightId.')';
	 	$result = $GLOBALS['db']->query($sql);
		if ($result instanceof PEAR_ERROR)	{
			return BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_INSERT,
				BcmsSystem::SEVERITY_ERROR, 'addGroupRightAssoc()',
				__FILE__,__LINE__);
		}
	  	return $result;
	}

	public function getGroupList() {
		$groups = $this->dalObj->select('list_everything');
		$retArray[0] = ''; // add empty row for "no additional right"
		for ($i = 0; $i < sizeof($groups); $i++) {
			$retArray[$groups[$i]['group_id']] = $groups[$i]['groupname'];
		}
		return $retArray;
	}

	public function getGroupnameById($id) {
		$groups = $this->dalObj->select('list_everything','group_id='.$id);
		return $groups[0]['groupname'];
	}
}
?>
