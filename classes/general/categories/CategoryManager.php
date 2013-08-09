<?php
require 'classes/general/categories/Category_DAL.php';
require 'classes/general/categories/Category.php';
/**
 * Contains MenuManager class
 *
 * @module MenuManager.php
 * @author ahe <aheusingfeld@borderlesscms.de>
 * @package plugins/menues
 * @version $Id: $WCREV$
 * @modified $WCMODS?Modified:Not modified$
 * @link $WCURL$
 * @since $WCRANGE$ - $WCDATE$
 */

class CategoryManager extends AbstractManager {

	// the versionstring is needed e.g. for getHashcode()
	protected $versionstring = '0.15';
	protected $modname = 'CategoryManager';
    protected $dalObj   = null;  // saves Category_DAL class reference
    protected $logicObj = null;
    protected $configInstance = null;
    protected $parser = null;
    private $workMenuId;
    private $workMenuname;

	public function __construct() {
		$this->init($_SESSION['m_id']);
	}

	public function init($catId){
		$this->configInstance = BcmsConfig::getInstance();
		$this->setTablesToTablearray();
		$this->parser = BcmsFactory::getInstanceOf('Parser');
		$this->dalObj = Category_DAL::getInstance();
		$this->workMenuId = ($this->parser->getGetParameter('cat_id')!=null) ? intval($this->parser->getGetParameter('cat_id')) : $catId;
		$this->workMenuname = isset($_SESSION['mod']['oname']) ? $_SESSION['mod']['oname'] : null;
		$this->workMenuId = $this->dalObj->getIdByName($this->workMenuname );
		$this->logicObj = new Category($this);
	}

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
		$this->configInstance->setTablename('menu', 'plg_cat');
		$this->configInstance->setTablename('menu_layout_zo', 'cat_layout_zo');
	}

	public function getCurrentWorkMenuId() {
		return $this->workMenuId;
	}

	public function main($catId){
		$myModArray = PluginManager::getInstance()->getCurrentMainPlugin();
		switch ($myModArray['func']) {
			case 'list':
				return $this->showMenuList();
			case 'move':
				return $this->showMenuMoveDialog();
			case 'edit':
				return $this->showEditMenuDialog();
			case 'edit_layout':
				return $this->createEditMenuLayoutForm($this->workMenuId);
			case 'add_top':
			case 'add_bottom':
				return $this->showAddMenuDialog(true);
			case 'del':
				return $this->showDelConfirmDialog();
			default:
				break;
		}
	}

	/**
	 * returns the current menu's name to be added to teh page title
	 *
	 * @return string the current menu's name
	 * @author ahe
	 * @date 01.05.2006 00:20:33
	 * @package htdocs/plugins/menues
	 */
	public function getPageTitle() {
		return $this->logicObj->getCategoryName();
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
		return $this->logicObj->getMetaDescription();
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
		return $this->logicObj->getKeywords;
	}

	public function getCss($catId=0){
		return $this->logicObj->getAdditionalCss();
	}

	public function checkTransactions($catId=0)
	{
		$myModArray = PluginManager::getInstance()->getCurrentMainPlugin();
		$prot = ($this->getLogic()->isUseSsl()) ? 'https' : 'http';
		$location = $prot.'://'
			.$this->parser->getServerParameter('HTTP_HOST')
			.'/'.$this->getLogic()->getTechname().'/';

		switch ($myModArray['func']) {
			case 'moveup':
				$currMenu = $this->dalObj->getMoveObject('cat_id = '.$this->workMenuId);
				if($this->logicObj->moveMenuUp($currMenu))
					header('Location: '.$location, true);
				break;
			case 'movedown':
				$currMenu = $this->dalObj->getMoveObject('cat_id = '.$this->workMenuId);
				if($this->logicObj->moveMenuDown($currMenu))
					header('Location: '.$location, true);
				break;
			case 'moveleft':
				$currMenu = $this->dalObj->getMoveObject('cat_id = '.$this->workMenuId);
				if($this->logicObj->moveMenuLeft($currMenu))
					header('Location: '.$location, true);
				break;
			case 'moveright':
				$currMenu = $this->dalObj->getMoveObject('cat_id = '.$this->workMenuId);
				if($this->logicObj->moveMenuRight($currMenu))
					header('Location: '.$location, true);
				break;
			case 'edit':
				return $this->makeCheck($this->dalObj,'go_mm',$_POST,'update'
					,'cat_id = '.$this->workMenuId);
			case 'edit_layout':
				return $this->checkEditLayoutAssoc();
			case 'add_top':
				return $this->checkTransactionAddMenu(false);
			case 'add_bottom':
				return $this->checkTransactionAddMenu(true);
			case 'del':
				break;
			case 'delconfirm':
				$this->dalObj->delete($this->workMenuId,$this->logicObj);
				header('Location: '.$prot.'://'.$this->parser->getServerParameter('HTTP_HOST').'/'
					.$this->logicObj->getTechname().'/', false);
				break;
			default:
				break;
		}
	}

	private function checkEditLayoutAssoc() {
		require_once 'HTML/QuickForm.php';
		require_once 'HTML/QuickForm/advmultiselect.php';
		$form = new HTML_QuickForm('amsBasic2');
		if ($form->validate()) {
		    $clean = $form->getSubmitValues();
		    $retVal = $this->deleteMenuLayoutAssoc($this->workMenuId);
		    if(!$retVal) return false;
		    foreach ($clean['layout'] as $layoutId) {
				$retVal = $this->addMenuLayoutAssoc($this->workMenuId,$layoutId);
			    if(!$retVal) return false;
			}
		}
		return true;
	}

	private function deleteMenuLayoutAssoc($catId){
		$sql = 'DELETE FROM '.$this->configInstance->getTablename('menu_layout_zo')
			.' WHERE fk_cat='.$catId;

	 	$result = $GLOBALS['db']->query($sql);
		if ($result instanceof PEAR_ERROR)	{
			return BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_DELETE,
				BcmsSystem::SEVERITY_ERROR, 'deleteMenuLayoutAssoc()',
				__FILE__,__LINE__);
		}
	  	return $result;
	}

	private function addMenuLayoutAssoc($catId,$layoutId,$isDefault=0){
		$sql = 'INSERT INTO '.$this->configInstance->getTablename('menu_layout_zo')
			.' (fk_cat, fk_layout, is_default) VALUES ('.$catId.', '
			.$layoutId.', '.$isDefault.')';;

	 	$result = $GLOBALS['db']->query($sql);
		if ($result instanceof PEAR_ERROR)	{
			return BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_INSERT,
				BcmsSystem::SEVERITY_ERROR, 'addMenuLayoutAssoc()',
				__FILE__,__LINE__);
		}
	  	return $result;
	}

	private function checkTransactionAddMenu($createBottom) {
		if(!PluginManager::getPlgInstance('UserManager')->hasRight('category_add')) return(false);
		if(!isset($_POST['menu_add_submit'])) return false;

		// get recordset of the referenced menu
		$refMenu =
			$this->dalObj->getMoveObject('cat_id = '.$this->workMenuId);
		$menuData = $_POST;
		/**
		 * @see http://www.traum-projekt.com/forum/73-workshops-und-
		 * tutorials/58359-workshop-nested-sets.html
		 */
		if($createBottom)
		{
			$menuData['lft'] = $refMenu['rgt']+1;
			$menuData['rgt'] = $refMenu['rgt']+2;
			$referenceNode = $refMenu['rgt'];
		} else {
			$menuData['lft'] = $refMenu['lft'];
			$menuData['rgt'] = $refMenu['lft']+1;
			$referenceNode = $refMenu['lft']-1;
		}
		$menuData['root_id'] = $refMenu['root_id'];
		/* methode updateNodes benutzen!!!
		*/
		if(!$this->dalObj->updateNodes('+2',$referenceNode)) return false;
		return $this->makeCheck($this->dalObj,'menu_add_submit',$menuData,'insert');
	}

	public function printGeneralConfigForm(){
	}

	public function printCategoryConfigForm($catId){
		return $this->createEditMenuForm($catId);
	}

	private function createEditMenuLayoutForm($catId){
		require_once 'HTML/QuickForm.php';
		require_once 'HTML/QuickForm/advmultiselect.php';

		// query to get all layouts in db
		// TODO use ArticleLayoutManager for this!
		$queryAll = 'SELECT layout_id, layoutname '
		          . 'FROM '.$this->configInstance->getTablename('layoutpresets');

		// query to get all layouts related to specified category
		$querySel = 'SELECT fk_layout FROM '
			.$this->configInstance->getTablename('menu_layout_zo')
			.' WHERE fk_cat='.$catId;

		// execute query to get ident of users affected
		$associated_layouts =&$GLOBALS['db']->getCol($querySel);


		$form = new HTML_QuickForm('amsBasic2','post',$_SERVER['REQUEST_URI']);
		$form->removeAttribute('name');  // XHTML compliance

		$form->addElement('header', null, 'Menü "'.$_SESSION['mod']['oname']
			.'" Layoutstrukturen zuordnen'); // TODO use dictionary!

		$ams =& $form->addElement('advmultiselect', 'layout',
		    array('Layoutstrukturen:', 'Verfügbar', 'Zugeordnet'),         // TODO use dictionary
		    null,                                             // datas
		    array('style' => 'width:200px;')                  // custom layout
		);

		// load QFAMS values (unselected and selected)
		$ams->load($GLOBALS['db'], $queryAll, 'layoutname', 'layout_id', $associated_layouts);

		$form->addElement('submit', 'send', 'Send');

	    return $form->toHtml();
	}

	private function createEditMenuForm($catId){
		if (!PluginManager::getPlgInstance('UserManager')->hasEditRight())
		    return BcmsSystem::raiseNoAccessRightNotice(
				'showContentList()',__FILE__, __LINE__);

		$columns = $this->dalObj->select('list_everything','cat_id = '.$catId);
		if(!$columns)
			return BcmsSystem::raiseNotice('Es ist ein Fehler aufgetreten!',
				BcmsSystem::LOGTYPE_CHECK, BcmsSystem::SEVERITY_ERROR,
				'createEditMenuForm()',__FILE__, __LINE__);

		if(!PluginManager::getPlgInstance('UserManager')->hasRight($columns[0]['fk_edit_right']))
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createEditMenuForm()',__FILE__, __LINE__);

		// build link to category dependent plugin configuration if user possess right
		$refGui = Factory::getObject('GuiUtility');
		$heading = $refGui->createHeading(3,
			Factory::getObject('Dictionary')->getTrans('h.CategoryEdit'));

		$pluginConfigLink = '';
		if(PluginManager::getPlgInstance('UserManager')->hasRight($columns[0]['fk_plg_conf_right'])){
			$prot = ($columns[0]['use_ssl']) ? 'https' : 'http';
			$pluginConfigLink = $refGui->createAnchorTag(
				$prot.'://'.$this->configInstance->siteUrl.'/'.$columns[0]['techname'].'/config',
				Factory::getObject('Dictionary')->getTrans('plugin_config')
			);
		}

		return $this->createMenuForm($heading.$pluginConfigLink,'go_mm',$columns[0]);
	}

	private function createAddMenuForm($catId){
		$heading = Factory::getObject('GuiUtility')->createHeading(3,Factory::getObject('Dictionary')->getTrans('h.CatAdd'));
		return $this->createMenuForm($heading,'menu_add_submit');
	}

	private function createMenuForm($heading,$buttonName = 'menu_add_submit', $cols=null){
		$form = $this->dalObj->getForm('menuform',$buttonName
			,Factory::getObject('Dictionary')->getTrans('save'), $cols);
		return $heading.$form->toHtml();
	}

	private function showEditMenuDialog() {
		return $this->createEditMenuForm($this->workMenuId);
	}

	private function showAddMenuDialog() {
		if (!PluginManager::getPlgInstance('UserManager')->hasRight('category_add'))
		    return BcmsSystem::raiseNoAccessRightNotice(
				'showContentList()',__FILE__, __LINE__);

		return $this->createAddMenuForm($this->workMenuId);
	}

	private function showDelConfirmDialog(){
		$retStr = Factory::getObject('GuiUtility')->createHeading(3,
			Factory::getObject('Dictionary')->getTrans('cat.h.delete'),
			14, 'menuheader');
		$retStr .= '
		  <form id="menuDelConfirmForm" action="/'.$this->logicObj->getTechname()
		  		.'/delconfirm/'
		  		.$this->parser->getGetParameter('oname')
				.'" method="post">
			  <label for="action">'.Factory::getObject('Dictionary')->getTrans('cat.confirm_text')
			  	.': '.$this->parser->getGetParameter('oname').'</label>
			  <input type="submit" name="action" value="'
			  .Factory::getObject('Dictionary')->getTrans('yes').'" />
		  </form>'."\n";
		$retStr .= '
		  <form id="menuDelAbortForm" action="/'.$this->getLogic()->getTechname()
		  .'/" method="post">
			  <input type="submit" name="action" value="'
			  .Factory::getObject('Dictionary')->getTrans('no').'" />
		  </form>'."\n";
		return $retStr;

	}

	private function showMenuMoveDialog() {
		if (!PluginManager::getPlgInstance('UserManager')->hasRight('category_move'))
		    return BcmsSystem::raiseNoAccessRightNotice(
				'showMenuMoveDialog()',__FILE__, __LINE__);
		return $this->logicObj->createTreeMove();
	}

	private function showMenuList(){
		if (!PluginManager::getPlgInstance('UserManager')->hasRight('category_view_list'))
		    return BcmsSystem::raiseNoAccessRightNotice(
				'showMenuList()',__FILE__, __LINE__);
	    return $this->logicObj->printCategoryTree();
	}
}
?>