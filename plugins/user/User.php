<?php
/**
 * Created on 10.09.2005 by ahe
 * Filename: class.User.php
 * Project: borderless_server
 */

class User
{
	// Tabellennamen
 	protected $dbSessionTable;
 	protected $dbUserDataTable;
 	protected $dbBenutzerGruppenZOTable;
 	protected $manager;
 	protected $dalObj;
 	protected $parserObj;
	protected $maxLogintries;
	protected $accountLockTime;
	protected $db = null;
 	protected $userID;
 	protected $userRechte = array();
	protected $configInstance = null;



	// ===== KONSTRUKTOR/ INITIALIZATION =====
	function __construct($manager) {
		$this->configInstance = BcmsConfig::getInstance();
		$this->dbSessionTable = $this->configInstance->getTablename('usersession');
		$this->dbUserDataTable= $this->configInstance->getTablename('user');
		$this->dbBenutzerGruppenZOTable =
			$this->configInstance->getTablename('user_group_assoc');

		$this->manager = $manager;
		$this->dalObj = $this->manager->getModel();
		$this->parserObj = BcmsFactory::getInstanceOf('Parser');
		$this->db = &$GLOBALS['db'];
		$this->maxLogintries = $this->configInstance->login_max_tries;
		$this->accountLockTime = $this->configInstance->login_locktime;
		$this->userID = $this->getUserIDFromDB();
		$this->refreshVars();
	}

	public function getUserID(){ // TODO rename to getUserId()
		return $this->userID;
	}

	/**
	 * Checks whether user is logged in or not
	 *
	 * @return boolean
	 * @author ahe
	 * @date 19.10.2006 23:46:00
	 * @package htdocs/plugins/user
	 */
	public function isLoggedIn() {
		return ($this->getUserID()!=2);
	}

	protected function refreshVars() {
		$details = $this->getUserDetailsFromDB($this->getUserID()); // Userdaten holen
		// Daten an Klassenvariablen senden
		foreach($details as $key => $value)
			$this->$key = $value;
		$this->userRechte = $this->getUserRights($this->getUserID()); // Rechte holen
	}

	protected function getUserIDfromDB() {
		$sessionHandler = Factory::getObject('UserSession');
		$hash = $sessionHandler->getSessionHashValue(session_id());
		$sql = 'SELECT fk_user FROM '.$this->dbSessionTable.
				' WHERE (hash_val = '.$this->parserObj->prepDbStrng($hash).')';
	 	$result = $this->db->query($sql);
		if ($result instanceof PEAR_ERROR)
			return BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_SELECT,
				BcmsSystem::SEVERITY_ERROR, 'getUserIDfromDB()',__FILE__, __LINE__);

	 	$numrows = $result->numRows();
	 	if($numrows<1) return $this->configInstance->defaultUserId;

