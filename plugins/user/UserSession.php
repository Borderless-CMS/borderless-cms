<?php
/*
 * Created on 06.06.2006
 *
 */
class UserSession {

	private $db = null; // holds the database connection

	public function __construct() {
		$this->configInstance = BcmsConfig::getInstance();
		$this->configInstance->setTablename('usersession', 'usersessions');
		session_set_save_handler(
		    array($this,'_open'),
		    array($this,'_close'),
		    array($this,'_read'),
		    array($this,'_write'),
		    array($this,'_destroy'),
		    array($this,'_clean')
		);
	}

	/**
	 * open database connection
	 * 
	 * @param
	 */
	public function _open($save_path, $session_name) {
		if(!isset($GLOBALS['db'])) {
	 		$confObj = BcmsConfig::getInstance();
			$dsn = $confObj->dbType.'://'
				.$confObj->dbUser.':'
				.$confObj->dbPass.'@'
				.$confObj->dbServer.'/'
				.$confObj->dbDatabase;
			$GLOBALS['db'] = DB::connect($dsn);
		}
		$this->db = &$GLOBALS['db'];

	    return true;
	}

	/**
	 * read session from database
	 * 
	 * @param String $id the session_id
	 */
	public function _read($id)
	{
	 	$parser = BcmsFactory::getInstanceOf('Parser');
		$hash = $this->getSessionHashValue($id);
	    $hash = $parser->prepDbStrng($hash);
	    $sql = 'SELECT data_string
	            FROM   '.$this->configInstance->getTablename('usersession').'
	            WHERE  hash_val = '.$hash;

// debug
//		$this->db->query('INSERT INTO fzg_sysmessages (msgname,logmsg) VALUES (\'read: '.$_SERVER['REQUEST_URI'].'\','.$this->db->quoteSmart($sql).')');
		
	 	$result = $this->db->query($sql);
	    if (!($result instanceof DB_ERROR))
	    {
	        if ($result->numRows()>0)
	        {
	            $record = $result->fetchRow(DB_FETCHMODE_ASSOC);
	            return $record['data_string'];
			}
		}
	    return '';
	}

	/**
	 * write session data to database
	 * 
	 * @param String $id the session_id
	 * @param String $data the sessiondata as serialized string
	 * @return string
	 * @author ahe
	 * @date 31.08.2006 21:29:13
	 * @package htdocs/plugins/user
	 */
	public function _write($id, $data) {

	 	$parser = BcmsFactory::getInstanceOf('Parser');
	  	$userObj = PluginManager::getPlgInstance('UserManager')->getLogic();
		$hash = $this->getSessionHashValue($id);
		$action_uri = $parser->getServerParameter('REDIRECT_URL');
		$userId = intval($userObj->getUserID());
		$sql = 'SELECT sessionstring FROM '.$this->configInstance->getTablename('usersession').
				' WHERE (hash_val = \''.$hash.'\')';
		$result = $this->db->query($sql);
	    if($result->numRows()>0) {
			// update existing record
			$sql='UPDATE '.$this->configInstance->getTablename('usersession').
				' SET ' .
				'fk_user='.$userId.', '.
				'last_action='.$this->db->quoteSmart(date('YmdHis')).', '.
				'data_string='.$this->db->quoteSmart($data).', '.
				'action_uri='.$this->db->quoteSmart($action_uri).
				' WHERE (hash_val = \''.$hash.'\')';
	    } else {
			$sql='INSERT INTO '.$this->configInstance->getTablename('usersession').
				' (hash_val, sessionstring, fk_user, starttime, last_action, ' .
				'last_ip, data_string, action_uri) VALUES ('
				.'\''.$hash.'\', '
				.$this->db->quoteSmart($id).', '
				.$userId.', '
				.$this->db->quoteSmart(date('YmdHis')).', '
				.$this->db->quoteSmart(date('YmdHis')).', '
				.$this->db->quoteSmart($parser->getServerParameter('REMOTE_ADDR')).', '
				.$this->db->quoteSmart($data).', '
				.$this->db->quoteSmart($action_uri)
				.')';
	    }

// debug
//		$error=$this->db->query('INSERT INTO fzg_sysmessages (msgname,logmsg) VALUES (\'write: '.$_SERVER['REQUEST_URI'].'\','.$this->db->quoteSmart($sql).')');
//		print_r($error);
	    return $this->db->query($sql);
	}

	public function _destroy($id)
	{
		$this->delete_old_session($id);
	}

	public function _clean($max)
	{
		$this->delete_old_session();
	}

	public function _close() {
		$this->db->disconnect();

	}

	/**
	 * creates a sha1 hash over HTTP_USER_AGENT, ip-address and session_id
	 *
	 * @return string hashstring
	 * @author ahe
	 * @date 20.05.2006 10:07:53
	 * @package htdocs/classes/users
	 */
	public function getSessionHashValue($id) {
	 	$parser = BcmsFactory::getInstanceOf('Parser');
		$user_agent = $parser->getServerParameter('HTTP_USER_AGENT');
		$ip = $parser->getServerParameter('REMOTE_ADDR');
		return BcmsSystem::getHash($user_agent.' '.$ip.' '.$id);
	}

	/**
	* deletes records from sessiontable according to expiration time and
	* optionally to given user_id and/ or sessionid
	*
	* @param integer $userid optional, default value 0
	* @param mixed $sessionid optional, give 0 to generate sessionid
	* @access public
	* @return result
	*/
	public function delete_old_session($sessionid=null)
	{
		if(!empty($sessionid))
			$sessionid = $this->getSessionHashValue($sessionid);
		elseif(is_numeric($sessionid) && $sessionid==0)
			$sessionid = $this->getSessionHashValue(session_id());
		else
			$sessionid='';

 		$confObj = BcmsConfig::getInstance();
		$tstamp = strtotime('-'.$confObj->user_sessionTimeout
			.' minutes');
	 	$parser = BcmsFactory::getInstanceOf('Parser');
		$sessionid = $parser->prepDbStrng($sessionid);
		$expired = $parser->prepDbStrng(date('YmdHis',$tstamp));

		$sql='DELETE FROM '.$this->configInstance->getTablename('usersession')
			.' WHERE '
			.' (last_action < '.$expired.')'
			.' OR (hash_val='.$sessionid.')';
		return $this->db->query($sql);
	}

}
?>
