<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * The BcmsSystem class is a stateful helper class which hosts the most
 * necessary system methods.
 * Use like <code>BcmsSystem::getInstance()</code> or call static methods directly
 *
 * @since 0.8
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @date 29.07.2005 23:23:14
 * @class BcmsSystem
 * @ingroup sys
 * @package sys
 */
class BcmsSystem {

	const TRANSACTION_LIFETIME = 120;
	// LOGTYPE constants
	const LOGTYPE_EXCEPTION = 0;
	const LOGTYPE_SELECT  = 1;
	const LOGTYPE_CHECK   = 2;
	const LOGTYPE_INSERT  = 3;
	const LOGTYPE_UPDATE  = 4;
	const LOGTYPE_DELETE  = 5;
	const LOGTYPE_SECURITY= 6;
	// SEVERITY constants
	private static $severityArray = array('debug','info','warning','failure','error','critical');
	const SEVERITY_DEBUG    = 0;
	const SEVERITY_INFO     = 1;
	const SEVERITY_WARNING  = 2;
	const SEVERITY_FAILURE  = 3;
	const SEVERITY_ERROR    = 4;
	const SEVERITY_CRITICAL = 5;

	private static $severityToBeLogged = array();
	private static $severityToBeDisplayed = array();
	private static $transactionTable;
	private static $uniqueInstance = null;

	/**
	 * Get the instance of this class. Holds its instance by itself - not via
	 * BcmsFactory!
	 *
	 * @return BcmsSystem - the single instance of this class
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 * @date 05.03.2007 15:44:41
	 * @since 0.14
	 */
	public static function getInstance(){
		if(self::$uniqueInstance==null)
		self::$uniqueInstance = new self();
		return self::$uniqueInstance;
	}

	/**
	 * Inits class default values. ATTENTION: Needs whole config to be already
	 * loaded!!!
	 *
	 * @return void
	 * @author ahe
	 * @date 05.03.2007 15:47:40
	 * @access public
	 */
	public static function init(){
		self::getInstance()->internal_init();
	}

	/**
	 * Inits class default values. ATTENTION: Needs whole config to be already
	 * loaded!!!
	 *
	 * @author ahe
	 * @date 06.10.2006 00:20:40
	 * @access private
	 */
	private function internal_init(){
		// start session handling
		BcmsFactory::getInstanceOf('UserSession');
		session_name('BORDERLESS_CMS');
		session_start();
		if(!isset($GLOBALS['db'])) {
			// this is only fallback as connection has already been established inside of UserSession::_open()
			self::createGlobalDbObject();
		}
		$bcmsConfigObj = BcmsConfig::getInstance();
		$bcmsConfigObj->setTablename('syslog', 'syslog');
		// config-vars aus der Datenbank laden
		$bcmsConfigObj->loadConfigVars();
		$charset = $bcmsConfigObj->metaCharset;
		$charset = empty($charset) ? 'UTF-8' : $charset;
		mb_internal_encoding($charset); // sets encoding for all multibyte php functions (mb_*)

		self::$severityToBeLogged = explode(',',$bcmsConfigObj->notice_log_level);
		self::$severityToBeDisplayed = explode(',',$bcmsConfigObj->notice_display_level);
		self::$transactionTable = $bcmsConfigObj->getTablename('last_transactions');

		// $_GETs abfragen und in SESSION laden
		$this->setSessionMid();
		$this->setSessionPluginVars();

		$this->overwriteDbTableMessages();
		$parser = BcmsSystem::getParser();
		$parser->fetchAdditionalGetParameters();

		// @todo Nachfolgendes sollte von der StyleManager-Klasse behandelt werden
		// folgendes sollte in das Modul "stylemanager" verschoben werden!
		$config = BcmsConfig::getInstance();
		// CSS-File
		$_SESSION['cssfile'] = (!isset($_SESSION['cssfile'])) ? $config->default_css : $_SESSION['cssfile'];
		// wurde ein anderes css gewuenscht?
		if($parser->getGetParameter('css')!=null) {
		    $_SESSION['cssfile'] = (substr($parser->getGetParameter('css'),0,4)!='http') ? str_replace('..','',$parser->getGetParameter('css')) : $config->default_css;
		}
	}

