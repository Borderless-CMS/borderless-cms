<?php

// basic classes

class Plugin_DAL extends DataAbstractionLayer {

	public $col = array(

		// unique row ID
		'module_id' => array(
			'type'    => 'integer',
			'require' => true
		),
		'name' => array(
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
					'/^[\w|-|_]+$/' // TODO definy rules in separate method
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
		'name' => array(
			'type' => 'unique',
			'cols' => 'name'
		)
	);

	public $sql = array(

		// multiple rows for a list
		'listallcolumns' => array(
			'select' => '*',
			'fetchmode' => DB_FETCHMODE_ASSOC
		),
		'list' => array(
			'select' => 'module_id,name,created,classname,filename',
			'fetchmode' => DB_FETCHMODE_ASSOC
		),
		'listnames' => array(
			'select' => 'module_id, name',
			'order' => 'name ASC'
		)
	);

	public $uneditableElements = array (
		'module_id',
		'created');

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
	 * @package htdocs/classes/plugins
	 */
	public function isPluginListedInDb($modname){
		$result = $this->select('listnames','name LIKE '.$modname.' OR classname LIKE '.$modname );
		return (count($result)>0);
	}

}
?>