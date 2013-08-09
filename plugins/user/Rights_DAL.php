<?php

// basic classes
class Rights_DAL extends DataAbstractionLayer
{

    public $col = array(

        // unique row ID
        'right_id' => array(
            'type'    => 'integer',
            'require' => true
        ),
        'rightname' => array(
            'type'    => 'varchar',
            'size'    => 100,
			'qf_rules' => array(
                'maxlength' => array(
                    'Maximum length is 100 characters.',
                    100
                )
            ),
			'qf_client' => true
        ),
        'fk_syscat_id' => array(
            'type'    => 'integer',
            'qf_type' => 'select',
            'require' => true
        ),
        'fk_dict_id' => array(
            'type'    => 'integer',
            'qf_type' => 'select'
        )
    );

    public $idx = array(
        'right_id' => array(
            'type' => 'unique',
            'cols' => 'right_id'
        ),
        'rightname' => array(
            'type' => 'unique',
            'cols' => 'rightname'
        )
    );

    public $sql = array(

        // multiple rows for a list
        'list_everything' => array(
        	'select' => '*',
        	'order' => 'rightname ASC',
        	'fetchmode' => DB_FETCHMODE_ASSOC
        )
	);

	protected $elementsToFreeze = array(
		'right_id');
	public $uneditableElements = array (
		);

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
		parent::__construct($GLOBALS['db'],BcmsConfig::getInstance()->getTablename('rechte'));
		$this->setTypeSelect();
        $this->setLabels();
	}

	private function setRuleTranslation() {

	}


	/**
	 * handles filtering or values of special fields for insert or update
	 * @author ahe
	 */
	public function checkSpecialFields(&$cols, $func) {
		if($func == 'insert') $cols['right_id'] = $this->nextID();
	}

	/**
	 * gets translations of the tablefields and sets them as formfield labels
	 */
	public function setLabels() {
		$fieldnames = array_keys($this->col);
		foreach($fieldnames as $key) {
			$trans = null;
			if(!in_array($key, $this->uneditableElements)) {
				$trans = Factory::getObject('Dictionary')->getTrans('rights.'.$key);
			}
			$trans = ($trans==null) ? $key : $trans;
			$this->col[$key]['qf_label'] = $trans;
		}
	}

	/**
	 * TODO use classifications!
	 */
	public function setTypeSelect(){
		$this->sql['types'] = array(
				'select' => 'class.classify_id, class.name as type',
				'from' => BcmsConfig::getInstance()->getTablename('classification').' as class, '.
						BcmsConfig::getInstance()->getTablename('systemschluessel').' as sk',
				'where' =>
						'class.fk_syskey = sk.id_schluessel AND ' .
						'sk.schluesseltyp = \'category_sysconfig\'',
				'order' => 'class.name ASC',
				'fetchmode' => DB_FETCHMODE_ASSOC
		);
		$types = $this->select('types');

		for ($i = 0; $i < sizeof($types); $i++) {
			$typeValues[$types[$i]['classify_id']] = $types[$i]['type'];
		}
		$this->col['fk_syscat_id']['qf_vals'] = $typeValues;
		return true;
	}

	public function getSmallUserList() {
		$this->sql['list_small']['fetchmode'] = DB_FETCHMODE_ASSOC;
		return $this->select('list_small');
	}

	public function getList($offset=null,$limit=null)
	{
		$parser = BcmsFactory::getInstanceOf('Parser');
		$this->setTypeSelect();
		$this->setLabels();
		$this->sql['list4view'] = array(
				'select' => 'rights.right_id, rights.rightname , ' .
						'classif.name AS fk_syscat_id, rights.fk_dict_id ',
				'from' => $this->table.' as rights,' .
					BcmsConfig::getInstance()->getTablename('classification').' as classif ',
				'where' => 'classif.classify_id = rights.fk_syscat_id',
				'order' => 'classif.name, rights.rightname ASC',
				'fetchmode' => DB_FETCHMODE_ASSOC
		);
		$rows = $this->select('list4view',null,null,$offset,$limit);
		return $rows;
	}

	// TODO write phpdoc
	public function getObject($id) {
		$this->sql['list_everything']['fetchmode'] = DB_FETCHMODE_ASSOC;
		$result = $this->select('list_everything', 'right_id = '.$id);
		return $result[0];
	}

	// TODO write phpdoc
	public function getRightByName($rightname) {
		$this->sql['list_small']['fetchmode'] = DB_FETCHMODE_ASSOC;
		$result = $this->select('list_everything', 'rightname = '
			.BcmsFactory::getInstanceOf('Parser')->prepDbStrng($rightname));
		return $result[0];
	}
 }
?>