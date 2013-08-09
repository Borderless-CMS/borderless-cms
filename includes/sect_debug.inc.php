<?php
/**
* Prints out debugging information on top of the page
*
*/
$DEBUG_VARS = array('_SESSION', '_GET', '_POST', 'GLOBALS', '_SERVER', '_FILES'
	, 'BcmsConfig','UserManager','CategoryManager','ContentManager','Parser'
	,'db', 'ARTICLE_STATUS','tablenames', 'bcms_classes', 'bcms_templates');

// if new debug vars are selected, update session var
if(isset($_POST['debug_fields_submit']))
{
	for($i=0;$i<count($DEBUG_VARS);$i++)
	{
		$_SESSION['debug_fields'][$DEBUG_VARS[$i]] =
			(isset($_POST['debug_'.$DEBUG_VARS[$i]])) ? true : false;
	}
}
if(!array_key_exists('debug_fields', $_SESSION)) {
	$_SESSION['debug_fields'] = array();
}
// Klassen pfad-/ existenzabhaengig laden!
?>
    <div id="debugging">
      <h2 id="debugheader"><span>Select Debugging Vars:</span></h2>
<?php
$debugForm = new cForm();  // TODO use PEAR:HTML_QuickForm instead
$iIndent = 2;
$debugForm->addHeader('debug_form',
	BcmsFactory::getInstanceOf('Parser')->getServerParameter('REQUEST_URI'),$iIndent);

for($i=0;$i<count($DEBUG_VARS);$i++)
{
	$debugForm->addElement('checkbox', 'debug_'.$DEBUG_VARS[$i]
		, 'true', $iIndent+2, "\$".$DEBUG_VARS[$i]
		, false, $_SESSION['debug_fields'][$DEBUG_VARS[$i]]);
}
$debugForm->addElement('submit', 'debug_fields_submit'
 			, Factory::getObject('Dictionary')->getTrans('submit')
 			, $iIndent+2);
$debugForm->addBottom($iIndent);
echo $debugForm->printForm();

?>
        <h3 class="debugsection"><span>content of variables</span></h3>
        <pre>
<?php
$BcmsConfig = BcmsConfig::getInstance();
$UserManager = PluginManager::getPlgInstance('UserManager');
$CategoryManager = PluginManager::getPlgInstance('CategoryManager');
$ContentManager = PluginManager::getPlgInstance('ContentManager');
$Parser = Factory::getObject('Factory');

	for($i=0;$i<count($DEBUG_VARS);$i++)
	{
		if(isset($_SESSION['debug_fields']) && $_SESSION['debug_fields'][$DEBUG_VARS[$i]]) {
			echo "\$".$DEBUG_VARS[$i].": "; htmlspecialchars(print_r(${$DEBUG_VARS[$i]}));
		}
	}
?>
        </pre>

      </div>