	/**
	 * Sets the $_SESSION['mid'] and $_SESSION['cur_mname'] variables
	 *
	 * @return void
	 * @access private
	 * @author ahe
	 * @date 05.04.2007 23:10:03
	 * @since 0.13.109
	 */
	private function setSessionMid() {
		$cur_mname = BcmsSystem::getParser()->getGetParameter('cur_mname');
		if(!empty($cur_mname) && is_string($cur_mname) )
		{
			// $cur_mname is securely validated inside of CategoryManager!
			$categoryId = CategoryManager::getIdByTechname($cur_mname);
			if(!is_array($categoryId) && is_numeric($categoryId) && $categoryId>0)
		    {	// if there is a result... fetch it...
			    $_SESSION['m_id'] = $categoryId; // IMPORTANT: Set this first!!! Otherwise CategoryManager won't start!
				$_SESSION['cur_catname'] = $cur_mname;
			    BcmsSystem::getCategoryManager()->getLogic()->loadVars($categoryId);
		    }
		    elseif($cur_mname!='error') // @todo !='error' is a bugfix. fix reason ASAP
		    {
		    	$msg = 'FEHLER: Angegebene Rubrik "'.$cur_mname.'" existiert nicht! ' .
		    			'Bitte wählen Sie die Rubrik aus dem Menü.'; // @todo Use dictionary here!
		    	BcmsSystem::raiseNotice($msg, BcmsSystem::LOGTYPE_CHECK,
		    			BcmsSystem::SEVERITY_WARNING, 'get_category_id-section',__FILE__,__LINE__);

		    	// redirect to default error 404 page
		    	// @todo use BCMS internal error page for 404
		    	header("HTTP/1.1 404 Not Found");
		    	die();
			}
		}
		else
		{ 		// if there is an error and the parameter cur_mname is not a string...
			    $_SESSION['m_id'] = BcmsConfig::getInstance()->default_cat_id;
		}
	}

	/**
	 * Set
	 *
	 * @return void
	 * @access private
	 * @author ahe
	 * @date 05.04.2007 23:37:52
	 * @since 0.13.109
	 */
	private function setSessionPluginVars() {
		$parser = BcmsSystem::getParser();
		$config = BcmsConfig::getInstance();

		/*   FUER NEUE MOD_REWRITE RULES (20060105 ahe) */
		$plgname = (empty($_SESSION['mod']['name'])) ? $config->default_modname : $_SESSION['mod']['name'];
		$plgfunc = (empty($_SESSION['mod']['func'])) ? $config->default_modfunc : $_SESSION['mod']['func'];
		$plgoid = ($parser->getGetParameter('oid')!=null) ? intval($parser->getGetParameter('oid')) : $config->default_modoid;

		// check whether 'func' parameter is specified in uri
		if($parser->getGetParameter('func')!=null) {
			$plgfunc = $parser->filterTechName($parser->getGetParameter('func'));
		} else {
			$plgfunc = $plgfunc;
		}
		// check whether 'oname' parameter is specified in uri
		if($parser->getGetParameter('oname')!=null
			&& preg_match('/[\w]/',$parser->getGetParameter('oname')))
		{
			$plgoname = $parser->filterTechName($parser->getGetParameter('oname'));
		} else {
			$plgoname = null;
		}

		// set vars to $_SESSION
		$_SESSION['mod'] = array('name' => $plgname, 'func' => $plgfunc);
		if($plgoname != null) {
			$_SESSION['mod']['oname'] = $plgoname;
		} else {
			$_SESSION['mod']['oid'] = $plgoid;
		}
	}

	/**
	 * This method creates THE one and only connection to the database.
	 *
	 * @return void
	 * @access public
	 * @author ahe
	 * @since 05.03.2007 15:50:06
	 */
	public static function createGlobalDbObject() {
		$bcmsConfigObj = BcmsConfig::getInstance();
		include_once('inc/pear/DB.php');
		$dsn = array(
			'phptype'  => $bcmsConfigObj->dbType,
			'username' => $bcmsConfigObj->dbUser,
			'password' => $bcmsConfigObj->dbPass,
			'hostspec' => $bcmsConfigObj->dbServer,
			'database' => $bcmsConfigObj->dbDatabase
			);
		$options = array(
			'debug'       => 2,
			'portability' => DB_PORTABILITY_ALL,
		);
		$GLOBALS['db'] =& DB::connect($dsn,$options);
		if($GLOBALS['db'] instanceof PEAR_ERROR){
			throw new Exception('DB-Connect failed. Message: '.$GLOBALS['db']->getMessage());// quit on db-init error!
		}
	}

