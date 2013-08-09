<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * CategoryManager class
 *
 * @author ahe <aheusingfeld@borderlesscms.de>
 * @since 0.11
 * @class CategoryManager
 * @ingroup categories
 * @package categories
 */
class CategoryManager extends AbstractManager {

	// the versionstring is needed e.g. for getHashcode()
	protected $versionstring = '0.15';
	protected $modname = 'CategoryManager';

	/**
     * @var Category_DAL
     */
	protected $dalObj   = null;  // saves Category_DAL class reference

    /**
     * @var Category
     */
    private $logicObj = null;
    private $workMenuId;
    private $workMenuname;

    public function __construct() {
    	$cat_id= array_key_exists('m_id',$_SESSION) ? $_SESSION['m_id'] : null;
    	$this->init($cat_id);
    }

    public static function getIdByTechname($techname) {
    	$cat_id= array_key_exists('m_id',$_SESSION) ? $_SESSION['m_id'] : null;
    	BcmsConfig::getInstance()->setTablename('menu', 'plg_cat');
    	BcmsConfig::getInstance()->setTablename('menu_layout_zo', 'cat_layout_zo');
		require_once 'core/plugins/categories/Category_DAL.php';
    	return Category_DAL::getInstance()->getIdByName($techname);
	}

	public function init($catId){
		BcmsConfig::getInstance()->setTablename('menu', 'plg_cat');
		BcmsConfig::getInstance()->setTablename('menu_layout_zo', 'cat_layout_zo');
		$this->workMenuId = (BcmsSystem::getParser()->getGetParameter('cat_id')!=null) ? intval(BcmsSystem::getParser()->getGetParameter('cat_id')) : $catId;
		$this->workMenuname = isset($_SESSION['mod']['oname']) ? $_SESSION['mod']['oname'] : null;
		$this->workMenuId = $this->getModel()->getIdByName($this->workMenuname );
	}

	public function getCurrentWorkMenuId() {
		return $this->workMenuId;
	}