		$record = $result->fetchRow(DB_FETCHMODE_ASSOC);
		$result->free();
		return $record['fk_user'];
	}

	/**
	* Holt alle Daten aus der Usertabelle (ausser Passwort)
	*
	* @param integer $userID optional, default value 0
	* @access private
	* @return
	*/
	protected function getUserDetailsFromDB($userID) {
		if ($userID==0) $userID = $this->getUserIDfromDB();
		$sql='select
		user.username, user.vorname, user.nachname, user.email, user.telefon,
		user.fk_fav_menu, user.fav_layout, user.last_ip, user.last_login,
		user.akt_login, recht.rightname as zusatzrecht, user.login_tries,
		user.time2login, aenderer.username as aenderer, user.change_date,
		anleger.username as anleger, user.create_date, user.root_flag
		from '.$this->dbUserDataTable.' as user
		left join '.$this->configInstance->getTablename('rechte').' as recht
		on (user.fk_zusatzrecht = recht.right_id)
		left join '.$this->dbUserDataTable.' as anleger
		on (user.FK_ANLEGER = anleger.user_id)
		left join '.$this->dbUserDataTable.' as aenderer
		on (user.FK_AENDERER = aenderer.user_id)
		WHERE (user.user_id='.$userID.')';
	 	$result = $this->db->query($sql);
		if ($result instanceof PEAR_ERROR)
			return BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_SELECT,
				BcmsSystem::SEVERITY_ERROR, 'getUserIDfromDB()',__FILE__, __LINE__);

	 	$numrows = $result->numRows();
	 	if($numrows<1) return $this->configInstance->defaultUserId;

		$record = $result->fetchRow(DB_FETCHMODE_ASSOC);
		$result->free();
		return $record;
	}

	/**
	* returns
	*
	* @access public
	* @return
	*/
	public function getFavMenu() { return $this->fk_fav_menu; }

	public function getFavStyle() { return $this->fav_layout; }

	protected function getUserRights($userID) {
		$sql='select rechte.rightname FROM '
				.$this->dbBenutzerGruppenZOTable.' as ug_zo '
				.' INNER JOIN '.$this->configInstance->getTablename('groups_rechte_zo')
				.' as gr_zo'
				.' ON ug_zo.FK_ROLLE = gr_zo.FK_ROLLE'
				.' INNER JOIN '.$this->configInstance->getTablename('rechte').' as rechte'
				.' ON gr_zo.FK_RECHT = rechte.right_id'
				.' WHERE (ug_zo.FK_USER = '.$userID.')'
				.' ORDER BY rechte.rightname;';
	 	$result = $this->db->query($sql);
		if ($result instanceof PEAR_ERROR)
			return BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_SELECT,
				BcmsSystem::SEVERITY_ERROR, 'getUserRights()',__FILE__, __LINE__);

	 	$numrows = $result->numRows();
	 	if($numrows<1) return '';

		$rightname = '';
		$rightsArray = array();
		for ($i = 0; $i < $numrows; $i++) {
			// fetch current record of resultset into $rightname as array
			$record = $result->fetchInto($rightname, DB_FETCHMODE_ORDERED,$i);
			// if successful, add rightname to rightsarray in lower case
			if($record==1) $rightsArray[$i] = mb_strtolower($rightname[0]);
		}
		$result->free();
		if(!empty($this->zusatzrecht)) $rightsArray[] = $this->zusatzrecht;
		return $rightsArray;
	}

	public function createLoginForm()
	{
		$retStr = PluginManager::getPlgInstance('CategoryManager')->getLogic()->createHeading(
					3, 'Systemlogin', 10, 'menuheader');
		$retStr .= '
		  <form id="loginForm" action="'
		  		.$this->parserObj->getServerParameter('REQUEST_URI')
				.'" method="post" enctype="'
				.$this->configInstance->default_form_enctype.'">
			<div id="login_username">
			  <label for="username">'.Factory::getObject('Dictionary')->getTrans('username')
			  	.': </label>
			  <input type="text" id="username" tabindex="1" name="username" accesskey="l" />
			</div>
			<div id="login_password">
			  <label for="passworte">'.Factory::getObject('Dictionary')->getTrans('password')
			  	.': </label>
			  <input type="password" tabindex="2" id="passworte" name="passworte" />
			</div>
			<div id="login_return_to_prior_page">
			  <label for="return_to_prior_page">'
			  	.Factory::getObject('Dictionary')->getTrans('return_to_prior_page')
			  	.' </label>
			  <input type="checkbox" tabindex="3" id="return_to_prior_page" '
			  	.'name="return_to_prior_page" value="'
			  	.$this->parserObj->getServerParameter('HTTP_REFERER').'"/>
			</div>
			<div id="login_buttons">
			  <input type="submit" tabindex="4" name="action" value="login" />
			  <input type="reset" value="reset" tabindex="5" />
			</div>
		  </form>'."\n";
		$retStr .= '<ul id="additional_login_links">'."\n";
		if($this->configInstance->showForgotPword == 1)
			$retStr .= '      <li id="password"><a href="/login/password.html">'
				.Factory::getObject('Dictionary')->getTrans('forgot_password')
				.'</a></li>'."\n";
		if($this->configInstance->showRegisterUser == 1)
			$retStr .= '      <li id="register"><a href="/login/register.html">'
  				.Factory::getObject('Dictionary')->getTrans('register_now')
  				.'</a></li>'."\n";
		$retStr .= '</ul>'."\n";
		return $retStr;
	}

	/**
	 * Stores the hash value of the given password as the user's new password
	 *
	 * @param String $username the username of the user who forgot his password
	 * @param String $pw the generated new password
	 * @return return_type
	 * @author ahe
	 * @date 05.07.2006 01:52:14
	 * @package htdocs/classes/users
	 */
	protected function storeInitialPassword($username,$pw) {
			$pw = $this->getHashedPassword($pw);
			$sql = 'UPDATE '.$this->dbUserDataTable.' SET passwort=\''.$pw
				.'\' WHERE username=\''.$username.'\'';
		 	$result = $this->db->query($sql);
			if ($result instanceof PEAR_ERROR)	{
				return BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_UPDATE,
				BcmsSystem::SEVERITY_ERROR, 'storeInitialPassword()',
					__FILE__,__LINE__);
			}
		  	return $result;
	}