	/**
	 * Parse $GLOBALS['system_msg'] to separate divs
	 *
	 * @return String - String representation of messages
	 * @author ahe
	 * @since 07.03.2007 19:34:52
	 */
	private static function parseSystemMsg() {
		$msgString = '';
		for ($i = 0; $i < sizeof($_SESSION['system_msg']); $i++) {
			$msg = $_SESSION['system_msg'][$i]['message'];
			$severity = $_SESSION['system_msg'][$i]['severity'];
			$oddOrEven = ($i%2>0) ? 'odd' : 'even';
			$msgString .= BcmsFactory::getInstanceOf('GuiUtility')->fillTemplate('div_tpl'
					,array('id="sysmessage'.$i.'" ' .
			'class="message '.self::$severityArray[$severity].' '.$oddOrEven.'"'
					,$msg));
		}
		unset($_SESSION['system_msg']);
		return $msgString;
	}

	/**
	 * Creates a &lt;div&gt; containing all messages from $GLOBALS['system_msg']
	 *
	 * @return String - String representation of messages
	 * @author ahe
	 * @since 07.03.2007 19:34:52
	 */
	public static function getSystemMessages(){
		$parser = BcmsSystem::getParser();
		$msgString = '<div id="systemmsg" style="z-index:99999999999;">';
		$msgString .= BcmsFactory::getInstanceOf('GuiUtility')->createHeading(2,
		BcmsSystem::getDictionaryManager()->getTrans('h.systemMsg'),12);
		$msgString .= self::parseSystemMsg();

		$uri = BcmsConfig::getInstance()->completeSiteUrl.'/'.$_SESSION['cur_catname'].'/';
		return $msgString.'
	            <div id="systemmsg_weiter"><span><a class="button" href="'
	            .BcmsSystem::getParser()->getServerParameter('HTTP_REFERER')
				.'">&laquo; '.BcmsSystem::getDictionaryManager()->getTrans('back').'</a></span>'
	            .'<span><a class="button" href="'.$uri.'">'
				.BcmsSystem::getDictionaryManager()->getTrans('to_category').'</a></span></div>
	          </div>  <!-- /errorsection -->'."\n";
	}

// BEGIN ~~~ MESSAGING SECTION

	/**
	 * Handles an error, adds backtrace, writes it to the database and sends a
	 * related message to the system's message window.
	 *
     * @param mixed $error  PEAR_ERROR object or String message to write to file
     * @param short  $logtype constant of this class (BcmsSystem::LOGTYPE_*)
     * @param short  $severity constant of this class (BcmsSystem::SEVERITY_*)
     * @param String $methodname name of the method sending the message
     * @param String $file file name sending the message
     * @param int    $line line number sending the message
     * @param String $displayMsg the message that shall be displayed to the user
     * @param boolean $writeMsgToDb shall message be written to database?
	 * @return boolean ($severity<self::SEVERITY_FAILURE)
     * @author ahe <aheusingfeld@borderlesscms.de>
	 * @date 05.10.2006 21:48:37
	 */
	public static function raiseError($error, $logtype, $severity=null, $methodname=null, $file=null, $line=null, $displayMsg=null, $writeMsgToDb=true){

		if(PEAR::isError($error)){
			$displayMsg = $displayMsg.' '.$error->getMessage();
			$debugInfo['displayMsg'] = $displayMsg;
			$debugInfo['debugInfo'] = $error->getDebugInfo();
			$debugInfo['backtrace'] = array();
			foreach ($error->backtrace as $number => $traceArray) {
				$debugInfo['backtrace'][$number]['file'] = $traceArray['file'];
				$debugInfo['backtrace'][$number]['line'] = $traceArray['line'];
			}
			$notice = print_r($debugInfo,true);
		} else {
			if($displayMsg==null) {
				$displayMsg = $error;
			}
			$ex = new Exception();
			$notice = $error."\n\n".$ex->getTraceAsString();
			unset($ex);
		}
		if($severity==null) $severity=self::SEVERITY_ERROR;
		return self::raiseNotice($notice, $logtype, $severity, $methodname,
			$file, $line, $displayMsg, $writeMsgToDb);
	}

