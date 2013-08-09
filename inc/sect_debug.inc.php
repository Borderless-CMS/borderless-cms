<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * Prints out debugging information where this file is included.
 * Debugging information include a form which enables the developer to choose
 * from a set of variables which contents he wants to see.
 *
 * @file sect_debug.inc.php
 * @author ahe <aheusingfeld@borderlesscms.de>
 * @ingroup gui
 */

echo '    <div id="debugging">
';
echo '      <div id="rendertime">Rendertime: '.$renderTimer->GetDuration('render').' sec</div>';
echo '      <div id="memory_used">memory used: '.getMemoryUsage().'</div>';

/**
 * Calculates the currently used memory and returns the formatted result
 *
 * @return String - formatted number or "not available"
 */
function getMemoryUsage(){
	$mem = '<b>not available</b>';

	if(function_exists('memory_get_usage')) {
		$mem = memory_get_usage();
	} else {
		if ( substr(PHP_OS,0,3) == 'WIN'){
			$output = array();
			$succeeded = @exec( 'tasklist /FI "PID eq ' . getmypid() . '" /FO LIST', $output );
			if($succeeded){
				$mem = preg_replace( '/[\D]/', '', $output[5] ) * 1024;
			}
		} else {
			$succeeded = @exec('ps -o rss -p '.getmypid(), $output);
			if($succeeded){
				$mem = $output[1] *1024;
			}
		}
	}
	if(is_numeric($mem)){
		$mem = number_format($mem,',',',','.').' B';
	}
	return $mem;
}


$DEBUG_VARS = array('_SESSION', '_GET', '_POST', 'GLOBALS', '_SERVER', '_FILES'
	, 'BcmsSystem', 'BcmsConfig','UserManager','CategoryManager','Dictionary'
	,'Parser','db', 'ARTICLE_STATUS','tablenames', 'bcms_classes', 'bcms_templates');

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
?>
      <h2 id="debugheader"><span>Select Debugging Vars:</span></h2>
<?php
$debugForm = new cForm();  // @todo use PEAR:HTML_QuickForm instead
$iIndent = 2;
$debugForm->addHeader('debug_form',
	BcmsSystem::getParser()->getServerParameter('REQUEST_URI'),$iIndent);

for($i=0;$i<count($DEBUG_VARS);$i++)
{
	$checked = false;
	if(array_key_exists($DEBUG_VARS[$i],$_SESSION['debug_fields'])){
		$checked = $_SESSION['debug_fields'][$DEBUG_VARS[$i]];
	}
	$debugForm->addElement('checkbox', 'debug_'.$DEBUG_VARS[$i]
		, 'true', $iIndent+2, "\$".$DEBUG_VARS[$i]
		, false, $checked);
}
$debugForm->addElement('submit', 'debug_fields_submit'
 			, BcmsSystem::getDictionaryManager()->getTrans('submit')
 			, $iIndent+2);
$debugForm->addBottom($iIndent);
echo $debugForm->printForm();

?>
        <h3 class="debugsection"><span>content of variables</span></h3>
        <pre>
<?php
$BcmsConfig = BcmsConfig::getInstance();
$BcmsSystem = BcmsSystem::getInstance();
$UserManager = BcmsSystem::getUserManager();
$Dictionary = BcmsSystem::getDictionaryManager();
$CategoryManager = BcmsSystem::getCategoryManager();
$Parser = BcmsSystem::getParser();

	for($i=0;$i<count($DEBUG_VARS);$i++)
	{
		if(	isset($_SESSION['debug_fields'])
			&& array_key_exists($DEBUG_VARS[$i],$_SESSION['debug_fields'])
			&& $_SESSION['debug_fields'][$DEBUG_VARS[$i]])
		{
			echo "\$".$DEBUG_VARS[$i].": "; htmlspecialchars(print_r(${$DEBUG_VARS[$i]}));
		}
	}
?>
        </pre>

      </div>
