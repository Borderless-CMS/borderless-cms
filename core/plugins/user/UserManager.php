<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * UserManager class
 *
 * @since 0.9
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @date 27.01.2006
 * @class UserManager
 * @ingroup users
 * @package users
 */
class UserManager extends AbstractManager {

	// the versionstring is needed e.g. for getHashcode()
	protected $versionstring = '0.19';
	protected $modname = 'UserManager';
	protected $dalObj = null;
	protected $logicObj = null;

	public function __construct(){
		BcmsConfig::getInstance()->setTablename('user', 'plg_users');
		BcmsConfig::getInstance()->setTablename('groups', 'plg_groups');
		BcmsConfig::getInstance()->setTablename('user_group_assoc', 'user_group_assoc');
		BcmsConfig::getInstance()->setTablename('rechte', 'plg_rights');
		BcmsConfig::getInstance()->setTablename('groups_rechte_zo', 'grouprightassoc');
		$this->actions = array(
			0 => array('edit', BcmsSystem::getDictionaryManager()->getTrans('edit'), 'Edit'),
			1 => array('delete', BcmsSystem::getDictionaryManager()->getTrans('delete'), 'Delete'),
			2 => array('send_new_pw', BcmsSystem::getDictionaryManager()->getTrans('send_new_pw'), 'SendNewPassword')
		);
	}

	public function init($menuId){
	}

	public function main($menuId) {

		$myModArray = PluginManager::getInstance()->getCurrentMainPlugin();
		switch ($myModArray['func']) {
			case 'login':
				return $this->getLogic()->createLoginForm();
				break;
			case 'password':
				return $this->getLogic()->showForgotPwordDlg();
				break;
			case 'register':
				return $this->showRegisterUserDialog();
				break;
			case 'profile':
				return $this->createProfileEditForm();
				break;
			case 'list':
				return $this->printGeneralConfigForm();
			case 'show':
				return $this->showUserProfile();
				break;
		}
	}

	/**
	 * Returns the logic class of the CategoryManager
	 *
	 * @return User - instance logic class
	 * @author ahe  <aheusingfeld@borderlesscms.de>
	 * @date 18.04.2007
	 * @since 0.13.153
	 */
	public function getLogic(){
		if(!isset($this->logicObj)){
			require_once 'core/plugins/user/User.php';
			$this->logicObj = new User($this);
		}
		return $this->logicObj;
	}

	/**
	 * Returns a child instance of DataAbstractionLayer
	 *
	 * @return DataAbstractionLayer a child instance of DataAbstractionLayer
	 * @author ahe  <aheusingfeld@borderlesscms.de>
	 * @date 18.04.2007
	 * @since 0.13.153
	 */
	public function getDalObj(){
		if(!isset($this->dalObj)){
			require_once 'core/plugins/user/Users_DAL.php';
			$this->dalObj = Users_DAL::getInstance();
		}
		return $this->dalObj;
	}

	/**
	 * Dummy method will return nothing as password sending has already been
	 * processed in checkForListAction(). This way the userlist will show up again.
	 *
	 * @return mixed - see #showUserList() for return value
	 * @author ahe
	 * @date 23.11.2006 23:53:47
	 * @see #checkForListAction()
	 */
	protected function createSendNewPasswordDialog(){
		return null; // do nothing as everything is done in checkForListAction()
	}

	/**
	 * Checks whether user is logged in or not
	 *
	 * @return boolean
	 * @author ahe
	 * @date 19.10.2006 23:46:00
	 */
	public function isLoggedIn() {
		return ($this->getLogic()->isLoggedIn());
	}

	private function createUserTable($offset=null,$limit=null) {
		// get turn page vars
		$tableObj = new HTMLTable('user_table');
		$tableObj->setBounds('page',$limit,$this->getModel()->getNumberOfEntries());
		$offset = $tableObj->getListOffset();
		$limit = $tableObj->getListLimit();
        $tableObj->setTranslationPrefix('user.');
		$tableObj->setActions($this->actions);

		$user = $this->getModel()->getList($offset,$limit);
		$tableObj->setData($user);
		unset($user);
		return $tableObj->render(
			BcmsSystem::getDictionaryManager()->getTrans('user.app_heading'),
			'user_id',true);
	}

