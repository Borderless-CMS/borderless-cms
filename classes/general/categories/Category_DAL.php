<?php
/**
 * Data Abstraction Layer for menu_table
 *
 * TODO Adapt singleton pattern to all parallel classes!
 * @version $ID: Category_DAL.php version
 * @package files/classes/menu
 * @author ahe
 * @date 18.01.2006 09:51:38
 */
class Category_DAL extends DataAbstractionLayer {

	// needed for use of Singleton
	protected static $uniqueInstance = null;

	public $col = array(

		// unique row ID
		'cat_id' => array(
			'type'    => 'integer',
			'require' => true
		),
		'categoryname' => array(
			'type'    => 'varchar',
			'required' => true,
			'size'    => 50,
			'qf_rules' => array(
				'maxlength' => array(
					'Der Inhalt darf maximal 50 Zeichen lang sein!',
					50
				)
			)
		),
		'techname' => array(
			'type'    => 'varchar',
			'size'    => 50,
			'required' => true,
			'qf_rules' => array(
				'maxlength' => array(
					'Der Inhalt darf maximal 50 Zeichen lang sein!',
					50
				)
			),
			'qf_client' => true
		),
		'categorylink_title' => array(
			'type'    => 'varchar',
			'size'    => 255,
			'qf_rules' => array(
				'maxlength' => array(
					'Der Inhalt darf maximal 255 Zeichen lang sein!',
					255
				)
			)
		),
		'icon_src' => array(
			'type'    => 'varchar',
			'size'    => 80,
			'qf_rules' => array(
				'maxlength' => array(
					'Der Inhalt darf maximal 80 Zeichen lang sein!',
					80
				)
			)
		),
		'root_id' => array(
			'type'    => 'integer',
			'required' => true
		),
		'lft' => array(
			'type'    => 'integer',
			'required' => true
		),
		'rgt' => array(
			'type'    => 'integer',
			'required' => true
		),
		'user_only' => array(
			'type'    => 'integer',
			'qf_type' => 'select',
			'required' => true
		),
		'fk_view_right' => array(
			'type'    => 'integer',
			'qf_type' => 'select',
			'required' => true
		),
		'fk_edit_right' => array(
			'type'    => 'integer',
			'qf_type' => 'select',
			'required' => true
		),
		'fk_delete_right' => array(
			'type'    => 'integer',
			'qf_type' => 'select',
			'required' => true
		),
		'fk_plg_conf_right' => array(
			'type'    => 'integer',
			'qf_type' => 'select',
			'required' => true
		),
		'viewable4all' => array(
			'type'    => 'integer',
			'qf_type' => 'select',
			'required' => true
		),
		'writeable4all' => array(
			'type'    => 'integer',
			'qf_type' => 'select',
			'required' => true
		),
		'commentable' => array(
			'type'    => 'integer',
			'qf_type' => 'select',
			'required' => true
		),
		'show_cat_desc' => array(
			'type'    => 'integer',
			'qf_type' => 'select',
			'required' => true
		),
		'show_pathway' => array(
			'type'    => 'integer',
			'qf_type' => 'select',
			'required' => true
		),
		'show_opt_plugins' => array(
			'type'    => 'integer',
			'qf_type' => 'select',
			'required' => true
		),
		'use_ssl' => array(
			'type'    => 'integer',
			'qf_type' => 'select',
			'required' => true
		),
		'type' => array(
			'type'    => 'varchar',
			'size'	=> 30,
			'required' => true,
			'qf_type' => 'select'
		),
		'description' => array(
			'type'    => 'clob',
			'qf_type' => 'textarea',
			'qf_attrs'  => array(
				'rows' => 3,
				'cols' => 30
			 )
		),
		'accesskey' => array(
			'type'    => 'varchar',
			'size'    => 1,
			'qf_rules' => array(
				'maxlength' => array(
					'Der Inhalt darf maximal 1 Zeichen lang sein!',
					1
				)
			)
		),
		'meta_description' => array(
			'type'    => 'varchar',
			'size'    => 200,
			'qf_rules' => array(
				'maxlength' => array(
					'Der Inhalt darf maximal 200 Zeichen lang sein!',
					200
				)
			)
		),
		'meta_keywords' => array(
			'type'    => 'varchar',
			'size'    => 255,
			'qf_rules' => array(
				'maxlength' => array(
					'Der Inhalt darf maximal 255 Zeichen lang sein!',
					255
				)
			)
		),
		'additional_css' => array(
			'type'    => 'clob',
			'qf_type' => 'textarea',
			'qf_attrs'  => array(
				'rows' => 3,
				'cols' => 30
			 )
		),
		'status' => array(
			'type'    => 'integer',
			'required' => true
		),
		'publishing_date' => array(
			'type'    => 'timestamp',
			'require' => true
		),
		'edit_date' => array(
			'type'    => 'timestamp'
		),
		'editor_id' => array(
			'type'    => 'integer'
		)
	);

