<?php /*
+----------------------------------------------------------------------------+
| B O R D E R L E S S   C M S                                                |
+----------------------------------------------------------------------------+
| (c) Copyright 2004 - 2006                                                  |
|      by goldstift (aheusingfeld@borderlesscms.de)                          |
+----------------------------------------------------------------------------+
*/
if(!defined('BORDERLESS')) exit;
 /**
  * Diese Include-Datei beschreibt den zweiten Teil des Initprozesses.
  */
$refParser = BcmsFactory::getInstanceOf('Parser');

include_once 'init_load_status.inc.php'; // TODO durch getClassification('status') ersetzen

$parser = BcmsFactory::getInstanceOf('Parser');
$parser->fetchAdditionalGetParameters();

// $_GETs abfragen und in SESSION laden
include_once 'init_global_gets.inc.php';

PluginManager::getInstance()->init($_SESSION['m_id']);
PluginManager::getInstance()->checkAllTransactions($_SESSION['m_id']);

// TODO remove this when content creation process is refactored
// URGENT The following code blocks users from opening second windows when writing an article!!
if( isset($_SESSION['current_article_data'])
	&& ($_SESSION['mod']['func']!='edit_article')
	&& ($_SESSION['mod']['func']!='write') )
{
	session_unregister('current_article_data');
}

?>