	/**
	 * Writes Message to logfile and to messages window.
	 *
     * @param String $msg  textmessage to write to file
     * @param short  $logtype constant of this class (BcmsSystem::LOGTYPE_*)
     * @param short  $severity constant of this class (BcmsSystem::SEVERITY_*)
     * @param String $methodname name of the method sending the message
     * @param String $file file name sending the message
     * @param int    $line line number sending the message
     * @param String $displayMsg  text to be displayed to the user
     * @param boolean $writeMsgToDb shall the message be written to logfile
	 * @return boolean ($severity<self::SEVERITY_FAILURE)
     * @author ahe <aheusingfeld@borderlesscms.de>
     * @date 24.06.2006 21:37:49
	 */
	public static function raiseNotice($notice, $logtype, $severity, $methodname=null, $file=null, $line=null, $displayMsg=null, $writeMsgToDb=true){

		// validateSeverity
		if($severity>sizeof(self::$severityArray) || $severity<0)
		throw new Exception('Invalid severity specified!');

		if($writeMsgToDb && in_array($severity,self::$severityToBeLogged))
		self::writeMsgToLogfile($notice,$logtype,$severity,$methodname,$file,$line);

		// shall message with this severity be displayed?
		if(!in_array($severity,self::$severityToBeDisplayed)) return true;

		// initialize
		if(empty($_SESSION['system_msg']) || !is_array($_SESSION['system_msg']))
		$_SESSION['system_msg'] = array();

		// @todo There should be an index for error translation!
		$displayMsg = ($displayMsg==null) ? $notice : $displayMsg;
		$_SESSION['system_msg'][] = array(
		'message' => $displayMsg,
		'severity' => $severity
		);

		// if severity is lower than FAILURE, return true!
		return ($severity<self::SEVERITY_WARNING);
	}

	/**
	 * Writes Message to logfile and to messages window.
	 *
	 * @param String $dict_defTrans defTrans for the Message
	 * @param short  $logtype constant of this class (BcmsSystem::LOGTYPE_*)
	 * @param short  $severity constant of this class (BcmsSystem::SEVERITY_*)
	 * @param String $methodname name of the method sending the message
	 * @param String $file file name sending the message
	 * @param int    $line line number sending the message
	 * @return boolean ($severity<self::SEVERITY_FAILURE)
     * @author ahe <aheusingfeld@borderlesscms.de>
	 * @date 05.10.2006 22:47:49
	 */
	public static function raiseDictionaryNotice($dict_defTrans, $logtype, $severity, $methodname=null, $file=null, $line=null, $additionalNote=null){
		$notice = BcmsSystem::getDictionaryManager()->getTrans($dict_defTrans);
		if(!$notice) return false; // exit in case of error
		if(!empty($additionalNote)){
			$notice .= ' - '.$additionalNote;
		}
		return self::raiseNotice($notice,$logtype,$severity,$methodname,$file,$line);
	}

	/**
	 * Displays NoAccessRight-Message and writes it to logfile.
	 *
     * @param String $methodname name of the method sending the message
     * @param String $file file name sending the message
     * @param int    $line line number sending the message
	 * @return boolean ($severity < self::SEVERITY_FAILURE)
     * @author ahe <aheusingfeld@borderlesscms.de>
	 * @date 05.10.2006 22:47:49
	 */
	public static function raiseNoAccessRightNotice($methodname, $file=null, $line=null){
		return self::raiseDictionaryNotice('NoAccessRight',
				BcmsSystem::LOGTYPE_CHECK, BcmsSystem::SEVERITY_FAILURE,
				$methodname,$file,$line);
	}

// END ~~~ MESSAGING SECTION


// BEGIN ~~~ PUBLIC HELPER METHODS

	/**
	 * Returns regular expression to validate "technames".
	 */
	public static function getTechnameRegex(){
		return '/^[a-zA-Z0-9\-\_\.]+$/';
	}

	/**
	 * Instantiates and initializes the default system plugins
	 * <ol>
	 * <li>Dictionary</li>
	 * <li>CategoryManager</li>
	 * <li>UserManager</li>
	 * </ol>
	 *
	 * @return void
	 * @author ahe
	 * @since 06.03.2007 15:47:05
	 */
	public static function initSystemPlugins() {
		self::getDictionaryManager();
		self::getCategoryManager();
    	self::getUserManager();
    }

    /**
     * Returns the ONE existing instance of the UserManager class
     *
     * @return UserManager - the ONE existing instance of the UserManager
     * @author ahe
     * @date 06.03.2007 15:54:42
     * @since 0.13
     */
    public static function getUserManager() {
    	return BcmsFactory::getInstanceOf('UserManager');
    }

