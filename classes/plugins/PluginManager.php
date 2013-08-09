<?php /*
+----------------------------------------------------------------------------+
| B O R D E R L E S S   C M S                                                |
+----------------------------------------------------------------------------+
| (c) Copyright 2004 - 2006                                                  |
|      by goldstift (aheusingfeld@borderlesscms.de)                          |
+----------------------------------------------------------------------------+
*/
if(!defined('BORDERLESS')) exit;

// includes
include_once 'classes/plugins/Plugin_DAL.php';
require_once 'classes/plugins/ModEntries_DAL.php';
require_once 'classes/plugins/ModComps_DAL.php';
require_once 'classes/plugins/MenuModComps_DAL.php';

/**
 * Contains PluginManager class
 *
 * @module PluginManager.php
 * @author ahe <aheusingfeld@borderlesscms.de>
 * @package plugins
 * @version $Id$
 */
class PluginManager extends AbstractManager {

/* variable definition */
    // the versionstring is needed e.g. for getHashcode()
    protected $versionstring = '0.28';
    protected $modname = 'PluginManager';
    private $activePlugins = array();
    private $installedPlugins = array(); // lazy loading
    private $currMainPlugin = null;
    private $mainObject = null;
    private static $plgMgrObj = null;
    protected $dalObj;
    protected $modEntriesObj = null;
    protected $modCompsObj = null;
    protected $catModCompsObj = null;
    protected $configInstance = null;

/* public methods */
    protected function __construct() {
        $this->setTablesToTablearray();

        $this->dalObj = Plugin_DAL::getInstance();
        $this->modEntriesObj = ModEntries_DAL::getInstance();
        $this->modCompsObj = ModComps_DAL::getInstance();
        $this->catModCompsObj = MenuModComps_DAL::getInstance();
    }

    /**
     * Adaption of Singleton pattern
     *
     * @return PluginManager instance of this class
     * @author ahe
     * @date 04.10.2006 21:53:50
     * @package htdocs/classes/plugins
     */
    public static function getInstance() {
        if(self::$plgMgrObj==null)
            self::$plgMgrObj = new PluginManager();
        return self::$plgMgrObj;
    }

    public function init($menuId) {

        $this->getPluginsCurrMenu($menuId);
        $this->setPluginClasses();
        $class = $this->currMainPlugin['classname'];
        if($class!=null && $class!=''
            && in_array($class,array_keys($GLOBALS['bcms_classes'])))
        {
            if($class != $this->modname) {
                $this->mainObject = Factory::getObject($class);
                $this->mainObject->init($menuId);
            } else {
                $this->mainObject = $this;
            }
        }
    }

    /**
     *
     *
     * @param String pluginName - The name of the plugin to be instantiated
     * @return AbstractManager
     * @author ahe
     * @date 15.12.2006 22:50:14
     * @package htdocs/classes/plugins
     */
    public static function getPlgInstance($pluginName)
    {
        if(self::$plgMgrObj==null) self::getInstance();

        if(self::$plgMgrObj->isPluginInstalled($pluginName))
        {
            return Factory::getObject($pluginName);
        }
    }

/* BEGIN ********** helper methods for init process ********** */
    /**
     * To assure that relevant tables are defined in global array, they are set
     * here
     *
     * @author ahe
     * @date 02.11.2006 23:52:24
     * @package htdocs/plugins/dictionary
     * @since 0.14 - 02.11.2006
     */
    protected function setTablesToTablearray(){
        $this->configInstance = BcmsConfig::getInstance();
        $this->configInstance->setTablename('plugins',     'plg_plugins');
        $this->configInstance->setTablename('modentries',  'plg_entries');
        $this->configInstance->setTablename('modcomps',    'plg_comps');
        $this->configInstance->setTablename('menumodcomp', 'cat_plg_comp');
    }

