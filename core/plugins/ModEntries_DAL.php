<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * Dataabstractionlayer class for so called entry plugins (see online documentation)
 *
 * @since 0.11
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @class ModEntries_DAL
 * @ingroup plugins
 * @package plugins
 */
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
					'/^[\w|-|_]+$/' // @todo use dictionary here
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
		'status_id' => array(
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
	protected $primaryKeyColumnName = 'me_id';
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
				.'plg_e.func, plg_e.status_id ',
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
	    $this->col['status_id']['qf_vals'] = $this->configInstance->getTranslatedStatusList();

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
	 */
	public function getPlugins() {
		$this->sql['modulelist'] = array(
			'fetchmode' => DB_FETCHMODE_ASSOC,
			'select' => 'me_id, techname',
			'where' => ' status_id >=100'
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
    if(!is_int((int)$id) || is_null($id)) return null;

		$retValues = $this->select('list_with_classname','me_id = '.$id);
		return $retValues[0];
	}
}
?>