    /**
     * Returns the ONE existing instance of the CategoryManager class
     *
     * @return CategoryManager - the ONE existing instance of the CategoryManager
     * @author ahe
     * @since 0.13
     * @date 06.03.2007 15:54:42
     */
    public static function getCategoryManager() {
    	return BcmsFactory::getInstanceOf('CategoryManager');
    }

    /**
     * Returns the ONE existing instance of the DictionaryManager class
     *
     * @return Dictionary - the ONE existing instance of the DictionaryManager
     * @author ahe
     * @date 06.03.2007 15:54:42
     * @since 0.13
     */
    public static function getDictionaryManager() {
    	return BcmsFactory::getInstanceOf('Dictionary');
    }

    /**
     * Returns the ONE existing instance of the Parser class
     *
     * @return Parser - the ONE existing instance of the Parser
     * @author ahe <aheusingfeld@borderlesscms.de>
     * @date 06.03.2007 15:54:42
     * @since 0.13.108
     */
    public static function getParser() {
    	return BcmsFactory::getInstanceOf('Parser');
    }

    /**
	 * Calculate SHA-1-Digest
	 * @param String $s the string to be hashed
	 * @return String the hashcode of the given string
     * @author ahe <aheusingfeld@borderlesscms.de>
	 */
	public static function getHash($s){
		return sha1($s);
	}

	/**
	 * sends a real mail using the specified parameters
	 *
	 * @static
	 * @param String $to - the address of the recepient
	 * @param String $from - the address of the sender
	 * @param String $subject - the mail subject
	 * @param String $text - the message body to be sent
	 */
	public static function sendmail_to_address($to, $from, $subject, $text)
	{
		if(!mail($to, $subject, $text, 'From: '.$from."\n"
		  .'Content-type: text/plain; charset=UTF-8'
		  .'\nX-Mailer: Borderless_CMS'))
		{
			exception_handler(new SendMailFailedException($from,$subject));
			return false;
		}
		return true;
	}

    /**
    * writes a message into the logfile
    *
    * @todo move this method to SystemLogManager
    * @param String $msg  textmessage to write to file
    * @param short  $logtype constant of this class (BcmsSystem::LOGTYPE_*)
    * @param short  $severity constant of this class (BcmsSystem::SEVERITY_*)
    * @param String $methodname name of the method sending the message
    * @param String $file file name sending the message
    * @param int    $line line number sending the message
    * @return int  DB_result
    * @author ahe <aheusingfeld@borderlesscms.de>
    * @access public
    * @date 05.10.2006 21:12:34
    */
    public static function writeMsgToLogfile($msg, $logtype, $severity, $methodName=null, $file=null, $line=null) {
		$parser = BcmsSystem::getParser();
	    $userID = BcmsSystem::getUserManager()->getUserId();
		$referrer = $parser->getServerParameter('HTTP_REFERER');
		$requestUri = $parser->getServerParameter('REQUEST_URI');
		$remoteAddr = $parser->getServerParameter('REMOTE_ADDR');
	    $sql = 'INSERT INTO '.BcmsConfig::getInstance()->getTablename('syslog')
	    	.' (timestmp, syslog, fk_session, fk_user_id, logtype, severity, '
	    	.'referrer_uri, request_uri, ip_address, ref_application, user_agent, '
	    	.'filename, linenum)'.' VALUES ('
	    	.date('YmdHis',time()).','
	    	.$parser->prepDBStrng($msg).','
	    	.$parser->prepDBStrng(session_id()).', '
	    	.$userID.', '
	    	.$logtype.', '
	    	.$severity.', '
	    	.$parser->prepDBStrng($referrer).', '
	    	.$parser->prepDBStrng($requestUri).', '
	    	.$parser->prepDBStrng($remoteAddr).', '
	    	.$parser->prepDBStrng($methodName).', \''
	    	.$parser->getServerParameter('HTTP_USER_AGENT').'\', '
	    	.$parser->prepDBStrng($file).', '
	    	.$parser->prepDBStrng($line).')';
	    unset($parser);
	    $result = $GLOBALS['db']->query($sql);
		if($result instanceof PEAR_ERROR) {
			return self::raiseError($result, self::LOGTYPE_INSERT,
				self::SEVERITY_CRITICAL, 'writeMsgToLogfile()',
				__FILE__,__LINE__,'ACHTUNG: Schwerer Fehler beim Schreiben ' .
				'des Protokolls! Bitte benachrichtigen Sie den Administrator!',false); // @todo use dictionary here!);
		} else {
			return $result;
		}
    }
    // END ~~~ PUBLIC HELPER METHODS

