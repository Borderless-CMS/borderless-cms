<?php
require 'Users_DAL.php';
require 'User.php';
/**
 * Contains UserManager class
 *
 * @module UserManager.php
 * @author ahe <aheusingfeld@borderlesscms.de>
 * @package plugins/user
 * @version $Id: $WCREV$
 * @modified $WCMODS?Modified:Not modified$
 * @link $WCURL$
 * @since $WCRANGE$ - $WCDATE$
 */

class UserManager extends AbstractManager {

	// the versionstring is needed e.g. for getHashcode()
	protected $versionstring = '0.19';
	protected $modname = 'UserManager';
	protected $dalObj = null;
	protected $logicObj = null;
	protected $actions = array();

	public function __construct(){
		$this->init($_SESSION['m_id']);
	}

	public function init($menuId){
		$this->dalObj = Users_DAL::getInstance();
		$this->logicObj = new User($this);
		$this->actions = array(
			0 => array('edit', Factory::getObject('Dictionary')->getTrans('edit'), 'Edit'),
			1 => array('delete', Factory::getObject('Dictionary')->getTrans('delete'), 'Delete')
		);
	}

	public function main($menuId) {

		$myModArray = PluginManager::getInstance()->getCurrentMainPlugin();
		switch ($myModArray['func']) {
			case 'login':
				return PluginManager::getPlgInstance('UserManager')->getLogic()->createLoginForm();
				break;
			case 'logout':
				PluginManager::getPlgInstance('UserManager')->getLogic()->logout();
				break;
			case 'password':
				return PluginManager::getPlgInstance('UserManager')->getLogic()->showForgotPwordDlg();
				break;
			case 'register':
				return $this->showRegisterUserDialog();
				break;
			case 'profile':
				return $this->createProfileEditForm();
				break;
			case 'list':
				if(isset($_POST['table_action_select_user_table'])){
					$i = -1;
					$actionFound = false;
					while($i < sizeof($this->actions) && !$actionFound){
						$i++;
						if($_POST['table_action_select_user_table'] == $this->actions[$i][0])
							$actionFound=true;
					}
					$methodName = 'create'.$this->actions[$i][2].'Dialog';
					return $this->$methodName();
				}
				// ...else print general table overview
				return $this->showUserList();
			case 'show':
				return $this->showUserProfile();
				break;
		}
	}

	private function createUserTable($offset=null,$limit=null) {
		// get turn page vars
		$tableObj = new HTMLTable('user_table');
        $tableObj->setTranslationPrefix('user.');
		$tableObj->setActions($this->actions);
		$tableObj->setBounds('page',null,$this->dalObj->getNumberOfEntries());
		$limit = $tableObj->getListLimit();
		$offset = $tableObj->getListOffset();
		$forms = $this->dalObj->getList($offset,$limit);
		$tableObj->setData($forms);
		unset($forms);
		return $tableObj->render(
			Factory::getObject('Dictionary')->getTrans('forms.app_heading'),
			'form_id',true);
	}

	public function getCss($menuId=0){}

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

