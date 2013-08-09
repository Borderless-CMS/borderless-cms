<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * Borderless CMS Installer class. Cares about install and upgrade process.
 *
 * @since 0.14
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @date 13.03.2007
 * @class BcmsInstaller
 * @ingroup installer
 * @package installer
 */
class BcmsInstaller {

	/**
	 * Reference to global instance of PEAR_DB (db connection)
	 *
	 * @var DB
	 */
	private $db = null;

	/**
	 * Reference to instance of BcmsConfig
	 *
	 * @var BcmsConfig
	 */
	private $bcmsConfig = null;

	/**
	 * Reference to instance of BcmsConfig
	 *
	 * @var BcmsInstallView
	 */
	private $view = null;

	/**
	 * Holds all executed sql statements during upgrade process
	 *
	 * @var array
	 */
	private $stmts = array();

	/**
	 * @todo document this
	 *
	 * @param DB $db - instance of a PEAR::DB object
	 * @author ahe
	 * @date 13.03.2007
	 */
	public function __construct($db=null) {
		$this->db = $db;
		if($db!=null && !($db instanceof DB_Error)) {
			$this->bcmsConfig = BcmsConfig::getInstance();
		}

		include('install/BcmsInstallView.php');
		$this->view = new BcmsInstallView();
	}

	/**
	 * This will automatically be called when a preinstalled version is detected
	 *
	 * @author ahe
	 * @date 13.03.2007
	 */
	public function upgrade($fromRevision, $toRevision) {
		if(!array_key_exists('install_form_submitted',$_POST) || !array_key_exists('confirm_backup_self_accountable',$_POST)
		) {
		    $this->checkEnvironment();
		    $this->createView('Upgrade');
		} else {
			try {
				$this->updateConfig();
				BcmsSystem::initSystemPlugins();
				$this->performDatabaseUpgrade($fromRevision, $toRevision);
				$this->finishInstallerProcess($toRevision);
				$this->view->assign('hideUpgradeForm', true);
		    	$this->createView('Upgraded to Revision '.$toRevision, 'finished');

			} catch(Exception $ex) {
				throw new BcmsException($ex->getMessage() . "\n\n" . $ex->getTrace());
			}
		}
	}

	/**
	 * Call this to perform a fresh installation
	 *
	 * @author ahe
	 * @date 13.03.2007
	 * @throws Exception if error occurs
	 */
	public function install($revision) {
		if(!array_key_exists('install_form_submitted',$_POST)) {
		    $this->checkEnvironment();
		    $this->createView('Installation');
		} else {
		    $this->updateConfig();
    		// insert sql file
    		$sql = implode ('', file('./initial_db_build.sql'));

    		$parser = BcmsSystem::getParser();
    		// add table prefix
    		$table_prefix = $parser->getPostParameter('table_prefix');
   			$sql = str_replace('%%prefix%%', $table_prefix, $sql);
   			$sqlArray = explode(";_", $sql);
   			foreach ($sqlArray as $sqlStmt) {
   				$this->executeSql($sqlStmt);
   			}
   			$this->bcmsConfig = BcmsConfig::getInstance();

   			$this->upgrade(180, $revision);
		}
	}

	/**
	 * Call this to perform a check of your installation
	 *
	 * @author ahe
	 * @date 13.03.2007
	 * @throws BcmsException if error occurs
	 */
	public function checkInstall($revision) {
//	    $this->checkEnvironment();
//	    $this->createView('Systemcheck');
	}