	public function getCss($menuId=0){}

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
	 * @author ahe <aheusingfeld@borderlesscms.de>
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
	 * Returns user id of currently logged in user
	 *
	 * @return int - user id of currently logged in user
	 * @since 0.13.75
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 * @date 28.03.2007
	 */
	public function getUserId(){
		return $this->getLogic()->getUserID();
	}

	public function checkTransactions($menuId=0) {
		$myModArray = PluginManager::getInstance()->getCurrentMainPlugin();
		switch ($myModArray['func']) {
			case 'login':
				return $this->checkForLoginAction();
			case 'logout':
				return $this->getLogic()->logout();
			case 'profile':
				$userId = $this->getUserId();

				return $this->makeCheck($this->getModel(), 'update_user',$_POST
					,'update','user_id = '.$userId);

			case 'password':
				if(array_key_exists('password_forgotten', $_POST)){
					// ACHTUNG: HIER MUESSTE DER WORKFLOW angestossen werden!
					if( (BcmsSystem::getParser()->getPostParameter('username')!='Not_logged_in') // @todo ERROR: the username can be changed!!! don't use usernames as business keys!
						&& (BcmsSystem::getParser()->getPostParameter('username') != 'admin') )
						return $this->getLogic()->forgotPassword(BcmsSystem::getParser()->getPostParameter('username'));
					else
					    return BcmsSystem::raiseNoAccessRightNotice(
							'checkTransactions():password',__FILE__, __LINE__);


			}
				break;
			case 'list':
				return $this->checkForListAction();
		}
	}

	private function checkForLoginAction(){
		if( array_key_exists('action',$_POST) && $_POST['action']=='login'
			&& array_key_exists('username',$_POST) && isset($_POST['passworte'])
		)
		{
			$userId=$this->getLogic()->login($_POST['username'],$_POST['passworte']);
			// if login-method returned boolean value (false), exit here!
			if(is_bool($userId)) return $userId;

			$_SESSION['cssfile'] = $this->getLogic()->getFavStyle();
			if(!empty($_POST['return_to_prior_page']) // if 'return_to_prior_page' is clicked and
				&& strstr($_POST['return_to_prior_page'],$_SERVER['HTTP_HOST']) // specified url belongs to current host
				)
			{
				$location = BcmsSystem::getParser()->getPostParameter('return_to_prior_page');
			} else {
				// create call for users favorite starting category
				$nextCatId = $this->getLogic()->getFavMenu();
				$location = BcmsSystem::getCategoryManager()->getLogic()->createModRewriteLink($nextCatId);
			}

		  	/* IMPORTANT: Write session_data to assure that login is persisted!
		  	 * Reason for this was a problem with session handling: the
		  	 * following page already tried to read session from db before
		  	 * previous page had stored session_data to db.
		  	 */
		  	BcmsFactory::getInstanceOf('UserSession')->_write(
		  		session_id(),
		  		serialize($_SESSION),
		  		$this->getUserId()
		  	);
			header('Location: '.$location, true);
		}
	}

	private function checkForListAction(){
		// check for edit
		if(isset($_POST['editUserElement']))
			return $this->makeCheck($this->getModel(),'editUserElement',$_POST,
				'update','user_id='.intval($_POST['user_id']));

		// check for "send new password"
		if(!empty($_POST['table_action_select_user_table'])
			&& $_POST['table_action_select_user_table']==$this->actions[2][0]
		){
			$result = HTMLTable::getAffectedIds();
			$id=$result[0];
			$username = $this->getLogic()->getUserNameFromDB($id);
			return $this->getLogic()->forgotPassword($username,true);
		}

		// check for insert
		if(isset($_POST['go_user_action'])) {
			return $this->getLogic()->registerUser($_POST,'go_user_action');
		}

		return $this->checkForDeleteTransaction('user_id',__FILE__,__LINE__);
	}

