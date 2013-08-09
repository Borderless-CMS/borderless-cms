<?php

/*
 * Adapt singleton pattern to all parallel classes!
 */

class File_DAL extends DataAbstractionLayer {

	// needed for use of Singleton
	protected static $uniqueInstance = null;

	public $col = array(

		// unique row ID
		'object_id' => array(
			'type'    => 'integer',
			'require' => true,
			'qf_label' => 'Objekt-ID'
		),
		'object_filename' => array(
			'type'    => 'varchar',
			'size'    => 255,
			'qf_label' => 'Dateiname',
			'qf_rules' => array(
				'maxlength' => array(
					'Der Inhalt darf maximal 255 Zeichen lang sein!',
					255
				)
			)
		),
		'object_folder' => array(
			'type'    => 'varchar',
			'size'    => 255,
			'qf_label' => 'Verzeichnis',
			'qf_rules' => array(
				'maxlength' => array(
					'Der Inhalt darf maximal 255 Zeichen lang sein!',
					255
				)
			)
		),
		'object_shortdesc' => array(
			'type'    => 'varchar',
			'size'    => 255,
			'qf_label' => 'Kurzbeschreibung',
			'qf_rules' => array(
				'maxlength' => array(
					'Der Inhalt darf maximal 255 Zeichen lang sein!',
					255
				)
			)
		),
		'object_longdesc' => array(
			'type'    => 'clob',
			'qf_label' => 'Langbeschreibung',
			'qf_type' => 'textarea',
			'qf_attrs'  => array(
				'rows' => 3,
				'cols' => 30
			 )
		),
		'object_type' => array(
			'type'    => 'varchar',
			'size'    => 50,
			'qf_label' => 'Kurzbeschreibung',
			'qf_rules' => array(
				'maxlength' => array(
					'Der Inhalt darf maximal 50 Zeichen lang sein!',
					50
				)
			)
		),
		'object_width' => array(
			'type'    => 'integer',
			'qf_label' => 'Breite'
		),
		'object_height' => array(
			'type'    => 'integer',
			'qf_label' => 'Hoehe'
		),
		'object_smallimage_filename' => array(
			'type'    => 'varchar',
			'size'    => 255,
			'qf_label' => 'Dateiname z.B. des groesseren Bildes',
			'qf_rules' => array(
				'maxlength' => array(
					'Der Inhalt darf maximal 255 Zeichen lang sein!',
					255
				)
			)
		),
		'object_origin' => array(
			'type'    => 'clob',
			'qf_label' => 'Herkunft des Objekts',
			'qf_type' => 'textarea',
			'qf_attrs'  => array(
				'rows' => 3,
				'cols' => 30
			 )
		),
		'object_author' => array(
			'type'    => 'varchar',
			'size'    => 255,
			'qf_label' => 'Name des Autors',
			'qf_rules' => array(
				'maxlength' => array(
					'Der Inhalt darf maximal 255 Zeichen lang sein!',
					255
				)
			)
		),
		'object_created' => array(
			'type'    => 'timestamp',
			'qf_label' => 'Erstellungsdatum'
		),
		'object_importdate' => array(
			'type'    => 'timestamp',
			'require' => true,
			'qf_label' => 'Importdatum'
		),
		'object_import_user' => array(
			'type'    => 'integer',
			'qf_label' => 'Importiert von'
		),
	);

	public $idx = array(
		'object_id' => array(
			'type' => 'unique',
			'cols' => 'object_id'
		)
	);

	public $sql = array(

		// multiple rows for a list
		'list_everything' => array(
			'select' => 'object_id, object_filename, object_folder, object_shortdesc, object_longdesc, object_type, object_width, object_height, object_smallimage_filename, object_origin, object_author, object_created, object_importdate, object_import_user',
			'order'  => 'object_importdate DESC',
			'fetchmode' => DB_FETCHMODE_ASSOC
		),
		'list_by_shortdesc' => array(
			'select' => 'object_id, object_filename, object_shortdesc, object_type, object_width, object_height',
			'order'  => 'object_importdate DESC',
			'fetchmode' => DB_FETCHMODE_ASSOC
		)
	);

	public $uneditableElements = array (
		'object_importdate','object_import_user');

	public $elementsToFreeze = array(
		'object_id','object_type','object_filename','object_folder',
		'object_smallimage_filename','object_width','object_height'
	);