	public $idx = array(
		'cat_id' => 'unique',
		'techname' => 'unique',
		'accesskey' => 'unique',
		'status' => 'normal',
		'lr' => array(
			'type' => 'normal',
			'cols' => array('lft','rgt')
		),
		'lr_and_uo' => array(
			'type' => 'normal',
			'cols' => array('lft','rgt','user_only')
		),
		'lr_and_uo' => array(
			'type' => 'normal',
			'cols' => array('lft','rgt','status')
		),
		'lr_uo_and_status' => array(
			'type' => 'normal',
			'cols' => array('lft','rgt','user_only','status')
		)
	);

	public $sql = array(

		// multiple rows for a list
		'list_everything' => array(
			'select' => '*',
			'order'  => 'lft ASC',
			'fetchmode' => DB_FETCHMODE_ASSOC
		),
		'list_for_move' => array(
			'select' => 'cat_id, techname, root_id, lft, rgt',
			'order'  => 'lft DESC',
			'fetchmode' => DB_FETCHMODE_ASSOC
		),
		'list_id' => array(
			'select' => 'cat_id',
			'fetchmode' => DB_FETCHMODE_ASSOC
		),
		'list_type' => array(
			'select' => 'type',
			'fetchmode' => DB_FETCHMODE_ASSOC
		)
	);

	public $uneditableElements = array (
		'editor_id'
	);

	public $elementsToFreeze = array (
		'cat_id',
		'edit_date',
		'lft',
		'rgt',
		'root_id'
	);


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

	protected function __construct(){
		parent::__construct($GLOBALS['db'],BcmsConfig::getInstance()->getTablename('menu'));
		$this->col['techname']['qf_rules']['regex'] = array(
					'Der technische Rubrikname darf nur aus a-z, A-Z, 0-9, \'-\'(Bindestrich) und \'_\'(Unterstrich) bestehen!',
					BcmsSystem::getTechnameRegex() 					// TODO use dictionary here
				);
		// create statement for listview
		$this->sql['list_full_tree'] = array(
			'select' => 'a.*, COUNT(*) AS level, (a.rgt - a.lft -1)/2 AS NoOfChildren ',
			'from' => $this->table.' AS a, '.$this->table.' AS b ',
			'where' => '(a.lft BETWEEN b.lft AND b.rgt)',
			'group' => 'a.lft',
			'order'  => 'a.lft ASC',
			'fetchmode' => DB_FETCHMODE_ASSOC
		);
		$this->sql['list_small_tree'] = array(
			'select' => 'a.cat_id, a.categoryname, a.techname, a.accesskey, ' .
					'a.categorylink_title, a.root_id, a.lft, a.rgt, a.use_ssl, ' .
					'a.icon_src, COUNT(*) AS level, ' .
					'(a.rgt - a.lft -1)/2 AS NoOfChildren, ' .
					'a.fk_view_right, a.fk_edit_right ',
			'from' => $this->table.' AS a, '.$this->table.' AS b ',
			'where' => '(a.lft BETWEEN b.lft AND b.rgt)',
			'group' => 'a.lft',
			'order'  => 'a.lft ASC',
			'fetchmode' => DB_FETCHMODE_ASSOC
		);
		$this->setLabels();
	}

	protected function addColPresetValues() {
	    $this->col['status']['qf_vals'] = BcmsConfig::getInstance()->getTranslatedStatusList();
		// assign module entries list to menu.type
		$meObj = PluginManager::getInstance()->getModEntries_DAL();
		$dictObj = Factory::getObject('Dictionary');
		$this->col['type']['qf_vals'] = $meObj->getPlugins();
		$yes_no_array = $dictObj->getModel()->getYesNoArray();
		$this->col['user_only']['qf_vals'] = $yes_no_array;
		$this->col['commentable']['qf_vals'] = $yes_no_array;
		$this->col['show_cat_desc']['qf_vals'] = $yes_no_array;
		$this->col['show_pathway']['qf_vals'] = $yes_no_array;
		$this->col['show_opt_plugins']['qf_vals'] = $yes_no_array;
		$this->col['use_ssl']['qf_vals'] = $yes_no_array;
		$this->col['viewable4all']['qf_vals'] = $yes_no_array;
		$this->col['writeable4all']['qf_vals'] = $yes_no_array;
		$this->col['publishing_date']['qf_setvalue'] = date('Y.m.d H:i:s');
		if(PluginManager::getInstance()->isPluginInstalled('RightManager')) {
			$rightlist = PluginManager::getPlgInstance('RightManager')->getRightList();
			$this->col['fk_view_right']['qf_vals'] = $rightlist;
			$this->col['fk_edit_right']['qf_vals'] = $rightlist;
			$this->col['fk_delete_right']['qf_vals'] = $rightlist;
			$this->col['fk_plg_conf_right']['qf_vals'] = $rightlist;
		}
	}