    // BEGIN ~~~ TRANSACTION HANDLING
    /**
     *  fuegt das uebergene SQL der Transactionlist hinzu
     * @todo move this to SessionManager
     */
    public static function addTransaction($transsql)
    {
    	$sql = 'INSERT INTO '.self::$transactionTable.' (session_id, sql_stmt, timestmp)'
        .' VALUES ('.
    	$GLOBALS['db']->quote(session_id()).', '.
    	$GLOBALS['db']->quote($transsql).', '.
    	(time()).')';
    	$result = $GLOBALS['db']->query($sql);
    	if($result instanceof PEAR_ERROR) {
    		return BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_INSERT,
    		BcmsSystem::SEVERITY_ERROR, 'addTransaction()',
    		__FILE__,__LINE__);
    	} else {
    		return $result;
    	}
    }

    /**
     * Wird NUR von au&szlig;erhalb aufgerufen und prueft, ob das uebergene SQL
     *   in der Transactionlist steht.
     * Moved here from old database classes.
     *
     * @todo move this to SessionManager
     */
    public static function checkTransaction($transsql)
    {
    	self::deleteOldTransactions();
    	$sql = 'SELECT action_id FROM '.self::$transactionTable
    	.' WHERE (sql_stmt LIKE \''.substr($transsql,0,60)
    	.'%\') AND (session_id = \''.session_id().'\')';
    	$result = $GLOBALS['db']->query($sql);
    	return $result->numRows();
    }

    /**
     * Entfernt Eintraege, die die Transactionlifetime ueberschritten haben,
     *  aus der Transactionlist.
     * Moved here from old database classes.
     *
     * @todo move this to SessionManager
     */
    public static function deleteOldTransactions()
    {
    	$sql = 'DELETE FROM '.self::$transactionTable
    	.' WHERE (timestmp < '.(time() - self::TRANSACTION_LIFETIME).')';
    	return $GLOBALS['db']->query($sql);
    }

    // END OF TRANSACTION HANDLING

    // BEGINNING OF DB_HELPER METHODS
    /**
     * Fetches the data of an attribute according to specified parameters
     *
     * @param String name of the attribute in the database table
     * @param int id of the category
     * @param String name of the primary key attribute in the database table
     * @param String name of the database table
     * @return mixed
     * @author ahe
     * @date 28.10.2006 21:35:06
     * @since 0.14
     * URGENT when Category is being derived from BcmsObject move this to DataAbstractionLayer class
	 */
  	public static function getAttributeDataFromDb($attribName, $catId, $idAttribute, $tableName){
	  	$sql='SELECT '.$attribName.' FROM '.$tableName.' WHERE ( '.$idAttribute.'='.$catId.' )';
	 	$result = $GLOBALS['db']->query($sql);
		if ($result instanceof PEAR_ERROR)
			return BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_SELECT,
				BcmsSystem::SEVERITY_ERROR, 'getUserIDfromDB()',__FILE__, __LINE__);

	 	$numrows = $result->numRows();
	 	if($numrows<1) return '';

	 	$record = $result->fetchRow(DB_FETCHMODE_ASSOC);
	 	$result->free();
	 	return $record[$attribName];
  	}

	/**
	 * Includes Translation for DB_Table error messages in german
	 *
	 * @todo move to dictionary!
	 * @todo Continue translation AND use dictionary for it!
	 * @access private
	 * @author ahe
	 * @date 24.06.2006
	 * @since 0.10
	 */
	private function overwriteDbTableMessages() {
		include_once('DB/Table.php');
		$GLOBALS['_DB_TABLE']['error'] = array(
				DB_TABLE_ERR_NOT_DB_OBJECT       => 'Erster Parameter muss ein DB/MDB2 Objekt sein',
				DB_TABLE_ERR_PHPTYPE             => 'DB/MDB2 phptype (oder dbsyntax) wird nicht unterstützt',
				DB_TABLE_ERR_SQL_UNDEF           => 'Select key ist nicht in der Map enthalten',
				DB_TABLE_ERR_INS_COL_NOMAP       => 'Folgende Spalte ist der Datenbank unbekannt',
				DB_TABLE_ERR_INS_COL_REQUIRED    => 'Pflichtfeld nicht gesetzt. Inhalt darf muss gesetzt werden und darf nicht leer sein. Feldname:',
				DB_TABLE_ERR_INS_DATA_INVALID    => 'Ungültiger Inhalt in Feld ',
				DB_TABLE_ERR_UPD_COL_NOMAP       => 'Folgende Spalte ist der Datenbank unbekannt',
				DB_TABLE_ERR_UPD_COL_REQUIRED    => 'Pflichtfeld nicht gesetzt. Inhalt muss gesetzt werden und darf nicht leer sein. Feldname:',
				DB_TABLE_ERR_UPD_DATA_INVALID    => 'Ungültiger Inhalt in Feld',
				DB_TABLE_ERR_CREATE_FLAG         => 'Create flag not valid',
				DB_TABLE_ERR_IDX_NO_COLS         => 'Keine Spalten für den Index angegeben',
				DB_TABLE_ERR_IDX_COL_UNDEF       => 'Spalten ist nicht in der Map des Index',
				DB_TABLE_ERR_IDX_TYPE            => 'Typ ist ungültig für Index',
				DB_TABLE_ERR_DECLARE_STRING      => 'String column declaration not valid',
				DB_TABLE_ERR_DECLARE_DECIMAL     => 'Decimal column declaration not valid',
				DB_TABLE_ERR_DECLARE_TYPE        => 'Spaltentyp nicht gültig',
				DB_TABLE_ERR_VALIDATE_TYPE       => 'Kann unbekannten Typ für diese Spalte nicht validieren',
				DB_TABLE_ERR_DECLARE_COLNAME     => 'Spaltenname ungültig',
				DB_TABLE_ERR_DECLARE_IDXNAME     => 'Indexname ungültig',
				DB_TABLE_ERR_DECLARE_TYPE        => 'Spaltentyp ungültig',
				DB_TABLE_ERR_IDX_COL_CLOB        => 'CLOB column not allowed for index',
				DB_TABLE_ERR_DECLARE_STRLEN      => 'Column name too long, 30 char max',
				DB_TABLE_ERR_IDX_STRLEN          => 'Index name too long, 30 char max',
				DB_TABLE_ERR_TABLE_STRLEN        => 'Table name too long, 30 char max',
				DB_TABLE_ERR_SEQ_STRLEN          => 'Sequence name too long, 30 char max',
				DB_TABLE_ERR_VER_TABLE_MISSING   => 'Verification failed: table does not exist',
				DB_TABLE_ERR_VER_COLUMN_MISSING  => 'Verification failed: column does not exist',
				DB_TABLE_ERR_VER_COLUMN_TYPE     => 'Verification failed: wrong column type',
				DB_TABLE_ERR_NO_COLS             => 'Column definition array may not be empty',
				DB_TABLE_ERR_VER_IDX_MISSING     => 'Verification failed: index does not exist',
				DB_TABLE_ERR_VER_IDX_COL_MISSING => 'Verification failed: index does not contain all specified cols',
				DB_TABLE_ERR_CREATE_PHPTYPE      => 'Creation mode is not supported for this phptype',
				DB_TABLE_ERR_DECLARE_PRIMARY     => 'Only one primary key is allowed',
				DB_TABLE_ERR_DECLARE_PRIM_SQLITE => 'SQLite does not support primary keys',
				DB_TABLE_ERR_ALTER_TABLE_IMPOS   => 'Alter table failed: changing the field type not possible',
				DB_TABLE_ERR_ALTER_INDEX_IMPOS   => 'Alter table failed: changing the index/constraint not possible'
		);
				$GLOBALS['_DB_TABLE']['qf_rules'] = array(
		  'required'  => 'Das Feld %s ist ein Pflichtfeld. Es darf nicht leer sein.',
		  'numeric'   => 'Das Feld %s darf nur Ziffern enthalten.',
		  'maxlength' => 'Das Feld %s darf maximal %d Zeichen lang sein.'
		);
		  $GLOBALS['_DB_TABLE']['qf_JsWarnings'] = array(
		  'prefix' => 'ACHTUNG: Ungültige Feldinhalte!',
		  'postfix'   => 'Bitte korrigieren Sie Ihre Eingaben.'
		);
	}
}
?>