// ===== PROFIL-FUNKTIONEN =====
	public function forgotPassword($username,$admin=false) {

		if ($this->configInstance->showForgotPword!=1 && !$admin)
		    return BcmsSystem::raiseDictionaryNotice('function deactivated',
		 		BcmsSystem::LOGTYPE_CHECK, BcmsSystem::SEVERITY_FAILURE,
				'forgotPassword()',__FILE__, __LINE__);

		$adm_email = $this->configInstance->webmasterEmail;
		$email = $this->getUserEMailFromDB($username);
		$pword = $this->generatePWord($username);
		if(!$this->storeInitialPassword($username,$pword))
			return BcmsSystem::raiseNotice('Generiertes Passwort konnte nicht ' .
					'gespeichert werden. Bitte benachrichtigen Sie den ' .
					'Systemadministrator', BcmsSystem::LOGTYPE_UPDATE,
					BcmsSystem::SEVERITY_FAILURE, 'forgotPasswort()',
					__FILE__,__LINE__); // TODO Use dictionary here!

		// text der EMail sprachabhaengig aus DB laden
		$reminder_text = '   P A S S W O R T E R I N N E R U N G

   '.$this->configInstance->page_title // URGENT Use dictionary!!!
   .'

   Diese E-Mail wurde Ihnen zugesandt, da Sie oder jemand anderes auf 
   unserer Internetseite für den Benutzername "'.$username.'" ein 
   neues Passwort angefordert haben. Aus Gründen der Datensicherheit
   versenden wir diese Daten allerdings nur an die im System
   hinterlegte E-Mail-Adresse f�r das entsprechende Benutzerkonto.

   
   Ihre neuen Benutzerdaten lauten:
   ------------------------------------------------------------
      USERNAME: '.$username.'
      PASSWORT: '.$pword.' (NEU)
   ------------------------------------------------------------

   Um Ihre Benutzerdaten zu ändern, können Sie Sich auf unserer 
   Internetseite "'.$this->configInstance->completeSiteUrl.'" 
   anmelden und dort den Profileditor verwenden.

   Mit freundlichen Grüßen,
   Ihr Administrations-Team
   
   ------------------------------------------------------------
   Diese Nachricht wurde automatisch generiert!';

		$subject = 'Passworterinnerung von "'.$this->configInstance->completeSiteUrl.'"';
		if( BcmsSystem::sendmail_to_address($email, $adm_email,$subject, $reminder_text) )
		{
			// Erfolgsmeldungen den Systemnachrichten hinzufuegen
			return $this->sendAdminUserMailSendMessage($email);
		}

		return exception_handler(new SendMailFailedException($username,$subject));
	}

	/**
	 * Checks whether user can be created and sets default values
	 *
	 * @param array cols - associative array holding data; array('username' => 'jsmith')
	 * @return boolean
	 * @author ahe
	 * @date 08.01.2007 23:53:14
	 * @package htdocs/plugins/user
	 */
	public function registerUser(&$cols, $submitButtonName = 'go_user_action')
	{
		if(!isset($_POST[$submitButtonName])) return null;

		// Benutzername bereits vorhanden?
		if($this->getUserIDbyName($cols['username'])>0){
			// dann ist hier Ende!
			$msg = ('ERROR: Der Benutzername "'.$cols['username']
				  .'" ist bereits im System vorhanden.');
			return BcmsSystem::raiseNotice($msg,
				BcmsSystem::LOGTYPE_CHECK, BcmsSystem::SEVERITY_ERROR,
				'registerUser()',	__FILE__,__LINE__);
		}

		// Ein Passwort für den User generieren, falls keins eingegeben wurde.
		if($cols['pword']==null)
			$password = $this->generatePWord($cols['username']);
		$cols['pword'] = $this->getHashedPassword($password); // hash pw
		$cols['pword_again'] = $cols['pword'];

		if(!$this->dalObj->checkForAction($submitButtonName,$cols)){
			return BcmsSystem::raiseDictionaryNotice('dataInsertFailed', BcmsSystem::LOGTYPE_CHECK,
					BcmsSystem::SEVERITY_FAILURE, 'registerUser()',
					__FILE__, __LINE__);
		}

		// send mail only if insert succeeded
		if( $this->send_welcomemail($cols['username'],$password,
			$cols['vorname'],$cols['nachname'],$cols['email'],$cols['telefon']) )
		{
		  	// Erfolgsmeldungen den Systemnachrichten hinzufuegen
			$this->sendAdminUserMailSendMessage($cols['email']);

		  	$msg = ('Ihr neues Benutzerkonto wurde '.
			'erfolgreich angelegt!<br />Das Anmeldekennwort finden Sie in der '.
			' Ihnen zugesandten E-Mail.'); // TODO use dictionary
			return BcmsSystem::raiseNotice($msg,
				BcmsSystem::LOGTYPE_CHECK, BcmsSystem::SEVERITY_INFO,
				'registerUser()', __FILE__,__LINE__);
		}
		else
		{
			$msg = ('ACHTUNG: E-Mail-Versand fehlgeschlagen!<br />
				Bitte kontaktieren Sie den <a href="mailto:'
				.$this->configInstance->adm_email
				.'?subject=Mailsend_Error_'.$cols['username']
				.'">Systemadminstrator</a>.');
			return BcmsSystem::raiseNotice($msg,
				BcmsSystem::LOGTYPE_CHECK, BcmsSystem::SEVERITY_CRITICAL,
				'registerUser()',	__FILE__,__LINE__);
		}
	}

	protected function send_welcomemail($username,$pword,$vorname,$nachname,$email,$telefon)
	{
		$welcomemail_text = '   Herzlich willkommen als Benutzer auf '
		.$this->configInstance->completeSiteUrl.'


   Sie wurden mit folgenden Daten in unser System
   eingetragen:
   ------------------------------------------------------------
      USERNAME: '.$username.'
      PASSWORT: '.$pword.'
      VORNAME:  '.$vorname.'
      NACHNAME: '.$nachname.'
      EMAIL:    '.$email.'
      TELEFON:  '.$telefon.'


   Verwenden Sie zum ändern dieser Daten bitte das Formular
   auf unserer Internetseite '.$this->configInstance->completeSiteUrl.'.

   Sollten Ihre Daten gegen Ihren Wunsch und ohne Ihr Wissen
   auf unserer Seite eingetragen worden sein, schicken Sie
   bitte eine Antwort auf diese E-Mail mit dem Betreff
   "remove user '.$username.'".

   Wir danken Ihnen für Ihr Verständnis.

   Das Team von '.$this->configInstance->siteUrl.'.

   ------------------------------------------------------------
   Diese Nachricht wurde automatisch generiert!'; // TODO use dictionary!!

		// Infomail an Benutzer
		BcmsSystem::sendmail_to_address($email, $this->configInstance->webmasterEmail
			, $this->configInstance->welcomemail_subject, $welcomemail_text);

		// Infomail an Admin zusaetzlich mit IP-Adresse und Hostname
		$adm_info = 'IP: '.$this->parserObj->getServerParameter('REMOTE_ADDR').'  Host: '
			.@gethostbyaddr($this->parserObj->getServerParameter('REMOTE_ADDR'))."\n\n";
		$subject = $this->configInstance->completeSiteUrl.' - New User: '.$email;
		return BcmsSystem::sendmail_to_address($this->configInstance->webmasterEmail,
			$email, $subject, $adm_info.$welcomemail_text);
	}