	/**
	 * gets translations of the tablefields and sets them as formfield labels
	 */
	public function setLabels() {
		$fieldnames = array_keys($this->col);
		foreach($fieldnames as $key) {
			$trans = null;
			if(!in_array($key, $this->uneditableElements))
				$trans = stripslashes(Factory::getObject('Dictionary')->getTrans('cat.'.$key));
			if($trans==null) $trans = $key;
			$this->col[$key]['qf_label'] = $trans.':&nbsp;';
		}
	}

	/**
	 * handles filtering or values of special fields for insert or update
	 * @author ahe
	 */
	public function checkSpecialFields(&$p_aCols, $func) {

		if($func=='insert') $p_aCols['cat_id'] = $this->nextID();
		$p_aCols['editor_id'] = PluginManager::getPlgInstance('UserManager')->getLogic()->getUserID();
		$p_aCols['edit_date'] = date('YmdHis');
	}

	public function delete($menuID,$currMenuObj)
	{
		if(!PluginManager::getPlgInstance('UserManager')->hasRight($currMenuObj->getDeleteRight()))
			return(false);
		$row=$this->select('list_for_move','(cat_id = '.$menuID.')');
		$row=$row[0];

		// cancel if result is not a number
		if(is_nan($row['lft'])) return false;

		if(parent::delete('(lft BETWEEN '.$row['lft'].' AND '.$row['rgt'].')')){
			return $this->updateNodes('-2',$row['rgt']);
		} else {
			return(false);
		}
	}

	public function getForm($p_sFormName, $p_sSubmitButtonName,$p_sSubmitButtonText
		,$columns = null, $array_name = null, $args = array(),
		$clientValidate = null, $formFilters = null) {

		$this->addColPresetValues();
		return parent::getForm($p_sFormName, $p_sSubmitButtonName,$p_sSubmitButtonText
			,$columns, $array_name, $args,$clientValidate, $formFilters);
	}

	/**
	 *
	 *
	 * @param char	mode	can be '+' or '-' plus a number; e.g. '+2'
	 * @param int	right	is rgt value of node
	 * @param int	left	is lft value of node (optional)
	 * @return return_type
	 * @author ahe
	 * @date 01.12.2005 21:18:01
	 */
	public function updateNodes($mode, $right, $menuId=null)
	{
		$this->autoRecast(false);
		$this->autoValidUpdate(false);
		$returnVal = true;
		if($menuId != null) {
			$sql = 'UPDATE '.$this->table.' SET lft=lft'.$mode
				.', rgt=rgt'.$mode.' WHERE cat_id = '.$menuId;
			$ret = $this->db->simpleQuery($sql);
/*
			$ret = $this->update(
				array('lft' => 'lft'.$mode,	'rgt' =>'rgt'.$mode),
				'cat_id = '.$menuId
			);
			*/
			$returnVal = ($ret) ? $returnVal : false;
		} else {
			$ret = $this->db->simpleQuery('UPDATE '.$this->table.' SET ' .
					'lft = lft'.$mode.' WHERE lft > '.$right
			);
/*
			$ret = $this->update(
				array('lft' => '`lft`'.$mode),
				'lft > '.$right
			);
*/
			$returnVal = ($ret) ? $returnVal : false;
			$ret = $this->db->simpleQuery('UPDATE '.$this->table.' SET ' .
					'rgt = rgt'.$mode.' WHERE rgt > '.$right
			);
/*
			$ret = $this->update(
				array('rgt' => '\'rgt'.$mode.'\''),
				'rgt > '.$right
			);
*/
			$returnVal = ($ret) ? $returnVal : false;
		}
		$this->autoRecast(true);
		$this->autoValidUpdate(true);
		return $returnVal;
	}


	/**
	 * @author ahe
	 * @return array
	 */
	public function getObjectList($sqlname='list_everything')
	{
		return $this->select($sqlname);
	}