	public function main($catId){
		$myModArray = PluginManager::getInstance()->getCurrentMainPlugin();
		switch ($myModArray['func']) {
			case 'admin':
				return $this->showAdmMenu();
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
	 */
	public function getPageTitle() {
		return $this->getLogic()->getCategoryName();
	}

	/**
	 * returns the MetaDescription of the current menu
	 *
	 * @return string
	 * @author ahe
	 * @date 01.05.2006 00:21:56
	 */
	public function getMetaDescription() {
		return $this->getLogic()->getMetaDescription();
	}

	/**
	 * returns the MetaKeywords of the current menu
	 *
	 * @return string
	 * @author ahe
	 * @date 01.05.2006 00:23:41
	 */
	public function getMetaKeywords() {
		return $this->getLogic()->getKeywords();
	}

	public function getCss($catId=0){
		return $this->getLogic()->getAdditionalCss();
	}

	/**
	 * Returns the logic class of the CategoryManager
	 *
	 * @return Category - instance logic class
	 * @author ahe  <aheusingfeld@borderlesscms.de>
	 * @date 18.04.2007
	 * @since 0.13.153
	 */
	public function getLogic(){
		if(!isset($this->logicObj)){
			require_once 'core/plugins/categories/Category.php';
			$this->logicObj = new Category($this);
		}
		return $this->logicObj;
	}

	/**
	 * Returns a child instance of DataAbstractionLayer
	 *
	 * @return DataAbstractionLayer a child instance of DataAbstractionLayer
	 * @author ahe  <aheusingfeld@borderlesscms.de>
	 * @date 18.04.2007
	 * @since 0.13.153
	 */
	public function getDalObj(){
		if(!isset($this->dalObj)){
			require_once 'core/plugins/categories/Category_DAL.php';
			$this->dalObj = Category_DAL::getInstance();
		}
		return $this->dalObj;
	}

	/**
	 *
	 *
	 * @param boolean indentTree - specify whether values shall be indented by &amp;nbsp;
	 * @return Array - description
	 * @access public
	 * @author ahe
	 * @since 06.03.2007 11:02:46
	 */
	public function getCategoryTree($indentTree=false) {
		$loggedIn = BcmsSystem::getUserManager()->isLoggedIn();
		$allMenuesSys = $this->getDalObj()->getSmallTreeList('__system__',false,$loggedIn);
	    $allMenuesMain = $this->getDalObj()->getSmallTreeList('__main__',false,$loggedIn);
	    $allMenuesAdmin = $this->getDalObj()->getSmallTreeList('__admin__',false,$loggedIn);
	    $allMenues = array_merge($allMenuesSys,$allMenuesMain,$allMenuesAdmin);
	    for ($i = 0; $i < count($allMenues); $i++) {
	      // add indent in front of menu names
	      $spaces = '';
	      for ($k = 0; $k < ($allMenues[$i]['level']-1)*3; $k++) {
	        $spaces .= '&nbsp;';
	      }
	      $menues[$allMenues[$i]['cat_id']] =
	        $spaces.$allMenues[$i]['categoryname'];
	    }
	    return $menues;
	}


	public function checkTransactions($catId=0)
	{
		$myModArray = PluginManager::getInstance()->getCurrentMainPlugin();
		$prot = ($this->getLogic()->isUseSsl()) ? 'https' : 'http';
		$location = $prot.'://'
			.BcmsSystem::getParser()->getServerParameter('HTTP_HOST')
			.'/'.$this->getLogic()->getTechname().'/';

		switch ($myModArray['func']) {
			case 'moveup':
				$currMenu = $this->getModel()->getMoveObject('cat_id = '.$this->workMenuId);
				if($this->getLogic()->moveMenuUp($currMenu))
					header('Location: '.$location, true);
				break;
			case 'movedown':
				$currMenu = $this->getModel()->getMoveObject('cat_id = '.$this->workMenuId);
				if($this->getLogic()->moveMenuDown($currMenu))
					header('Location: '.$location, true);
				break;
			case 'moveleft':
				$currMenu = $this->getModel()->getMoveObject('cat_id = '.$this->workMenuId);
				if($this->getLogic()->moveMenuLeft($currMenu))
					header('Location: '.$location, true);
				break;
			case 'moveright':
				$currMenu = $this->getModel()->getMoveObject('cat_id = '.$this->workMenuId);
				if($this->getLogic()->moveMenuRight($currMenu))
					header('Location: '.$location, true);
				break;
			case 'edit':
				return $this->makeCheck($this->getModel(),'go_mm',$_POST,'update'
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
				$this->getModel()->delete($this->workMenuId,$this->getLogic());
				header('Location: '.$prot.'://'.BcmsSystem::getParser()->getServerParameter('HTTP_HOST').'/'
					.$this->getLogic()->getTechname().'/', false);
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
		$sql = 'DELETE FROM '.BcmsConfig::getInstance()->getTablename('menu_layout_zo')
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
		$sql = 'INSERT INTO '.BcmsConfig::getInstance()->getTablename('menu_layout_zo')
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
		if(!BcmsSystem::getUserManager()->hasRight('category_add')) return(false);
		if(!isset($_POST['menu_add_submit'])) return false;

		// get recordset of the referenced menu
		$refMenu =
			$this->getModel()->getMoveObject('cat_id = '.$this->workMenuId);
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
		if(!$this->getModel()->updateNodes('+2',$referenceNode)) return false;
		return $this->makeCheck($this->getModel(),'menu_add_submit',$menuData,'insert');
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
		// @todo use ArticleLayoutManager for this!
		$queryAll = 'SELECT layout_id, layoutname '
		          . 'FROM '.BcmsConfig::getInstance()->getTablename('layoutpresets');

		// query to get all layouts related to specified category
		$querySel = 'SELECT fk_layout FROM '
			.BcmsConfig::getInstance()->getTablename('menu_layout_zo')
			.' WHERE fk_cat='.$catId;

		// execute query to get ident of users affected
		$associated_layouts =&$GLOBALS['db']->getCol($querySel);


		$form = new HTML_QuickForm('amsBasic2','post',$_SERVER['REQUEST_URI']);
		$form->removeAttribute('name');  // XHTML compliance

		$form->addElement('header', null, 'Menü "'.$_SESSION['mod']['oname']
			.'" Layoutstrukturen zuordnen'); // @todo use dictionary!

		$ams =& $form->addElement('advmultiselect', 'layout',
		    array('Layoutstrukturen:', 'Verfügbar', 'Zugeordnet'),         // @todo use dictionary
		    null,                                             // datas
		    array('style' => 'width:200px;')                  // custom layout
		);

		// load QFAMS values (unselected and selected)
		$ams->load($GLOBALS['db'], $queryAll, 'layoutname', 'layout_id', $associated_layouts);

		$form->addElement('submit', 'send', 'Send');

	    return $form->toHtml();
	}

	private function createEditMenuForm($catId){
		if (!BcmsSystem::getUserManager()->hasEditRight())
		    return BcmsSystem::raiseNoAccessRightNotice(
				'showContentList()',__FILE__, __LINE__);

		$columns = $this->getModel()->select('list_everything','cat_id = '.$catId);
		if(!$columns)
			return BcmsSystem::raiseNotice('Es ist ein Fehler aufgetreten!',
				BcmsSystem::LOGTYPE_CHECK, BcmsSystem::SEVERITY_ERROR,
				'createEditMenuForm()',__FILE__, __LINE__);

		if(!BcmsSystem::getUserManager()->hasRight($columns[0]['fk_edit_right']))
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createEditMenuForm()',__FILE__, __LINE__);

		// build link to category dependent plugin configuration if user possess right
		$refGui = BcmsFactory::getInstanceOf('GuiUtility');
		$heading = $refGui->createHeading(3,
			BcmsSystem::getDictionaryManager()->getTrans('h.CategoryEdit'));

		$pluginConfigLink = '';
		if(BcmsSystem::getUserManager()->hasRight($columns[0]['fk_plg_conf_right'])){
			$prot = ($columns[0]['use_ssl']) ? 'https' : 'http';
			$pluginConfigLink = $refGui->createAnchorTag(
				$prot.'://'.BcmsConfig::getInstance()->siteUrl.'/'.$columns[0]['techname'].'/config',
				BcmsSystem::getDictionaryManager()->getTrans('plugin_config')
			);
		}

		return $this->createMenuForm($heading.$pluginConfigLink,'go_mm',$columns[0]);
	}

	private function createAddMenuForm($catId){
		$heading = BcmsFactory::getInstanceOf('GuiUtility')->createHeading(3,BcmsSystem::getDictionaryManager()->getTrans('h.CatAdd'));
		return $this->createMenuForm($heading,'menu_add_submit');
	}

	private function createMenuForm($heading,$buttonName = 'menu_add_submit', $cols=null){
		$form = $this->getModel()->getForm('menuform',$buttonName
			,BcmsSystem::getDictionaryManager()->getTrans('save'), $cols);
		return $heading.$form->toHtml();
	}

	private function showEditMenuDialog() {
		return $this->createEditMenuForm($this->workMenuId);
	}

	private function showAddMenuDialog() {
		if (!BcmsSystem::getUserManager()->hasRight('category_add'))
		    return BcmsSystem::raiseNoAccessRightNotice(
				'showContentList()',__FILE__, __LINE__);

		return $this->createAddMenuForm($this->workMenuId);
	}

	private function showDelConfirmDialog(){
		$retStr = BcmsFactory::getInstanceOf('GuiUtility')->createHeading(3,
			BcmsSystem::getDictionaryManager()->getTrans('cat.h.delete'),
			14, 'menuheader');
		$retStr .= '
		  <form id="menuDelConfirmForm" action="/'.$this->getLogic()->getTechname()
		  		.'/delconfirm/'
		  		.BcmsSystem::getParser()->getGetParameter('oname')
				.'" method="post">
			  <label for="action">'.BcmsSystem::getDictionaryManager()->getTrans('cat.confirm_text')
			  	.': '.BcmsSystem::getParser()->getGetParameter('oname').'</label>
			  <input type="submit" name="action" value="'
			  .BcmsSystem::getDictionaryManager()->getTrans('yes').'" />
		  </form>'."\n";
		$retStr .= '
		  <form id="menuDelAbortForm" action="/'.$this->getLogic()->getTechname()
		  .'/" method="post">
			  <input type="submit" name="action" value="'
			  .BcmsSystem::getDictionaryManager()->getTrans('no').'" />
		  </form>'."\n";
		return $retStr;

	}

	private function showMenuMoveDialog() {
		if (!BcmsSystem::getUserManager()->hasRight('category_move'))
		return BcmsSystem::raiseNoAccessRightNotice(
				'showMenuMoveDialog()',__FILE__, __LINE__);
		return $this->getLogic()->createTreeMove();
	}

	/**
	 *
	 *
	 * @return String - description
	 * @access private
	 * @author ahe
	 * @since 07.03.2007 20:14:37
	 */
	private function showAdmMenu() {
		return $this->getLogic()->createAdminMenu();
	}


	private function showMenuList(){
		if (!BcmsSystem::getUserManager()->hasRight('category_view_list'))
		    return BcmsSystem::raiseNoAccessRightNotice(
				'showMenuList()',__FILE__, __LINE__);
	    return $this->getLogic()->printCategoryTree();
	}
}
?>