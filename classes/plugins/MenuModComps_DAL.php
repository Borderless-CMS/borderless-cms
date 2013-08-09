<?php

// basic classes

class MenuModComps_DAL extends DataAbstractionLayer {

	public $col = array(

		// unique row ID
		'mmc_id' => array(
			'type'    => 'integer',
			'require' => true
		),
		'fk_cat' => array(
			'type'    => 'integer',
			'qf_type' => 'select',
			'require' => true
		),
		'fk_modcomp' => array(
			'type'    => 'integer',
			'qf_type' => 'select',
			'require' => true
		),
		'order_num' => array(
			'type' => 'integer',
			'require' => true
		)
	);

	public $idx = array(
		'prim' => array(
			'type' => 'unique',
			'cols' => array('fk_cat', 'fk_modcomp')
		)
	);

	public $sql = array(

		// multiple rows for a list
		'listallcolumns' => array(
			'select' => '*'
		),
	);

	public $uneditableElements = array ('mmc_id');

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
		parent::__construct($GLOBALS['db'],$this->configInstance->getTablename('menumodcomp'));
		$this->sql['list_with_classname'] = array(
			'fetchmode' => DB_FETCHMODE_ASSOC,
			'select' => 'mc.compname as techname, modul.techname AS modulename, ' .
					'modul.classname, modul.filename, ' .
					'mc.func, \'mmc\' as type',
			'from' => $this->table.' AS mmc, '
				.$this->configInstance->getTablename('modcomps').' AS mc, '
				.$this->configInstance->getTablename('plugins').' AS modul ',
			'where' => ' modul.module_id = mc.fk_module AND ' .
					'mmc.fk_modcomp = mc.mc_id',
			'order' => 'order_num ASC'
		);
		$this->sql['listwithmenu'] = array(
			'fetchmode' => DB_FETCHMODE_ASSOC,
			'select' => 'mmc.mmc_id, mc.compname as modcomp, menu.techname AS categoryname, ' .
					'mc.func ',
			'from' => $this->table.' AS mmc, '
				.$this->configInstance->getTablename('modcomps').' AS mc, '
				.$this->configInstance->getTablename('menu').' AS menu ',
			'where' => ' menu.cat_id = mmc.fk_cat AND ' .
					' mmc.fk_modcomp = mc.mc_id ',
			'order' => ' mmc.order_num ASC'
		);
		$this->sql['listwithoutmenu'] = array(
			'fetchmode' => DB_FETCHMODE_ASSOC,
			'select' => 'mmc.mmc_id, mc.compname as modcomp, \''
					.Factory::getObject('Dictionary')->getTrans('all')
					.'\' AS categoryname, ' .
					'mc.func ',
			'from' => $this->table.' AS mmc, '
				.$this->configInstance->getTablename('modcomps').' AS mc ',
			'where' => ' mmc.fk_modcomp = mc.mc_id ',
			'order' => ' mmc.order_num ASC'
		);
	}

	public function getList($offset=null,$limit=null,$where=null,$searchphrase=null)
	{
		$this->addLabels('plgmmc.');
		if(empty($where)){
			$where = $this->buildWhereString($searchphrase,' mmc.');
		} else {
			$where .= ' AND ('.$this->buildWhereString($searchphrase,' mmc.').')';
		}
		$rows1 = $this->select('listwithmenu',$where,null,$offset,$limit);
		$rows2 = $this->select('listwithoutmenu',$where,null,$offset,$limit);
		$rows = array_merge($rows1,$rows2);
		return $rows;
	}

	public function getForm($p_sFormName, $p_sSubmitButtonName,$p_sSubmitButtonText
		,$columns = null, $array_name = null, $args = array(),
		$clientValidate = null, $formFilters = null) {

		$this->addMenuList();
		$this->addModCompList();
		$form =& parent::getForm($p_sFormName, $p_sSubmitButtonName,
				$p_sSubmitButtonText,$columns, $array_name, $args,
				$clientValidate, $formFilters);
		return $form;
	}

  protected function addMenuList()
  {
    // get menu tree
    $allMenues = PluginManager::getPlgInstance('CategoryManager')->getLogic()->getMenuTreeList();
    $menues[0] = Factory::getObject('Dictionary')->getTrans('all');
    for ($i = 0; $i < count($allMenues); $i++) {
      // add indent in front of menu names
      $spaces = '';
      for ($k = 0; $k < ($allMenues[$i]['level']-1)*2; $k++) {
        $spaces .= '&nbsp;';
      }
      $menues[$allMenues[$i]['cat_id']] =
        $spaces.$allMenues[$i]['categoryname'];
    }
    $this->col['fk_cat']['qf_vals'] = $menues;
  }

	/**
	 * Setzt die Liste der verfuegbaren Modultypen
	 *
	 */
	protected function addModCompList() {
		$moduleObj = PluginManager::getInstance()->getModComps_DAL();
		$plugins = $moduleObj->select('listnames');
		for ($index = 0; $index < count($plugins); $index++) {
			$type[$plugins[$index][0]] = $plugins[$index][1];
		}
		$this->col['fk_modcomp']['qf_vals'] = $type;
	}

	/**
	 * handles filtering or values of special fields for insert or update
	 * @author ahe
	 */
	public function checkSpecialFields(&$p_aCols,$func,$p_iPluginId=0) {

			if($func=='insert') $p_aCols['mmc_id'] = $this->nextID();
	}

	public function getObject($id) {
		$this->sql['listallcolumns']['fetchmode'] = DB_FETCHMODE_OBJECT;
		return $this->select('listallcolumns','mmc_id = '.$id);
	}

	public function getPluginsByMenuId($id) {
		return $this->select('list_with_classname'
			,'mmc.fk_cat = 0 OR mmc.fk_cat = '.$id);
	}

}
?>