<?php

// basic classes

class FileConfig_DAL extends DataAbstractionLayer {

	public $col = array(

		// unique row ID
		'cat_id' => array(
			'type'    => 'integer',
			'require' => true
		),
		'add_right' => array(
			'type'    => 'integer',
			'require' => true
		),
		'view_details_right' => array(
			'type'    => 'integer',
			'require' => true
		),
		'edit_right' => array(
			'type'    => 'integer',
			'require' => true
		),
		'edit_own_right' => array(
			'type'    => 'integer',
			'require' => true
		),
		'change_status_right' => array(
			'type'    => 'integer',
			'require' => true
		),
		'del_right' => array(
			'type'    => 'integer',
			'require' => true
		),
		'change_size_right' => array(
			'type'    => 'integer',
			'require' => true
		),
		'folder' => array(
			'type'    => 'varchar',
			'size'    => 50,
			'required' => true
		),
		'files_per_page' => array(
			'type'    => 'integer',
			'require' => true
		),
		'order_by' => array(
			'type'    => 'varchar',
			'size'    => 40,
			'required' => true
		),
		'sort_direction' => array(
			'type'    => 'integer',
			'require' => true
		),
        'user_id' => array(
            'type'    => 'integer'
        ),
        'change_date' => array(
            'type'    => 'timestamp'
        )

	);

	public $idx = array(
		'cat_id' => array(
			'type' => 'unique',
			'cols' => 'cat_id'
		)
	);

	public $sql = array(

		'listallcolumns' => array(
			'select' => '*'
		),
	);

	public $uneditableElements = array (
		'user_id',
		'change_date');

	public $elementsToFreeze = array (
		'cat_id'
	);

	// needed for use of Singleton
	protected static $uniqueInstance = null;

/*
 * Declaration of methods
 */
	/**
	 * Get the instance of this class. Asks BcmsFactory for instance if not
	 * instantiated yet.
	 *
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 * @date 14.10.2006 23:12:41
	 * @since 0.12
	 */
	public static function getInstance(){
		if(self::$uniqueInstance==null)
			self::$uniqueInstance = new FileConfig_DAL();
		return self::$uniqueInstance;
	}

	protected function __construct() {
		parent::__construct($GLOBALS['db'],	BcmsConfig::getInstance()->getTablename('file_config'), true);
	}

	public function getForm($p_sFormName, $p_sSubmitButtonName,$p_sSubmitButtonText
		,$columns = null, $array_name = null, $args = array(),
		$clientValidate = null, $formFilters = null) {

		$this->addLabels('om.');
		$this->addColPresetValues();
		if(!PluginManager::getPlgInstance('UserManager')->hasRight('file_change_cat_filefolder'))
		{
			$this->elementsToFreeze[] = 'folder';
		}
		return parent::getForm($p_sFormName, $p_sSubmitButtonName,$p_sSubmitButtonText
			,$columns, $array_name, $args,$clientValidate, $formFilters);
	}

	protected function addColPresetValues() {
		$dictObj = Factory::getObject('Dictionary');
		$this->col['sort_direction']['qf_vals'] =
			array(41 => $dictObj->getTrans('ASC'), 42 => $dictObj->getTrans('DESC')); // URGENT use classifications for sort_direction
		$this->col['order_by']['qf_vals'] = array(
			'object_folder' =>$dictObj->getTrans('obj.object_folder'),
			'object_filename' =>$dictObj->getTrans('obj.object_filename'),
			'object_type' =>$dictObj->getTrans('obj.object_type'),
			'object_shortdesc' =>$dictObj->getTrans('obj.object_shortdesc'),
			'object_width' =>$dictObj->getTrans('obj.object_width'),
			'object_author' =>$dictObj->getTrans('obj.object_author'),
			'object_created' =>$dictObj->getTrans('obj.object_created'),
			'object_importdate' =>$dictObj->getTrans('obj.object_importdate')
		);

		$this->addRightList();
	}

	public function addRightList() {
		if(PluginManager::getInstance()->isPluginInstalled('RightManager')) {
			$rightlist = PluginManager::getPlgInstance('RightManager')->getRightList();
			$this->col['add_right']['qf_vals'] = $rightlist;
			$this->col['edit_right']['qf_vals'] = $rightlist;
			$this->col['edit_own_right']['qf_vals'] = $rightlist;
			$this->col['view_details_right']['qf_vals'] = $rightlist;
			$this->col['change_status_right']['qf_vals'] = $rightlist;
			$this->col['del_right']['qf_vals'] = $rightlist;
			$this->col['change_size_right']['qf_vals'] = $rightlist;
		}
	}

	/**
	 * handles filtering or values of special fields for insert or update
	 * @author ahe
	 */
	public function checkSpecialFields(&$p_aCols,$func,$p_iCatID=0) {

		if($func=='insert')
			$p_aCols['cat_id'] = (empty($p_iCatID)) ? $_SESSION['m_id'] : $p_iCatID;
		$p_aCols['user_id'] = PluginManager::getPlgInstance('UserManager')->getLogic()->getUserID();
		$p_aCols['change_date'] = date('YmdHis');
		if(array_key_exists('new_record', $p_aCols)) unset($p_aCols['new_record']);
	}

	public function getObject($id) {
		$this->sql['listallcolumns']['fetchmode'] = DB_FETCHMODE_ASSOC;
		$array = $this->select('listallcolumns','cat_id = '.$id);
		return $array[0];
	}

 }
?>