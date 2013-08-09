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
 * @since 0.10
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @date 2006-07-05
 * @class Groups_DAL
 * @ingroup groups
 * @package groups
 */
class Groups_DAL extends DataAbstractionLayer
{

    public $col = array(

        // unique row ID
        'group_id' => array(
            'type'    => 'integer',
            'require' => true
        ),
        'groupname' => array(
            'type'    => 'varchar',
            'size'    => 40,
			'qf_rules' => array(
                'maxlength' => array(
                    'Maximum length is 40 characters.',
                    40
                )
            ),
			'qf_client' => true
        ),
        'position' => array(
            'type'    => 'smallint'
        ),
        'fk_grouptype_id' => array(
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
        'group_id' => array(
            'type' => 'unique',
            'cols' => 'group_id'
        ),
        'groupname' => array(
            'type' => 'unique',
            'cols' => 'groupname'
        )
    );

    public $sql = array(

        // multiple rows for a list
        'list_everything' => array(
        	'select' => '*',
        	'order' => 'groupname ASC',
        	'fetchmode' => DB_FETCHMODE_ASSOC
        )
	);

	protected $elementsToFreeze = array(
	);
	public $uneditableElements = array (
		'group_id');

	private static $uniqueInstance = null;
	protected $configInstance = null;
	protected $primaryKeyColumnName = 'group_id';

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
		parent::__construct($GLOBALS['db'],$this->configInstance->getTablename('groups'));
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
		if($func == 'insert') $cols['group_id'] = $this->nextID();
	}

	/**
	 * gets translations of the tablefields and sets them as formfield labels
	 */
	public function setLabels() {
		$fieldnames = array_keys($this->col);
		foreach($fieldnames as $key) {
			$trans = null;
			if(!in_array($key, $this->uneditableElements)) {
				$trans = BcmsSystem::getDictionaryManager()->getTrans('groups.'.$key);
			}
			$trans = ($trans==null) ? $key : $trans;
			$this->col[$key]['qf_label'] = $trans;
		}
	}

	// \bug URGENT use classifications for Typeselect
	public function setTypeSelect(){
		$this->sql['types'] = array(
				'select' => 'class.classify_id, class.classify_name as type',
				'from' => $this->configInstance->getTablename('classification').' as class, '.
						$this->configInstance->getTablename('systemschluessel').' as sk',
				'where' =>
						'class.fk_syskey = sk.id_schluessel AND ' .
						'sk.schluesseltyp = \'ROLLENTYP\'',
				'order' => 'class.classify_name ASC',
				'fetchmode' => DB_FETCHMODE_ASSOC
		);
		$types = $this->select('types');

		for ($i = 0; $i < sizeof($types); $i++) {
			$typeValues[$types[$i]['classify_id']] = $types[$i]['type'];
		}
		$this->col['fk_grouptype_id']['qf_vals'] = $typeValues;
		return true;
	}

	public function getSmallUserList() {
		$this->sql['list_small']['fetchmode'] = DB_FETCHMODE_ASSOC;
		return $this->select('list_small');
	}

	public function getList($offset=null,$limit=null)
	{
		$parser = BcmsSystem::getParser();
		$this->setTypeSelect();
		$this->sql['list4view'] = array(
				'select' => 'groups.group_id, groups.groupname , classif.name AS fk_grouptype_id, groups.position , groups.fk_dict_id ',
				'from' => $this->configInstance->getTablename('groups').' as groups,' .
					$this->configInstance->getTablename('classification').' as classif ',
				'where' => 'classif.classify_id = groups.fk_grouptype_id',
				'order' => 'classif.name, groups.position, groups.groupname ASC',
				'fetchmode' => DB_FETCHMODE_ASSOC
		);
		$rows = $this->select('list4view',null,null,$offset,$limit);
		return $rows;
	}

	public function getObject($id) {
		$this->sql['list_small']['fetchmode'] = DB_FETCHMODE_ASSOC;
		$result = $this->select('list_everything', 'group_id = '.$id);
		return $result[0];
	}

	public function delete($where){
		$ids = array();
		$whereParts = explode(' ',$where);
		foreach ($whereParts as $part) {
			if(mb_strlen($part)>2){ // longer than 'OR'
				$ids[]=intval(mb_substr($part,8));
			}
		}
		foreach ($ids as $groupId) {
			// delete group user association
		  	$sql='DELETE FROM '.$this->configInstance->getTablename('user_group_assoc')
		  		.' WHERE (FK_ROLLE='.$groupId.')';
		 	$result = $this->db->query($sql);
			if ($result instanceof PEAR_ERROR)
				return BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_DELETE,
					BcmsSystem::SEVERITY_ERROR,
					'delete()::user_group_assoc',__FILE__,__LINE__);

			// delete group right association
		  	$sql='DELETE FROM '.$this->configInstance->getTablename('groups_rechte_zo')
		  		.' WHERE (FK_ROLLE='.$groupId.')';
		 	$result = $this->db->query($sql);
			if ($result instanceof PEAR_ERROR)
				return BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_DELETE,
					BcmsSystem::SEVERITY_ERROR,
					'delete()::groups_rechte_zo',__FILE__,__LINE__);

			$result = parent::delete('group_id='.$groupId);
				return BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_DELETE,
				BcmsSystem::SEVERITY_ERROR,
					'delete()::group',__FILE__,__LINE__);
		}
	}
 }
?>