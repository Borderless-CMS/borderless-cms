<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * data abstraction layer class for systemlog data
 *
 * @since 0.13
 * @date 16.12.2006 09:56:43
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @class SystemLog_DAL
 * @ingroup systemlog
 * @package systemlog
 */
class SystemLog_DAL extends DataAbstractionLayer {

	public $col = array(
		// unique row ID
		'syslog_id' => array(
			'type'    => 'integer',
			'require' => true
		),
		'timestamp' => array(
			'type'    => 'timestamp'
		),
		'syslog' => array(
			'type'    => 'clob',
			'qf_type' => 'textarea',
			'qf_attrs'  => array(
				'rows' => 1,
				'cols' => 30
			 )
		),
		'fk_session' => array(
			'type'    => 'integer',
			'require' => true
		),
		'fk_user_id' => array(
			'type'    => 'integer',
			'require' => true
		),
		'logtype' => array(
			'type'    => 'integer',
			'require' => true
		),
		'severity' => array(
			'type'    => 'integer',
			'require' => true
		),
		'referrer_uri' => array(
			'type'    => 'clob',
			'require' => true,
			'qf_type' => 'textarea',
			'qf_attrs'  => array(
				'rows' => 1,
				'cols' => 30
			 )
		),
		'request_uri' => array(
			'type'    => 'clob',
			'require' => true,
			'qf_type' => 'textarea',
			'qf_attrs'  => array(
				'rows' => 1,
				'cols' => 30
			 )
		),
		'ip_address' => array(
			'type'    => 'varchar',
			'size'  => 24,
			'require' => true
		),
		'ref_application' => array(
			'type'    => 'varchar',
			'size'  => 50,
			'require' => true
		),
		'user_agent' => array(
			'type'    => 'varchar',
			'size'  => 255,
			'require' => true
		),
		'filename' => array(
			'type'    => 'varchar',
			'size'  => 255,
			'require' => true
		),
		'linenum' => array(
			'type'    => 'varchar',
			'size'  => 6,
			'require' => true
		)
	);

	public $idx = array(
		'primary' => array(
			'type' => 'unique',
			'cols' => 'syslog_id'
		),
		'time' => array(
			'type' => 'index',
			'cols' => 'timestamp'
		)
	);

	public $sql = array(

		// multiple rows for a list
		'listallcolumns' => array(
			'select' => '*',
			'order' => 'timestamp, fk_user_id'
		)
		);

	public $uneditableElements = array ();
	public $elementsToFreeze = null;
	protected $primaryKeyColumnName = 'syslog_id';

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
		parent::__construct($GLOBALS['db'],BcmsConfig::getInstance()->getTablename('syslog'));
		$this->elementsToFreeze = $this->col;
	}

	public function getList($offset=null,$limit=null,$where=null,$searchphrase=null, $order_by=null, $order_dir=null)
	{

		$this->sql['list4view'] = array(
				'select' => 'syslog.syslog_id, syslog.timestamp, user.username, ' .
						'syslog.request_uri, syslog.syslog, syslog.logtype, ' .
						'syslog.filename',
				'from' => $this->table.' as syslog, ' .
						BcmsConfig::getInstance()->getTablename('user').' as user',
				'where' => 'user.user_id=syslog.fk_user_id',
				'order' => 'syslog.timestamp DESC',
				'fetchmode' => DB_FETCHMODE_ASSOC
		);

		return parent::getList($offset,$limit,$where, $order_by, $order_dir,
					$searchphrase,'syslog.',' syslog.','list4view');
	}

    /**
	 * prepare values in specified array for display in table view
	 *
	 * @param array rows - associative array with column => value
	 * @return array - rows with prepared values
	 * @author ahe
	 * @date 16.12.2006 23:11:28
	 * @since 0.13.180
	 */
    protected function prepareResultForTableView($rows) {
        if(count($rows)<1) return $rows;
        $maxLen = 25;
		$parser = BcmsSystem::getParser();
        for ($i = 0; $i < count($rows); $i++) {
            foreach($rows[$i] as $key => $value) {
                // cut string if necessary
                if(mb_strlen($value)>$maxLen){
					if($key=='filename'){
	                    $length = mb_strlen($value);
	                    $rows[$i][$key] = '...'.mb_substr($value,($length-$maxLen));
					} else {
	                    $rows[$i][$key] = mb_substr($value,0,($maxLen-3)).'...';
					}
                }
            }

			// add html encode values in column 'var_value' so html-tags are displayed
			$rows[$i]['syslog'] = $parser->htmlentities($rows[$i]['syslog']);
        }
        return $rows;
	}

	/**
	 * handles filtering or values of special fields for insert or update
	 * @author ahe
	 */
	public function checkSpecialFields(&$p_aCols,$func) {
	}

	public function getObject($id) {
		$this->sql['listallcolumns']['fetchmode'] = DB_FETCHMODE_OBJECT;
		return $this->select('listallcolumns','syslog_id = '.$id);
	}

	protected function getSearchableFieldsArray()
	{
		return array(
			'syslog' => 'LIKE',
			'request_uri' => 'LIKE',
			'ref_application' => 'LIKE',
			'filename' => 'LIKE',
			'user_agent' => 'LIKE'
		);
	}

	public function getPluginsCatName() {
		$confInst = BcmsConfig::getInstance();
		$sql = 'SELECT cat.techname '.
			'FROM '.$confInst->getTablename('menu').' as cat ' .
			'JOIN '.$confInst->getTablename('modentries').' as modentries '.
			' ON cat.type = modentries.me_id '.
			'JOIN '.$confInst->getTablename('plugins').' as plugins '.
			' ON modentries.fk_module = plugins.module_id '.
			'WHERE plugins.classname = \'SystemLogManager\'';
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