	/**
	 * Call this to perform a check of your installation
	 *
	 * @author ahe
	 * @since 0.13.187
	 * @date 25.06.2007
	 * @throws BcmsException if error occurs
	 */
	private function finishInstallerProcess($toRevision) {
		// set admin password from $_POST
		$password = BcmsSystem::getParser()->getPostParameter('user_admin_password');
		if(!empty($password)){
			$this->executeSql('UPDATE '.$this->getTblName('user').
				' SET passwort = '.$this->db->quoteSmart(
				BcmsSystem::getUserManager()->getEncodedPassword($password)).
				' WHERE username = \'admin\'');
		}
		$this->executeSql('UPDATE '.$this->getTblName('config').
			' SET var_value = '.$this->db->quoteSmart($toRevision).' WHERE var_name = \'db_revision\'');

		$message = 'Installation process finished. You should now set back file permissions on your '.
				'config file and delete the install directory!'; // TODO use dictionary
		$this->view->assign('finalMessage', $message);
	}

	/**
	 * creates config file from POST and tries to re-init the system.
	 *
	 * @return void - the full tablename
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 * @date 18.04.2007
	 * @since 0.13.170
	 */
	private function updateConfig() {
		$dbPass = BcmsSystem::getParser()->getPostParameter('dbPass');

		if(!empty($dbPass)) {
			// try to generate config file
			$configFilename = BcmsConfig::getConfigFilename();

			try {
				$configContent = $this->createConfigFileContentFromPost();
				if(!file_put_contents($configFilename, $configContent)){
					throw new BcmsException('ERROR: Config file could not be '.
						'created or overwritten! Filename: ' . $configFilename);
				}

			} catch(Exception $ex) {
				throw new BcmsException($ex->getMessage() . "\n\n" . $ex->getTrace());
			}
		}
		// initializes System
		BcmsSystem::init();
		$this->db = $GLOBALS['db'];
	}

	/**
	 * creates config file from POST and tries to establish $GLOBALS['db'] connection
	 *
	 * @return void - the full tablename
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 * @date 18.04.2007
	 * @since 0.13.187
	 */
	private function performDatabaseUpgrade($fromRevision, $toRevision){
		include_once('install/upgrade_statements.inc.php');
		foreach ($sqls as $stmt) {
			$this->executeSql($stmt);
		}

	}

	/**
	 * Inserts a record into the specified table
	 * Example for call:
	 * <code>
	 *  $this->registerInsert('tablealias', array(
	 * 	 	'fieldname'=>'value',
	 * 		//...
	 *      )
	 *  );
	 * </code>
	 *
	 * @param String $statement - sql statement to be executed
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 * @date 13.03.2007
	 * @since 0.13.121
	 */
	private function executeSql($statement) {
		$statement = trim($statement);
		if(empty($statement)){
			return null;
		}

		$this->stmts[] = $statement;
		$res = $this->db->query($statement);

		if (PEAR::isError($res)) {
			echo 'Last correctly executed statement was: '.$this->stmts[count($this->stmts)-2];
			echo "<br />\r\n";
			print_r($res);
			throw new PearDbErrorException($res,BcmsSystem::SEVERITY_ERROR,true);
		}
	}

	/**
	 * Fetches tablename from system config.
	 *
	 * @param String $tablealias - bcms internal alias of the table
	 * @return String - the full tablename
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 * @date 18.04.2007
	 * @since
	 */
	private function getTblName($tablealias) {
		return $this->bcmsConfig->getTablename($tablealias);
	}

	/**
	 * Creates a form where the user can specify the basic configuration parameters
	 *
	 * @param array $data - (optional) specify presets for the form fields
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 * @date 13.03.2007
	 */
	private function createView($processName='Installation',$step=1) {
		$this->view->assign_by_ref("configInstance", $this->bcmsConfig);
		$this->view->display($processName,$step);
	}

