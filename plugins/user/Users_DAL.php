<?php

// basic classes
class Users_DAL extends DataAbstractionLayer{

	// needed for use of Singleton
	protected static $uniqueInstance = null;

    public $col = array(

        // unique row ID
        'user_id' => array(
            'type'    => 'integer',
            'require' => true
        ),
        'username' => array(
            'type'    => 'varchar',
            'size'    => 20,
			'qf_client' => true
        ),
        'passwort' => array(
            'type'    => 'varchar',
            'size'    => 60,
            'qf_type' => 'password',
			'qf_client' => true
        ),
        'vorname' => array(
            'type'    => 'varchar',
            'size'    => 25
        ),
        'nachname' => array(
            'type'    => 'varchar',
            'size'    => 25
        ),
        'about_me' => array(
            'type'    => 'clob',
			'qf_type' => 'textarea',
			'qf_attrs'  => array(
				'rows' => 5,
				'cols' => 40
			 )
        ),
        'homepage' => array(
            'type'    => 'varchar',
            'size'    => 255
        ),
        'company' => array(
            'type'    => 'varchar',
            'size'    => 120
        ),
        'address' => array(
            'type'    => 'varchar',
            'size'    => 80
        ),
        'postzip' => array(
            'type'    => 'varchar',
            'size'    => 8
        ),
        'city' => array(
            'type'    => 'varchar',
            'size'    => 80
        ),
        'country' => array(
            'type'    => 'varchar',
            'size'    => 80
        ),
        // email address
        'email' => array(
            'type'    => 'varchar',
            'size'    => 128,
            'require' => true,
			'qf_client' => true
        ),
        'skype_username' => array(
            'type'    => 'varchar',
            'size'    => 80
        ),
        'telefon' => array(
            'type'    => 'varchar',
            'size'    => 20,
        ),
        'fax' => array(
            'type'    => 'varchar',
            'size'    => 30
        ),

        'fk_fav_menu' => array(
            'type'    => 'integer',
            'require' => true
        ),
        'fav_layout' => array(
            'type'    => 'varchar',
            'size'    => 20
        ),
        'generally_notice_on_comment' => array(
            'type'    => 'integer',
            'qf_type' => 'select'
        ),
        'generally_notice_on_answer_to_comment' => array(
            'type'    => 'integer',
            'qf_type' => 'select'
        ),
        'public_fields' => array(
            'type'    => 'text',
			'qf_type' => 'select',
			'qf_attrs'  => array(
				'multiple' => 'multiple',
				'size' => 8
			 )
        ),
        'last_ip' => array(
            'type'    => 'varchar',
            'size' => 15
        ),
        'akt_login' => array(
            'type'    => 'timestamp'
        ),
        'last_login' => array(
            'type'    => 'timestamp'
        ),
        'root_flag' => array(
            'type'    => 'integer',
			'qf_type' => 'select',
			'qf_vals' => array(0,1)
        ),
        'fk_zusatzrecht' => array(
            'type'    => 'integer',
            'qf_type' => 'select'
        ),
        'login_tries' => array(
            'type'    => 'integer'
        ),
        'time2login' => array(
            'type'    => 'timestamp'
        ),
        'fk_aenderer' => array(
            'type'    => 'integer',
            'qf_freeze' => true
        ),
        'change_date' => array(
            'type'    => 'timestamp'
        ),
        'fk_anleger' => array(
            'type'    => 'integer'
        ),
        'create_date' => array(
            'type'    => 'timestamp'
        )
    );

    public $idx = array(
        'user_id' => array(
            'type' => 'unique',
            'cols' => 'user_id'
        ),
        'username' => array(
            'type' => 'unique',
            'cols' => 'username'
        )
    );

    public $sql = array(

        // multiple rows for a list
        'list' => array(
            'select' => 'user_id, username, CONCAT(vorname, \' \', nachname) AS fullname, email',
            'order'  => 'create_date DESC'
        ),
        'list_id_name' => array(
            'select' => 'user_id, username',
            'order'  => 'username ASC'
        ),
        'list_small' => array(
            'select' => 'user_id, username, vorname, nachname, email, last_login',
            'order'  => 'create_date DESC'
        ),
        'list_everything' => array(
        	'select' => '*',
        	'order' => 'nachname ASC',
        	'fetchmode' => DB_FETCHMODE_ASSOC
        )
	);

