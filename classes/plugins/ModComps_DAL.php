<?php

// basic classes

class ModComps_DAL extends DataAbstractionLayer {

	public $col = array(

		// unique row ID
		'mc_id' => array(
			'type'    => 'integer',
			'require' => true,
			'qf_label' => 'PluginSpecification-ID'
		),
		'fk_module' => array(
			'type'    => 'integer',
			'require' => true,
			'qf_label' => 'Plugin'
		),
		'fk_type' => array(
			'type'    => 'integer',
			'require' => true,
			'qf_label' => 'Modul-Typ'
		),
		'compname' => array(
			'type'    => 'varchar',
			'size'    => 40,
			'qf_rules' => array(
				'regex' => array(
					'Compname must only consist of chars in a-z, A-Z, 0-9, \'-\' and \'_\'!',
					'/^[\w|-|_]+$/' // TODO use dictionary here
				)
			),
			'qf_client' => true
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
		'mc_id' => array(
			'type' => 'unique',
			'cols' => 'mc_id'
		)
	);

	public $sql = array(

		// multiple rows for a list
		'listallcolumns' => array(
			'select' => '*'
		),
		'listnames' => array(
			'select' => 'mc_id, compname'
		),
	);

	public $uneditableElements = array ('mc_id');

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
	 * @since 0.14
	 */
	public static function getInstance(){
		if(self::$uniqueInstance==null)
			self::$uniqueInstance = new self();
		return self::$uniqueInstance;
	}

	protected function __construct() {
		$this->configInstance = BcmsConfig::getInstance();
		parent::__construct($GLOBALS['db'],$this->configInstance->getTablename('modcomps'));
		$this->sql['listentries'] = array(
			'fetchmode' => DB_FETCHMODE_ASSOC,
			'select' => 'mc.mc_id, modul.techname AS modulename, mc.fk_type, ' .
					'mc.compname, mc.func, mc.status as status',
			'from' => $this->table.' AS mc, '
				.$this->configInstance->getTablename('plugins').' AS modul ',
			'order' => 'modul.techname, mc.compname',
			'where' => ' modul.module_id = mc.fk_module'
		);
	}

	public function getForm($p_sFormName, $p_sSubmitButtonName,$p_sSubmitButtonText
		,$columns = null, $array_name = null, $args = array(),
		$clientValidate = null, $formFilters = null) {

		$this->addPluginList();
		$this->addTypeList();
	    $this->col['status']['qf_vals'] = $this->configInstance->getTranslatedStatusList();

		$form =& parent::getForm($p_sFormName, $p_sSubmitButtonName,
				$p_sSubmitButtonText,$columns, $array_name, $args,
				$clientValidate, $formFilters);
		return $form;
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
	 * Setzt die Liste der verfuegbaren ModulTypen
	 * TODO use classifications!
	 */
	protected function addTypeList() {
		$this->sql['status'] = array(// classifications
				'select' => 'class.number, class.name',
				'from' => $this->configInstance->getTablename('classification').' as class, ',
				'join' => $this->configInstance->getTablename('systemschluessel').' as sk ',
				'where' => ' class.fk_syskey = sk.id_schluessel AND sk.schluesseltyp = \'modul_typ\'',
				'order' => ' class.number ASC'
		);
		$status_arr = $this->select('status');

		for ($i=0; $i< count($status_arr); $i++) {
			$status[$status_arr[$i][0]] = $status_arr[$i][1];
		}
		$this->col['fk_type']['qf_vals'] = $status;
	}

	/**
	 * handles filtering or values of special fields for insert or update
	 * @author ahe
	 */
	public function checkSpecialFields(&$p_aCols,$func,$p_iPluginId=0) {

			if($func=='insert') $p_aCols['mc_id'] = $this->nextID();
	}

	public function getObject($id) {
		$this->sql['listallcolumns']['fetchmode'] = DB_FETCHMODE_OBJECT;
		return $this->select('listallcolumns','mc_id = '.$id);
	}

}
?>