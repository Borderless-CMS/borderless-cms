<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * Dataabstractionlayer class for general plugin data
 *
 * @since 0.11
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @class Plugin_DAL
 * @ingroup plugins
 * @package plugins
 */
class Plugin_DAL extends DataAbstractionLayer {

	public $col = array(

		// unique row ID
		'module_id' => array(
			'type'    => 'integer',
			'require' => true
		),
		'plg_name' => array(
			'type'    => 'varchar',
			'size'    => 40
		),
		'techname' => array(
			'type'    => 'varchar',
			'size'    => 20,
			'require' => true,
			'qf_rules' => array(
				'regex' => array(
					'Techname must only consist of chars in a-z, A-Z, 0-9, \'-\' and \'_\'!',
					'/^[\w|-|_]+$/' // @todo definy rules in separate method
				)
			),
			'qf_client' => true
		),
		'filename' => array(
			'type'    => 'varchar',
			'size'    => 50,
			'require' => true
		),
		'created' => array(
			'type'    => 'timestamp',
			'require' => true
		),
		'classname' => array(
			'type'    => 'varchar',
			'size'	=> 50,
			'require' => true
		)
	);

	public $idx = array(
		'module_id' => array(
			'type' => 'unique',
			'cols' => 'module_id'
		),
		'plg_name' => array(
			'type' => 'unique',
			'cols' => 'plg_name'
		)
	);

	public $sql = array(

		// multiple rows for a list
		'listallcolumns' => array(
			'select' => '*',
			'fetchmode' => DB_FETCHMODE_ASSOC
		),
		'list' => array(
			'select' => 'module_id,plg_name,created,classname,filename',
			'fetchmode' => DB_FETCHMODE_ASSOC
		),
		'listnames' => array(
			'select' => 'module_id, plg_name',
			'order' => 'plg_name ASC'
		)
			);

			public $uneditableElements = array (
		'module_id',
		'created');

	private static $uniqueInstance = null;

	protected $primaryKeyColumnName = 'module_id';

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
		parent::__construct($GLOBALS['db'],BcmsConfig::getInstance()->getTablename('plugins'));
	}

	/**
	 * handles filtering or values of special fields for insert or update
	 * @author ahe
	 */
	public function checkSpecialFields(&$p_aCols,$func) {

			if($func=='insert') $p_aCols['module_id'] = $this->nextID();
			$p_aCols['created'] = date('YmdHis');
	}

	public function getObject($id) {
		$this->sql['listallcolumns']['fetchmode'] = DB_FETCHMODE_ASSOC;
		return $this->select('listallcolumns','module_id = '.$id);
	}

	/**
	 *
	 *
	 * @param String $modname name or classname of a plugin
	 * @return boolean
	 * @author ahe
	 * @date 04.10.2006 22:07:03
	 */
	public function isPluginListedInDb($modname){
		$result = $this->select('listnames','plg_name LIKE '.$modname.' OR classname LIKE '.$modname );
		return (count($result)>0);
	}

}
?>