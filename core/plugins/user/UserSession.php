<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * Connector for php session processing between php and the database
 *
 * @since 0.9
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @date 06.06.2006
 * @class UserSession
 * @ingroup users
 * @package users
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
			BcmsSystem::createGlobalDbObject();
		}
		$this->db = $GLOBALS['db'];
	    return true;
	}

	/**
	 * read session from database
	 *
	 * @param String $id the session_id
	 */
	public function _read($id)
	{
		$parser = BcmsSystem::getParser();
		$hash = $this->getSessionHashValue($id);
	    $hash = $parser->prepDbStrng($hash);
	    $sql = 'SELECT data_string
	            FROM   '.$this->configInstance->getTablename('usersession').'
	            WHERE  hash_val = '.$hash;

// debug
//		$this->db->query('INSERT INTO fzg_sysmessages (msgname,logmsg) VALUES (\'read: '.$_SERVER['REQUEST_URI'].'\','.$this->db->quote($sql).')');

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
	 */
	public function _write($id, $data, $userId=0) {

		$parser = BcmsSystem::getParser();
		$hash = $this->getSessionHashValue($id);
		$action_uri = $parser->getServerParameter('REDIRECT_URL');
		if($userId==0){
			$userId = BcmsSystem::getUserManager()->getUserId();
		}
		$sql = 'SELECT sessionstring FROM '.$this->configInstance->getTablename('usersession').
				' WHERE (hash_val = \''.$hash.'\')';
		$result = $this->db->query($sql);
	    if($result->numRows()>0) {
			// update existing record
			$sql='UPDATE '.$this->configInstance->getTablename('usersession').
				' SET ' .
				'fk_user='.$userId.', '.
				'last_action='.$this->db->quote(date('YmdHis')).', '.
				'data_string='.$this->db->quote($data).', '.
				'action_uri='.$this->db->quote($action_uri).
				' WHERE (hash_val = \''.$hash.'\')';
	    } else {
			$sql='INSERT INTO '.$this->configInstance->getTablename('usersession').
				' (hash_val, sessionstring, fk_user, starttime, last_action, ' .
				'last_ip, data_string, action_uri) VALUES ('
				.'\''.$hash.'\', '
				.$this->db->quote($id).', '
				.$userId.', '
				.$this->db->quote(date('YmdHis')).', '
				.$this->db->quote(date('YmdHis')).', '
				.$this->db->quote($parser->getServerParameter('REMOTE_ADDR')).', '
				.$this->db->quote($data).', '
				.$this->db->quote($action_uri)
				.')';
	    }

// debug
//		$error=$this->db->query('INSERT INTO fzg_sysmessages (msgname,logmsg) VALUES (\'write: '.$_SERVER['REQUEST_URI'].'\','.$this->db->quote($sql).')');
//	    $error = $this->db->query($sql);
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
	 */
	public function getSessionHashValue($id) {
	 	$parser = BcmsSystem::getParser();
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
	 	$parser = BcmsSystem::getParser();
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