    private function getPluginsCurrMenu($menuId) {
        // URGENT HERE!!! Wieso werden hier Anfuehrungsstriche verwendet? -> Muesste eigentlich gefixt sein (siehe $parser->convStrToCharOnly())
        if(mb_substr($_SESSION['mod']['func'],0,1)=='\''){
            echo '<h1>ACHTUNG, hier nachsehen '.__FILE__.' '.__LINE__.'';
            $_SESSION['mod']['func'] = mb_substr($_SESSION['mod']['func'],1,-1);
        }
        // get main module
        $currMainPlugin = $this->getMainPlugin($menuId);
        $currMainPlugin['func'] = (BcmsFactory::getInstanceOf('Parser')->getGetParameter('func')!=null) ? $_SESSION['mod']['func'] : $currMainPlugin['func'];
        $_SESSION['mod']['name'] = $currMainPlugin['modulename'];
        $_SESSION['mod']['func'] = $currMainPlugin['func'];
        $this->currMainPlugin = $currMainPlugin;

        // get component Plugins
        $plugins = $this->catModCompsObj->getPluginsByMenuId($menuId);
        for ($i = 0; $i < count($plugins); $i++) {
            $this->activePlugins[] = $plugins[$i];
        }
    }

    private function getMainPlugin($menuId) {
        $typeId = PluginManager::getPlgInstance('CategoryManager')->getModel()->getTypeById($menuId);
        if(is_numeric($typeId))
            return $this->modEntriesObj->getMainPluginById($typeId);
        else
            return $typeId;
    }

    /**
     * this methods behaviour differs from the supposed behaviour by
     * AbstractManager! This method collects the classname arrays of all
     * currently active plugins and merges it with the $GLOBALS['bcms_classes']
     *
     * @author ahe
     * @date 29.01.2006 00:05:20
     * @package htdocs/classes/plugins
     * @project bcms_orga
     */
    private function setPluginClasses() {
        // add main module classname
        $GLOBALS['bcms_classes'][$this->currMainPlugin['classname']]
             = $this->currMainPlugin['filename'].'.php';
        // add classnames of component plugins
        for ($i = 0; $i < count($this->activePlugins); $i++) {
            $GLOBALS['bcms_classes'][$this->activePlugins[$i]['classname']]
                 = $this->activePlugins[$i]['filename'].'.php';
        }
    }
/* END ********** helper methods for init process ********** */

    /**
     * returns the current menu's name to be added to teh page title
     *
     * @return string the current menu's name
     * @author ahe
     * @date 01.05.2006 00:20:33
     * @package htdocs/plugins/menues
     */
    public function getPageTitle() {
        return '';
    }

    /**
     * returns the MetaDescription of the current menu
     *
     * @return string
     * @author ahe
     * @date 01.05.2006 00:21:56
     * @package htdocs/plugins/menues
     */
    public function getMetaDescription() {
        return null;
    }

    /**
     * returns the MetaKeywords of the current menu
     *
     * @return string
     * @author ahe
     * @date 01.05.2006 00:23:41
     * @package htdocs/plugins/menues
     */
    public function getMetaKeywords() {
        return null;
    }

    public function getCss($menuId=0){
        return '';
    }

    public function checkTransactions($menuId=0){
        return $this->makeCheck($this->dalObj,'go_mm');
    }

    public function printCategoryConfigForm($menuId) {
    }

    public function printGeneralConfigForm() {
		$dialog = $this->performListAction('plg_table');
		if($dialog!=null) return $dialog;
		// ...else print general table overview

        if( !PluginManager::getPlgInstance('UserManager')->hasViewRight() )
		    return BcmsSystem::raiseNoAccessRightNotice(
				'printGeneralConfigForm()',__FILE__, __LINE__);

        $retValue = $this->listPlugins(true);
        $retValue .= $this->createPluginsForm();
        $retValue .= $this->createModEntriesDialog();
        $retValue .= $this->listModComps(true);
        $retValue .= $this->createModCompsForm();
        $retValue .= $this->listMenuModComps(true);
        $retValue .= $this->createMenuModCompsForm();
        return $retValue;
    }

    public function isPluginInstalled($modname){
        if(array_key_exists($modname,$this->installedPlugins))
            return $this->installedPlugins[$modname];

        if($this->dalObj->isPluginListedInDb(BcmsFactory::getInstanceOf('Parser')->prepDbStrng($modname)))
            $this->installedPlugins[$modname]=true;
        else
            $this->installedPlugins[$modname]=false;

        if(!$this->installedPlugins[$modname])
            BcmsSystem::raiseNotice('Plugin \''.$modname.'\' ist nicht installiert!',
                BcmsSystem::LOGTYPE_CHECK, BcmsSystem::SEVERITY_WARNING,
                'isPluginInstalled()',__FILE__,__LINE__);
        return $this->installedPlugins[$modname];
    }