	/**
	 * String to hold the techname of a category this plugin is assigned to
	 * @access protected
	 */
	protected $categoryTechname = null;

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
			self::$uniqueInstance = new self();
		return self::$uniqueInstance;
	}

	protected function __construct(){
		parent::__construct($GLOBALS['db'],BcmsConfig::getInstance()->getTablename('objects'));

		$this->sql['list_everything'] = array(
			'select' => 'object_id, object_filename, object_folder, ' .
					'object_shortdesc, object_longdesc, object_type, ' .
					'object_width, object_height, object_smallimage_filename, ' .
					'object_origin, object_author, object_created, ' .
					'object_importdate, CONCAT(user.nachname, \',\', user.vorname) as object_import_user, ' .
					'obj.object_import_user as object_import_user_id',
			'from' => $this->table.' as obj, ',
			'join' => BcmsConfig::getInstance()->getTablename('user').' as user ',
			'where' => ' obj.object_import_user = user.user_id ',
			'order'  => 'object_importdate DESC',
			'fetchmode' => DB_FETCHMODE_ASSOC
		);
	}

	/**
	 * handles filtering or values of special fields for insert or update
	 * @author ahe
	 */
	public function checkSpecialFields(&$p_aCols, $func) {

			if($func=='insert') $p_aCols['object_id'] = $this->nextID();
			$p_aCols['object_import_user'] = PluginManager::getPlgInstance('UserManager')->getLogic()->getUserID();
			$p_aCols['object_importdate'] = date('YmdHis');
/* 20051026 AHE: Wird erst gesetzt, wenn hidden fields mit IDs statt werten gefuellt werden
			$p_aCols['object_type'] = "";
			$p_aCols['object_filename'] = "";
			$p_aCols['object_smallimage_filename'] = "";
			if(is_file($p_aFile[0]) && file_exists($p_aFile[0]))
			{
				$p_aCols['object_type'] = filetype($p_sFile[0]);
				$p_aCols['object_filename'] = $p_sFile[0];
				$p_aCols['object_smallimage_filename'] = $p_sFile[1];
			}
*/
	}

	/**
	 * @author ahe
	 * @return array
	 */
	public function getList($offset=null,$limit=null,$where=null,$searchphrase=null, $order_by=null, $order_dir=null)
	{
		$this->sql['list_small'] = array(
			'select' => 'obj.object_id, obj.object_filename , object_shortdesc '
					.', obj.object_type , obj.object_importdate , user.username AS object_import_user',
			'from' => $this->table.' as obj, ',
			'join' => BcmsConfig::getInstance()->getTablename('user').' as user ',
			'where' => ' obj.object_import_user = user.user_id ',
			'order'  => 'object_filename ASC',
			'fetchmode' => DB_FETCHMODE_ASSOC
		);
		return parent::getList($offset,$limit,$where, $order_by,$order_dir,$searchphrase,'om.',' obj.','list_small');
	}

	protected function getSearchableFieldsArray()
	{
		return array(
			'object_filename' => 'LIKE',
			'object_folder' => 'LIKE',
			'object_shortdesc' => 'LIKE',
			'object_longdesc' => 'LIKE',
			'object_origin' => 'LIKE',
			'object_author' => 'LIKE'
		);
	}

	/**
	 * @author ahe
	 * @return array
	 */
	public function getObjectList($sqlname='list_everything')
	{
		return $this->select($sqlname);
	}

	public function getObject($id) {
		$row = $this->select('list_everything','object_id='.$id);
		return $row[0];
	}

	public function getObjectIdByWhere($where) {
		$this->sql['getId'] = array(
			'select' => 'object_id',
			'fetchmode' => DB_FETCHMODE_ASSOC
		);
		$row = $this->select('getId',$where);
		return $row[0];
	}

	public function getObjectFilenameById($id) {
		$this->sql['getFilename'] = array(
			'select' => 'object_filename, object_folder'
		);
		$row = $this->select('getFilename','object_id='.$id);
		return $row[0][1].$row[0][0];
	}

	public function getObjectTypeById($id) {
		$this->sql['getType'] = array(
			'select' => 'object_type'
		);
		$row = $this->select('getType','object_id='.$id);
		return $row[0][0];
	}

	public function getObjectFileByName($name) {
		$this->sql['getFile'] = array(
			'select' => 'object_filename, object_folder'
		);
		$row = $this->select('getFile','object_filename LIKE \''.$name.'%\'');
		return $row[0][1].$row[0][0];
	}

	// TODO Unbedingt Zustaendigkeiten besser verteilen! Plugin sollte sowas nicht machen muessen!
	public function getPluginsCatName() {
		if(empty($this->categoryTechname)) {
			$confInst = BcmsConfig::getInstance();
			$sql = 'SELECT cat.techname '.
				'FROM '.$confInst->getTablename('menu').' as cat ' .
				'JOIN '.$confInst->getTablename('modentries').' as modentries '.
				' ON cat.type = modentries.me_id '.
				'JOIN '.$confInst->getTablename('plugins').' as plugins '.
				' ON modentries.fk_module = plugins.module_id '.
				'WHERE plugins.classname = \'ObjectManager\'';
		 	$result = $GLOBALS['db']->query($sql);
			if ($result instanceof PEAR_ERROR)
				return BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_SELECT,
					BcmsSystem::SEVERITY_ERROR, 'getPluginsCatName()',__FILE__, __LINE__);

		 	$numrows = $result->numRows();
		 	if($numrows<1) return '';

			$record = $result->fetchRow(DB_FETCHMODE_ASSOC);
			$result->free();
			$this->categoryTechname = $record['techname'];
		}
		return $this->categoryTechname;
	}
}
?>