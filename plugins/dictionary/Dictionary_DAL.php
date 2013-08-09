<?php
class Dictionary_DAL extends DataAbstractionLayer {

	public $col = array(
		// unique row ID
		'dict_id' => array(
			'type'    => 'integer',
			'require' => true
		),
		'deftrans' => array(
			'type'    => 'varchar',
			'size'    => 255,
			'require' => true
		),
		'type_classify_id' => array(
			'type'    => 'integer',
			'require' => true,
			'qf_type' => 'select'
		),
		'de' => array(
			'type' => 'clob',
			'qf_type' => 'textarea',
			'qf_attrs'  => array(
				'rows' => 1,
				'cols' => 30
			 )
		),
		'en' => array(
			'type' => 'clob',
			'qf_type' => 'textarea',
			'qf_attrs'  => array(
				'rows' => 1,
				'cols' => 30
			 )
		)
	);

	public $idx = array(
		'dict_id' => array(
			'type' => 'unique',
			'cols' => 'dict_id'
		),
		'deftrans' => array(
			'type' => 'unique',
			'cols' => 'deftrans'
		)
	);

	public $sql = array(

		// multiple rows for a list
		'listallcolumns' => array(
			'select' => '*',
			'order' => 'deftrans'
		),
		'listnames' => array(
			'select' => 'dict_id, deftrans'
		)
	);

	public $uneditableElements = array ();
	public $elementsToFreeze = array ('dict_id');
	protected $yes_no_array = null;
	protected $cache = array();

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
		parent::__construct($GLOBALS['db'],BcmsConfig::getInstance()->getTablename('dict'));