    /**
     * the main method of the PluginManager-Class
     *
     * @param enclosing_method_arguments
     * @author ahe
     * @date 01.05.2006 01:03:37
     * @package htdocs/classes/plugins
     */
    public function main($menuId) {
        $this->actions = array(
            0 => array('edit', Factory::getObject('Dictionary')->getTrans('edit'), 'Edit'),
            1 => array('delete', Factory::getObject('Dictionary')->getTrans('delete'), 'Delete')
        );
        switch ($this->currMainPlugin['func']) {
            case 'editall':
                $retValue = $this->printGeneralConfigForm();
                break;
            case 'list':
                $retValue = $this->listPlugins();
                $retValue .= $this->listModEntries();
                $retValue .= $this->listModComps();
                break;
            case 'plugins':
                $retValue = $this->listPlugins(true);
                $retValue .= $this->createPluginsForm();
                break;
            case 'modentries':
                $retValue = $this->createModEntriesDialog();
                break;
            case 'modcomps':
                $retValue = $this->listModComps(true);
                $retValue .= $this->createModCompsForm();
                $retValue .= $this->listMenuModComps(true);
                $retValue .= $this->createMenuModCompsForm();
                break;
            default:
                break;
        }
        return $retValue;
    }

/**** Additional methods for PluginManager-functionality *****/

    public function checkAllTransactions($menuId=0) {
        // for all current plugins, check transactions
        if($this->mainObject != null) {
            $transactionsOk = $this->mainObject->checkTransactions($menuId);
        }
    }

    public function getAllCss($menuId=0) {
        if($this->mainObject != null) {
            $css ='';
            // add css of all plugins to css string
            for ($i = 0; $i < count($this->activePlugins); $i++) {
                $classname = $this->activePlugins[$i]['classname'];
                if($classname!=$this->modname)
                    $currObj = Factory::getObject($classname);
                else
                    $currObj = $this;
                $css = $this->appendCss($currObj, $menuId, $css);
            }
            $css = $this->appendCss($this->mainObject, $menuId, $css);
            $css = '<style type="text/css">/*<![CDATA[*/ '."\n"
                .$css."\n".' /*]]>*/</style>';
            return $css;
        } else return false;
    }

    private function appendCss(AbstractManager $obj, $menuId, $css){
        $csstext=$obj->getCss($menuId);
        if(substr($csstext,0,7)=='@import')
            $css = $csstext.$css;
        else
            $css .= $csstext;
        return $css;
    }

    public function getAllPageTitles() {
        $refValue = '';
        if($this->mainObject != null)
            $refValue .= $this->mainObject->getPageTitle();
        if($refValue != null && $refValue!='')
            $refValue .= ' '.BcmsConfig::getInstance()->connector.' ';
        $refValue .= PluginManager::getPlgInstance('CategoryManager')->getPageTitle();
        if($refValue != null && $refValue!='')
            $refValue .= ' '.BcmsConfig::getInstance()->connector.' ';
        $refValue .= BcmsConfig::getInstance()->page_title;
        $refParser = BcmsFactory::getInstanceOf('Parser');
        return $refParser->filterPageTitle($refValue);
    }

    public function getAllMetaDescriptions() {
        $refValue = '';
        if($this->mainObject != null)
            $refValue .= $this->mainObject->getMetaDescription().' ';
        $refValue .= PluginManager::getPlgInstance('CategoryManager')->getMetaDescription().' ';
        $refValue .= BcmsConfig::getInstance()->metaDescription;
        return BcmsFactory::getInstanceOf('Parser')->filterMetaDescription($refValue);
    }

    public function getAllMetaKeywords() {
        $refValue = '';
        if($this->mainObject != null)
            $refValue .= $this->mainObject->getMetaKeywords();
        if($refValue != null && $refValue!='') $refValue .= ',';
        $refMenuManager = PluginManager::getPlgInstance('CategoryManager');
        $refValue .= $refMenuManager->getMetaKeywords();
        if($refValue != null && $refValue!='') $refValue .= ',';
        $refValue .= BcmsConfig::getInstance()->metaKeywords;
        return $refValue;
    }

