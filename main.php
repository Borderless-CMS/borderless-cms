<?php /*
+----------------------------------------------------------------------------+
| B O R D E R L E S S   C M S                                                |
+----------------------------------------------------------------------------+
| (c) Copyright 2004-2006                                                    |
|      by     goldstift (mail@goldstift.de) - www.goldstift.de               |
|      alias  ahe       (aheusingfeld@borderlesscms.de)                      |
+----------------------------------------------------------------------------+*/
if (version_compare(phpversion(), '5', '<')) {
    die('This file was generated for PHP 5');
}

// BORDERLESS: prevents execution of php scripts separetly from the main file
define('BORDERLESS',true);
	ini_set('display_errors','On');
	ini_set('display_startup_errors','On');
/**
 * @author ahe <aheusingfeld@borderlesscms.de>
 * @version Revision: $WCREV$
 * @date Last updated: $WCDATE$
 * @since $WCRANGE$
 * @see $WCURL$
 */
define('BCMS_VERSION','(v0.13.190)');
define('LOG_ERROR_LEVEL',(E_ALL ^ E_NOTICE));
define('BASEPATH',dirname(__FILE__));
error_reporting(LOG_ERROR_LEVEL);

require_once 'includes/set_inc_path.inc.php';     // set include_path
require_once 'classes/sys/Timer.php'; // start timer
$renderTimer = new Timer();
$renderTimer->StartTimer('render');

// start init process
require_once 'includes/init_part1.inc.php';
require_once 'includes/init_part2.inc.php';

//if( ($refBcmsConfig->debugging_active == 1)
//	&& PluginManager::getPlgInstance('UserManager')->hasRight('debugginginfo_view')
//) {
//	ini_set('display_errors','On');
//	ini_set('display_startup_errors','On');
//} else {
//	ini_set('display_errors','Off');
//	ini_set('display_startup_errors','Off');
//}
require_once 'includes/header.inc.php'; // den Header der Seite laden
$refBcmsConfig = BcmsConfig::getInstance();
?>
    <div id="horizon">
      <a id="top" name="top"></a>
      <div id="container" class="floatclear">
        <div id="welcome_div">
          <h1 id="welcome" title="<?php
	echo $refBcmsConfig->welcomemessage; ?>">
            <span><?php
	echo $refBcmsConfig->welcomemessage; ?></span>
<?php
if(is_string($refBcmsConfig->page_subtitle)){
	echo '            <em id="subtitle">'.$refBcmsConfig->page_subtitle.'</em>';
}
?>

          </h1>
        </div>

        <div id="skipLink">
          <a href="#content" title="Dieser Link bringt Sie zum Inhaltsbereich">Zum Inhalt (ALT+2)</a>,
          <a href="#shortcuts" title="Dieser Link bringt Sie zur Liste mit den Accesskeys">Zu den Shortcuts (ALT+z)</a>
        </div>

        <div id="leftside" class="floatclear">
          <div id="allcontent" class="floatbox">
            <h1><a accesskey="2" href="#content" id="content" name="content"><span>Inhaltsbereich</span></a></h1>
<?php

$catLogic = PluginManager::getPlgInstance('CategoryManager')->getLogic();
// Aktuellen Pfad ausgeben
if($refBcmsConfig->showPathway) echo $catLogic->createPathway();
echo $catLogic->createMenuDescription();
if(PluginManager::getPlgInstance('UserManager')->getLogic()->isLoggedIn()) {
	echo '            <div id="articles" class="logged_in">' . "\n";
} else {
	echo '            <div id="articles">' . "\n";
}
echo $refPluginManager->start($_SESSION['m_id']);

echo Factory::getObject('GuiUtility')->getToTopAnchorDiv(14);
echo '            </div> <!-- /articles -->' . "\n";
?>
          </div>  <!-- /#allcontent -->

          <div id="menusection">
            <h1><a href="#mmenu" id="mmenu" name="mmenu" accesskey="4"><span>Men&uuml;bereich</span></a></h1>
<?php
if($refBcmsConfig->showSystemMenu){
	echo $catLogic->createSystemMenu();
}
echo $catLogic->createMainMenu();
echo $catLogic->createUserMenu();
echo $catLogic->createAdminMenu();
?>
          </div>   <!-- /menusection -->
        </div>  <!-- /#leftside -->


<?php
if(PluginManager::getPlgInstance('CategoryManager')->getLogic()->isShowOptPlugins()) {
// URGENT make opt_components dynamic!!!
	echo '        <div id="opt_components">
          <h1><a href="#optcomp" id="optcomp" name="optcomp" class="unsichtbar" accesskey="6"><span>Verschiedenes</span></a></h1>
';
	echo $catLogic->createFontSizeMenu();

	if($refBcmsConfig->showStyleswitcher == 1)
		include 'includes/comp_styleswitcher.inc.php';
	if($refBcmsConfig->showTop5)
	{
		if(!isset($homeObj)) $homeObj = new cHome();
		$homeObj->latestContent();
	}
	echo '        </div>  <!-- /opt_components -->'."\n";
}
echo '      </div>  <!-- /container -->'."\n";
require_once 'includes/footer.php';

// stop rendering timer
$renderTimer->StopTimer('render');
echo '      <div id="rendertime">Rendertime: '.$renderTimer->GetDuration('render').' sec</div>
    </div>  <!-- /horizon -->'."\n";
if( ($refBcmsConfig->debugging_active == 1)
  	&& PluginManager::getPlgInstance('UserManager')->hasRight('debugginginfo_view') )
{
    include_once 'includes/sect_debug.inc.php';
}
echo '  </body>
</html>';
  session_write_close();
?>
