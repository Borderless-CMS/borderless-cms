<?php /*
* B O R D E R L E S S   C M S
*  (c)  Copyright 2004-2006 by goldstift (mail@goldstift.de / www.goldstift.de)
*
*/
if(!defined('BORDERLESS')) exit;

// send "no-cache" header information for all browser versions
  header ('Expires: Mon, 01 Jan 1990 01:00:00 GMT');
  header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
  header ('Cache-Control: no-cache, no-store, must-revalidate');
  header ('Pragma: no-cache');

/**
* The XHTML-Header information
*
* @author Alex Heusingfeld <alex@goldstift.de>
*/
$configInstance = BcmsConfig::getInstance();
$parser = BcmsFactory::getInstanceOf('Parser');
/*
echo '<?xml version="1.0" encoding="'.$configInstance->metaCharset.'"?>',"\n";
*/
if(!isset($refPluginManager)) $refPluginManager = PluginManager::getInstance();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php
echo $configInstance->langKey ?>" lang="<?php
echo $configInstance->langKey ?>">
  <head>
    <title><?=$refPluginManager->getAllPageTitles(); ?></title>
    <meta http-equiv="content-type" content="text/html; charset=<?php echo $configInstance->metaCharset;?>" />
    <meta http-equiv="imagetoolbar" content="no" /><!-- This hides microsofts image toolbar -->
    <meta http-equiv="Content-Script-Type" content="javascript" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta name="content-language" content="<?php echo $configInstance->langKey;?>" />
    <meta name="description" content="<?= $refPluginManager->getAllMetaDescriptions();?>" />
    <meta name="keywords" content="<?=$refPluginManager->getAllMetaKeywords() ?>" />
    <meta name="author" content="<?php echo $configInstance->metaAuthor; ?>" />
    <meta name="copyright" content="<?php echo $configInstance->copyright_linktext;?>" />
    <meta name="distributor" content="<?php echo $configInstance->metaDistributor; ?>" />
    <meta name="revisit-after" content="<?php echo $configInstance->metaRevisitAfter; ?>" />
    <meta name="generator" content="BordeRleSS cms - http://www.borderless-cms.de/" />

    <meta name="robots" content="<?php echo $configInstance->metaRobots;?>" />
<?php
    if(!empty($configInstance->metaRating))
    {
		echo '    <meta name="rating" content="'.$configInstance->metaRating.'" />';
	}
/*
<link rel="start" href="/" title="Startseite von 'Einfach f&uuml;r Alle'" />
<link rel="next" href="/artikel/menues/tag1/" title="Tag 1" />
<link rel="bookmark" title="Zum Seitenanfang" href="#top" />#mmenu
<link rel="bookmark" title="Zur Navigation" href="#mmenu" />
<link rel="bookmark" title="Zu den optionalen Komponenten" href="#optcomp" />
<link rel="content" href="#content" title="Zum Inhaltsbereich" />
<link rel="imprint" href="/impressum/" title="Das Impressum dieser Seite" />
<link rev="made" href="/kontakt/" title="Kontakt zum Betreiber dieser Seite" />
<link rel="help" href="http://doc.borderlesscms.de/" title="Hinweise zur Bedienung und Orientierung" />

<link rel="search" href="/suche/search.pl" title="Volltextsuche mit erweiterten Optionen" />
<link rel="privacy" href="/datenschutz/" title="Hinweise zum Datenschutz" />

In Zukunft sollen auch alternative Stylesheets in Bezug auf Kontrast und Schriftgroesse angeboten werden.
	<link rel="alternate stylesheet" type="text/css" media="screen, projection" title="Black on White" href="css/bow/bow.css" />
	<link rel="alternate stylesheet" type="text/css" media="screen, projection" title="White on Black" href="css/wob/wob.css" />
	<link rel="stylesheet" type="text/css" media="print" href="css/print.css" />
*/

// RSS Feeds
$prot = PluginManager::getPlgInstance('CategoryManager')->getLogic()->isUseSsl() ? 'https' : 'http';
echo '    <link rel="alternate" type="application/rss+xml" title="'
		.$configInstance->rss_title.'" href="'.$prot.'://'
		.$configInstance->siteUrl.'/rss.php" />',"\n";

// create category dependent rss feed
$catRssTitle = $configInstance->rss_title.' - '
		.Factory::getObject('Dictionary')->getTrans('cont.categoryname').': '
		.PluginManager::getPlgInstance('CategoryManager')->getLogic()->getCategoryName();
echo '    <link rel="alternate" type="application/rss+xml" title="'.$catRssTitle
		.' - " href="'.$prot.'://'.$configInstance->siteUrl.'/rss.php?rsscat='
		.PluginManager::getPlgInstance('CategoryManager')->getLogic()->getTechname()
		.'" />',"\n";

// URGENT Fix adm.css display problem!
//$parentmenu_array = PluginManager::getPlgInstance('CategoryManager')->getLogic()->getMenuAncestors($_SESSION['m_id']);
//if($parentmenu_array[0]['categoryname']=='__admin__') {
//	echo '    <link rel="stylesheet" title="default stylesheet" '
//		.'type="text/css" media="all" href="/adm.css" />'."\n";
//} else {
// allgemeines Stylesheet einbinden
	echo '    <link rel="stylesheet" title="default stylesheet" '
		.'type="text/css" media="all" href="http://'
		.$configInstance->siteUrl.'/css/'
		.$_SESSION['cssfile'].'/'.$_SESSION['cssfile'].'.css" />'."\n";
//}

// import css file with font-size information
echo '    <style type="text/css">/*<![CDATA[*/
	  @import url(http://'
		.$configInstance->siteUrl.'/css/'.$_SESSION['cssfile'].'/fs_'.$_SESSION['css_fs'].'.css);
	/*]]>*/</style>'."\n";

// load layout css from fs...
echo $refPluginManager->getAllCss($_SESSION['m_id']);
?>
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
	<script type="text/javascript" src="/includes/sortable.js"></script>

  </head>
  <body>