    /**
     * this method is used as the start point of the "main" action. This method
     * calls the main-method current MainPlugin.
     *
     * @param int $menuId
     * @author ahe
     * @date 28.01.2006 23:15:43
     * @package htdocs/classes/plugins
     */
    public function start($menuId) {
        $catManager = PluginManager::getPlgInstance('CategoryManager');
        $userManager = PluginManager::getPlgInstance('UserManager');
        if($catManager==null || $userManager==null) return null;

        // global view right check for current category
        $typeId = $catManager->getLogic()->getType();
        $viewright = $catManager->getLogic()->getViewRight();

        $mainfunc = $this->modEntriesObj->getMainPluginById($typeId);
        if( // if user is not logged in and category is for logged in users only
            ((!$userManager->getLogic()->isLoggedIn())
                && ($catManager->getLogic()->isUserOnly()))
            // or if function is module's main function and  user has no right to view it
         ||	($this->currMainPlugin['func']==$mainfunc['func']	&& !$userManager->hasRight($viewright))
        ){
            // send error message and skip display!
            return BcmsSystem::raiseNoAccessRightNotice('start()',__FILE__, __LINE__);
        }

        if($this->mainObject!=null)
            return $this->mainObject->main($menuId);
    }

    public function getCurrentMainPlugin() {
        return $this->currMainPlugin;
    }

    public function getModEntries_DAL(){ return $this->modEntriesObj; }

    public function getModComps_DAL(){ return $this->modCompsObj; }

    public function getMenuModComps_DAL(){ return $this->menuModCompsObj; }

/* ************   END OF PUBLIC METHODS   ************ */

	protected function createEditDialog(){
        if( !PluginManager::getPlgInstance('UserManager')->hasEditRight() ) // TODO plg_cat_conf-edit_right should actually be checked here!
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createEditDialog()',__FILE__, __LINE__);

		$result = HTMLTable::getAffectedIds();
		$id=$result[0];
		$element = $this->dalObj->getObject($id);
		$form = $this->dalObj->getForm('plugineditform','editPluginElement'
			,Factory::getObject('Dictionary')->getTrans('save'),$element);
		return $form->toHTML();
	}

	protected function createDeleteDialog(){
        if( !PluginManager::getPlgInstance('UserManager')->hasDeleteRight() ) // TODO plg_cat_conf-delete_right should actually be checked here!
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createDeleteDialog()',__FILE__, __LINE__);

		$heading = Factory::getObject('Dictionary')->getTrans('plg.h.deleteEntries');
		return $this->createDeletionConfirmFormForHTML_TableForms($heading);
	}

    public function createModEntriesDialog() {
        $retValue .= $this->listModEntries(true);
        $retValue .= $this->createModEntriesForm();
        return $retValue;
    }

    /**
     *
     *
     * @author ahe
     * @date 27.01.2006 20:07:42
     * @package plugins
     * @project bcms
     */
    private function createModEntriesForm() {
        $retValue = $this->makeCheck($this->modEntriesObj,'go_modentries');
        $form = $this->modEntriesObj->getForm('modent_config','go_modentries',
            Factory::getObject('Dictionary')->getTrans('save'));
        if($retValue==1) $retValue = '';
        $retValue .= $form->toHtml();
        return $retValue;
    }

    /**
     *
     *
     * @author ahe
     * @date 27.01.2006 20:07:42
     * @package plugins
     * @project bcms
     */
    private function listPlugins($createForm=false) {
        $tableObj = new HTMLTable('plg_table');
        $tableObj->setTranslationPrefix('plg.');
        $tableObj->setActions($this->actions);
        $tableObj->setBounds('page',null,$this->dalObj->getNumberOfEntries());
        $limit = $tableObj->getListLimit();
        $offset = $tableObj->getListOffset();
        $plugins = $this->dalObj->select('list',null,null,$offset,$limit);
        $tableObj->setData($plugins);
        unset($plugins);
        return $tableObj->render('Aktuelle Plugins','module_id',$createForm); //TODO use dictionary
    }

    /**
     *
     *
     * @author ahe
     * @date 27.01.2006 20:07:42
     * @package plugins
     * @project bcms
     */
    private function listModEntries($createForm=false) {
        $tableObj = new HTMLTable('me_table');
        $tableObj->setActions($this->actions); // URGENT these are the actions of Plugins NOT ModEntries!!!
        $tableObj->setTranslationPrefix('plg.');
        $tableObj->setBounds('page',null,$this->modEntriesObj->getNumberOfEntries());
        $limit = $tableObj->getListLimit();
        $offset = $tableObj->getListOffset();
        $modEntries = $this->modEntriesObj->select('listentries',null,null,$offset,$limit);
        // get maximum string length
        $maxLen = BcmsConfig::getInstance()->dict_max_trans_length;
        $statusArr = BcmsConfig::getInstance()->getTranslatedStatusList();
        for ($i = 0; $i < count($modEntries); $i++) {
            foreach($modEntries[$i] as $key => $value) {
                if($key == 'status') {
                    $modEntries[$i][$key] = $statusArr[$value];
                }
                // cut string if necessary
                if(mb_strlen($value)>$maxLen)
                    $modEntries[$i][$key] = mb_substr($value,0,($maxLen-3)).'...';
            }
        }
        $tableObj->setData($modEntries);
        unset($modEntries);
        return $tableObj->render('Hauptseiten-Plugin (PluginEntries) ' .
                'erstellen','me_id',$createForm);
    }