	/**
	 * Tries to create the config file holding important system settings.
	 * Writes the file to "BASEPATH/inc/config/CONFIGFILENAME"
	 *
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 * @since 0.13.170
	 * @date 16.05.2007 00:14
	 */
	private function createConfigFileContentFromPost() {
		$parser = BcmsSystem::getParser();
		$postvars = array('dbType', 'dbServer', 'dbDatabase', 'dbUser', 'dbPass', 'table_prefix', 'siteUrl', 'adm_email', 'offlineMessage');
		$buffer = '<?php if(!defined(\'BORDERLESS\')) { header(\'Location: / \',true,403); exit(); }'."\r\n";
		$buffer .= '$confVars = array('."\r\n";
		foreach($postvars as $var){
		    $value = $parser->getPostParameter($var);
		    $buffer .= '	\''.$var.'\' => ';
		    if(is_string($value)){
		        $buffer .= '\''.$value.'\'';
		    } else {
		        $buffer .= $value;
		    }
		    $buffer .= ",\r\n";
		}
		$buffer .= ');'."\r\n".'?>';
		return $buffer;
	}

	/**
	 * Helper function that checks whether php connect functions of supported DBMS are present.
	 * If so it assumes that the according dbms is installed on the server and adds them to
	 * the $supported_db_types array.
	 *
	 * @return void (sets $this->supported_db_types)
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 * @since 0.13.170
	 * @date 16.05.2007 00:14
	 */
	private function checkInstalledDatabases(){
		// \bug URGENT refactor this to find the installed and supported db_types
		$dbTypes = array (
			'mysql' => 'mysql_connect',
			'mysqli' => 'mysqli_connect',
			'pgsql' => 'pg_connect',
			'sqllite' => 'sqlite_open',
			'fbsql' => 'fbird_connect',
			'ibase' => 'ibase_connect',
			'ifx' => 'ifx_connect',
			'sybase' => 'sybase_connect',
			'mssql' => 'mssql_connect'
		);
		foreach ($dbTypes as $db => $connectFunc) {
			if(function_exists($connectFunc)){
			    $this->supported_db_types[] = $db;
			}
		}
	}

	private function checkEnvironment() {

		$flag_errors=false;

		$testSection['title'] = 'Checking environment...';

		$testSection['tests'][] = $this->runPhpVersionTest();
	    $testSection['tests'][] = $this->runDbmsTest();
	    $testSection['tests'][] = $this->runLoadPreviousConfigTest();
	    $uploadDir = ($this->bcmsConfig != null) ? $this->bcmsConfig->upload_dir : 'upload/';
		$directories = array(
	    	'upload' => BASEPATH . $uploadDir,
	    	'config' => BcmsConfig::getConfigFilename(),
	    	'template compile' => $this->view->compile_dir,
	    	'template cache' =>$this->view->cache_dir
	    );
		foreach ($directories as $dirname => $directory) {
	    	$testSection['tests'][] = $this->runDirectoryWritableTest($dirname, $directory);
		}

		// assign all test results
		$this->view->assign('testSection',$testSection);

		// abort on errors
		if($flag_errors) {
			$this->view->assign('finalMsgTitle','<h2>Systemcheck failed</h2>');
			$this->view->assign('finalMsgBody','Please correct the above mentioned errors');
		} else {
			$this->view->assign('showSettingsForm',true);
		}
	}

	/**
	 * check php version is ok
	 *
	 * @return array('result' => ..., 'message' => ...)
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 * @since 0.13.186
	 * @date 11.06.2007
	 */
	private function runPhpVersionTest() {
        if(version_compare(phpversion(), '5.1', '<')) {
            $result = false;
        } else {
            $result = true;
        }
        return array(
        	'result'	=> $result,
        	'message'	=> 'Required <strong>PHP-version</strong> is 5.1! Your version is ' . phpversion() // TODO use dictionary
        );
    }

	/**
	 * check supported database-installed
	 *
	 * @return array('result' => ..., 'message' => ...)
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 * @since 0.13.186
	 * @date 11.06.2007
	 */
	private function runDbmsTest() {
		$this->checkInstalledDatabases();
		$this->view->assign('supported_db_types', $this->supported_db_types);
		if(count($this->supported_db_types)>0) {
			$result= array(
	        	'result'	=> true,
	        	'message'	=> 'At least one supported <strong>database installation</strong> was found' // TODO use dictionary
	        );
    	} else {
			$result= array(
	        	'result'	=> false,
	        	'message'	=> 'NO supported <strong>database installation</strong> could be found! '.
	        					' Please check our <a href="http://www.borderlesscms.de/streber/67" '.
	        					'target="_blank" title="Link to Bordereless CMS Homepage - Link will '.
								'open in new windows!">documentation</a> for supported databases.' // TODO use dictionary
	        );
    	}
    	return $result;
    }