// ===== GET-FUNKTIONEN =====

	protected function getUserEMailFromDB($username)
	{
		$sql="SELECT email FROM ".$this->dbUserDataTable." WHERE (username='".$username."')";
	 	$result = $this->db->query($sql);
		if ($result instanceof PEAR_ERROR)
			return BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_SELECT,
				BcmsSystem::SEVERITY_ERROR, 'getUserEMailFromDB()',__FILE__, __LINE__);

	 	$numrows = $result->numRows();
	 	if($numrows<1) return '';

		$record = $result->fetchRow(DB_FETCHMODE_ASSOC);
		$result->free();
		return $record['email'];
	}

	/**
	 * Wird z.B. bei Neuanmeldung/ Anlegung von Benutzern benoetigt um zu
	 * kontrollieren, ob ein Benutzer bereits in der Datenbank ist.
	 *
	 * @param $username string
	 * @access private
	 * @return $userID int
	 */
	public function getUserIdByName($username)
	{
		$sql='SELECT user_id FROM '.$this->dbUserDataTable.' WHERE (username=\''
			.$username.'\')';
	 	$result = $this->db->query($sql);
		if ($result instanceof PEAR_ERROR)
			return BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_SELECT,
				BcmsSystem::SEVERITY_ERROR, 'getUserIdByName()',__FILE__, __LINE__);

	 	$numrows = $result->numRows();
	 	if($numrows<1) return 0;

		$record = $result->fetchRow(DB_FETCHMODE_ASSOC);
		$result->free();
		return $record['user_id'];
	}

	/**
	 * generate a password with a length of 15 chars depending on systen timestamp
	 * and username
	 *
	 * @access protected
	 * @return string
	 * @deprecated
	 */
	protected function generatePWord($username)
	{
		return mb_substr(BcmsSystem::getHash(date('r')."\n".$username),0,15);
	}

	function getUserName(){   return($this->username); 	}

	function getUsersRealname(){   return($this->vorname.' '.$this->nachname); 	}

	function getUserNameFromDB($userID=0) {
		if ($userID == 0) $userID = $this->getUserID();
		if ($userID==0) return null;
		$sql='SELECT username FROM '.$this->dbUserDataTable.' WHERE (user_id=\''.$userID.'\')';
	 	$result = $this->db->query($sql);
		if ($result instanceof PEAR_ERROR)
			return BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_SELECT,
				BcmsSystem::SEVERITY_ERROR, 'getUserNameFromDB()',__FILE__, __LINE__);

	 	$numrows = $result->numRows();
	 	if($numrows<1) return null;

		$record = $result->fetchRow(DB_FETCHMODE_ASSOC);
		$result->free();
		return $record['username'];
	}

	/**
	 * Berechne das mit Salt-Wert versehene, gehashte Passwort.
	 *
	 * @author ahe
	 * @param String $password the password to be hashed
	 * @return String the hash of the given password
	 */
	public function getHashedPassword($password){
		return BcmsSystem::getHash($password);
/* URGENT kommentiere den Salt-Wert ein, wann immer du moechtest!!! ABER BALD!
		return BcmsSystem::getHash('ZH,.Sr?:;L�KUZ/&%3z6�%$&�U�!fbCI"9&r'."\n"
			.$password."\n"
			.'<Jhq3z6�%$&�U�!EZH,.Sr?:;L�KUZ/&%$tg´n%Kh4H54FrfbCI"9&rr4' );
*/
	}

	/**
	 * Nimm einen Benutzernamen und ein Klartextpasswort, berechne das mit Salt-
	 * Wert versehene, gehashte Passwort und vergleiche es mit demjenigen in der
	 * Datenbank. Gebe UserId zurueck, wenn der Benutzer erfolgreich verifiziert
	 * wurde.
	 * @author ahe
	 */
	protected function verifyPassword($username, $password){
		$hashedPassword = $this->getHashedPassword($password);
		return $this->checkUserPWCombination($username, $hashedPassword);
	}

	/**
	 * Macht eine Abfrage mit Benutzername und hashedPW auf die Datenbank und
	 * prueft, ob eine gueltige ID zurueckgegeben wird. Gebe UserId oder 0
	 * zurueck
	 *
	 * @param enclosing_method_arguments
	 * @return integer userId or 0
	 * @author ahe
	 * @date 05.07.2006 01:38:08
	 * @package htdocs/classes/users
	 */
	protected function checkUserPWCombination($username, $hashedPassword) {
		$sql='SELECT user_id FROM '.$this->dbUserDataTable
			.' WHERE (USERNAME='.$this->parserObj->prepDbStrng($username)
			.' AND PASSWORT='.$this->parserObj->prepDbStrng($hashedPassword).')';
	 	$result = $this->db->query($sql);
		if (!($result instanceof PEAR_ERROR) && $result->numRows()>0)
		{
			$record = $result->fetchRow(DB_FETCHMODE_ASSOC);
			return $record['user_id'];
		}
	  	return 0;
	}