	public function printGeneralConfigForm(){
		if(!$this->getLogic()->hasRight('user_view_list'))
		    return BcmsSystem::raiseNoAccessRightNotice(
				'printGeneralConfigForm()',__FILE__, __LINE__);

		$dialog = $this->performListAction('user_table');
		if($dialog!=null) return $dialog;
		// ...else print general table overview

		// get dictionary table and surround it with a scrollpane div-tag
		$retStr = $this->createUserTable();

		$retStr .= $this->createAddNewUserForm();
		return $retStr;
	}

	public function printCategoryConfigForm($menuId){
		// @todo implement this to show pluginCatConfig
	}

	/**
	 * Checks whether current user possess specified right
	 *
	 * @param String $rightname name of the right to be checked for
	 * @return boolean true if user possess right
	 * @author ahe
	 * @date 20.10.2006 22:15:51
	 */
	public function hasRight($rightname){
		return $this->getLogic()->hasRight($rightname);
	}

	/**
	 * Checks whether current user possess view right for current category
	 *
	 * @return boolean true if user possess right
	 * @author ahe
	 * @date 17.11.2006 21:23:51
	 */
	public function hasViewRight(){
		return $this->getLogic()->hasRight(
			BcmsSystem::getCategoryManager()->getLogic()->getViewRight());
	}

	/**
	 * Checks whether current user possess edit right for current category
	 *
	 * @return boolean true if user possess right
	 * @author ahe
	 * @date 17.11.2006 21:23:51
	 */
	public function hasEditRight(){
		return $this->getLogic()->hasRight(
			BcmsSystem::getCategoryManager()->getLogic()->getEditRight());
	}

	/**
	 * Checks whether current user possess delete right for current category
	 *
	 * @return boolean true if user possess right
	 * @author ahe
	 * @date 17.11.2006 21:23:51
	 */
	public function hasDeleteRight(){
		return $this->getLogic()->hasRight(
			BcmsSystem::getCategoryManager()->getLogic()->getDeleteRight());
	}

	protected function createEditDialog(){
        // @todo plg_cat_conf-edit_right should actually be checked here!
		if(!$this->getLogic()->hasRight('user_edit'))
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createEditDialog()',__FILE__, __LINE__);

		$result = HTMLTable::getAffectedIds();
		$id=$result[0];
		$element = $this->getModel()->getFullObject($id);
		$element['pword'] = '';
		$element['pword_again'] = '';
		$element['public_fields'] = unserialize($element['public_fields']);
		$this->getModel()->addPwordFields();
		$this->getModel()->setLabels();
		$form = $this->getModel()->getForm('usereditform','editUserElement'
			,BcmsSystem::getDictionaryManager()->getTrans('save'),$element);
		return $form->toHTML();
	}

	private function createAddNewUserForm() {
		if(!$this->getLogic()->hasRight('user_create'))
			 return false;

		$form =& $this->getModel()->getForm('user_form','go_user_action'
			,BcmsSystem::getDictionaryManager()->getTrans('save'));
		$refGUI = BcmsFactory::getInstanceOf('GuiUtility');
		return $refGUI->fillTemplate('fieldset_tpl'
					,array('id="user_creation_fieldset"'
					,BcmsSystem::getDictionaryManager()->getTrans('user.addUser')
					,$form->toHtml(),null));

	}

	protected function createDeleteDialog(){
		if(!$this->getLogic()->hasRight('user_delete'))
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createDeleteDialog()',__FILE__, __LINE__);

		$heading = BcmsSystem::getDictionaryManager()->getTrans('user.h.deleteEntries');
		return $this->createDeletionConfirmFormForHTML_TableForms($heading);
	}