	/**
	 * check db-setting exists
	 *
	 * @return array('result' => ..., 'message' => ...)
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 * @since 0.13.186
	 * @date 11.06.2007
	 */
	private function runLoadPreviousConfigTest(){
    	$defaultMsg = "checking for previous db-settings in'<b>". $this->view->config_dir ."</b>'...";
		$configFilename = BcmsConfig::getConfigFilename();

    	if(file_exists($configFilename) && !($GLOBALS['db'] instanceof DB_Error)) {
            require($configFilename);
    		if(array_key_exists('dbUser',$confVars)){
    			$this->bcmsConfig->loadConfigVars();
    			$result= array(
		        	'result'	=> true,
		        	'message'	=> $defaultMsg . 'Config file exists. Using settings of previous installation.'
		        );
    		} else {
	            $result= array(
		        	'result'	=> false,
		        	'message'	=> $defaultMsg . 'Config file exists but contains no database credentials.'
		        );
    		}
    	} else {
	            $result= array(
		        	'result'	=> true,
		        	'message'	=> $defaultMsg . 'Config file does not exist. Assuming fresh installation!'
		        );
    	}

        // if no config was found or db-settings are wrong, set empty values to form fields
    	if(!isset($confVars)) $confVars=array();
    	// ... else load the values out of the config file
    	$fieldValues['dbType'] =     array_key_exists('dbType',$confVars) ? $confVars['dbType'] : null;
        $fieldValues['dbServer'] =     array_key_exists('dbServer',$confVars) ? $confVars['dbServer'] : null;
        $fieldValues['dbUser'] =     array_key_exists('dbUser',$confVars) ? $confVars['dbUser'] : null;
        $fieldValues['dbDatabase'] =   array_key_exists('dbDatabase',$confVars)     ? $confVars['dbDatabase'] : null;
        $fieldValues['table_prefix'] = array_key_exists('table_prefix',$confVars) ? $confVars['table_prefix'] : null;
        $fieldValues['adm_email'] = array_key_exists('adm_email',$confVars) ? $confVars['adm_email'] : null;
        $fieldValues['siteUrl'] = array_key_exists('siteUrl',$confVars) ? $confVars['siteUrl'] : null;
        $fieldValues['offlineMessage'] = array_key_exists('offlineMessage',$confVars) ? $confVars['offlineMessage'] : null;
		$this->view->setFormData($fieldValues);

        return $result;
	}

    /**
	 * check directories writeable
	 *
	 * @return array('result' => ..., 'message' => ...)
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 * @since 0.13.186
	 * @date 11.06.2007
	 */
	private function runDirectoryWritableTest($dirname, $directory) {
    	$defaultMsg = 'checking write permissions for '.$dirname.' directory: "<strong>'. $directory .'</strong>"...'; // TODO use dictionary
    	if(!is_writeable($directory)) {
            if(!is_dir($directory)){
                @mkdir($directory);
            }
            @chmod($directory, 0777);
            if(!is_writeable($directory)){
				$result= array(
		        	'result'	=> false,
		        	'message'	=> $defaultMsg.' Please grand write-permissions for this directory.' // TODO use dictionary
		        );
            } else {
				$result= array(
		        	'result'	=> true,
		        	'message'	=> $defaultMsg.' Folder written by BCMS, please check permissions rights with your root account.' // TODO use dictionary
		        );
            }
    	} else {
				$result= array(
		        	'result'	=> true,
		        	'message'	=> $defaultMsg. ' OK!'
		        );
    	}
		return $result;
	}
}
?>
