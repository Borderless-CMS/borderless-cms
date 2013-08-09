<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * Class holding all system configuration variables
 * Get value via <code>BcmsConfig::getInstance()->varname</code>
 *
 * @since 0.10
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @class BcmsConfig
 * @ingroup sys
 * @package sys
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
	 * @return BcmsConfig - returns an instance of the current class
	 */
	public static function getInstance(){
		if(self::$uniqueInstance==null){
			self::$uniqueInstance = new self();
		}
		return self::$uniqueInstance;
	}

	/**
	 * loads config file from includes/config folder and creates object
	 *
	 * @author ahe
	 * @date 23.11.2005 15:13:44
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
	 * @throws MissingConfigFileException
	 * @throws BcmsException
	 */
	public function init(){
		// flag that can be asked whether config has yet been loaded from db
		$this->set('loadedFromDb',false);

		$configFilename = BcmsConfig::getConfigFilename();
		if(!file_exists($configFilename)) {
			throw new MissingConfigFileException($_SERVER['SERVER_NAME']);
		}

		// load database-conf-vars from file system
		require $configFilename;
		// $confVars variable is set in config file
		foreach($confVars as $key =>$value) {
			$this->set($key,$value);
		}
		$this->initTablenameList();
	}

	/**
	 * Returns name of config file according to current SERVER_NAME
	 *
	 * @return unknown
	 * @throws BcmsException if SERVER_NAME is invalid
	 * @date 15.05.2007 23:57
	 * @since 0.13.170
	 */
	public static function getConfigFilename() {
		if(!preg_match('/[a-z0-9\.\-\_]+/',$_SERVER['SERVER_NAME'])){
			throw new BcmsException('InvalidServerName: Server name must only consist ' .
					'of a-z, 0-9, . (dot), - (hyphen) and _ (underscore)');
		}

		return BASEPATH.'/inc/config/config.'.htmlentities($_SERVER['SERVER_NAME']).'.inc.php';
	}

	public function __get($memberName)
	{
		return $this->get($memberName);
	}

	public function __set($memberName, $value)
	{
		throw new BcmsException('config variables may not be set to BcmsConfig '.
			'object! Use BcmsConfig::loadConfigVars() instead!');
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
		if (!($res instanceof PEAR_ERROR) && $res->numRows()>0) {
			$numrows = $res->numRows();
			for ($i = 0; $i < $numrows; $i++) {
				$conf_array = $res->fetchRow(DB_FETCHMODE_ORDERED,$i);
				$this->set($conf_array[0], $conf_array[1]);
			}
		}
		$this->set('loadedFromDb',true);
		if(!isset($this->completeSiteUrl)){
			$this->set('completeSiteUrl', 'http://'.$this->siteUrl);
		}
		$this->loadGlobalStatusArray();
	}

	/**
	 * In dieser Methode werden die Systemstatus aus der Datenbank geladen
	 * @todo use classifications!
	 */
	private function loadGlobalStatusArray(){
		$sql = 'SELECT class.classify_name, class.number ' .
		'FROM '.$this->getTablename('classification').' AS class, '.
		$this->getTablename('systemschluessel').' AS syskey ' .
		'WHERE ' .
		' class.fk_syskey = syskey.id_schluessel '.
		' AND syskey.schluesseltyp = \'status\' '.
		'ORDER BY class.number DESC';
		$result=$GLOBALS['db']->query($sql);
		if (!($result instanceof PEAR_ERROR) && $result->numRows()>0) {
		 	$numrows = $result->numRows();
			for ($i = 0; $i < $numrows; $i++) {
				$conf_array = $result->fetchRow(DB_FETCHMODE_ASSOC,$i);
				$GLOBALS['ARTICLE_STATUS'][$conf_array['classify_name']] = $conf_array['number'];
			}
			$result->free();
			unset($conf_array,$numrows);
		}
		unset($result,$sql);
	}

	/**
	 * returns the status array translated to current language
	 *
	 * @return array of (key => translated_name)
	 * @author ahe
	 * @date 17.11.2006 23:13:19
	 * @todo move to classifications
	 */
	public function getTranslatedStatusList()
	{
	    if(empty($this->statusList)) {
		    foreach ($GLOBALS['ARTICLE_STATUS'] as $name => $key) { // @todo use classifications for status!
				$this->statusList[$key] = BcmsSystem::getDictionaryManager()->getTrans($name);
			}
	    }
	    return $this->statusList;
	}

	/**
	 * Sets a tablename to the config using the specified key
	 *
	 * @param String $key a short name used as array key
	 * @param String $tablename the name of the database table WITHOUT
	 * table_prefix!
	 * @author ahe
	 * @date 03.11.2006 10:13:02
	 */
	public function setTablename($key, $tablename){
		$tp = $this->table_prefix;
		$tablenameSequence = '_'.$tp.$tablename.'__seq';
		$tablename = $tp.$tablename;
		if(mb_strlen($tablenameSequence)>40){ // @todo set the maxlength of a database tablename dynamic (currently 30 is hardcoded)
			$maxchars = 40 - mb_strlen('_'.$tp.'__seq');
			throw new Exception('Tablename "'.$tablename.'" is too long. ' .
					'A maximum of '.$maxchars.' characters is permitted!');
		}
		// @todo $key might not be valid -> validate!
		$this->tablenames[$key] = $tablename;
		$this->tablenames[$key.'__seq'] = $tablenameSequence;
	}

	/**
	 * Sets a tablename to the config using the specified key
	 *
	 * @param String $key a short name used as array key
	 * @param boolean $quoted - whether tablename shall be surrounded by db specific identifier quotes
	 * @return String the tablename
	 * @author ahe
	 * @date 03.11.2006 10:13:02
	 */
	public function getTablename($key, $quoted=true){
		// @todo $key might not be valid -> validate!
		return $GLOBALS['db']->quoteIdentifier($this->tablenames[$key]);
	}

	/**
	 * Sets a tablename to the config using the specified key
	 *
	 * @author ahe
	 * @date 03.11.2006 10:13:02
	 */
	protected function initTablenameList(){

		// Tabellennamen laden
	$tablenames = array(
		'systemschluessel' => 'systemschluessel',
		'config' => 'config',
		'classification' => 'classification', // @todo remove this entry when classification plugin is finished

// Build plugins for the following tables
		'last_transactions' => 'last_transactions',
		'layout_fieldtype_zo' => 'layout_fieldtype_zo',
		'layoutpresets' => 'layoutpresets',
		'syslog' => 'syslog',
	    'fieldtypes' => 'fieldtypes'
	);

		foreach($tablenames as $key => $value){
			// @todo $key might not be valid -> validate!
			$this->setTablename($key, $value);
		}
	}

}
?>