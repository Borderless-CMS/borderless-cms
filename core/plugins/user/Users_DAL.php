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
 *
 * @since 0.9
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @date 2006-01-27
 * @class User_DAL
 * @ingroup users
 * @package users
 */
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
            'require' => true,
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
            'require' => true,
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
            'require' => true,
            'size'    => 20
        ),
        'generally_notice_on_comment' => array(
            'type'    => 'smallint',
            'default' => 0,
            'qf_type' => 'select'
        ),
        'generally_notice_on_answer_to_comment' => array(
            'type'    => 'smallint',
            'default' => 0,
            'qf_type' => 'select'
        ),
        'public_fields' => array(
            'type'    => 'text',
            'default' => 'username',
            'require' => true,
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
            'type'    => 'smallint',
            'default' => 0,
            'qf_type' => 'select',
            'qf_vals' => array(0,1)
        ),
        'fk_zusatzrecht' => array(
            'type'    => 'integer',
            'qf_type' => 'select'
        ),
        'login_tries' => array(
            'default' => 0,
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
    protected $primaryKeyColumnName = 'user_id';

/*
 * Declaration of methods
 */
    /**
     * Get the instance of this class.
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
        $this->col['fav_layout']['qf_setvalue'] = BcmsConfig::getInstance()->default_css;
        $this->col['fk_fav_menu']['qf_setvalue'] = BcmsConfig::getInstance()->default_cat_id;
        $this->col['public_fields']['qf_setvalue'] = 'username';

        $this->setLabels();
        $this->setRuleTranslation();
        $this->col['fk_aenderer']['qf_vals'] = $this->getUserList4DAL();
        $this->col['fk_anleger']['qf_vals'] = $this->getUserList4DAL();
        $yes_no_array = BcmsSystem::getDictionaryManager()->getModel()->getYesNoArray();
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
                    $this->col['passwort']['qf_label'].' - '.
                    'Minimum length is 6 characters.',
                    6
                ),
                'maxlength' => array(
                    $this->col['passwort']['qf_label'].' - '.
                    'Maximum length is 60 characters.',
                    60
                ),
                'regex' => array(
                    $this->col['passwort']['qf_label'].' - '.
                    'Das Passwort muss mindestens 6 Zeichen lang sein und darf nur aus den folgenden Zeichen bestehen: a-z A-Z 0-9 _ * ! ? = . , - \'', // @todo use dictionary
                    '/^[\w\*\-\_\!\,\.\'\?\=]{6,60}$/'
                 )
        );
        $this->col['username']['qf_rules'] = array(
                'minlength' => array(
                    $this->col['username']['qf_label'].' - '.
                    BcmsSystem::getDictionaryManager()->getTrans('minlength'),
                    4
                ),
                'maxlength' => array(
                    $this->col['username']['qf_label'].' - '.
                    BcmsSystem::getDictionaryManager()->getTrans('maxlength'),
                    14
                ),
                'regex' => array(
                    $this->col['username']['qf_label'].' - '.
                    BcmsSystem::getDictionaryManager()->getTrans('user.rules.username_regex'),
                    '/^[\w\_]+$/'  // @todo use dictionary here
                 )
            );
        $this->col['email']['qf_rules'] = array(
            'email' => 'E-Mail-Adresse nicht angegeben oder ungültig!',
            'required' => 'Dies ist ein Pflichtfeld!' // @todo use dictionary
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

    protected function addMenuList() {
        $this->col['fk_fav_menu']['qf_vals'] =
            BcmsSystem::getCategoryManager()->getCategoryTree(true);
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
     */
    function delete($where) {
        $deleteIds = HTMLTable::getAffectedIds();
        $sql = 'DELETE FROM '.BcmsConfig::getInstance()->getTablename('user_group_assoc').
                ' WHERE 1=0 ';
        foreach ($deleteIds as $value) {
            if($value <=3){
                return BcmsSystem::raiseError('Systembenutzerkonten dürfen nicht gelöscht werden!!!', // @todo use dictionary!!!
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
                BcmsSystem::getUserManager()->getLogic()->getHashedPassword($cols['pword']);
            unset($cols['pword'],$cols['pword_again']);
        }
        $cols['fk_aenderer'] = BcmsSystem::getUserManager()->getUserId();
        if(empty($cols['fk_anleger'])){
            $cols['fk_anleger'] = BcmsSystem::getUserManager()->getUserId();
        }
        if(!array_key_exists('akt_login',$cols) || is_array($cols['akt_login'])) {
            $cols['akt_login'] = date('YmdHis',time());
        }
        if(!array_key_exists('last_login',$cols) || is_array($cols['last_login'])) {
            $cols['last_login'] = date('YmdHis',time());
        }
        if(!array_key_exists('create_date',$cols) || is_array($cols['create_date'])) {
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
                $trans = BcmsSystem::getDictionaryManager()->getTrans('user.'.$key);
            }
            $trans = ($trans==null) ? $key : $trans;
            $this->col[$key]['qf_label'] = $trans;
        }
    }

    private function setPublicFieldsCol(){
        foreach($this->col as $key => $value) {
            $keys[$key] = $value['qf_label'];
        }
        $keys = BcmsSystem::getParser()->stripArrayFields($keys,
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
        $parser = BcmsSystem::getParser();
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

        $gui = BcmsFactory::getInstanceOf('GuiUtility');
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