	public function checkTransactions($menuId=0) {
		$myModArray = PluginManager::getInstance()->getCurrentMainPlugin();
		switch ($myModArray['func']) {
			case 'login':
				return $this->checkForLoginAction();
			case 'logout':
				return PluginManager::getPlgInstance('UserManager')->getLogic()->logout();
			case 'profile':
				$parser = BcmsFactory::getInstanceOf('Parser');
				$userId = PluginManager::getPlgInstance('UserManager')->getLogic()->getUserID();
				if(isset($_SESSION['mod']['oid']))
					$userId = intval($_SESSION['mod']['oid']);
				if($parser->getGetParameter('userID')!=null)
					$userId = intval($parser->getGetParameter('userID'));

				return $this->makeCheck($this->dalObj, 'update_user',$_POST
					,'update','user_id = '.$userId);

			case 'password':
				if (isset($_POST['password_forgotten'])) {
				$parser = BcmsFactory::getInstanceOf('Parser');
					// ACHTUNG: HIER MUESSTE DER WORKFLOW angestossen werden!
					if( ($parser->getPostParameter('username')!='Not_logged_in')
						&& ($parser->getPostParameter('username') != 'admin') )
						return $this->logicObj->forgotPassword($parser->getPostParameter('username'));
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
		if( isset($_POST['action'])	&& $_POST['action']=='login'
			&& isset($_POST['username']) && isset($_POST['passworte'])
		)
		{
			$parser = BcmsFactory::getInstanceOf('Parser');
			$userId=$this->logicObj->login($_POST['username'],$_POST['passworte']);
			// if login-method returned boolean value (false), exit here!
			if(is_bool($userId)) return $userId;

			$_SESSION['cssfile'] = $this->logicObj->getFavStyle();

			if(!empty($_POST['return_to_prior_page']) // if 'return_to_prior_page' is clicked and
				&& strstr($_SERVER['HTTP_HOST'],$_POST['return_to_prior_page'])) // specified url belongs to current host
			{
				$location = $parser->getPostParameter('return_to_prior_page');
			} else {
				// create call for users favorite starting category
				$nextCatId = $this->logicObj->getFavMenu();
				$location = PluginManager::getPlgInstance('CategoryManager')->getLogic()->createModRewriteLink($nextCatId);
			}

		  	/* IMPORTANT: Write session_data to assure that login is persisted!
		  	 * Reason for this was a problem with session handling: the
		  	 * following page already tried to read session from db before
		  	 * previous page had stored session_data to db.
		  	 */
		  	$sessionObj = Factory::getObject('UserSession');
		  	$sessionObj->_write(session_id(),serialize($_SESSION));
			header('Location: '.$location, true);
		}
	}

	private function checkForListAction(){
		// check for edit
		if(isset($_POST['editUserElement']))
			return $this->makeCheck($this->dalObj,'editUserElement',$_POST,
				'update','user_id='.intval($_POST['user_id']));

		// check for insert
		if(isset($_POST['go_user_action']))
			return $this->makeCheck($this->dalObj,'go_user_action');

		return $this->checkForDeleteTransaction('user_id',__FILE__,__LINE__);
	}

	public function showUserList(){
		if(!$this->logicObj->hasRight('benutzerliste_sehen'))
		    return BcmsSystem::raiseNoAccessRightNotice(
				'printGeneralConfigForm()',__FILE__, __LINE__);

		// get dictionary table and surround it with a scrollpane div-tag
		$retStr = $this->createUserTable();

		$retStr .= $this->createAddNewUserForm();
		return $retStr;
	}

	public function printGeneralConfigForm(){}

	public function printCategoryConfigForm($menuId){}

	/**
	 * Checks whether current user possess specified right
	 *
	 * @param String $rightname name of the right to be checked for
	 * @return boolean true if user possess right
	 * @author ahe
	 * @date 20.10.2006 22:15:51
	 * @package htdocs/plugins/user
	 */
	public function hasRight($rightname){
		return $this->logicObj->hasRight($rightname);
	}

	private function createEditDialog(){
		if(!$this->logicObj->hasRight('user_edit'))
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createEditDialog()',__FILE__, __LINE__);

		$result = HTMLTable::getAffectedIds();
		if(count($result)<1) return null;
		$id=$result[0];
		$element = $this->dalObj->getFullObject($id);
		$element['pword'] = '';
		$element['pword_again'] = '';
		$element['public_fields'] = unserialize($element['public_fields']);
		$this->dalObj->addPwordFields();
		$this->dalObj->setLabels();
		$form = $this->dalObj->getForm('usereditform','editUserElement'
			,Factory::getObject('Dictionary')->getTrans('save'),$element);
		return $form->toHTML();
	}

	private function createAddNewUserForm() {
		if(!$this->logicObj->hasRight('user_create'))
			 return false;

		$form =& $this->dalObj->getForm('user_form','go_user_action'
			,Factory::getObject('Dictionary')->getTrans('save'));
		$refGUI = Factory::getObject('GuiUtility');
		return $refGUI->fillTemplate('fieldset_tpl'
					,array('id="user_creation_fieldset"'
					,Factory::getObject('Dictionary')->getTrans('user.addUser')
					,$form->toHtml(),null));

	}

	private function createDeleteDialog(){
		if(!$this->logicObj->hasRight('user_delete'))
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createDeleteDialog()',__FILE__, __LINE__);

		$heading = Factory::getObject('Dictionary')->getTrans('user.h.deleteEntries');
		return $this->createDeletionConfirmFormForHTML_TableForms($heading);
	}

	private function createProfileEditForm($userId=0){

		if (!$this->logicObj->hasRight('user_profile_update'))
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createProfileEditForm()',__FILE__, __LINE__);

		$parser = BcmsFactory::getInstanceOf('Parser');
		$userId = isset($_SESSION['mod']['oid']) ? $_SESSION['mod']['oid'] : PluginManager::getPlgInstance('UserManager')->getLogic()->getUserID();
		if($parser->getGetParameter('userID')!=null)
			$userId = intval($parser->getGetParameter('userID'));

		$refGui = Factory::getObject('GuiUtility');
		$heading =
			$refGui->createHeading(3,Factory::getObject('Dictionary')->getTrans('h.UserProfileEdit'));

		$columns = $this->dalObj->getFullObject($userId);
		$columns['pword'] = '';
		$columns['pword_again'] = '';
		$columns['public_fields'] =
			unserialize($columns['public_fields']);
		$this->dalObj->addPwordFields();
		$this->dalObj->setLabels();
		$form = $this->dalObj->getForm('userprofile','update_user'
			,Factory::getObject('Dictionary')->getTrans('save'),$columns);
		return $heading.$form->toHtml();
	}

	private function showUserProfile($userId=0){

		if (!$this->logicObj->hasRight('user_view'))
		    return BcmsSystem::raiseNoAccessRightNotice(
				'showUserProfile()',__FILE__, __LINE__);

		$parser = BcmsFactory::getInstanceOf('Parser');

		// getUserInfosByName
		$username = isset($_SESSION['mod']['oname']) ? $_SESSION['mod']['oname'] : PluginManager::getPlgInstance('UserManager')->getLogic()->getUserName();
		$userID = PluginManager::getPlgInstance('UserManager')->getLogic()->getUserIdByName($username);
		$refGui = Factory::getObject('GuiUtility');
		$heading = $refGui->createHeading(3,
			Factory::getObject('Dictionary')->getTrans('h.UserProfileOf').$username);

		$columns = $this->dalObj->select('list_everything','user_id = '.$userID);

		// parse content of some fields
		$columns[0]['about_me'] =
			$parser->parseTagsByAllRegex($columns[0]['about_me']);
		$columns[0]['homepage'] =
			$parser->parseLinksByRegex($columns[0]['homepage']);

		// make public fields viewable and hide all others
		$columns[0]['public_fields'] =
			unserialize($columns[0]['public_fields']);
		if($columns[0]['public_fields']=='' || $columns[0]['public_fields']==null){
			$columns[0]['public_fields'] = array('username');
		}

		// if user doesn't possess right to see all fields, hide others
		if (!PluginManager::getPlgInstance('UserManager')->hasRight('SHOW_ALL_USER_FIELDS')) {
		$columns[0] = $parser->stripArrayFieldsInverse(
						$columns[0],
						$columns[0]['public_fields']);
		}

		$this->dalObj->setLabels();
		$form = $this->dalObj->getForm('userprofile','update_user'
			,Factory::getObject('Dictionary')->getTrans('save'),$columns[0]);
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
// TODO find a way to make 'homepage a link'
//			$elem=$form->getElement('homepage');
//				$elem->setValue($parser->parseLinksByRegex($elem->getValue()));
		return $heading.$form->toHtml().$skype;
	}

	private function showRegisterUserDialog() {
		$configInstance = BcmsConfig::getInstance();
		if (!$configInstance->showRegisterUser==1)
		    return BcmsSystem::raiseDictionaryNotice('function deactivated',
		 		BcmsSystem::LOGTYPE_CHECK, BcmsSystem::SEVERITY_WARNING,
				'showRegisterUserDialog()',__FILE__, __LINE__);

		$parser = BcmsFactory::getInstanceOf('Parser');
		if ($_POST['register_user']){
			if(($_POST['username'] && $_POST['vorname']
				&& $_POST['nachname'] && $_POST['email']))
			{
					// ACHTUNG: HIER MUESSTE DER WORKFLOW angestossen werden!
				return $this->logic->registerUser(
					$parser->getPostParameter('username'),
					$parser->getPostParameter('vorname'),
					$parser->getPostParameter('nachname'),
					$parser->getPostParameter('email'),
					$parser->getPostParameter('telefon'));
			} else {
				$msg = 'WARNUNG: Unvollst&auml;ndige Angaben. ".
					"Bitte vervollst&auml;ndigen Sie Ihre Eingabe!<br />'; // TODO use dictionary
				return BcmsSystem::raiseNotice($msg,
					BcmsSystem::LOGTYPE_CHECK, BcmsSystem::SEVERITY_ERROR,
					'showRegisterUserDialog()',	__FILE__,__LINE__);
			}
		} else {
			// hat der Benutzer bereits die Benutzungsvereinbarungen bestaetigt?
			if (! $_POST['agreement_confirmed'])
			{
				echo '          <h2 id="profile_header">'
					.Factory::getObject('Dictionary')->getTrans('h.user_agreement').'</h2>',"\n";
				echo '          <div id="user_agreement">'."\n";
				$agreement = Factory::getObject('Dictionary')->getTrans('user_agreement');
//				$agreement = $parser->filter($agreement);
				$agreement = $parser->addParagraphs($agreement);
				echo $agreement;
				echo '          </div> <!-- /user_agreement -->'."\n";
				echo '
						<div id="profile_buttons">
							<form class="profile_frm" action="'.
					$parser->getServerParameter('REQUEST_URI')
					.'" method="post" enctype="'
					.$configInstance->default_form_enctype
					.'">
								<input type="submit" name="agreement_confirmed" value="'
					.Factory::getObject('Dictionary')->getTrans('accept').'" />
							</form>
							<form class="profile_frm" action="/login/" method="post" enctype="'
					.$configInstance->default_form_enctype.'">

								<input type="submit" name="agreement_rejected" value="'
					.Factory::getObject('Dictionary')->getTrans('reject').'" />
							</form>
						</div>
				';
			}
			else
			{
				echo '
						<h2 id="profile_header">Benutzerkonto anlegen</h2>
						<form class="profile_frm" action="'.
					$parser->getServerParameter('REQUEST_URI')
					.'" method="post" enctype="'
					.$configInstance->default_form_enctype
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