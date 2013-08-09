<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * data abstraction layer class for config variables
 * 
 * @since 0.13
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @class Config_DAL
 * @ingroup config
 * @package config
 */
class Config_DAL extends DataAbstractionLayer {

	public $col = array(
		// unique row ID
		'config_id' => array(
			'type'    => 'integer',
			'require' => true
		),
		'fk_section' => array(
			'type'    => 'integer',
			'require' => true,
			'qf_type' => 'select'
		),
		'var_name' => array(
			'type'    => 'varchar',
			'size'    => 100,
			'require' => true
		),
		'var_value' => array(
			'type' => 'clob',
			'qf_type' => 'textarea',
			'qf_attrs'  => array(
				'rows' => 1,
				'cols' => 30
			 )
		),
		'var_description' => array(
			'type' => 'clob',
			'qf_type' => 'textarea',
			'qf_attrs'  => array(
				'rows' => 1,
				'cols' => 30
			 )
		),
		'var_type' => array(
			'type'    => 'integer',
			'require' => true,
			'qf_type' => 'select'
		),
		'editable' => array(
			'type'    => 'integer',
			'require' => true,
			'qf_type' => 'select'
		)
	);

	public $idx = array(
		'config_id' => array(
			'type' => 'unique',
			'cols' => 'config_id'
		),
		'var_name' => array(
			'type' => 'unique',
			'cols' => 'var_name'
		)
	);

	public $sql = array(

		// multiple rows for a list
		'listallcolumns' => array(
			'select' => '*',
			'order' => 'var_name'
		),
		'listnames' => array(
			'select' => 'config_id, var_name'
		),
		'listnamesandvalues' => array(
			'select' => 'var_name, var_value'
		)	);

	public $uneditableElements = array ();
	public $elementsToFreeze = array ('config_id');
	protected $primaryKeyColumnName = 'config_id';

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
		parent::__construct($GLOBALS['db'],BcmsConfig::getInstance()->getTablename('config'));
		$this->col['editable']['qf_vals'] = BcmsSystem::getDictionaryManager()->getDalObj()->getYesNoArray();
		$this->setTypeSelect();
		$this->setSectionSelect();
	}

	public function getList($offset=null,$limit=null,$where=null,$searchphrase=null, $order_by=null, $order_dir=null)
	{

		$this->sql['list4view'] = array(
				'select' => 'config.config_id, config.var_name, config.var_value '
					.', class.classify_name as fk_section '
					.', config.editable',
				'from' => $this->table.' as config ,' .
						BcmsConfig::getInstance()->getTablename('classification').' as class',
				'where' => 'config.fk_section=class.classify_id',
				'order' => 'class.classify_name, config.var_name',
				'fetchmode' => DB_FETCHMODE_ASSOC
		);

		return parent::getList($offset,$limit,$where, $order_by, $order_dir,$searchphrase,'config.',' config.','list4view');
	}

    /**
	 * prepare values in specified array for display in table view
	 *
	 * @param array rows - associative array with column => value
	 * @return array - rows with prepared values
	 * @author ahe
	 * @date 16.12.2006 23:11:28
	 * @since 0.13.5
	 */
    protected function prepareResultForTableView($rows) {
        $maxLen = BcmsConfig::getInstance()->dict_max_trans_length;
        for ($i = 0; $i < count($rows); $i++) {
            foreach($rows[$i] as $key => $value) {
                // cut string if necessary
                if(mb_strlen($value)>$maxLen)
                    $rows[$i][$key] = mb_substr($value,0,($maxLen-3)).'...';
            }
			// add yes-no-translation for values in column 'editable'
            $rows[$i]['editable'] = $this->col['editable']['qf_vals'][$rows[$i]['editable']];
			// add html encode values in column 'var_value' so html-tags are displayed
			$rows[$i]['var_value'] = htmlentities(
				$rows[$i]['var_value'],
				ENT_QUOTES,
				BcmsConfig::getInstance()->metaCharset);
        }
        return $rows;
	}

	// \bug URGENT use classifications for Typeselect
	public function setTypeSelect(){
		$this->sql['var_type'] = array(
				'select' => 'class.classify_id, class.classify_name',
				'from' => BcmsConfig::getInstance()->getTablename('classification').' as class, '.
						BcmsConfig::getInstance()->getTablename('systemschluessel').' as sk',
				'where' => 'class.fk_syskey = sk.id_schluessel AND ' .
						'sk.schluesseltyp = \'datatype\'',
				'order' => 'class.number ASC',
				'fetchmode' => DB_FETCHMODE_ASSOC
		);
		$types = $this->select('var_type');
		for ($i = 0; $i < sizeof($types); $i++) {
			$typeValues[$types[$i]['classify_id']] = $types[$i]['classify_name'];
		}
		unset($types);
		$this->col['var_type']['qf_vals'] = $typeValues;
	}

	// \bug URGENT use classifications for Typeselect
	public function setSectionSelect(){
		$this->sql['fk_section'] = array( // @todo use classifications
				'select' => 'class.classify_id, class.classify_name',
				'from' => BcmsConfig::getInstance()->getTablename('classification').' as class, '.
						BcmsConfig::getInstance()->getTablename('systemschluessel').' as sk',
				'where' => 'class.fk_syskey = sk.id_schluessel AND ' .
						'sk.schluesseltyp = \'category_sysconfig\'',
				'order' => 'class.classify_name ASC',
				'fetchmode' => DB_FETCHMODE_ASSOC
		);
		$types = $this->select('fk_section');
		for ($i = 0; $i < sizeof($types); $i++) {
			$typeValues[$types[$i]['classify_id']] = $types[$i]['classify_name'];
		}
		$this->col['fk_section']['qf_vals'] = $typeValues;
	}

	/**
	 * handles filtering or values of special fields for insert or update
	 * @author ahe
	 */
	public function checkSpecialFields(&$p_aCols,$func) {
		if($func=='insert') $p_aCols['config_id'] = $this->nextID();
	}

	public function getNamesAndValues() {
		$this->sql['listnamesandvalues']['fetchmode'] = DB_FETCHMODE_ASSOC;
		return $this->select('listnamesandvalues');
	}

	public function getObject($id) {
		$this->sql['listallcolumns']['fetchmode'] = DB_FETCHMODE_OBJECT;
		return $this->select('listallcolumns','config_id = '.$id);
	}

	protected function getSearchableFieldsArray()
	{
		return array(
			'var_name' => 'LIKE',
			'var_value' => 'LIKE',
			'var_description' => 'LIKE'
		);
	}
}
?>