	private function createProfileEditForm($userId=0){

		if (!$this->getLogic()->hasRight('user_profile_update'))
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createProfileEditForm()',__FILE__, __LINE__);

		$userId = $this->getUserId();
		if(BcmsSystem::getParser()->getGetParameter('userID')!=null)
			$userId = intval(BcmsSystem::getParser()->getGetParameter('userID'));

		$heading =
			BcmsFactory::getInstanceOf('GuiUtility')->createHeading(3,
					BcmsSystem::getDictionaryManager()->getTrans('h.UserProfileEdit'));

		$columns = $this->getModel()->getFullObject($userId);
		$columns['pword'] = '';
		$columns['pword_again'] = '';
		$columns['public_fields'] =
			unserialize($columns['public_fields']);
		$this->getModel()->addPwordFields();
		$this->getModel()->setLabels();
		$form = $this->getModel()->getForm('userprofile','update_user'
			,BcmsSystem::getDictionaryManager()->getTrans('save'),$columns);

		// freeze elements if user does not have right to change them
		if(!$this->hasRight('user_change_additional_right'))
		{
			$form->getElement('fk_zusatzrecht')->freeze();
		}
		if(!$this->hasRight('user_set_root_flag'))
		{
			$form->getElement('root_flag')->freeze();
		}

		return $heading.$form->toHtml();
	}

    /**
     *
     *
     * @author ahe
     * @param String $password the password to be hashed
     * @return String the hash of the given password
     */
    public function getEncodedPassword($password){
        return $this->getLogic()->getHashedPassword($password);
    }

	private function showUserProfile($userId=0){

		if (!$this->getLogic()->hasRight('user_view'))
		    return BcmsSystem::raiseNoAccessRightNotice(
				'showUserProfile()',__FILE__, __LINE__);

		// getUserInfosByName
		$username = isset($_SESSION['mod']['oname']) ? $_SESSION['mod']['oname'] : $this->getLogic()->getUserName();
		$userID = $this->getLogic()->getUserIdByName($username);
		$refGui = BcmsFactory::getInstanceOf('GuiUtility');
		$heading = $refGui->createHeading(3,
			BcmsSystem::getDictionaryManager()->getTrans('h.UserProfileOf').$username);

		$columns = $this->getModel()->select('list_everything','user_id = '.$userID);

		// parse content of some fields
		$columns[0]['about_me'] =
			BcmsSystem::getParser()->parseTagsByAllRegex($columns[0]['about_me']);
		$columns[0]['homepage'] =
			BcmsSystem::getParser()->parseLinksByRegex($columns[0]['homepage']);

		// make public fields viewable and hide all others
		$columns[0]['public_fields'] =
			unserialize($columns[0]['public_fields']);
		if($columns[0]['public_fields']=='' || $columns[0]['public_fields']==null){
			$columns[0]['public_fields'] = array('username');
		}

		// if user doesn't possess right to see all fields, hide others
		if (!$this->hasRight('SHOW_ALL_USER_FIELDS')) {
		$columns[0] = BcmsSystem::getParser()->stripArrayFieldsInverse(
						$columns[0],
						$columns[0]['public_fields']);
		}

		$this->getModel()->setLabels();
		$form = $this->getModel()->getForm('userprofile','update_user'
			,BcmsSystem::getDictionaryManager()->getTrans('save'),$columns[0]);
		$form->removeElement('update_user');
		$form->removeElement('abort_action');
		$form->removeElement('reset_values');

		$skype='';
		if(array_key_exists('skype_username', $columns[0])) {
			$user = $columns[0]['skype_username'];
			$action = 'userinfo'; // can be "add, chat, call, Click, userinfo, sendfile"
			$skype = '<script type="text/javascript" src="http://download.' .
					'skype.com/share/skypebuttons/js/skypeCheck.js"></script>' .
					'<img style="border:0px" ' .
					'alt="My Status" src="http://mystatus.skype.com/mediumicon/'
					.$user.'" />' .
					'<a href="skype:'.$user.'?add">add</a> | '.
					'<a href="skype:'.$user.'?click">click</a> | '.
					'<a href="skype:'.$user.'?info">info</a> | '.$user;
		}
		$form->freeze();
// @todo find a way to make 'homepage a link'
//			$elem=$form->getElement('homepage');
//				$elem->setValue(BcmsSystem::getParser()->parseLinksByRegex($elem->getValue()));
		return $heading.$form->toHtml().$skype;
	}