	public function getTreeList($techname=null,$allmenues=false) {

		$where = $this->getWhereForTreelist($techname);
		if($allmenues==false) $where .= ' AND (a.status >= '
			.$GLOBALS['ARTICLE_STATUS']['published'].')';// TODO use classifications for status!
		return $this->select('list_full_tree',$where);
	}

	public function getSmallTreeList($techname=null,$allmenues=false,$isLoggedIn=false) {
		$where = $this->getWhereForTreelist($techname);
		if(!$allmenues) $where .= ' AND (a.status >= '
			.$GLOBALS['ARTICLE_STATUS']['published'].')';// TODO use classifications for status!
		if(!$isLoggedIn)
			$where .= ' AND (a.user_only = 0) ';

		return $this->select('list_small_tree',$where);
	}

	/**
	 * get parent menues lft and rgt values and create an additional where
	 * condition out of it.
	 *
	 * @author ahe
	 * @param string techname the techname of the parent menu
	 * @return string where condition
	 */
	public function getWhereForTreelist($techname,$prefix='a.',$offset=1)
	{
		if($techname!=null) {
			$parentMenuArray = $this->getMenuByTechname($techname);
			return ' '.$prefix.'lft BETWEEN '.($parentMenuArray['lft']+$offset)
				.' AND '.$parentMenuArray['rgt'];
		} else {
			return ' 1=1 ';
		}
	}

	public function getSiblingObject($rootId, $where, $order) {
		$this->sql['list_for_move']['order'] = 'lft '.$order;
		$retObj = $this->select('list_for_move','root_id = '.$rootId
			.' AND '.$where);
		if(!is_array($retObj[0]))
			return false;
		else
			return $retObj[0];
	}

	/**
	 * returns the
	 *
	 * @param integer $rootId the root_id of the current menu
	 * @return mixed array if select is good, otherwise false
	 * @package files/classes/menu
	 * @author ahe
	 * @date 18.01.2006 09:52:32
	 */
	public function getParentObject($rootId) {
		$retObj = $this->select('list_for_move','cat_id = '.$rootId);
		if(!is_array($retObj[0]))
			return false;
		else
			return $retObj[0];
	}

	public function getChildIds($lft,$rgt) {
		$this->sql['list_id']['order'] = 'lft ASC';
		return $this->select('list_id','lft>'.$lft.' AND lft BETWEEN '.$lft.' AND '.$rgt);
	}

	public function getMoveObject($where) {
		$retObj = $this->select('list_for_move',$where);
		if(!is_array($retObj[0]))
			return false;
		else
			return $retObj[0];
	}

	public function getMenuByTechname($techname) {
		return $this->getMoveObject('techname=\''.$techname.'\'');
	}

	public function getObject($id) {
		$retObj = $this->select('list_everything','cat_id = '.$id);
		return $retObj[0];
	}

	/**
	 *
	 *
	 * @param int $menuId
	 * @return mixed type_id:int or db_error:object
	 * @author ahe
	 * @date 29.01.2006 00:37:23
	 * @package htdocs/classes/menu
	 * @project bcms_orga
	 */
	public function getTypeById($id) {
		$retObj = $this->select('list_type','cat_id = '.$id);
		if(is_array($retObj) && isset($retObj[0]['type']))
			return $retObj[0]['type'];
		else
			return $retObj;
	}

	/**
	 *
	 *
	 * @param int $menuId
	 * @return mixed type_id:int or db_error:object
	 * @author ahe
	 * @date 29.01.2006 00:37:23
	 * @package htdocs/classes/menu
	 * @project bcms_orga
	 */
	public function getIdByName($techname) {
	    $mname_prepared = BcmsFactory::getInstanceOf('Parser')->filterTechName($techname);
		$this->sql['list_id']  = array(
			'select'=> 'cat_id',
			'fetchmode' => DB_FETCHMODE_ASSOC
		);
		$retObj = $this->select('list_id','techname = \''.$mname_prepared.'\'');
		if(is_array($retObj) && isset($retObj[0]['cat_id']))
			return $retObj[0]['cat_id'];
		else
			return $retObj;
	}

	/**
	 *
	 *
	 * @param int $menuId
	 * @return string
	 * @author ahe
	 * @date 29.01.2006 00:37:23
	 * @package htdocs/classes/menu
	 * @project bcms_orga
	 */
	public function getTechnameById($id) {
		$this->sql['list_id']  = array(
			'select'=> 'techname',
			'fetchmode' => DB_FETCHMODE_ASSOC
		);
		$retObj = $this->select('list_for_move','cat_id = '.$id);
		if(is_array($retObj) && isset($retObj[0]['techname']))
			return $retObj[0]['techname'];
		else
			return null;
	}
 }
?>