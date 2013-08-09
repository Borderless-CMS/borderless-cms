<?php
/**
 *
 *
 * @module Config.php
 * @author ahe <aheusingfeld@borderless-cms.de>
 * @package tools
 * @version $Id: Config.php,v 0.0 00.00.0000 00:00:00 ahe Exp $
 */
class BcmsConfig extends SingleObjectPattern {

	private static $uniqueInstance = null;
	protected $tablenames = array();
	protected $statusList = array();

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

	/**
	 * loads config file from includes/config folder and creates object
	 *
	 * @author ahe
	 * @date 23.11.2005 15:13:44
	 * @package object
	 * @project bcms
	 */
	protected function __construct()
	{
		$this->init();
	}

	/**
	 * Tries to load initial configuration from config file
	 *
	 * @author ahe
	 * @date 09.12.2006 16:10:41
	 * @package htdocs/classes/sys
	 */
	public function init(){
		// flag that can be asked whether config has yet been loaded from db
		$this->set('loadedFromDb',false);

		if(!preg_match('/[a-z0-9\.\-\_]+/',$_SERVER['SERVER_NAME']))
			exit('The server name is not valid! Server name must only consist ' .
					'of a-z, 0-9, . (dot), - (hyphen) and _ (underscore)');

		// replace / with _ to admit running bcms in subdirectories
		$servername = str_replace('/','_',$_SERVER['SERVER_NAME']);

		if(!file_exists(BASEPATH.'/includes/config/config.'.$servername.'.inc.php')) {
			die('A configuration file for the Domain '.$servername.' could not be found!');
		}

		// load database-conf-vars from file system
		require BASEPATH.'/includes/config/config.'.$servername.'.inc.php';
		// $confVars variable is set in config file
		foreach($confVars as $key =>$value) {
			$this->set($key,$value);
		}
		$this->initTablenameList();
	}

	public function __get($memberName)
	{
		return $this->get($memberName);
	}

	public function __set($memberName, $value)
	{
		throw new Exception('config variables may not be set to BcmsConfig object!');
	}

/* "normal" methods follow here */

	/**
	 * Hier werden die SystemVariablen aus der Datenbank geladen.
	 *
	 * @author ahe
	 * @date 25.11.2005 00:01:33
	 */
	public function loadConfigVars() {
		$sql = 'SELECT var_name, var_value FROM '.$this->getTablename('config');
		$res = $GLOBALS['db']->query($sql);
	 	$numrows = $res->numRows();
		if (!($res instanceof DB_ERROR) && $numrows>0) {
			for ($i = 0; $i < $numrows; $i++) {
				$conf_array = $res->fetchRow(DB_FETCHMODE_ORDERED,$i);
				$this->set($conf_array[0], $conf_array[1]);
			}
		}
		$this->set('loadedFromDb',true);
		$this->set('completeSiteUrl', 'http://'.$this->siteUrl);
	}

	/**
	 * TODO document this method
	 */
	public function setObjectDataWithArray($objectData){
		$this->virtualMembers = $objectData;
	}

	/**
	 * Sets a tablename to the config using the specified key
	 *
	 * @param String $key a short name used as array key
	 * @param String $tablename the name of the database table WITHOUT
	 * table_prefix!
	 * @package htdocs/classes/sys
	 * @author ahe
	 * @date 03.11.2006 10:13:02
	 */
	public function setTablename($key, $tablename){
		$tp = $this->table_prefix;
		$tabQuot = $this->tabQuot;

		$tablename = $tabQuot.$tp.$tablename.$tabQuot;
		if(mb_strlen($tablename)>30){ // TODO set the maxlength of a database tablename dynamic (currently 30 is hardcoded)
			$maxchars = 30 - mb_strlen($tabQuot.$tp.$tabQuot);
			throw new Exception('Tablename "'.$tablename.'" is too long. ' .
					'A maximum of '.$maxchars.' characters is permitted!');
		}
		// TODO $key might not be valid -> validate!
		$this->tablenames[$key] = $tablename;
	}

	/**
	 * Sets a tablename to the config using the specified key
	 *
	 * @param String $key a short name used as array key
	 * @return String the tablename
	 * @package htdocs/classes/sys
	 * @author ahe
	 * @date 03.11.2006 10:13:02
	 */
	public function getTablename($key){
		// TODO $key might not be valid -> validate!
		return $this->tablenames[$key];
	}

	/**
	 * Sets a tablename to the config using the specified key
	 *
	 * @package htdocs/classes/sys
	 * @author ahe
	 * @date 03.11.2006 10:13:02
	 */
	protected function initTablenameList(){

		// Tabellennamen laden
	$tablenames = array(
		'systemschluessel' => 'systemschluessel',
		'usersession' => 'usersessions',
		'user' => 'plg_users',
		'config' => 'config',
		'classification' => 'classification',

// Build plugins for the following tables
		'last_transactions' => 'last_transactions',
		'layout_fieldtype_zo' => 'layout_fieldtype_zo',
		'layoutpresets' => 'layoutpresets',
		'syslog' => 'syslog',
	    'fieldtypes' => 'fieldtypes'
	);

		foreach($tablenames as $key => $value){
			// TODO $key might not be valid -> validate!
			$this->setTablename($key, $value);
		}
	}

	/**
	 * returns the status array translated to current language
	 *
	 * @return array of (key => translated_name)
	 * @author ahe
	 * @date 17.11.2006 23:13:19
	 * @package htdocs/classes/sys
	 * TODO eventually move to classifications
	 */
	public function getTranslatedStatusList()
	{
	    if(empty($this->statusList)) {
		    foreach ($GLOBALS['ARTICLE_STATUS'] as $name => $key) { // TODO use classifications for status!
				$this->statusList[$key] = Factory::getObject('Dictionary')->getTrans($name);
			}
	    }
	    return $this->statusList;
	}

}
?>