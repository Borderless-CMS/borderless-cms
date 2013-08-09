<?php
/* Borderless CMS - the easiest and most flexible way to a valid website
*   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
*   Distributed under the terms and conditions of the GPL as stated in /license.txt
* EXCLUSION:
*   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
*   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
*/

/**
 * @file install.php
 * Index file for system installer
 *
 * @ingroup installer
 */
require_once('../global_defines.inc.php');

// IMPORTANT comment out the following for live environments
define('LOG_ERROR_LEVEL',(E_ALL));
error_reporting(LOG_ERROR_LEVEL);
ini_set('display_errors','On');
ini_set('display_startup_errors','On');
//	ini_set('display_errors','Off');
//	ini_set('display_startup_errors','Off');
include_once('../inc/set_inc_path.inc.php');
include_once('install/installer.php');

try {
	require_once 'inc/init.inc.php';
	BcmsSystem::createGlobalDbObject();
} catch (Exception $ex){
	// if connection cannot be established, assume fresh installation
	$installer = new BcmsInstaller(null);
	echo $installer->install(BCMS_REVISION);

	// Ausgabe der Errorhandling-Variable
	if(!empty($_SESSION['system_msg']))	echo BcmsSystem::getSystemMessages();
	exit();
}

// The following will only be processed if upgrade shall be performed

$configObj = BcmsConfig::getInstance();
if($configObj->db_version < BCMS_VERSION) {
	$installer = new BcmsInstaller($GLOBALS['db']);
	$installer->upgrade($configObj->db_revision, BCMS_REVISION);

} else if($configObj->db_revision == BCMS_REVISION){
	$installer = new BcmsInstaller($GLOBALS['db']);
	$installer->checkInstall(BCMS_REVISION);
}

// Ausgabe der Errorhandling-Variable
if(!empty($_SESSION['system_msg']))	echo BcmsSystem::getSystemMessages();
?>