    private function listModComps($createForm=false) {
        $tableObj = new HTMLTable('mc_table');
        $tableObj->setActions($this->actions);// URGENT these are the actions of Plugins NOT ModEntries!!!
        $tableObj->setTranslationPrefix('plg.');
        $tableObj->setBounds('page',null,$this->modCompsObj->getNumberOfEntries());
        $limit = $tableObj->getListLimit();
        $offset = $tableObj->getListOffset();
        $modComps = $this->modCompsObj->select('listentries',null,null,$offset,$limit);
        // get maximum string length
        $maxLen = BcmsConfig::getInstance()->dict_max_trans_length;
        $statusArr = BcmsConfig::getInstance()->getTranslatedStatusList();
        for ($i = 0; $i < count($modComps); $i++) {
            foreach($modComps[$i] as $key => $value) {
                if($key == 'status') {
                    $modComps[$i][$key] = $statusArr[$value];
                }
                // cut string if necessary
                if(mb_strlen($value)>$maxLen)
                    $modComps[$i][$key] = mb_substr($value,0,($maxLen-3)).'...';
            }
        }
        $tableObj->setData($modComps);
        unset($modComps);
        return $tableObj->render('Komponenten-Plugin (PluginComponents) erstellen',
            'mc_id',$createForm);
    }

    private function listMenuModComps($createForm=false) {
        $tableObj = new HTMLTable('mmc_table');
        $tableObj->setTranslationPrefix('plg.');
        $tableObj->setBounds('page',null,$this->catModCompsObj->getNumberOfEntries());
        $offset = $tableObj->getListOffset();
        $limit = $tableObj->getListLimit();
        $tableObj->setActions($this->actions);// URGENT these are the actions of Plugins NOT ModEntries!!!
        $menuModComps = $this->catModCompsObj->getList($offset,$limit);
        // get maximum string length
        $maxLen = BcmsConfig::getInstance()->dict_max_trans_length;
        for ($i = 0; $i < count($menuModComps); $i++) {
            foreach($menuModComps[$i] as $key => $value) {
                // cut string if necessary
                if(mb_strlen($value)>$maxLen)
                    $menuModComps[$i][$key] = mb_substr($value,0,($maxLen-3)).'...';
            }
        }
        $tableObj->setData($menuModComps);
        unset($menuModComps);
        return $tableObj->render('Komponenten-Plugin den Men&uuml;s zuordnen',
            'mmc_id',$createForm);
    }

    /**
     *
     *
     * @author ahe
     * @date 27.01.2006 20:07:42
     * @package plugins
     * @project bcms
     */
    private function createModCompsForm() {
        $retValue = $this->makeCheck($this->modCompsObj,'go_modcomps');
        $form = $this->modCompsObj->getForm('modcom_config','go_modcomps',
            Factory::getObject('Dictionary')->getTrans('save'));
        if($retValue==1) $retValue = '';
        $retValue .= $form->toHtml();
        return $retValue;
    }

    /**
     *
     *
     * @author ahe
     * @date 27.01.2006 20:07:42
     * @package plugins
     * @project bcms
     */
    private function createMenuModCompsForm() {
        $retValue = $this->makeCheck($this->catModCompsObj,'go_menumodcomps');
        $form = $this->catModCompsObj->getForm('menumodcom_config','go_menumodcomps',
            Factory::getObject('Dictionary')->getTrans('save'));
        if($retValue==1) $retValue = '';
        $retValue .= $form->toHtml();
        return $retValue;
    }

    public function createPluginsForm() {
        $form = $this->dalObj->getForm('mmconfig','go_mm',
            Factory::getObject('Dictionary')->getTrans('save'));
        return $form->toHtml();
    }

}
?>