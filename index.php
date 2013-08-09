<?php /*
+----------------------------------------------------------------------------+
| B O R D E R L E S S   C M S                                                |
+----------------------------------------------------------------------------+
| (c) Copyright 2004-2005                                                    |
|      by goldstift (mail@goldstift.de) - www.goldstift.de                   |
+----------------------------------------------------------------------------+*/
// BORDERLESS: prevents execution of php scripts separetly from the main file
define('BORDERLESS',true);
define('BCMS_VERSION','(Version 0.10.$WCREV$)');
define('LOG_ERROR_LEVEL',(E_ALL ^ E_NOTICE));
define('BASEPATH',dirname(__FILE__));
//error_reporting(LOG_ERROR_LEVEL);

// include path setzen
require_once 'includes/set_inc_path.inc.php';
// Klassen initialisieren (incl. DB) und zusaetzliche Init-Dateien laden
require_once $includeRoot.'/init_part1.inc.php';

$configInstance = BcmsConfig::getInstance();

// Test if prepage shall be shown ... otherwise redirect to main!
if($configInstance->showPrePage == 0)  {
	$techname = Factory::getObject('CategoryManager')->getModel()->getTechnameById($configInstance->default_cat_id);
	header('Location: /'.$techname.'/', true);
}

require_once $includeRoot.'/init_part2.inc.php';
?>
<!DOCTYPE html PUBLIC"-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php
echo $configInstance->langKey ?>" lang="<?php
echo $configInstance->langKey ?>">
  <head>
	<title><?=PluginManager::getInstance()->getAllPageTitles(); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $configInstance->metaCharset;?>" />
	<meta http-equiv="imagetoolbar" content="no" />
	<meta http-equiv="Window-target" content="_top" />
	<meta http-equiv="Content-Script-Type" content="javascript" />
	<meta http-equiv="Content-Style-Type" content="text/css" />

	<meta name="generator" content="BordeRleSS cms - http://www.borderless-cms.de/" />
	<meta name="description" content="<?= PluginManager::getInstance()->getAllMetaDescriptions();?>" />
	<meta name="keywords" content="<?=PluginManager::getInstance()->getAllMetaKeywords() ?>" />
	<meta name="author" content="<?php echo $configInstance->metaAuthor; ?>" />
	<meta name="copyright" content="<?php echo $configInstance->copyright_linktext;?>" />
	<meta name="distributor" content="<?php echo $configInstance->metaDistributor; ?>" />
	<meta name="revisit-after" content="<?php echo $configInstance->metaRevisitAfter; ?>" />
	<meta name="robots" content="<?php echo $configInstance->metaRobots;?>" />
	<meta name="rating" content="<?php echo $configInstance->metaRating;?>" />

    <link rel="shortcut icon" href="favicon.ico" />
<?php
// allgemeines Stylesheet einbinden
echo '    <link rel="stylesheet" title="default stylesheet" '
		.'type="text/css" media="all" href="/css/'
		.$_SESSION['cssfile'].'/'.$_SESSION['cssfile'].'.css" />'."\n";
?>
  </head>
  <body id="introbody">
    <div id="intromessage">
      <div id="introimage">
        <a href="/main/" title="<?php
          echo BcmsConfig::getInstance()->siteUrl.' - '
          .Factory::getObject('Dictionary')->getTrans('continue').'...';?>"><span><?php
          echo $configInstance->welcomemessage; ?></span></a>
      </div>
      <div id="introdetails">
        <?php echo nl2br($configInstance->intropage_details); ?>
      </div>
    </div>
  </body>
</html>
