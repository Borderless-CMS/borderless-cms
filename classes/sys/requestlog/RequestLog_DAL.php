<?php
class RequestLog_DAL extends DataAbstractionLayer {

	public $col = array(
		// unique row ID
		'requestlog_id' => array(
			'type'    => 'integer',
			'require' => true
		),
		'user_id' => array(
			'type'    => 'integer',
			'require' => true
		),
		'request_date' => array(
			'type'    => 'timestamp',
			'require' => true
		),
		'uri' => array(
			'type'    => 'varchar',
			'size'    => 255,
			'require' => true
		),
		'post' => array(
			'type' => 'clob',
			'qf_type' => 'textarea',
			'qf_attrs'  => array(
				'rows' => 1,
				'cols' => 30
			 )
		),
		'get' => array(
			'type' => 'clob',
			'qf_type' => 'textarea',
			'qf_attrs'  => array(
				'rows' => 1,
				'cols' => 30
			 )
		),
		'session' => array(
			'type' => 'clob',
			'qf_type' => 'textarea',
			'qf_attrs'  => array(
				'rows' => 1,
				'cols' => 30
			 )
		)
	);

	public $idx = array(
		'primary' => array(
			'type' => 'unique',
			'cols' => 'requestlog_id'
		),
		'user_request' => array(
			'type' => 'unique',
			'cols' => 'user_id, requestdate'
		)
	);

	public $sql = array(

		// multiple rows for a list
		'listallcolumns' => array(
			'select' => '*',
			'order' => 'user_id, request_date'
		)
		);

	public $uneditableElements = array ();
	public $elementsToFreeze = null;
	protected $primaryKeyColumnName = 'requestlog_id';

	private static $uniqueInstance = null;

/*
 * Declaration of methods
 */
	/**
	 * Get the instance of this class. Holds its instance by itself - not via
	 * BcmsFactory!
	 *
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 * @date 15.10.2006 00:41:41
	 * @since 0.14
	 */
	public static function getInstance(){
		if(self::$uniqueInstance==null)
			self::$uniqueInstance = new self();
		return self::$uniqueInstance;
	}

	protected function __construct() {
		parent::__construct($GLOBALS['db'],BcmsConfig::getInstance()->getTablename('requestlog'));
		$this->elementsToFreeze = $this->cols;
		$this->insertRecordForCurrentRequest();
		$this->deleteOldEntries();
	}

	public function getListForBugReport()
	{
		$userId = PluginManager::getPlgInstance('UserManager')->getLogic()->getUserID();
		$this->sql['list4report'] = array(
				'select' => 'requestlog.post, requestlog.get, requestlog.session, ' .
						'requestlog.request_date, requestlog.uri ',
				'from' => $this->table.' as requestlog ',
				'where' => 'user_id = '.$userId,
				'order' => 'requestlog.request_date DESC',
				'fetchmode' => DB_FETCHMODE_ASSOC
		);
		return $this->select('list4report',null,null,1,BcmsConfig::getInstance()->bugreportNoOfPriorRequest);
	}

	public function getList($offset=null,$limit=null,$where=null,$searchphrase=null, $order_by=null, $order_dir=null)
	{

		$this->sql['list4view'] = array(
				'select' => 'requestlog.requestlog_id, user.username, ' .
						'requestlog.request_date, requestlog.uri ',
				'from' => $this->table.' as requestlog, ' .
						BcmsConfig::getInstance()->getTablename('user').' as user',
				'where' => 'user.user_id=requestlog.user_id',
				'order' => 'requestlog.request_date DESC',
				'fetchmode' => DB_FETCHMODE_ASSOC
		);

		return parent::getList($offset,$limit,$where, $order_by, $order_dir,
					$searchphrase,'requestlog.',' requestlog.','list4view');
	}

