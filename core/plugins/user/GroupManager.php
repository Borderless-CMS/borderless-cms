<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

require_once 'core/plugins/user/Groups_DAL.php';

/**
 * GroupManager somehow combines the controller and all the views needed for
 * group management. This is e.g. being used by UserManager
 *
 * @since 0.10
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @date 2006-07-05
 * @class GroupManager
 * @ingroup groups
 * @package groups
 */
class GroupManager extends AbstractManager {

	// the versionstring is needed e.g. for getHashcode()
	protected $versionstring = '0.6';
	protected $modname = 'GroupManager';
	protected $dalObj = null;
	protected $configInstance = null;

	/**
	 * Constructor of GroupManager. Instantiates needed objects, sets plugin
	 * tables to config and defines plugin actions
	 *
	 * @author ahe
	 * @date 22.11.2005 20:07:42
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
			0 => array('edit', BcmsSystem::getDictionaryManager()->getTrans('edit'), 'Edit'),
			1 => array('delete', BcmsSystem::getDictionaryManager()->getTrans('delete'), 'Delete'),
			2 => array('user_group',
				BcmsSystem::getDictionaryManager()->getTrans('groups.editUserGroupAssoc'),
				'EditUserGroupAssoc'),
			3 => array('group_right',
				BcmsSystem::getDictionaryManager()->getTrans('groups.editGroupRightAssoc'),
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
	 */
	public function getPageTitle() {
		return null; // @todo implement this
	}

	/**
	 * returns the MetaDescription of the current menu
	 *
	 * @return string
	 * @author ahe
	 * @date 01.05.2006 00:21:56
	 */
	public function getMetaDescription() {
		return null; // @todo implement this
	}

	/**
	 * returns the MetaKeywords of the current menu
	 *
	 * @return string
	 * @author ahe
	 * @date 01.05.2006 00:23:41
	 */
	public function getMetaKeywords() {
		return null; // @todo implement this
	}

	/**
	 *
	 *
	 * @author ahe
	 * @date 22.11.2005 20:07:42
	 */
	public function printCategoryConfigForm($menuId) {
		return null; // @todo implement this
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

		$dialog = $this->performListAction('groups_table');
		if($dialog!=null) return $dialog;
		// ...else print general table overview

		// get dictionary table and surround it with a scrollpane div-tag
		$retStr = $this->createGroupsTable();

		$form =& $this->dalObj->getForm('groups_form','go_group_action'
			,BcmsSystem::getDictionaryManager()->getTrans('save'));
		$retStr .= BcmsFactory::getInstanceOf('GuiUtility')->fillTemplate('fieldset_tpl'
					,array('id="groups"'
					,BcmsSystem::getDictionaryManager()->getTrans('groups.addGroup')
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
			BcmsSystem::getDictionaryManager()->getTrans('groups.heading'),
			'group_id',true);
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
		$form = $this->dalObj->getForm('groupeditform','editGroupElement'
			,BcmsSystem::getDictionaryManager()->getTrans('save'),$element);
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

		$ams =& $form->addElement('advmultiselect', 'user', // @todo use dictionary
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

		$ams =& $form->addElement('advmultiselect', 'user', // @todo use dictionary
		    array('Rechte:', 'Verfügbar', 'Zugeordnet'), // labels
		    null,                                             // datas
		    array('style' => 'width:200px;')                  // custom layout
		);

		// load QFAMS values (unselected and selected)
		$ams->load($GLOBALS['db'], $queryAll, 'rightname', 'right_id', $associated_rights);

		$form->addElement('hidden', 'group_id', $groupId);
		$form->addElement('submit', 'send_group_right', BcmsSystem::getDictionaryManager()->getTrans('save'));
	    return $form->toHtml();
	}

	protected function createDeleteDialog(){
        if( !BcmsSystem::getUserManager()->hasDeleteRight() )
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createDeleteDialog()',__FILE__, __LINE__);

		$heading = BcmsSystem::getDictionaryManager()->getTrans('groups.h.deleteEntries');
		return $this->createDeletionConfirmFormForHTML_TableForms($heading);
	}

	/**
	 *
	 *
	 * @author ahe
	 * @date 22.11.2005 20:07:42
	 */
	public function checkTransactions($id=0) {
		$this->checkForDeleteTransaction('group_id',__FILE__,__LINE__);

		$this->checkEditUserGroupAssoc();

		$this->checkEditGroupRightsAssoc();

		if(array_key_exists('group_id',$_POST)){
			// check for edit
			$this->makeCheck($this->dalObj,'editGroupElement',$_POST,
				'update','group_id='.intval($_POST['group_id']));
		}

		// check for insert
		$this->makeCheck($this->dalObj,'go_group_action');

	}

	private function checkEditUserGroupAssoc() {
		require_once 'HTML/QuickForm.php';
		require_once 'HTML/QuickForm/advmultiselect.php';
		$form = new HTML_QuickForm('ug_assoc');
		if(array_key_exists('send_user_group', $_POST)){
		    $groupId = intval($_POST['group_id']);
			if (!$form->validate())
			    return BcmsSystem::raiseNotice('Forminhalt ist ungültig!', // @todo use dictionary
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
		if(array_key_exists('send_group_right', $_POST)){
		    $groupId = intval($_POST['group_id']);
			if (!$form->validate())
			    return BcmsSystem::raiseNotice('Forminhalt ist ungültig!', // @todo use dictionary
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