	private function showRegisterUserDialog() {
		if (!BcmsConfig::getInstance()->showRegisterUser==1)
		    return BcmsSystem::raiseDictionaryNotice('function deactivated',
		 		BcmsSystem::LOGTYPE_CHECK, BcmsSystem::SEVERITY_WARNING,
				'showRegisterUserDialog()',__FILE__, __LINE__);

		if ($_POST['register_user']){
			if(($_POST['username'] && $_POST['vorname']
				&& $_POST['nachname'] && $_POST['email']))
			{
				$cols['username'] = BcmsSystem::getParser()->getPostParameter('username');
				$cols['vorname'] = BcmsSystem::getParser()->getPostParameter('vorname');
				$cols['nachname'] = BcmsSystem::getParser()->getPostParameter('nachname');
				$cols['email'] = BcmsSystem::getParser()->getPostParameter('email');
				$cols['telefon'] = BcmsSystem::getParser()->getPostParameter('telefon');
				// ACHTUNG: HIER MUESSTE DER WORKFLOW angestossen werden!
				return $this->getLogic()->registerUser($cols,'register_user');
			} else {
				$msg = 'WARNUNG: Unvollst&auml;ndige Angaben. ".
					"Bitte vervollst&auml;ndigen Sie Ihre Eingabe!<br />'; // @todo use dictionary
				return BcmsSystem::raiseNotice($msg,
					BcmsSystem::LOGTYPE_CHECK, BcmsSystem::SEVERITY_ERROR,
					'showRegisterUserDialog()',	__FILE__,__LINE__);
			}
		} else {
			// hat der Benutzer bereits die Benutzungsvereinbarungen bestaetigt?
			if (! $_POST['agreement_confirmed'])
			{
				echo '          <h2 id="profile_header">'
					.BcmsSystem::getDictionaryManager()->getTrans('h.user_agreement').'</h2>',"\n";
				echo '          <div id="user_agreement">'."\n";
				$agreement = BcmsSystem::getDictionaryManager()->getTrans('user_agreement');
//				$agreement = BcmsSystem::getParser()->filter($agreement);
				$agreement = BcmsSystem::getParser()->addParagraphs($agreement);
				echo $agreement;
				echo '          </div> <!-- /user_agreement -->'."\n";
				echo '
						<div id="profile_buttons">
							<form class="profile_frm" action="'.
					BcmsSystem::getParser()->getServerParameter('REQUEST_URI')
					.'" method="post" enctype="'
					.BcmsConfig::getInstance()->default_form_enctype
					.'">
								<input type="submit" name="agreement_confirmed" value="'
					.BcmsSystem::getDictionaryManager()->getTrans('accept').'" />
							</form>
							<form class="profile_frm" action="/login/" method="post" enctype="'
					.BcmsConfig::getInstance()->default_form_enctype.'">

								<input type="submit" name="agreement_rejected" value="'
					.BcmsSystem::getDictionaryManager()->getTrans('reject').'" />
							</form>
						</div>
				';
			}
			else
			{
				echo '
						<h2 id="profile_header">Benutzerkonto anlegen</h2>
						<form class="profile_frm" action="'.
					BcmsSystem::getParser()->getServerParameter('REQUEST_URI')
					.'" method="post" enctype="'
					.BcmsConfig::getInstance()->default_form_enctype
					.'">
							<div id="login_daten">
								<h4>Login Daten</h4>
								<span>Username: <input type="text" maxlength="20" name="username"></span>
							</div> <!-- /login_daten -->

							<div id="perso_daten">
								<h4>Pers&ouml;nliche Daten</h4>
								<span>Vorname: <input type="text" maxlength="25" name="vorname"></span>
								<span>Nachname: <input type="text" maxlength="25" name="nachname"></span>
								<span>E-Mail-Adresse: <input type="text" maxlength="50" name="email"></span>
								<span>Telefon: <input type="text" maxlength="20" name="telefon"></span>
							</div> <!-- /perso_daten -->
							<h5>INFO: Geben Sie bitte unbedingt eine korrekte E-Mailadresse an,
							 da Ihnen das Passwort f&uuml;r Ihren Account per E-Mail zugeschickt wird.</h5>
							<div id="profile_buttons">
								<input type="submit" name="register_user" value="Konto anlegen">
							</div>

						</form>'."\n";
			}
		}
	}

}
?>