	protected $elementsToFreeze = array(
		'user_id', // make this uneditable as soon as "formId-Management" is build up completely
		'fk_aenderer',
		'fk_anleger',
		'change_date',
		'last_ip',
		'akt_login',
		'login_tries',
		'last_login',
		'create_date');
	public $uneditableElements = array (
		'passwort');

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
			self::$uniqueInstance = new Users_DAL();
		return self::$uniqueInstance;
	}

	protected function __construct() {
		parent::__construct($GLOBALS['db'],BcmsConfig::getInstance()->getTablename('user'));
	    $this->sql['showprofile'] = array(
            'select' => 'username, vorname, nachname, email, about_me, '.
            		'homepage, company, address, postzip, city, country, fax,' .
            		'telefon, fk_fav_menu, fav_layout, last_login',
            'fetchmode' => DB_FETCHMODE_ASSOC
        );
	}

	public function getForm($p_sFormName, $p_sSubmitButtonName,$p_sSubmitButtonText
		,$columns = null, $array_name = null, $args = array(),
		$clientValidate = null, $formFilters = null) {

		$this->addRightList();
		$this->addMenuList();
		$this->setLabels();
        $this->setRuleTranslation();
	    $this->col['fk_aenderer']['qf_vals'] = $this->getUserList4DAL();
	    $this->col['fk_anleger']['qf_vals'] = $this->getUserList4DAL();
		$yes_no_array = Factory::getObject('Dictionary')->getModel()->getYesNoArray();
		$this->col['generally_notice_on_comment']['qf_vals'] = $yes_no_array;
		$this->col['generally_notice_on_answer_to_comment']['qf_vals'] = $yes_no_array;
		$this->col['root_flag']['qf_vals'] = $yes_no_array;

        $this->setPublicFieldsCol();

		return parent::getForm($p_sFormName, $p_sSubmitButtonName,$p_sSubmitButtonText
			,$columns, $array_name, $args,$clientValidate, $formFilters);
	}

	private function setRuleTranslation() {
		$this->col['passwort']['qf_rules'] = array(
                'minlength' => array(
                    'Minimum length is 6 characters.',
                    6
                ),
                'maxlength' => array(
                    'Maximum length is 60 characters.',
                    60
                ),
                'regex' => array(
                    'Must only consist of the following chars: a-zA-Z0-9_*!?=.,-\'',
                    '/^[\w\*\-\!\,\.\'\?\=]{5,}$/'
                 )
        );
		$this->col['username']['qf_rules'] = array(
                'minlength' => array(
                    $this->col['username']['qf_label'].' - '.
                    Factory::getObject('Dictionary')->getTrans('minlength'),
                    4
                ),
                'maxlength' => array(
                    $this->col['username']['qf_label'].' - '.
                    Factory::getObject('Dictionary')->getTrans('maxlength'),
                    14
                ),
                'regex' => array(
                    $this->col['username']['qf_label'].' - '.
                    Factory::getObject('Dictionary')->getTrans('user.rules.username_regex'),
                    '/^[a-z0-9|_]+$/'  // TODO use dictionary here
                 )
			);
		$this->col['email']['qf_rules'] = array(
				'email' => true,
                'required' => 'Dies ist ein Pflichtfeld!' // TODO use dictionary
		);

	}

	public function addPwordFields() {
		$this->col['pword'] = $this->col['passwort'];
        $this->col['pword_again'] = $this->col['passwort'];
	}

	public function addRightList() {
		if(PluginManager::getInstance()->isPluginInstalled('RightManager')) {
			$rights = PluginManager::getPlgInstance('RightManager')->getRightList();
	        $this->col['fk_zusatzrecht'] = array(
	            'type'    => 'integer',
	            'qf_vals'    => $rights
	        );
		}
	}

  protected function addMenuList()
  {
    // get menu tree
    $allMenues = PluginManager::getPlgInstance('CategoryManager')->getLogic()->getMenuTreeList('__main__');
    for ($i = 0; $i < count($allMenues); $i++) {
      // add indent in front of menu names
      $spaces = '';
      for ($k = 0; $k < ($allMenues[$i]['level']-1)*3; $k++) {
        $spaces .= '&nbsp;';
      }
      $menues[$allMenues[$i]['cat_id']] =
        $spaces.$allMenues[$i]['categoryname'];
    }
    $this->col['fk_fav_menu']['qf_vals'] = $menues;
  }

  protected function getUserList4DAL()
  {
    // get menu tree
    $allUsers = $this->getSmallUserList();
    for ($i = 0; $i < count($allUsers); $i++) {
      $users[$allUsers[$i]['user_id']] =
        $allUsers[$i]['nachname'].', '.$allUsers[$i]['vorname'];
    }
    return $users;
  }

	/**
     * Deletes table rows matching a custom WHERE clause.
     *
     * @access public
     * @param string $where The WHERE clause for the delete command.
     * @return mixed Void on success or a PEAR_Error object on failure.
     * @see DB::query()
     * @see MDB2::exec()
	 * @author ahe
	 * @date 20.01.2007 23:11:21
	 * @package htdocs/plugins/user
     */
    function delete($where) {
		$deleteIds = HTMLTable::getAffectedIds();
		$sql = 'DELETE FROM '.BcmsConfig::getInstance()->getTablename('user_group_assoc').
				' WHERE 1=0 ';
		foreach ($deleteIds as $value) {
			if($value <=3){
				return BcmsSystem::raiseError('Systembenutzerkonten dürfen nicht gelöscht werden!!!', // TODO use dictionary!!!
					BcmsSystem::LOGTYPE_SECURITY, BcmsSystem::SEVERITY_ERROR,
					'delete()',__FILE__, __LINE__);
			}
			$sql .= ' OR FK_USER='.intval($value);
		}
	 	$result = $this->db->query($sql);
		if ($result instanceof PEAR_ERROR)
			return BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_SELECT,
				BcmsSystem::SEVERITY_ERROR, 'getUserIDfromDB()',__FILE__, __LINE__);

    	return parent::delete($where);
    }

	/**
	 * handles filtering or values of special fields for insert or update
	 * @author ahe
	 */
	public function checkSpecialFields(&$cols, $func) {
		if($func == 'insert') $cols['user_id'] = $this->nextID();

		if( ($cols['pword']!=$cols['pword_again'])
			|| $cols['pword'] == null
			|| $cols['pword'] == ''
		) {
			unset($cols['pword'],$cols['pword_again']);
		} else {
			$cols['passwort'] =
				PluginManager::getPlgInstance('UserManager')->getLogic()->getHashedPassword($cols['pword']);
			unset($cols['pword'],$cols['pword_again']);
		}
		$cols['fk_aenderer'] = PluginManager::getPlgInstance('UserManager')->getLogic()->getUserID();
		if(empty($cols['fk_anleger'])){
			$cols['fk_anleger'] = PluginManager::getPlgInstance('UserManager')->getLogic()->getUserID();
		}
		if(is_array($cols['akt_login'])) {
			$cols['akt_login'] = date('YmdHis',time());
		}
		if(is_array($cols['last_login'])) {
			$cols['last_login'] = date('YmdHis',time());
		}
		if(is_array($cols['create_date'])) {
			$cols['create_date'] = date('YmdHis',time());
		}
		$cols['change_date'] = date('YmdHis',time());
		$cols['public_fields'] = serialize($cols['public_fields']);
	}

	/**
	 * gets translations of the tablefields and sets them as formfield labels
	 */
	public function setLabels() {
		$fieldnames = array_keys($this->col);
		foreach($fieldnames as $key) {
			$trans = null;
			if(!in_array($key, $this->uneditableElements)) {
				$trans = Factory::getObject('Dictionary')->getTrans('user.'.$key);
			}
			$trans = ($trans==null) ? $key : $trans;
			$this->col[$key]['qf_label'] = $trans;
		}
	}

	private function setPublicFieldsCol(){
		foreach($this->col as $key => $value) {
			$keys[$key] = $value['qf_label'];
		}
		$keys = BcmsFactory::getInstanceOf('Parser')->stripArrayFields($keys,
					$this->uneditableElements);
		$this->col['public_fields']['qf_vals'] = $keys;
	}

	public function getSmallUserList() {
		$this->sql['list_small']['fetchmode'] = DB_FETCHMODE_ASSOC;
		return $this->select('list_small');
	}

	public function getIdNameList() {
		$this->sql['list_id_name']['fetchmode'] = DB_FETCHMODE_ASSOC;
		return $this->select('list_id_name');
	}

	public function getObject($id) {
		$this->sql['list_small']['fetchmode'] = DB_FETCHMODE_ASSOC;
		$result = $this->select('list_small', 'user_id = '.$id);
		return $result[0];
	}

	public function getFullObject($id) {
		$this->sql['list_everything']['fetchmode'] = DB_FETCHMODE_ASSOC;
		$result = $this->select('list_everything', 'user_id = '.$id);
		return $result[0];
	}

	public function getList($offset=null,$limit=null,$where=null)
	{
		$parser = BcmsFactory::getInstanceOf('Parser');
		$this->setLabels();
		$usernameTrans = $parser->prepDBStrng($this->col['username']['qf_label']);
		$creatorTrans = $parser->prepDBStrng($this->col['fk_anleger']['qf_label']);
		$this->sql['list4view'] = array(
			'select' => 'user.user_id, user.username ' .
				', CONCAT(user.nachname , \', \', user.vorname) AS realname'.
				', user.akt_login , user.root_flag , user.create_date ' .
				', user2.username AS fk_anleger',
			'from' => $this->table.' as user, '
				.$this->table.' as user2',
			'where' => 'user.fk_anleger = user2.user_id',
			'order' => 'user.username ASC',
			'fetchmode' => DB_FETCHMODE_ASSOC
		);
		$rows = $this->select('list4view',$where,null,$offset,$limit);

		$gui = Factory::getObject('GuiUtility');
		for ($i = 0; $i < sizeof($rows); $i++) {
			$rows[$i]['username'] =
				$gui->createAuthorName($rows[$i]['username']);
			$rows[$i]['fk_anleger'] =
				$gui->createAuthorName($rows[$i]['fk_anleger']);
		}
		return $rows;
	}

 }
?>