    /**
	 * prepare values in specified array for display in table view
	 *
	 * @param array rows - associative array with column => value
	 * @return array - rows with prepared values
	 * @author ahe
	 * @date 16.12.2006 23:11:28
	 * @since 0.13.180
	 * @package htdocs/classes/sys/config
	 */
    protected function prepareResultForTableView($rows) {
        if(count($rows)<1) return $rows;
        $maxLen = 35;
        for ($i = 0; $i < count($rows); $i++) {
            foreach($rows[$i] as $key => $value) {
                // cut string if necessary
                if(mb_strlen($value)>$maxLen)
                    $rows[$i][$key] = mb_substr($value,0,($maxLen-3)).'...';
            }
        }
        return $rows;
	}

	/**
	 * handles filtering or values of special fields for insert or update
	 * @author ahe
	 */
	public function checkSpecialFields(&$p_aCols,$func) {
	}

	/**
	 * Inserts a new request record for the current request
	 *
	 * @author ahe
	 * @date 19.12.2006 00:59:06
	 * @package htdocs/classes/sys/requestlog
	 */
	protected function insertRecordForCurrentRequest() {
		$func = 'insert';
		$parser = BcmsFactory::getInstanceOf('Parser');
		$cols = array(
			// id are inserted automatically
			'user_id' => PluginManager::getPlgInstance('UserManager')->getLogic()->getUserID(),
			'requestlog_id' => $this->nextID(),
			'request_date' => date('Y-m-d H:i:s'),
			'uri' => $parser->getServerParameter('REQUEST_URI'),
			'post' => $parser->prepDbStrng(print_r($_POST,true)),
			'get' => $parser->prepDbStrng(print_r($_GET,true)),
			'session' => $parser->prepDbStrng(print_r($_SESSION,true)),
		);
		$result = $this->insert($cols);
	    if($result instanceof PEAR_ERROR) {
	    	return BcmsSystem::raiseError($result,BcmsSystem::LOGTYPE_INSERT,
			BcmsSystem::SEVERITY_ERROR, 'insertRecordForCurrentRequest()',
	    		__FILE__, __LINE__, $result->message);
	    }
    	return true;
	}

	/**
	 *  The requests of a user are deleted when they are older than 30
	 *  minutes and the user is logged on
	 *
	 * @return boolean - has the delete process been successful?
	 * @author ahe
	 * @date 19.12.2006 00:58:34
	 * @package htdocs/classes/sys/requestlog
	 */
	public function deleteOldEntries()
	{
		$where = 'user_id='.PluginManager::getPlgInstance('UserManager')->getLogic()->getUserID()
				.' AND request_date < '.$this->db->quoteSmart(date('Y-m-d H:i:s',time()-60*BcmsConfig::getInstance()->user_request_keep_time));
		return $this->delete($where);
	}

	public function getObject($id) {
		$this->sql['listallcolumns']['fetchmode'] = DB_FETCHMODE_OBJECT;
		return $this->select('listallcolumns','requestlog_id = '.$id);
	}

	protected function getSearchableFieldsArray()
	{
		return array(
			'post' => 'LIKE',
			'get' => 'LIKE',
			'session' => 'LIKE'
		);
	}

	// TODO Unbedingt Zustaendigkeiten besser verteilen! Plugin sollte sowas nicht machen muessen!
	public function getPluginsCatName() {
		$confInst = BcmsConfig::getInstance();
		$sql = 'SELECT cat.techname '.
			'FROM '.$confInst->getTablename('menu').' as cat ' .
			'JOIN '.$confInst->getTablename('modentries').' as modentries '.
			' ON cat.type = modentries.me_id '.
			'JOIN '.$confInst->getTablename('plugins').' as plugins '.
			' ON modentries.fk_module = plugins.module_id '.
			'WHERE plugins.classname = \'RequestLogManager\'';
	 	$result = $GLOBALS['db']->query($sql);
		if ($result instanceof PEAR_ERROR)
			return BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_SELECT,
				BcmsSystem::SEVERITY_ERROR, 'getPluginsCatName()',__FILE__, __LINE__);

	 	$numrows = $result->numRows();
	 	if($numrows<1) return '';

		$record = $result->fetchRow(DB_FETCHMODE_ASSOC);
		$result->free();
		return $record['techname'];
	}
}
?>