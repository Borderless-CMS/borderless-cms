<?php
/**
 * The BcmsSystem class is a static, stateful helper class which hosts the most
 * necessary system functions/ methods.
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

	/**
	 * Inits class default values. ATTENTION: Needs whole config to be already
	 * loaded!!!
	 *
	 * @return void
	 * @author ahe
	 * @date 06.10.2006 00:20:40
	 * @package htdocs/classes/system
	 */
	public static function init(){
		$bcmsConfigObj = BcmsConfig::getInstance();
		$bcmsConfigObj->setTablename('syslog', 'syslog');
		$sessionHandler = Factory::getObject('UserSession');

		// Session starten
		session_name('BORDERLESS_CMS');
		session_start();

		// connect to database
		if(!isset($GLOBALS['db'])) {
			// the following should actually never be called as the db connect is made in UserSession class
			$dsn = $bcmsConfigObj->dbType.'://'.$bcmsConfigObj->dbUser.':'
				.$bcmsConfigObj->dbPass.'@'
				.$bcmsConfigObj->dbServer.'/'
				.$bcmsConfigObj->dbDatabase;
			$GLOBALS['db'] = DB::connect($dsn);
		}

		// config-vars aus der Datenbank laden
		$bcmsConfigObj->loadConfigVars();
		$charset = $bcmsConfigObj->metaCharset;
		$charset = empty($charset) ? 'UTF-8' : $charset;
		mb_internal_encoding($charset); // sets encoding for all multibyte php functions (mb_*)
		self::$severityToBeLogged = explode(',',$bcmsConfigObj->notice_log_level);
		self::$severityToBeDisplayed = explode(',',$bcmsConfigObj->notice_display_level);
		self::$transactionTable = $bcmsConfigObj->getTablename('last_transactions');
	}

	public static function getSystemMessages(){
		$parser = BcmsFactory::getInstanceOf('Parser');
		$gui = BcmsFactory::getInstanceOf('GuiUtility');
		$msgString = '<div id="systemmsg" style="z-index:99999999999;">';
		$msgString .= $gui->createHeading(2, Factory::getObject('Dictionary')->getTrans('h.systemMsg'),12);

		for ($i = 0; $i < sizeof($_SESSION['system_msg']); $i++) {
			$msg = $_SESSION['system_msg'][$i]['message'];
			$severity = $_SESSION['system_msg'][$i]['severity'];
			$oddOrEven = ($i%2>0) ? 'odd' : 'even';
			$msgString .= $gui->fillTemplate('div_tpl'
					,array('id="sysmessage'.$i.'" ' .
						'class="message '.self::$severityArray[$severity].' '.$oddOrEven.'"'
					,$msg));
		}
		unset($_SESSION['system_msg']);

//		$uri_parts = explode('/',$parser->getServerParameter('REQUEST_URI'));
//		if($uri_parts[(sizeof($uri_parts)-1)] == '') array_pop($uri_parts);
//		array_pop($uri_parts);
//		$uri = implode('/',$uri_parts);
		$uri = 'http://'.BcmsConfig::getInstance()->siteUrl.'/'.$_SESSION['cur_catname'].'/';
		return $msgString.'
	            <div id="systemmsg_weiter"><span><a class="button" href="'
	            .$parser->getServerParameter('HTTP_REFERER')
	            .'">&laquo; '.Factory::getObject('Dictionary')->getTrans('back').'</a></span>'
	            .'<span><a class="button" href="'.$uri.'">'
				.Factory::getObject('Dictionary')->getTrans('to_category').'</a></span></div>
	          </div>  <!-- /errorsection -->'."\n";
	}

	/**
	 * Returns regular expression to validate "technames".
	 */
	public static function getTechnameRegex(){
		return '/^[a-zA-Z0-9\-\_]+$/';
	}

	/**
	 * Handles a PEAR_ERROR object, writes to the db and sends a related message
	 * to the message window.
	 *
     * @param String $msg  textmessage to write to file
     * @param short  $logtype constant of this class (BcmsSystem::LOGTYPE_*)
     * @param short  $severity constant of this class (BcmsSystem::SEVERITY_*)
     * @param String $methodname name of the method sending the message
     * @param String $file file name sending the message
     * @param int    $line line number sending the message
	 * @return boolean ($severity<self::SEVERITY_FAILURE)
     * @author ahe <aheusingfeld@borderlesscms.de>
	 * @package htdocs/classes/system
	 * @date 05.10.2006 21:48:37
	 */
	public static function raiseError($error,  $logtype, $severity=null, $methodname=null, $file=null, $line=null, $displayMsg=null, $writeMsgToDb=true){

		if($error instanceof PEAR_ERROR){
			if($displayMsg==null) {
				$displayMsg = $error->message;
			}
			$notice = $displayMsg."\n\n".print_r($error,true);
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
     * @param boolean $writeMsgToDb shall the message be written to logfile
	 * @return boolean ($severity<self::SEVERITY_FAILURE)
     * @author ahe <aheusingfeld@borderlesscms.de>
	 * @package htdocs/classes/system
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

		// TODO There should be an index for error translation!
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
	 * @package htdocs/classes/system
	 * @date 05.10.2006 22:47:49
	 */
	public static function raiseDictionaryNotice($dict_defTrans, $logtype, $severity, $methodname=null, $file=null, $line=null){
		$notice = Factory::getObject('Dictionary')->getTrans($dict_defTrans);
		if(!$notice) return false; // exit in case of error
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
	 * @package htdocs/classes/system
	 * @date 05.10.2006 22:47:49
	 */
	public static function raiseNoAccessRightNotice($methodname, $file=null, $line=null){
		return self::raiseDictionaryNotice('NoAccessRight',
				BcmsSystem::LOGTYPE_CHECK, BcmsSystem::SEVERITY_FAILURE,
				$methodname,$file,$line);
	}

	/**
	 * Calculate SHA-1-Digest
	 * @param String $s the string to be hashed
	 * @return String the hashcode of the given string
	 */
	public static function getHash($s){
		return sha1($s);
	}

	/**
	 * takes a boolean var and sets it the int value; MUST be called "call-by-reference"
	 *
	 * should be moved to class.cSystem.php! if it will exist one day ;-)
	 */
	public static function setBooleanToInt(&$var)
	{
	$var = ($var) ? 1 : 0;
	}

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
    * @param String $msg  textmessage to write to file
    * @param short  $logtype constant of this class (BcmsSystem::LOGTYPE_*)
    * @param short  $severity constant of this class (BcmsSystem::SEVERITY_*)
    * @param String $methodname name of the method sending the message
    * @param String $file file name sending the message
    * @param int    $line line number sending the message
    * @return int  DB_result
    * @author ahe <aheusingfeld@borderlesscms.de>
	* @package htdocs/classes/system
    * @access public
	* @date 05.10.2006 21:12:34
    */
	public static function writeMsgToLogfile($msg, $logtype, $severity, $methodName=null, $file=null, $line=null) {
		$parser = BcmsFactory::getInstanceOf('Parser');
	    $userID = PluginManager::getPlgInstance('UserManager')->getLogic()->getUserID();
		$referrer = $parser->getServerParameter('HTTP_REFERER');
		$requestUri = $parser->getServerParameter('REQUEST_URI');
		$remoteAddr = $parser->getServerParameter('REMOTE_ADDR');
	    $sql = 'INSERT INTO '.BcmsConfig::getInstance()->getTablename('syslog')
	    	.' (timestamp, syslog, fk_session, fk_user_id, logtype, severity, '
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
			'des Protokolls! Bitte benachrichtigen Sie den Administrator!',false); // TODO use dictionary here!);
		} else {
			return $result;
		}
	}

// BEGIN OF TRANSACTION HANDLING
  /* fuegt das uebergene SQL der Transactionlist hinzu
  */
  public static function addTransaction($transsql)
  {
    $tq = BcmsConfig::getInstance()->tabQuot;
    $sql = 'INSERT INTO '.self::$transactionTable.' (session_id, sql, timestamp)'
        .' VALUES ('.
        $GLOBALS['db']->quoteSmart(session_id()).', '.
        $GLOBALS['db']->quoteSmart($transsql).', '.
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

  /* Wird NUR von au&szlig;erhalb aufgerufen und prueft, ob das uebergene SQL
  *   in der Transactionlist steht.
  */
  public static function checkTransaction($transsql)
  {
    self::deleteOldTransactions();
	$tabQuot = BcmsConfig::getInstance()->tabQuot;
    $sql = 'SELECT action_id FROM '.self::$transactionTable
    	.' WHERE ('.$tabQuot.'sql'.$tabQuot.' LIKE \''.substr($transsql,0,60)
    	.'%\') AND (session_id = \''.session_id().'\')';
    $result = $GLOBALS['db']->query($sql);
    return $result->numRows();
  }

  /**
   * Entfernt Eintraege, die die Transactionlifetime ueberschritten haben,
   *  aus der Transactionlist.
   */
  public static function deleteOldTransactions()
  {
    $sql = 'DELETE FROM '.self::$transactionTable
    	.' WHERE (timestamp < '.(time() - self::TRANSACTION_LIFETIME).')';
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
	 * @package htdocs/classes/categories
	 * @since 0.14
	 */
  	public static function getAttributeDataFromDb($attribName, $catId, $idAttribute, $tableName){
	  	$sql='SELECT '.$attribName.' FROM '.$tableName.' WHERE (cat_id='.$catId.')';
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



}
?>