		$this->langNum = BcmsConfig::getInstance()->langKey;
		$this->sql['getTrans'] = array(
				'select' => 'dict.'
					.$this->langNum.' as trans',
				'from' => $this->table.' as dict ',
				'fetchmode' => DB_FETCHMODE_ASSOC
		);
		// set preset values
		$this->yes_no_array = array(
			0 => $this->getTrans('no'),
			1 => $this->getTrans('yes')
		);
	}

	/**
	 * Returns array with translation of 0=no and 1=yes
	 *
	 * @return array
	 * @author ahe
	 * @date 20.10.2006 22:01:16
	 * @package htdocs/classes/system
	 */
	public function getYesNoArray(){ return $this->yes_no_array; }

	public function getList($offset=null,$limit=null,$where=null,$searchphrase=null, $order_by=null, $order_dir=null)
	{
		$this->setTypeSelect();
		$this->sql['list4view'] = array(
				'select' => 'dict1.dict_id, dict1.deftrans '
					.', dict2.'.$this->langNum.' as type_classify_id'
					.', dict1.de AS \'-de\''
					.', dict1.en AS \'-en\'',
				'from' => $this->table.' as dict1 ,' .
						$this->table.' as dict2, ' .
						BcmsConfig::getInstance()->getTablename('classification').' as class',
				'where' => 'dict1.type_classify_id=class.classify_id AND class.fk_dict=dict2.dict_id',
				'order' => 'dict1.deftrans',
				'fetchmode' => DB_FETCHMODE_ASSOC
		);

		return parent::getList($offset,$limit,$where, $order_by, $order_dir,$searchphrase,'dict.',' dict1.','list4view');
	}


    /**
	 * prepare values in specified array for display in table view.
	 * ATTENTION: This is a hook method to implement special handling of result array
	 *
	 * @param array rows - associative array with column => value
	 * @return array - rows with prepared values
	 * @author ahe
	 * @date 16.12.2006 23:11:28
	 * @since 0.13.180
	 * @package htdocs/classes/sys/config
	 */
    protected function prepareResultForTableView($rows) {
		$parser = BcmsFactory::getInstanceOf('Parser');
		$maxLen = BcmsConfig::getInstance()->dict_max_trans_length;
		for ($i = 0; $i < count($rows); $i++) {
			foreach($rows[$i] as $key => $value) {
				$rows[$i][$key] = $parser->prepareText4Preview($value,$maxLen);
			}
		}
		unset($parser);
    	return $rows;
	}

	public function setTypeSelect(){
		$this->sql['types'] = array(
				'select' => 'class.classify_id, dict2.'
					.$this->langNum.' as type',
				'from' => $this->table.' as dict2, ' .
						BcmsConfig::getInstance()->getTablename('classification').' as class, '.
						BcmsConfig::getInstance()->getTablename('systemschluessel').' as sk',
				'where' => 'class.fk_dict=dict2.dict_id AND ' .
						'class.fk_syskey = sk.id_schluessel AND ' .
						'sk.schluesseltyp = \'dict_type\'',
				'order' => 'class.classify_id DESC',
				'fetchmode' => DB_FETCHMODE_ASSOC
		);
		$types = $this->select('types');
		for ($i = 0; $i < sizeof($types); $i++) {
			$typeValues[$types[$i]['classify_id']] = $types[$i]['type'];
		}
		$this->col['type_classify_id']['qf_vals'] = $typeValues;
		return true;
	}

	/**
	 * Setzt die Labels der einzelnen Spalten
	 *
	 */
	public function addLabels() {
		$fieldnames = array_keys($this->col);
		foreach($fieldnames as $key) {
			$trans = null;
			if(!in_array($key, $this->uneditableElements)) {
				if(strlen($key)>2)
					$trans = $this->getTrans('dict.'.$key);
				else
					$trans = $this->getTrans($key);
			}
			if($trans==null) $trans=$key;
			$this->col[$key]['qf_label'] = $trans;
		}
/*
		$this->sql['labels'] = array(
				'select' => 'class.name, dict.'
					.$this->langNum.' as lang',
				'from' => $GLOBALS['tablenames']['classification'].' as class, '.
					$GLOBALS['tablenames']['dict'].' as dict, '.
					$GLOBALS['tablenames']['systemschluessel'].' as sk ',
				'where' => ' class.fk_syskey = sk.id_schluessel AND '.
					'class.fk_dict = dict.dict_id AND '.
					'sk.schluesseltyp = \'language\' ',
				'fetchmode' => DB_FETCHMODE_ASSOC
		);
		$labels = $this->select('labels');
		for ($i=0;$i < count($labels); $i++) {
			$this->col[$labels[$i]['name']]['qf_label'] =
				$labels[$i]['lang'];
		}
		*/
		return true;
	}

	/**
	 * handles filtering or values of special fields for insert or update
	 * @author ahe
	 */
	public function checkSpecialFields(&$p_aCols,$func) {
		if($func=='insert') $p_aCols['dict_id'] = $this->nextID();
	}

	public function getObject($id) {
		$this->sql['listallcolumns']['fetchmode'] = DB_FETCHMODE_OBJECT;
		return $this->select('listallcolumns','dict_id = '.$id);
	}

	/**
	 *
	 * @author ahe
	 */
	public function getTransByID($id) {
		$row = $this->select('getTrans','dict_id = '.$id);
   		return $row[0]['trans'];
	}

	/**
	 *
	 * @author ahe
	 */
	public function getTrans($default) {
      return $this->getTransByDefault($default);
  }

	/**
	 *
	 * @author ahe
	 */
	public function getTransByDefault($default) {

		$default = BcmsFactory::getInstanceOf('Parser')->prepDbStrng($default);
		if(!empty($this->cache[$default])) return $this->cache[$default];

		$row = $this->select('getTrans','deftrans = '.$default);
		if(!array_key_exists(0,$row)) {
			$msg = 'Es konnte kein Wörterbucheintrag für die Standardübersetzung \''
				.$default.'\' gefunden!'; // TODO Use dictionary!
			return BcmsSystem::raiseNotice($msg,
				BcmsSystem::LOGTYPE_CHECK, BcmsSystem::SEVERITY_DEBUG,
				'getTransByDefault()',__FILE__, __LINE__);
		}
		$row[0]['trans'] = stripslashes($row[0]['trans']);
		$this->cache[$default] = $row[0]['trans'];
		return $row[0]['trans'];
	}

	protected function getSearchableFieldsArray()
	{
		return array(
			'deftrans' => 'LIKE',
			'de' => 'LIKE',
			'en' => 'LIKE'
		);
	}
}
?>