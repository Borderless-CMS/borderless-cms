<?php

// basic classes

class ContentConfig_DAL extends DataAbstractionLayer {

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
		'content_order_by' => array(
			'type'    => 'varchar',
			'size'    => 40,
			'required' => true
		),
		'sort_direction' => array(
			'type'    => 'integer',
			'require' => true
		),
		'no_of_comment_per_page' => array(
			'type'    => 'integer',
			'require' => true
		),
		'hide_comments_on_show' => array(
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
			self::$uniqueInstance = new ContentConfig_DAL();
		return self::$uniqueInstance;
	}

	protected function __construct() {
		parent::__construct($GLOBALS['db'],	BcmsConfig::getInstance()->getTablename('content_config'), true);
	}

	public function getForm($p_sFormName, $p_sSubmitButtonName,$p_sSubmitButtonText
		,$columns = null, $array_name = null, $args = array(),
		$clientValidate = null, $formFilters = null) {

		$this->addLabels('cont.');
		$this->addColPresetValues();
		return parent::getForm($p_sFormName, $p_sSubmitButtonName,$p_sSubmitButtonText
			,$columns, $array_name, $args,$clientValidate, $formFilters);
	}

	protected function addColPresetValues() {
		$dictObj = Factory::getObject('Dictionary');
		$this->col['sort_direction']['qf_vals'] =
			array(41 => $dictObj->getTrans('ASC'), 42 => $dictObj->getTrans('DESC'));
		$this->col['content_order_by']['qf_vals'] = array(
			'publish_begin' =>$dictObj->getTrans('sr.PublishBegin'),
			'publish_end' =>$dictObj->getTrans('sr.PublishEnd'),
			'heading' =>$dictObj->getTrans('sr.Article'),
			'created' =>$dictObj->getTrans('sr.CreationDate')
		);
		$this->col['hide_comments_on_show']['qf_vals'] = $dictObj->getModel()->getYesNoArray();

		$this->addRightList();
	}

	public function addRightList() {
		if(PluginManager::getInstance()->isPluginInstalled('RightManager')) {
			$rightlist = PluginManager::getPlgInstance('RightManager')->getRightList();
			$this->col['add_right']['qf_vals'] = $rightlist;
			$this->col['edit_right']['qf_vals'] = $rightlist;
			$this->col['edit_own_right']['qf_vals'] = $rightlist;
			$this->col['change_status_right']['qf_vals'] = $rightlist;
			$this->col['del_right']['qf_vals'] = $rightlist;
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