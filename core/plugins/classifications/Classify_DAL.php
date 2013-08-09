<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * @todo document this
 * @todo name this properly e.g. Classification_DAL
 * 
 * @author ahe <aheusingfeld@borderlesscms.de>
 * @since 0.13 
 * @class Classify_DAL
 * @ingroup classifications
 * @package classifications
 */
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
				'select' => 'class.classify_name, dict.'
					.$this->langNum.' as lang',
				'from' => $this->table.' as class, '.
					BcmsConfig::getInstance()->getTablename('dict').' as dict, '.  // @todo optimize performance!
					BcmsConfig::getInstance()->getTablename('systemschluessel').' as sk ',
				'where' => ' class.fk_syskey = sk.id_schluessel AND '.
					'class.fk_dict = dict.dict_id AND '.
					'sk.schluesseltyp = \'language\' ',
				'fetchmode' => DB_FETCHMODE_ASSOC
		);
		$labels = $this->select('labels');
		for ($i=0;$i < count($labels); $i++) {
			$this->col[$labels[$i]['classify_name']]['qf_label'] =
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