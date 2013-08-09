<?php
/**
* @author ahe; aheusingfeld@borderlesscms.de
*
*   checks whether the needed global vars are set.
*   if not they are set with default values.
*/
$parser = BcmsFactory::getInstanceOf('Parser');
$config = BcmsConfig::getInstance();

$cur_mname = $parser->getGetParameter('cur_mname');
if($cur_mname!=null && ($cur_mname!='') && is_string($cur_mname) )
{
    // this GET is being validated for security purpose
    // TODO Factory::getObject('CategoryManager') is being used as PluginManager cannot be initialized yet
	$menuId = Factory::getObject('CategoryManager')->getModel()->getIdByName($cur_mname);
    if(!is_array($menuId) && is_numeric($menuId) && $menuId>0)
    {	// if there is a result... fetch it...
	    $_SESSION['m_id'] = $menuId; // IMPORTANT!!!
		$_SESSION['cur_catname'] = $cur_mname;
	    Factory::getObject('CategoryManager')->getLogic()->loadVars($menuId);
    }
    elseif($cur_mname!='error') // TODO !='error' is a bugfix. fix reason ASAP
    { 	// ... else use default m_id
    	$msg = 'FEHLER: Angegebene Rubrik "'.$cur_mname.'" existiert nicht! ' .
    			'Bitte wählen Sie die Rubrik aus dem Menü.'; // TODO Use dictionary here!
    	BcmsSystem::raiseNotice($msg, BcmsSystem::LOGTYPE_CHECK,
				BcmsSystem::SEVERITY_WARNING, 'get_category_id-section',__FILE__,__LINE__);
	    $_SESSION['m_id'] = $config->default_cat_id;
    }
}
else
{ 		// if there is an error and the parameter cur_mname is not a string...
	    $_SESSION['m_id'] = $config->default_cat_id;
}

/*   FUER NEUE MOD_REWRITE RULES (20060105 ahe) */
$modname = (!isset($_SESSION['mod']['name'])) ? $config->default_modname : $_SESSION['mod']['name'];
$modfunc = (!isset($_SESSION['mod']['func'])) ? $config->default_modfunc : $_SESSION['mod']['func'];
$modfunc = ($parser->getGetParameter('func')!=null) ? $refParser->convStrToCharOnly($parser->getGetParameter('func')) : $modfunc;
$modoid = ($parser->getGetParameter('oid')!=null) ? intval($parser->getGetParameter('oid')) : $config->default_modoid;
$modoname = ($parser->getGetParameter('oname')!=null && preg_match('/[\w]/',$parser->getGetParameter('oname'))) ? (string)$parser->getGetParameter('oname') : null;

if($modoname != null) {
	$_SESSION['mod'] = array(
		'name' => $modname
		, 'func' => $modfunc
		, 'oname' => $modoname);
} else {
	$_SESSION['mod'] = array(
		'name' => $modname
		, 'func' => $modfunc
		, 'oid' => $modoid);
}

// _____________________________________________________________________________
// TODO Nachfolgendes sollte von der StyleManager-Klasse behandelt werden
// folgendes sollte in das Modul "stylemanager" verschoben werden!
  // CSS-File
  $_SESSION['cssfile'] = (!isset($_SESSION['cssfile'])) ? $config->default_css : $_SESSION['cssfile'];
  // wurde ein anderes css gewuenscht?
  if($parser->getGetParameter('css')!=null) {
    $_SESSION['cssfile'] = (substr($parser->getGetParameter('css'),0,4)!='http') ? str_replace('..','',$parser->getGetParameter('css')) : $config->default_css;
  }
  // css file for font-size settings
  $_SESSION['css_fs'] = (!isset($_SESSION['css_fs'])) ? $config->default_fs_css : $_SESSION['css_fs'];
  if($parser->getGetParameter('css_fs')!=null) {
    $_SESSION['css_fs'] = (substr($parser->getGetParameter('css_fs'),0,4)!='http') ? $parser->getGetParameter('css_fs') : $config->default_fs_css;
  }
?>
