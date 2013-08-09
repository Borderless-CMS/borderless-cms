<?php /*
+----------------------------------------------------------------------------+
| B O R D E R L E S S   C M S                                                |
+----------------------------------------------------------------------------+
| (c) Copyright 2004 - 2006                                                  |
|      by goldstift (aheusingfeld@borderlesscms.de)                          |
+----------------------------------------------------------------------------+
*/
if(!defined('BORDERLESS')) exit;

class ModEntries_DAL extends DataAbstractionLayer {

	public $col = array(

		// unique row ID
		'me_id' => array(
			'type'    => 'integer',
			'require' => true,
			'qf_label' => 'PluginComponent-ID'
		),
		'techname' => array(
			'require' => true,
			'type'    => 'varchar',
			'size'    => 20,
			'qf_rules' => array(
				'regex' => array(
					'Techname must only consist of chars in a-z, A-Z, 0-9, \'-\' and \'_\'!',
					'/^[\w|-|_]+$/' // TODO use dictionary here
				)
			),
			'qf_client' => true
		),
		'fk_module' => array(
			'type'    => 'integer',
			'require' => true,
			'qf_label' => 'Plugin'
		),
		'func' => array(
			'type'    => 'varchar',
			'require' => true,
			'size'    => 40
		),
		'status' => array(
			'type'    => 'integer',
			'require' => true
		)
	);

	public $idx = array(
		'me_id' => array(
			'type' => 'unique',
			'cols' => 'me_id'
		)
	);

	public $sql = array(

		// multiple rows for a list
		'listallcolumns' => array(
			'fetchmode' => DB_FETCHMODE_ASSOC,
			'select' => '*',
		),
	);

	public $uneditableElements = array ('me_id');

	private static $uniqueInstance = null;
	protected $configInstance = null;

/*
 * Declaration of methods
 */
	/**
	 * Get the instance of this class. Holds its instance by itself - not via
	 * BcmsFactory!
	 *
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 * @date 15.10.2006 00:41:41
	 * @since 0.12
	 */
	public static function getInstance(){
		if(self::$uniqueInstance==null)
			self::$uniqueInstance = new self();
		return self::$uniqueInstance;
	}

	protected function __construct() {
		$this->configInstance = BcmsConfig::getInstance();
		parent::__construct($GLOBALS['db'],$this->configInstance->getTablename('modentries'));
		$this->sql['listentries'] = array(
			'fetchmode' => DB_FETCHMODE_ASSOC,
			'select' => 'plg_e.me_id, plg_e.techname, modul.techname AS modulename, '
				.'plg_e.func, plg_e.status ',
			'from' => $this->table.' AS plg_e, '
				.$this->configInstance->getTablename('plugins').' AS modul ',
			'order' => 'modul.techname, plg_e.techname',
			'where' => ' modul.module_id = plg_e.fk_module'
		);

		$this->sql['list_with_classname'] = array(
			'fetchmode' => DB_FETCHMODE_ASSOC,
			'select' => 'plg_e.techname, modul.techname AS modulename, ' .
					' modul.classname, modul.filename, ' .
					'plg_e.func, \'me\' as type',
			'from' => $this->table.' AS plg_e, '
				.$this->configInstance->getTablename('plugins').' AS modul ',
			'order' => 'modul.techname, plg_e.techname',
			'where' => ' modul.module_id = plg_e.fk_module'
		);
	}

	public function getForm($p_sFormName, $p_sSubmitButtonName,$p_sSubmitButtonText
		,$columns = null, $array_name = null, $args = array(),
		$clientValidate = null, $formFilters = null) {

		$this->addPluginList();
	    $this->col['status']['qf_vals'] = $this->configInstance->getTranslatedStatusList();

		$form =& parent::getForm($p_sFormName, $p_sSubmitButtonName,
				$p_sSubmitButtonText,$columns, $array_name, $args,
				$clientValidate, $formFilters);
		return $form;
	}

	/**
	 * creates an array of the currently "published" module entries
	 *
	 * @return array usable in PEAR::DB_TABLE forms; array( id => techname)
	 * @author ahe
	 * @date 29.01.2006 23:54:16
	 * @package htdocs/classes/plugins
	 * @project bcms_orga
	 */
	public function getPlugins() {
		$this->sql['modulelist'] = array(
			'fetchmode' => DB_FETCHMODE_ASSOC,
			'select' => 'me_id, techname',
			'where' => ' status >=100'
		);
		$modlist = $this->select('modulelist');

		for ($i=0; $i< count($modlist); $i++) {
			$plugins[$modlist[$i]['me_id']] = $modlist[$i]['techname'];
		}
		return $plugins;
	}

	/**
	 * Setzt die Liste der verfuegbaren Modultypen
	 *
	 */
	protected function addPluginList() {
		$plugins = PluginManager::getInstance()->getModel()->select('listnames');
		for ($index = 0; $index < count($plugins); $index++) {
			$type[$plugins[$index][0]] = $plugins[$index][1];
		}
		$this->col['fk_module']['qf_vals'] = $type;
	}

	/**
	 * handles filtering or values of special fields for insert or update
	 * @author ahe
	 */
	public function checkSpecialFields(&$p_aCols,$func) {

			if($func=='insert') $p_aCols['me_id'] = $this->nextID();
	}

	public function getObject($id) {
		$this->sql['listallcolumns']['fetchmode'] = DB_FETCHMODE_OBJECT;
		return $this->select('listallcolumns','me_id = '.$id);
	}

	public function getMainPluginById($id) {
		$retValues = $this->select('list_with_classname','me_id = '.$id);
		return $retValues[0];
	}
}
?>