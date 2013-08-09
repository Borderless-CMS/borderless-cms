<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * @file header.inc.php
 * Creates and outputs general the XHTML-Header including title, meta-tags, css, javascripts, etc.
 *
 * @todo use dictionary for these texts!!!
 * @todo include completely into GuiUtility
 * @date Created on 03.01.2006
 * @author ahe <aheusingfeld@borderlesscms.de>
 * @ingroup gui
 */

$configInstance = BcmsConfig::getInstance();

// send "no-cache" header information for all browser versions
header ('Expires: Mon, 01 Jan 1990 01:00:00 GMT');
header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header ('Cache-Control: no-cache, no-store, must-revalidate');
header ('Pragma: no-cache');

/* The following line would be required for browsers to really know that they are getting send xhtml
 * but unfortunately that doesn't work! 
 * More info on this: http://www.webdevout.net/articles/beware-of-xhtml
 */ 
//header ('Content-Type: application/xhtml+xml; charset=' . $configInstance->metaCharset);

$parser = BcmsSystem::getParser();

echo '<?xml version="1.0" encoding="'.$configInstance->metaCharset.'"?>',"\n";

$pm = PluginManager::getInstance();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php
echo $configInstance->langKey ?>" lang="<?php
echo $configInstance->langKey ?>">
  <head>
    <title><?php echo $pm->getAllPageTitles(); ?></title>
    <meta http-equiv="content-type" content="application/xhtml+xml; charset=<?php echo $configInstance->metaCharset;?>" />
    <meta http-equiv="Content-Script-Type" content="javascript" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta name="content-language" content="<?php echo $configInstance->langKey;?>" />
    <meta name="description" content="<?php echo $pm->getAllMetaDescriptions();?>" />
    <meta name="keywords" content="<?php echo $pm->getAllMetaKeywords() ?>" />
    <meta name="author" content="<?php echo $configInstance->metaAuthor; ?>" />
    <meta name="copyright" content="<?php echo $configInstance->copyright_linktext;?>" />
    <meta name="distributor" content="<?php echo $configInstance->metaDistributor; ?>" />
    <meta name="revisit-after" content="<?php echo $configInstance->metaRevisitAfter; ?>" />
    <meta name="generator" content="BordeRleSS cms - http://www.borderless-cms.de/" />

    <meta name="robots" content="<?php echo $pm->getMetaRobotsTags();?>" />
<?php echo $pm->getMetaRating(); ?>

    <link rel="start" href="/" title="Startseite von '<?php echo $configInstance->page_title ?>'" />
    <link rel="bookmark" title="Zum Seitenanfang" href="#top" />
    <link rel="bookmark" title="Zur Navigation" href="#mmenu" />
    <link rel="bookmark" title="Zu den optionalen Komponenten" href="#optcomp" />
    <link rel="content" href="#content" title="Zum Inhaltsbereich" />
    <link rel="imprint" href="/impressum/" title="Das Impressum dieser Seite" />
    <link rev="made" href="/kontakt/" title="Kontakt zum Betreiber dieser Seite" />
<?php
/*
    <link rel="next" href="/artikel/menues/tag1/" title="Tag 1" />
    <link rel="help" href="http://doc.borderlesscms.de/" title="Hinweise zur Bedienung und Orientierung" />
    <link rel="search" href="/suche/search.pl" title="Volltextsuche mit erweiterten Optionen" />
    <link rel="privacy" href="/datenschutz/" title="Hinweise zum Datenschutz" />

@todo In Zukunft sollen auch alternative Stylesheets in Bezug auf Kontrast und Schriftgroesse angeboten werden.
    <link rel="alternate stylesheet" type="text/css" media="screen, projection" title="Black on White" href="css/bow/bow.css" />
    <link rel="alternate stylesheet" type="text/css" media="screen, projection" title="White on Black" href="css/wob/wob.css" />
    <link rel="stylesheet" type="text/css" media="print" href="inc/css/print.css" />
*/

// RSS Feeds
$prot = BcmsSystem::getCategoryManager()->getLogic()->isUseSsl() ? 'https' : 'http';
echo '    <link rel="alternate" type="application/rss+xml" title="'
        .$configInstance->rss_title.'" href="'.$prot.'://'
        .$configInstance->siteUrl.'/rss.php" />',"\n";

// create category dependent rss feed
$catRssTitle = $configInstance->rss_title.' - '
        .BcmsSystem::getDictionaryManager()->getTrans('cont.categoryname').': '
        .BcmsSystem::getInstance()->getCategoryManager()->getLogic()->getCategoryName();
echo '    <link rel="alternate" type="application/rss+xml" title="'.$catRssTitle
        .' - " href="'.$prot.'://'.$configInstance->siteUrl.'/rss.php?rsscat='
        .BcmsSystem::getCategoryManager()->getLogic()->getTechname()
        .'" />',"\n";

// allgemeines Stylesheet einbinden
echo '    <link rel="stylesheet" title="default stylesheet" type="text/css" media="all" '.
        'href="'.$configInstance->completeSiteUrl.'/inc/css/'
        .$_SESSION['cssfile'].'/'.$_SESSION['cssfile'].'.css" />'."\n";

// load layout css from fs...
echo $pm->getAllCss($_SESSION['m_id']);

// free used variables
unset($configInstance,$parser,$pm);
?>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
  </head>
  <body>
