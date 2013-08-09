<?php
/* Borderless CMS - the easiest and most flexible way to a valid website
*   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
*   Distributed under the terms and conditions of the GPL as stated in /license.txt
* EXCLUSION:
*   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
*   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
*/

/**
 * @file index.php
 * The starting page for all actions. Checks required php version exists, makes
 * necessary defines and starts processing.
 *
 * @author ahe <aheusingfeld@borderlesscms.de>
 * @since 0.1
 * @ingroup _main
 */
require_once('global_defines.inc.php');


// IMPORTANT comment out the following for live environments
/** Switch error display on for debugging */
define('LOG_ERROR_LEVEL',(E_ALL));
error_reporting(LOG_ERROR_LEVEL);
ini_set('display_errors','Off');
ini_set('display_startup_errors','Off');

// start profiling
require_once 'core/general/Timer.php'; // start timer
$renderTimer = new Timer();
$renderTimer->StartTimer('render');

try {
	// System initialisieren und allgemeine Funktionen laden
	require_once 'inc/init.inc.php';
	BcmsSystem::init();

} catch (MissingConfigFileException $ex) {
	header("location:install/install.php");
} catch (Exception $ex) {
	// \bug URGENT handle this exception
}

// warn if install-dir present
if(file_exists('install')) {
	BcmsSystem::raiseNotice('<b>Install-directory still present.</b> This is a '.
		'massive security issue! Please delete your install directory!',
		BcmsSystem::LOGTYPE_SECURITY,
		BcmsSystem::SEVERITY_WARNING,
		'check install dir', __FILE__, __LINE__, null, false
	);
}

$configObj = BcmsConfig::getInstance();
/** test current category is specified otherwise redirect to starting category! */
if(!session_is_registered('cur_catname')){
	$_SESSION['cur_catname'] = BcmsSystem::getCategoryManager()->getModel()->getTechnameById($configObj->default_cat_id);
	header('Location: '.$_SESSION['cur_catname'].'/', true);
	exit('ERROR: If you see this the redirect to "'.$_SESSION['cur_catname'].'/ failed!');
}
// pre initialize default system plugins
BcmsSystem::initSystemPlugins();

// start initialization of PluginManager
PluginManager::getInstance()->init($_SESSION['m_id']);
PluginManager::getInstance()->checkAllTransactions($_SESSION['m_id']);

require_once 'inc/header.inc.php'; // den Header der Seite laden
?>
<div id="horizon"><a id="top" name="top"></a>
<div id="container" class="floatclear">
<div id="welcome_div">
<h1 id="welcome"
	title="<?php echo $configObj->welcomemessage; ?>"><span><?php
	echo $configObj->welcomemessage; ?></span> <?php
	if(is_string($configObj->page_subtitle)){
		echo '            <em id="subtitle">'.$configObj->page_subtitle.'</em>';
	}
	?></h1>
</div>

<div id="skipLink"><a href="#content"
	title="Dieser Link bringt Sie zum Inhaltsbereich">Zum Inhalt (ALT+2)</a>,
<a href="#shortcuts"
	title="Dieser Link bringt Sie zur Liste mit den Accesskeys">Zu den Shortcuts (ALT+z)</a></div>

<div id="leftside" class="floatclear">
<div id="allcontent" class="floatbox">
<h1><a accesskey="2" href="#content" id="content" name="content"><span>Inhaltsbereich</span></a></h1>
	<?php

	$catLogic =& BcmsSystem::getCategoryManager()->getLogic();
	// Aktuellen Pfad ausgeben
	if($configObj->showPathway) echo $catLogic->createPathway();
	echo $catLogic->createMenuDescription();
	echo '            <div id="articles"';
	if(BcmsSystem::getUserManager()->isLoggedIn()) {
		echo ' class="logged_in"';
	}
	echo '>'."\n";

	echo PluginManager::getInstance()->start($_SESSION['m_id']);
	echo BcmsFactory::getInstanceOf('GuiUtility')->getToTopAnchorDiv(14);
	?></div>
<!-- /articles --></div>
<!-- /#allcontent -->

<div id="menusection">
<h1><a href="#mmenu" id="mmenu" name="mmenu" accesskey="4"><span>Men&uuml;bereich</span></a></h1>
	<?php
	if($configObj->showSystemMenu){
		echo $catLogic->createSystemMenu();
	}
	echo $catLogic->createMainMenu();
	echo $catLogic->createUserMenu();
	?></div>
<!-- /menusection --></div>
<!-- /#leftside --> <?php
if(BcmsSystem::getCategoryManager()->getLogic()->isShowOptPlugins()) {
	// \bug URGENT make opt_components dynamic!!!
	echo '        <div id="opt_components">
          <h1><a href="#optcomp" id="optcomp" name="optcomp" class="unsichtbar" accesskey="6"><span>Verschiedenes</span></a></h1>
';
	if($configObj->showStyleswitcher == 1)
	include 'inc/comp_styleswitcher.inc.php';
	if($configObj->showTop5)
	{
		if(!isset($latestContent)) $latestContent = new LatestContent();
		$latestContent->latestContent();
	}
	echo '        </div>  <!-- /opt_components -->'."\n";
}
echo '      </div>  <!-- /container -->'."\n";
require_once 'inc/footer.php';
echo '    </div>  <!-- /horizon -->'."\n";

// stop rendering timer
$renderTimer->StopTimer('render');

if( ($configObj->debugging_active == 1)
&& BcmsSystem::getUserManager()->hasRight('debugginginfo_view') )
{
	include_once 'inc/sect_debug.inc.php';
}
echo '  </body>
</html>';
session_write_close();
?>