// ===== ANMELDE- UND ABMELDE-FUNKTIONEN =====

	/**
	 * Tries to get user_id, number of failed logins and the "time2login" with
	 * the specified username.
	 *
	 * @param String $username the username specified in the gui form field
	 * @return mixed  boolean if error occured, else array
	 * @author ahe
	 * @date 27.10.2006 23:49:48
	 * @package htdocs/plugins/user
	 * @since 0.13
	 * @see #login()
	 */
	private function getLoginUserDataByUsername($username){
		$sql='SELECT user_id, LOGIN_TRIES, TIME2LOGIN FROM '.$this->dbUserDataTable.'
			  WHERE (USERNAME='.$this->parserObj->prepDbStrng($username).')';
	 	$result = $this->db->query($sql);
		if ($result instanceof PEAR_ERROR)
			return BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_SELECT,
				BcmsSystem::SEVERITY_ERROR, 'getLoginUserDataByUsername()',__FILE__, __LINE__);

	 	$numrows = $result->numRows();
	 	if($numrows<1){
			$msg = ('Benutzername nicht in der Datenbank vorhanden.'); // TODO Use dictionary!
			return BcmsSystem::raiseNotice($msg, BcmsSystem::LOGTYPE_CHECK,
						BcmsSystem::SEVERITY_ERROR,
						'getLoginUserDataByUsername()',__FILE__, __LINE__);
		}

		$loginUserData = $result->fetchRow(DB_FETCHMODE_ORDERED);
		$result->free();
		return $loginUserData;
	}

	/**
	 * Checks whether account is currently locked and whether maximum
	 * number of failed login tries is reached. If this is the case this method
	 * also locks account.
	 *
	 * @param array $loginUserData
	 * @see #getLoginUserDataByUsername()
	 * @return boolean
	 * @author ahe
	 * @date 27.10.2006 23:56:55
	 * @package htdocs/plugins/user
	 */
	private function isUserAccountLocked($loginUserData){
		// Pruefung, ob Account z.Zt. gesperrt ist
		if(date('Y-m-d H:i:s') < $loginUserData[2])
		{
			$msg = 'ERROR: Der Account ist z.Zt. auf Grund
			  mehrfacher, fehlerhafter Loginversuche gesperrt.'; // TODO use dicitionary!
			return !BcmsSystem::raiseNotice($msg, BcmsSystem::LOGTYPE_CHECK,
						BcmsSystem::SEVERITY_ERROR,
						'login().isUserAccountLocked()',__FILE__, __LINE__);
		}

		$login_tries = is_numeric($loginUserData[1]) ? $loginUserData[1] : 0;
  		// Pruefung, der wievielte Loginversuch gerade gemacht wurde.
		if($this->maxLogintries-1 <= $login_tries)
		{
		  // Wenn Anz. Loginversuche ueberschritten, Account sperren
		    $sql='UPDATE '.$this->dbUserDataTable.' SET LOGIN_TRIES=0, ' .
		  		'TIME2LOGIN='.date('YmdHis',time()+($this->accountLockTime))
				.' WHERE (user_id=\''.$loginUserData[0].'\')';
		    $result = $this->db->query($sql);
			if($result instanceof PEAR_ERROR) {
				return !BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_UPDATE,
						BcmsSystem::SEVERITY_CRITICAL, 'login().isUserAccountLocked().lock_account',
					__FILE__,__LINE__);
			}
		    $msg = 'Loginversuch-Nr '.($login_tries+1).':
				Username oder Passwort sind nicht korrekt.<br/>
				Der Account wurde gesperrt und Ihre IP-Addresse gespeichert.'; // TODO use dictionary
			return !BcmsSystem::raiseNotice($msg, BcmsSystem::LOGTYPE_CHECK,
						BcmsSystem::SEVERITY_FAILURE,
						'login()',__FILE__, __LINE__);
		}
		return false;
	}

  /**
   * Manages the login process.
   * Also checks for bad login tries since last login and validates pw against db setting
   */
	public function login($username,$password,$adm=0)
	{
		// Abfrage der zu ueberpruefenden Daten
		$loginUserData = $this->getLoginUserDataByUsername($username);
		if(is_bool($loginUserData)) return $loginUserData;

		$locked = $this->isUserAccountLocked($loginUserData);
		if($locked) return !$locked;

		$userID = $this->verifyPassword($username,$password);

		/* Ist ein Datensatz mit Username und PW vorhanden, stimmt dessen ID mit der
		 * oberen ueberein und hat der User die Berechtigung, sich hier
		 * anzumelden?
		 */
		if ( $userID==0 || ($userID != $loginUserData[0])) {
			$login_tries = is_numeric($loginUserData[1]) ? $loginUserData[1] : 0;
			// increment login tries
			$sql='UPDATE '.$this->dbUserDataTable.' SET LOGIN_TRIES='.($login_tries+1)
							.' WHERE (user_id=\''.$loginUserData[0].'\')';
		    $result = $this->db->query($sql);
			if($result instanceof PEAR_ERROR) {
				return !BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_UPDATE,
						BcmsSystem::SEVERITY_CRITICAL, 'login().update_failed_logins',
					__FILE__,__LINE__);
			}
			$msg = 'Loginversuch-Nr '.($login_tries+1)
			  .': Username oder Passwort sind nicht korrekt oder Sie haben ' .
			  	'keine Berechtigung sich hier anzumelden.<br/>' ."\n".
			  	'Bitte versuchen Sie es erneut um Tippfehler ' .
			  	'auszuschlie&szlig;en.<br/>'."\n"; // TODO use dictionary
			return BcmsSystem::raiseNotice($msg, BcmsSystem::LOGTYPE_CHECK,
						BcmsSystem::SEVERITY_FAILURE,
						'login().verifyPassword',__FILE__, __LINE__);
		}

		// "eigentlichen" Loginvorgang durchfuehren
		return ($this->proceed_login($userID));
	}

  /* Bei einem Fehler wird als Returnvalue 1 zurueckgegeben, sonst 0 */
	function proceed_login($userID) {
	  	$sessionObj = Factory::getObject('UserSession');
		$sessionObj->delete_old_session(session_id());

		// regenerate the session -> for security purpose -> prevents session-hijackig
		session_regenerate_id();

		// Userdaten aktualisieren
		$sql='UPDATE '.$this->dbUserDataTable.' SET LOGIN_TRIES=0, TIME2LOGIN='
			 .$this->parserObj->prepDbStrng(date('YmdHis',time()-$this->accountLockTime))
			 .', LAST_LOGIN=AKT_LOGIN, '
			 .'LAST_IP='
			 .$this->parserObj->prepDbStrng($this->parserObj->getServerParameter('REMOTE_ADDR'))
			 .', AKT_LOGIN='.$this->parserObj->prepDbStrng(date('YmdHis'))
			 .' WHERE (user_id='.$userID.')';
	    $result = $this->db->query($sql);
		if($result instanceof PEAR_ERROR) {
			return !BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_UPDATE,
					BcmsSystem::SEVERITY_ERROR, 'proceed_login().updateUserData',
				__FILE__,__LINE__);
		}

	    BcmsSystem::raiseNotice('LOGIN: '.$this->getUserNameFromDB($userID), // TODO Use dictionary!
	 		BcmsSystem::LOGTYPE_CHECK, BcmsSystem::SEVERITY_DEBUG,
			'proceed_login()',__FILE__, __LINE__);
		$this->userID = $userID;
		$this->refreshVars();
		return $userID;
	}

	/**
	 * Removes old user sessions and resets user defined settings.
	 *
	 * @author ahe
	 * @date 28.10.2006 00:24:32
	 * @package htdocs/plugins/user
	 * @since 0.3
	 */
	function logout() {
	  	$sessionObj = Factory::getObject('UserSession');
		$sessionObj->delete_old_session(0);
		session_unregister('phase');
		session_unregister('cssfile');
		session_unregister('css_fs');
		session_unregister('content_offset');
		$this->userID = $this->configInstance->defaultUserId;
		BcmsSystem::raiseNotice('LOGOUT: '.$this->getUsername(),
			BcmsSystem::LOGTYPE_SECURITY, BcmsSystem::SEVERITY_INFO,
			'logout()',	__FILE__,__LINE__);
		header('Location: /', true);
	}

	/**
	 * checks whether user possess the given right.
	 *
	 * @param mixed (int or String) $rightname the right's techname
	 * @return boolean
	 * @author ahe
	 * @date 01.07.2006 01:04:10
	 * @package htdocs/classes/users
	 */
	function hasRight($rightname,$userID=0) {
		// if root_flag is set, this user has root privileges -> no rights are checked
		if($this->root_flag) return true;

		if($userID==0) $userID=$this->getUserID();

		// transform right_id into rightname
		if(is_numeric($rightname)) { // TODO this is only temporarily until rights are completed refactored!
			$right = PluginManager::getPlgInstance('RightManager')->getModel()->getObject($rightname);
		 	if(count($right)<1) return $this->sendMessageSpecifiedRightNotFound($rightname);
			$rightname = $right['rightname'];
		} else {
			// if not an id then check whether right actually exists in database
			$right = PluginManager::getPlgInstance('RightManager')->getModel()->getRightByName($rightname);
		 	if(count($right)<1) return $this->sendMessageSpecifiedRightNotFound($rightname);
		}

		// if users rights are not set yet...get them
		if(count($this->userRechte)<1) $this->userRechte = $this->getUserRights($userID);

		return in_array(mb_strtolower($rightname),$this->userRechte);
	}

	/**
	 * Sends a message that specified right could not be found
	 *
	 * @param mixed String/ integer the right that could not be found
	 * @return boolean
	 * @author ahe
	 * @date 28.10.2006 00:12:41
	 * @package htdocs/plugins/user
	 * @since 0.14
	 */
	private function sendMessageSpecifiedRightNotFound($rightname){
		$msg = 'Angegebenes Recht "'.$rightname.'" nicht in Datenbank vorhanden'; // TODO use dicitonary
		return BcmsSystem::raiseError($msg, BcmsSystem::LOGTYPE_CHECK,
					BcmsSystem::SEVERITY_ERROR,
					'hasRight()',__FILE__, __LINE__);
	}

	public function showForgotPwordDlg() {
		if(!$this->configInstance->showForgotPword== 1) return false;
			return '<h3 id="profile_header">Passwort vergessen?</h3>
				  <form id="password_frm" action="'.$this->parserObj->getServerParameter('SCRIPT_URL')
					  .'" method="post" enctype="application/x-www-form-urlencoded">
					<p id="pword_info">
					Tragen Sie hier Ihren Benutzernamen ein. Es wird umgehend eine E-Mail
					an die im System hinterlegte E-Mail-Adresse gesendet!
					</p>
					<div id="login_daten">
					  <h3>Username eingeben</h3>
					  <label lang="en">Username: <input type="text" maxlength="20" name="username" /></label>
					</div> <!-- /login_daten -->
					<div id="profile_buttons">
					  <input type="submit" name="password_forgotten" value="Passwort zuschicken" />
					</div>

				  </form>'."\n";
	}

	/**
	 * Raises an info message that indicates that an email has been send to the
	 * specified email address.
	 *
	 * @param String $email the email address the mail has been send to
	 * @return boolean
	 * @author ahe
	 * @date 19.10.2006 23:15:25
	 * @package htdocs/plugins/user
	 */
	protected function sendAdminUserMailSendMessage($email,$sendingMethod='forgotPasswort()'){
		return BcmsSystem::raiseNotice('INFO: Benachrichtigungs-E-Mail wurde ' .
				'an folgende E-Mail-Adresse gesandt: '.$email, // TODO Use dictionary!
				BcmsSystem::LOGTYPE_UPDATE,
				BcmsSystem::SEVERITY_INFO, $sendingMethod,
				__FILE__,__LINE__);
	}

}
?>