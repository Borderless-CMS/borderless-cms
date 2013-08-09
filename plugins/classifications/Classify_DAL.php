<?php
class Classify_DAL extends DataAbstractionLayer {

	public $col = array(
		// unique row ID
		'classify_id' => array(
			'type'    => 'integer',
			'require' => true,
			'qf_label' => 'DictionaryID'
		),
		'deftrans' => array(
			'type'    => 'varchar',
			'size'    => 255,
			'qf_label' => 'default translation'
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

	public $uneditableElements = array ('dict_id');


/*
 * Declaration of methods
 */
	public function __construct() {
		parent::__construct($GLOBALS['db'], BcmsConfig::getInstance()->getTablename('classification'));
		$this->langNum = BcmsConfig::getInstance()->langKey;
		$this->addLabels();
	}

	/**
	 * Setzt die Labels der einzelnen Spalten
	 *
	 */
	protected function addLabels() {
		$this->sql['labels'] = array(
				'select' => 'class.name, dict.'
					.$this->langNum.' as lang',
				'from' => $this->table.' as class, '.
					BcmsConfig::getInstance()->getTablename('dict').' as dict, '.  // TODO optimize performance!
					BcmsConfig::getInstance()->getTablename('systemschluessel').' as sk ',
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

}
?>