<?php
/* menu management class
*  -----------------------------------------------
*/
class Category extends GuiUtility
{

	private $dalObj;
	protected $dbMenuTable;
	public $vars = array ();
	protected $dbObject;

	function __construct($manager) {
		parent :: __construct();
		$this->db = $GLOBALS['db'];
		$this->manager = $manager;
		$this->dalObj = $this->manager->getModel();
		$this->dbMenuTable = BcmsConfig::getInstance()->getTablename('menu');
		$this->loadVars($_SESSION['m_id']);
	}

	/**
	 * Fetches the data of an attribute according to specified parameters
	 *
	 * @param String name of the attribute in the database table
	 * @param int id of the category
	 * @return mixed
	 * @author ahe
	 * @date 28.10.2006 21:35:06
	 * @package htdocs/classes/categories
	 * @since 0.14
	 */
  	private function getAttribute($attribName, $catId){
	  	if ($catId==0 || $catId==$this->vars['cat_id'])
	  		return $this->vars[$attribName];

	  	// if name of another category is requested
	  	return BcmsSystem::getAttributeDataFromDb($attribName,
	  		$catId, 'cat_id', $this->dbMenuTable);
  	}

	/**
	* Holt zur Initialisierung alle Daten des aktuellen Menues aus der Datenbank
	*
	* @param $m_id
	* @access public
	* @return array Resultset mit allen Daten
	*/
	private function getMenuDataFromDB($m_id) {
		$m_id = ($m_id == 0) ? $_SESSION['m_id'] : $m_id;

		if ($m_id <1) return false;

		$sql = 'SELECT * FROM '.$this->dbMenuTable.' WHERE cat_id='.$m_id;
	 	$result = $this->db->query($sql);
		if ($result instanceof PEAR_ERROR)
			return BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_SELECT,
				BcmsSystem::SEVERITY_ERROR, 'getUserIDfromDB()',__FILE__, __LINE__);

	 	$numrows = $result->numRows();
	 	if($numrows<1) return '';

		$record = $result->fetchRow(DB_FETCHMODE_ASSOC);
		$result->free();
		return $record;
	}

	/**
	* Holt zur Initialisierung alle Daten des aktuellen Menues aus der Datenbank
	*
	* @param $menuID
	* @access public
	* @return boolean load completed successfully?
	*/
	public function loadVars($menuID) {
		// Klassenvariablen initialisieren
		if ($menudata_array = $this->getMenuDataFromDB($menuID)) {
			foreach ($menudata_array as $key => $value) {
				$this->vars[$key] = $value;
			}
			return true;
		}
		return false;
	}

 	/**
	 * Get name of the specified category
	 *
	 * @param int category id
	 * @return return_type
	 * @author ahe
	 * @date 28.10.2006 21:31:02
	 * @package htdocs/classes/categories
	 */
 	function getCategoryName($catId=0) {
		return $this->getAttribute('categoryname', $catId);
 	}

 	function getAdditionalCss($catId=0) {
		return $this->getAttribute('additional_css', $catId);
	}

 	function getTechname($catId=0) {
		return $this->getAttribute('techname', $catId);
	}

	function getDescription($catId=0) {
		return $this->getAttribute('description', $catId);
	}

	function getMetaDescription($catId=0) {
		return $this->getAttribute('meta_description', $catId);
	}

	function getKeywords($catId=0) {
		return $this->getAttribute('meta_keywords', $catId);
	}

	function getType($catId=0) {
		return $this->getAttribute('type', $catId);
	}

 	function isUseSsl($catId=0) {
		return ($this->getAttribute('use_ssl', $catId)==1);
	}

	function getViewRight($catId=0) {
		return $this->getAttribute('fk_view_right', $catId);
	}

	function getEditRight($catId=0) {
		return $this->getAttribute('fk_edit_right', $catId);
	}

	function getEditOwnRight($catId=0) {
		return $this->getAttribute('fk_edit_own_right', $catId);
	}

	function getDeleteRight($catId=0) {
		return $this->getAttribute('fk_delete_right', $catId);
	}

	function isCommentable($catId=0) {
		return $this->getAttribute('commentable', $catId);
	}

	function isUserOnly($catId=0) {
		return $this->getAttribute('user_only', $catId);
	}

	function isShowOptPlugins($catId=0) {
		return $this->getAttribute('show_opt_plugins', $catId);
	}

/* BEGIN OF PATHWAY SECTION */
	function getMenuAncestors($catId)
	{
		$sql='SELECT b.categoryname, b.root_id, b.techname, b.use_ssl
			FROM '.$this->dbMenuTable.' AS a, '
			.$this->dbMenuTable.' AS b WHERE a.lft
			BETWEEN b.lft AND b.rgt AND (a.cat_id='.$catId.') '.
			' AND b.lft>0 ORDER by b.lft ASC';
	 	$result = $this->db->query($sql);
		if ($result instanceof PEAR_ERROR)
			return BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_SELECT,
				BcmsSystem::SEVERITY_ERROR, 'getUserIDfromDB()',__FILE__, __LINE__);

	 	$numrows = $result->numRows();
	 	if($numrows<1) return '';

		for ($i = 0; $i < $numrows; $i++) {
			$record[] = $result->fetchRow(DB_FETCHMODE_ASSOC,$i);
		}
		$result->free();
		return $record;
	}

	/**
	 * Creates the url to the specified category id
	 *
	 * @param integer a category's id
	 * @return String the url of the specified category
	 * @author ahe
	 * @date 28.10.2006 22:01:07
	 * @package htdocs/classes/categories
	 */
	public function createModRewriteLink($catId) {
		// TODO check for performance problem as this method submits 2 select-statements
		$prot = ($this->isUseSsl($catId)) ? 'https' : 'http';
		$aUrl = $prot.'://'
			.BcmsFactory::getInstanceOf('Parser')->getServerParameter('HTTP_HOST').'/'
	  		.$this->getTechname($catId).'/';
		return($aUrl);
	}

	public function getPathway($catId) {
		$returnvar = null;
		$parentmenu_array = $this->getMenuAncestors($catId);
		$aUrl = BcmsConfig::getInstance()->siteUrl.'/';
		for($i=0;$i<count($parentmenu_array);$i++)
		{
			// check whether root_id > 0
			if($parentmenu_array[$i]['root_id']>0) {
				$prot = ($parentmenu_array[0]['use_ssl']) ? 'https' : 'http';
				$theUrl = $prot.'://'.$aUrl.$parentmenu_array[$i]['techname'];
				if($i>0) $returnvar .= ' &raquo; ';
				$text = BcmsFactory::getInstanceOf('Parser')->filter($parentmenu_array[$i]['categoryname']);
				$returnvar .= $this->createAnchorTag($theUrl.'/',$text);
			}
		}
		return($returnvar);
	}
/* END OF PATHWAY SECTION */


// ===== MENUEERSTELLUNG/ -AUSGABE =====
	function createPathway()
	{
		$rString = '
		  <h2 id="pathway">
			<span class="sr_desc" title="additional screenreader information">'
			  .Factory::getObject('Dictionary')->getTrans('sr.Pathway').'</span>
			<span id="pathway_links">';
		$rString .= $this->getPathway($_SESSION['m_id']);
		$rString .= '</span>
		  </h2>'."\n";
		return $rString;
	}

	public function createMenuDescription()
	{
		if($this->vars['show_cat_desc']==0) return false;

		// get and parse menu description
		$menu_desc = $this->getDescription();
		if($menu_desc==null || $menu_desc=='') return false;

		$menu_desc = BcmsFactory::getInstanceOf('Parser')->filter($menu_desc);
		$menu_desc = BcmsFactory::getInstanceOf('Parser')->parseTagsByRegex($menu_desc);
		$return_val = '          <div id="menu_description">';
		$return_val .= "\n".'            <span class="sr_desc">'
			  .Factory::getObject('Dictionary')->getTrans('sr.CatDesc').'</span>'."\n";
		$return_val .= '            <span id="menudesc">'.$menu_desc.'</span>'."\n";
		$return_val .= '	  </div>'."\n";
		return $return_val;
	}

	function createMainMenu() {
		return $this->createMenu('mainmenu', 'mmenu', 'h.MainMenuName',
			18,'__main__',false);
	}

	public function createUserMenu()
	{
		$mString = '';
		include_once 'menu_hide.inc.php';
		$username = '';
		if(PluginManager::getPlgInstance('UserManager')->getLogic()->getUserID()!=2) {
			$username = '<span>'
				.Factory::getObject('Dictionary')->getTrans('logged_in_as')
				.':</span> <i>'.PluginManager::getPlgInstance('UserManager')->getLogic()->getUserName()
				.'</i> ('.PluginManager::getPlgInstance('UserManager')->getLogic()->getUsersRealname().')';
		}
		$gui = BcmsFactory::getInstanceOf('GuiUtility');
		$mString .= $jscode;
		$mString .= $gui->fillTemplate('div_tpl', array(
				'id="logged_in_as"',$link.$username
			)
		);
		$mString .= $this->createMenu('usermenu', 'umenu', 'h.UserMenuName',
			18,'__user__');
		return $mString;
	}

	public function createAdminMenu()
	{
		if(PluginManager::getPlgInstance('UserManager')->getLogic()->getUserID()!=2) {
			return $this->createMenu('adminmenu', 'amenu',
				'h.AdminMenuName',18,'__admin__',true,false,true);
		}
		return '';
	}

	public function createSystemMenu()
	{
		return $this->createMenu('systemmenu', 'smenu', 'h.SystemMenuName',
			18,'__system__');
	}

	private function createMenu($cssId, $a_name, $headingDefTrans,$noOfSpaces,$topTechname,$createJumpLabel=true, $showNoOfChildren=false, $showAllMenues=false)
	{
		$mString = $this->createSpaces($noOfSpaces).'<div id="'.$cssId.'">'."\n";
		if($createJumpLabel)
			$mString .= $this->createSpaces($noOfSpaces)
				.'  <a id="'.$a_name.'" name="'.$a_name
				.'" class="unsichtbar"></a>'."\n";

		$mString .= $this->createHeading(2
			,Factory::getObject('Dictionary')->getTrans($headingDefTrans),$noOfSpaces+2);
		$mString .= $this->createMenuHierarchie($topTechname, $noOfSpaces+2,
			$showNoOfChildren, $showAllMenues);
		$mString .= $this->createSpaces($noOfSpaces).'</div>  <!-- /'.$cssId.' -->'."\n";
		return $mString;
	}

	function createMenuHierarchie($topMenuString, $noOfSpaces, $showNoOfChildren=false, $showAllMenues=false)
	{

	$userObj = PluginManager::getPlgInstance('UserManager')->getLogic();
	$allmenus = $this->dalObj->getSmallTreeList($topMenuString,false,$userObj->isLoggedIn());
	$mString = '';
	$currLevel=1;
	$refLft = 0;
	$refRgt = 0;
	$dfnCounter = array();
	$dfnCounter[$currLevel]=0;

	$mString .= $this->createSpaces($noOfSpaces).'<ul id="ul'.$topMenuString
		.'0" class="menu'.$currLevel."\">\n";

	for($j=0; $j<count($allmenus); $j++)
	{
		if (PluginManager::getPlgInstance('UserManager')->hasRight($allmenus[$j]['fk_view_right'])) {

			if(($this->vars['lft']>$allmenus[$j]['lft'])
				&& ($this->vars['lft']<$allmenus[$j]['rgt']))
			{
				// set lft and rgt values of parent menu as reference for comparison
				$refLft = $allmenus[$j]['lft'];
				$refRgt = $allmenus[$j]['rgt'];
			}

			if( (($allmenus[$j]['lft']>=$refLft) && ($allmenus[$j]['lft']<$refRgt)) // same parent menu?
				|| ($allmenus[$j]['level']-1 == 1) // show toplevel nodes
				|| ($allmenus[$j]['root_id'] == $_SESSION['m_id']) // show childnodes
				|| $showAllMenues)
			{
				$cssClass = ($j%2==0) ? ' class="odd"' :  ' class="even"';

				if($currLevel < $allmenus[$j]['level']-1)
				{
					$dfn_id_ul = $topMenuString.$this->createHierarchyNo($dfnCounter, false);
					$mString .= $this->createSpaces($noOfSpaces+($currLevel*2));
					$currLevel = $allmenus[$j]['level']-1;
		  			@$dfnCounter[$currLevel]++;
					$dfn = $this->createHierarchyNo($dfnCounter);
					$dfn_id = $topMenuString.$this->createHierarchyNo($dfnCounter, false);

					$mString .= "\n".$this->createSpaces($noOfSpaces+($currLevel*2)-2);
					$mString .= '<ul class="menu menu'.$currLevel.'" id="ul'.$dfn_id_ul.'">'."\n";
					$mString .= $this->createSpaces($noOfSpaces+($currLevel*2)); // Spaces
					$mString .= '<li id="li'.$dfn_id.'"'.$cssClass
						.'><dfn class="unsichtbar">'.($dfn).'</dfn>';
				}
				elseif($currLevel > $allmenus[$j]['level']-1)
				{
					unset($dfnCounter[$currLevel]);
		 			$currLevel = $allmenus[$j]['level']-1;
		  			$dfnCounter[$currLevel]++;
					$dfn = $this->createHierarchyNo($dfnCounter);
					$dfn_id = $topMenuString.$this->createHierarchyNo($dfnCounter, false);

					$mString .= '</li>'."\n";
					$mString .= $this->createSpaces($noOfSpaces+($currLevel*2)).'</ul></li>'."\n";
					$mString .= $this->createSpaces($noOfSpaces+($currLevel*2));
					$mString .= '<li id="li'.$dfn_id.'"'.$cssClass
						.'><dfn class="unsichtbar">'.($dfn).'</dfn>';
		 		}
		 		else
		 		{
		  			$dfnCounter[$currLevel]++;
					if($j > 0) $mString .= "</li>\n"; // nicht beim ersten Durchlauf
					$dfn = $this->createHierarchyNo($dfnCounter);
					$dfn_id = $topMenuString.$this->createHierarchyNo($dfnCounter, false);
					if($allmenus[$j]['cat_id'] == $_SESSION['m_id']) {
						$cssClass = str_replace('="','="selected ',$cssClass);
						$additionalInfo = '<span class="unsichtbar">'
							.Factory::getObject('Dictionary')->getTrans('currentlyOpen').': </span>';
					} else {
						$additionalInfo ='';
					}

					$mString .= $this->createSpaces($noOfSpaces+($currLevel*2)); // Spaces
					$mString .= '<li id="li'.$dfn_id.'"'.$cssClass.'><dfn class="unsichtbar">'
						.($dfn).'</dfn>'.$additionalInfo;
				}
				$mString .=
					$this->createMenuLiContent($allmenus[$j],$showNoOfChildren);
			}
		}
	}
	while ($currLevel>0) {
		$currLevel--;
		$mString .= "</li>\n".$this->createSpaces($noOfSpaces+($currLevel*2))
			.'</ul> <!-- #menu'.($currLevel+1).' -->';
	}
	return $mString."\n";
  }

	private function createMenuLiContent($menuData,$showNoOfChildren){
		$parser = BcmsFactory::getInstanceOf('Parser');
		$p_mname = $parser->filter($menuData['categoryname']);
		if($showNoOfChildren)
			$p_mname .= ' ('.intval($menuData['NoOfChildren']).')';

		if($menuData['cat_id'] == $_SESSION['m_id']) {
			$title = Factory::getObject('Dictionary')->getTrans('currentlyOpen')
				.': '.$p_mname.' - '.$menuData['categorylink_title'];
		} else {
			$title = $menuData['categorylink_title'];
		}
		$title = $parser->filter($title);
		$prot = ($menuData['use_ssl']) ? 'https' : 'http';
		$address = $prot.'://'.BcmsConfig::getInstance()->siteUrl
			.'/'.$menuData['techname'].'/';

		// create icon for menu position (background-image)
		$styleTag='';
		$icon = $menuData['icon_src'];
		if($icon && file_exists($icon)){
			list($width, $height, $type, $attr) = getimagesize($icon);
			// prepend slash to path if not already existend
			if(substr($icon,0,1)!='/' && !stristr($icon,'http'))
				$icon = '/'.$icon;

			if(is_int($height))
				$styleTag=' style="background:transparent url('.$icon.') ' .
					'no-repeat top left;' .
					'padding-left:+'.($width+4).'px;"';
			unset($width, $height, $type, $attr);
		}

		$mString = $this->createAnchorTag($address,$p_mname,0
			,$menuData['accesskey'],0,$title,' class="menulink"'.$styleTag);

		if (PluginManager::getPlgInstance('UserManager')->hasRight($menuData['fk_edit_right']))
		{
			$mString .= ' '.$this->createAnchorTag(
				'/menu/edit/'.$menuData['techname'] // TODO create a method to getPlgCategoryname
				,'['.Factory::getObject('Dictionary')->getTrans('edit').']'
				,null,0,0
				,Factory::getObject('Dictionary')->getTrans('h.CategoryEdit')
					.' ('.$menuData['techname'].')'
				,' class="small_link"');
		}
		return $mString;
	}

  /**
  *
  *
  * @param $counterArray
  * @access private
  * @return
  */
  private function createHierarchyNo($counterArray, $withDot=true) {
  		$dfn = null; // reset dfn-var
  		foreach($counterArray as $key => $value) {
  			$dfn .= $value;
  			$dfn .= ($withDot) ? '.' : '_';
  		}
	  return substr($dfn,0,-1);
  }

	/**
	* Get details of all menu recordsets with given parameters
	*
	* @param $currParent
	* @param boolean $printAdmMenu optional, default value false
	* @param boolean $regardStatus optional, default value false
	* @access public
	* @return
	*/
	function getMenuTree($currMenuID,$currParent,$printAdmMenu=false,$regardStatus=false)
	{
		// ...and build the according tree
		$sql = 'SELECT m1.*, \'1\' AS LEVEL FROM '
			.$this->dbMenuTable.' as m1, '
			.$this->dbMenuTable.' as m2 '
			.' WHERE (m1.root_id=m2.cat_id) AND (m2.techname = \'__main__\') ';
		if(PluginManager::getPlgInstance('UserManager')->getLogic()->getUserID()==BcmsConfig::getInstance()->defaultUserId)
			$sql .= ' AND (m1.user_only=0) ';
		if($regardStatus)
			$sql .= ' AND (m1.status = '
				.$GLOBALS['ARTICLE_STATUS']['published'].')';// TODO use classifications for status!

		// get current menus ancestors...
		$path = $this->getMenuAncestors($currMenuID);
		for($i=1;$i<count($path);$i++) {
			$sql .= ' UNION SELECT *, \''.($i+1).'\' AS LEVEL FROM '
				.$this->dbMenuTable.' WHERE  (root_id = '.$path[$i]['root_id'].') ';
			if(PluginManager::getPlgInstance('UserManager')->getLogic()->getUserID()==BcmsConfig::getInstance()->defaultUserId)
				$sql .= ' AND (user_only=0) ';
			if($regardStatus)
				$sql .= ' AND (status = '
					.$GLOBALS['ARTICLE_STATUS']['published'].')';// TODO use classifications for status!
		}
		$sql .= ' ORDER BY lft;';

	 	$result = $this->db->query($sql);
	 	$numrows = $result->numRows();
		if (!($result instanceof DB_ERROR) && $numrows>0) {
			for ($i = 0; $i < $numrows; $i++) {
				$record[] = $result->fetchRow(DB_FETCHMODE_ORDERED,$i);
			}
			$result->free();
			return $record;
		}
	  	return '';
	}

	public function getMenuFullTree($techname=null) {
		return $this->dalObj->getTreeList($techname,true);
	}

	public function getMenuTreeList($section=null) {
		return $this->dalObj->getSmallTreeList($section);
	}

	/**
	 * TODO create separate module for font size
	 */
	public function createFontSizeMenu() {
		$mString = '
		  <div id="fontsize_menu">
			<h2 id="fs_info">Schriftgr&ouml;&szlig;e</h2>
			<ul>
				<li id="fs_small"><a href="?css_fs=default" title="normale Schrift">T<span class="unsichtbar"> - normale Schrift</span></a></li>
				<li id="fs_medium"><a href="?css_fs=medium" title="gro&szlig;e Schrift">T<span class="unsichtbar"> - gro&szlig;e Schrift</span></a></li>
				<li id="fs_large"><a href="?css_fs=large" title="gr&ouml;&szlig;ere Schrift">T<span class="unsichtbar"> - gr&ouml;&szlig;ere Schrift</span></a></li>
			</ul>
		  </div>
		';
		return $mString;
	}

  function printCategoryTree()
  {
	require_once 'classes/general/categories/Tree.php';
	$categoryTree = new Tree($this);
	$categoryTree->drawTree();
  }

	/**
	 * moves the given $currMenu one step up in hierarchy
	 *
	 * @author ahe
	 */
	public function moveMenuUp(&$currMenu) {
		$currMenuChildren = $this->dalObj->getChildIds($currMenu['lft']
			, $currMenu['rgt']);
		$siblingMenu = $this->dalObj->getSiblingObject($currMenu['root_id']
			,'rgt < '.$currMenu['lft'], 'DESC');
		if($siblingMenu == false) {
			return BcmsSystem::raiseDictionaryNotice('catAlreadyFirst',
					BcmsSystem::LOGTYPE_UPDATE, BcmsSystem::SEVERITY_WARNING,
					'moveMenuUp()', __FILE__, __LINE__);
		} else {
			$sibMenuChildren = $this->dalObj->getChildIds($siblingMenu['lft']
				, $siblingMenu['rgt']);
			$movedStepsUp = $currMenu['lft']-$siblingMenu['lft'];
			$movedStepsDown = $currMenu['rgt']-$siblingMenu['rgt'];
			$currMenu['rgt'] = $currMenu['rgt']-$movedStepsUp;
			$currMenu['lft'] = $currMenu['lft']-$movedStepsUp;
			$siblingMenu['rgt'] = $siblingMenu['rgt']+$movedStepsDown;
			$siblingMenu['lft'] = $siblingMenu['lft']+$movedStepsDown;

			// update currMenu
			// strip these otherwise there will be a 'uniquene index' error
			$data = BcmsFactory::getInstanceOf('Parser')->stripArrayFields($currMenu
				,array('techname','cat_id'));
			$this->dalObj->update($data,'cat_id = '.$currMenu['cat_id']);
			// update currMenu nodes
			for ($i = 0; $i < count($currMenuChildren); $i++) {
				$this->dalObj->updateNodes('-'.$movedStepsUp,null,$currMenuChildren[$i]['cat_id']);
			}

			// update siblingMenu
			// strip these otherwise there will be a 'uniquene index' error
			$data = BcmsFactory::getInstanceOf('Parser')->stripArrayFields($siblingMenu
				,array('techname','cat_id'));
			$this->dalObj->update($data,'cat_id = '.$siblingMenu['cat_id']);
			// update sibling menu nodes
			for ($i = 0; $i < count($sibMenuChildren); $i++) {
				$this->dalObj->updateNodes('+'.$movedStepsDown,null
					,$sibMenuChildren[$i]['cat_id']);
			}
			return true;
		}
	}

	/**
	 * moves the given $currMenu one step up in hierarchy
	 *
	 * @author ahe
	 */
	public function moveMenuDown(&$currMenu) {
		$currMenuChld = $this->dalObj->getChildIds($currMenu['lft']
			, $currMenu['rgt']);
		$siblingMenu = $this->dalObj->getSiblingObject($currMenu['root_id']
			,'lft > '.$currMenu['rgt'], 'ASC');
		if($siblingMenu == false) {
			return BcmsSystem::raiseDictionaryNotice('catAlreadyLast',
					BcmsSystem::LOGTYPE_UPDATE, BcmsSystem::SEVERITY_WARNING,
					'moveMenuDown()', __FILE__, __LINE__);
		} else {
			$sibMenuChildren = $this->dalObj->getChildIds($siblingMenu['lft']
				, $siblingMenu['rgt']);
			$movedStepsUp = $siblingMenu['lft']-$currMenu['lft'];
			$movedStepsDown = $siblingMenu['rgt']-$currMenu['rgt'];
			$currMenu['rgt'] = $currMenu['rgt']+$movedStepsDown;
			$currMenu['lft'] = $currMenu['lft']+$movedStepsDown;
			$siblingMenu['rgt'] = $siblingMenu['rgt']-$movedStepsUp;
			$siblingMenu['lft'] = $siblingMenu['lft']-$movedStepsUp;

			// update currMenu
			// strip these otherwise there will be a 'uniquene index' error
			$data = BcmsFactory::getInstanceOf('Parser')->stripArrayFields($currMenu
				,array('techname','cat_id'));
			$this->dalObj->update($data,'cat_id = '.$currMenu['cat_id']);
			// update currMenu nodes
			for ($i = 0; $i < count($currMenuChld); $i++) {
				$this->dalObj->updateNodes('+'.$movedStepsDown,null,$currMenuChld[$i]['cat_id']);
			}

			// update siblingMenu
			// strip these otherwise there will be a 'uniquene index' error
			$data = BcmsFactory::getInstanceOf('Parser')->stripArrayFields($siblingMenu
				,array('techname','cat_id'));
			$this->dalObj->update($data,'cat_id = '.$siblingMenu['cat_id']);
			// update sibling menu nodes
			for ($i = 0; $i < count($sibMenuChildren); $i++) {
				$this->dalObj->updateNodes('-'.$movedStepsUp,null
					,$sibMenuChildren[$i]['cat_id']);
			}
			return true;
		}
	}

	/**
	 * moves the given $currMenu one step up in hierarchy
	 *
	 * @author ahe
	 */
	public function moveMenuLeft(&$currMenu) {
		$currMenuChld = $this->dalObj->getChildIds($currMenu['lft']
			, $currMenu['rgt']);
		$siblingMenu = $this->dalObj->getParentObject($currMenu['root_id']);
		if($siblingMenu == false) {
			return BcmsSystem::raiseDictionaryNotice('catAlreadyTop',
					BcmsSystem::LOGTYPE_UPDATE, BcmsSystem::SEVERITY_WARNING,
					'moveMenuLeft()', __FILE__, __LINE__);
		} else {
			$currMenu['root_id'] = $siblingMenu['root_id'];
			$sibMenuChildren = $this->dalObj->getChildIds($currMenu['lft']
				, $siblingMenu['rgt']);
			$removedRange = $currMenu['rgt']-$currMenu['lft']+1;
			$moveRange = $siblingMenu['rgt']+1-$currMenu['lft'];
			$currMenu['rgt'] = $siblingMenu['rgt'];
			$siblingMenu['rgt'] = $siblingMenu['rgt']-$removedRange;
			$currMenu['lft'] = $siblingMenu['rgt']+1;

			// update siblingMenu
			// strip these otherwise there will be a 'uniquene index' error
			$data = BcmsFactory::getInstanceOf('Parser')->stripArrayFields($siblingMenu,array('cat_id'));
			$this->dalObj->update($data,'cat_id = '.$siblingMenu['cat_id']);
			// update sibling menu nodes
			for ($i = 0; $i < count($sibMenuChildren); $i++) {
				$this->dalObj->updateNodes('-'.$removedRange,null
					,$sibMenuChildren[$i]['cat_id']);
			}

			// update currMenu
			// strip these otherwise there will be a 'uniquene index' error
			$data = BcmsFactory::getInstanceOf('Parser')->stripArrayFields($currMenu,array('cat_id'));
			$this->dalObj->update($data,'cat_id = '.$currMenu['cat_id']);
			// update current menu nodes
			for ($i = 0; $i < count($currMenuChld); $i++) {
				$this->dalObj->updateNodes('+'.$moveRange,null
					,$currMenuChld[$i]['cat_id']);
			}
			return true;
		}
	}

	/**
	 * moves the given $currMenu one step up in hierarchy
	 *
	 * @author ahe
	 */
	public function moveMenuRight(&$currMenu) {
		$currMenuChld = $this->dalObj->getChildIds($currMenu['lft']
			, $currMenu['rgt']);
		$siblingMenu =
			$this->dalObj->getMoveObject('rgt = '.($currMenu['lft']-1));
		if($siblingMenu == false) {
			return BcmsSystem::raiseDictionaryNotice('catAlreadyLast',
					BcmsSystem::LOGTYPE_UPDATE, BcmsSystem::SEVERITY_WARNING,
					'moveMenuRight()', __FILE__, __LINE__);
		} else {
			$currMenu['root_id'] = $siblingMenu['cat_id'];
			$range = $currMenu['rgt']-$currMenu['lft']+1;
			$moveRange = $siblingMenu['rgt']-$currMenu['lft'];
			$currMenu['lft'] = $siblingMenu['rgt'];
			$siblingMenu['rgt'] = $currMenu['rgt'];
			$currMenu['rgt'] = $siblingMenu['rgt']-1;

			// update siblingMenu
			// strip these otherwise there will be a 'uniquene index' error
			$data = BcmsFactory::getInstanceOf('Parser')->stripArrayFields($siblingMenu,array('cat_id'));
			$this->dalObj->update($data,'cat_id = '.$siblingMenu['cat_id']);

			// update currMenu
			// strip these otherwise there will be a 'uniquene index' error
			$data =
				BcmsFactory::getInstanceOf('Parser')->stripArrayFields($currMenu,array('cat_id'));
			$this->dalObj->update($data,'cat_id = '.$currMenu['cat_id']);
			// update current menu nodes
			for ($i = 0; $i < count($currMenuChld); $i++) {
				$this->dalObj->updateNodes('+'.$moveRange,null
					,$currMenuChld[$i]['cat_id']);
			}
			return true;
		}
	}

	/**
	 * creates an icon imgTag of 12x12px
	 *
	 * @param string $direction can be Left, Right, Up, Down
	 * @return string imgTag
	 * @package files/adm/classes
	 * @author d_heusingf
	 * @date 18.01.2006 14:59:46
	 */
	private function createMoveIcon($direction,$imgFilename) {
		return $this->createImageTag(
			array(
				'src' => '/gfx/silk/'.$imgFilename,
				'width' => 16,
				'height' => 16,
				'alt' => Factory::getObject('Dictionary')->getTrans('move'.$direction),
				'style' => 'border:0px;'
			)
		);

	}

	/**
	 * creates an anchor tag and sets a style tag with margin according to the
	 * $menuArray['level'] index
	 *
	 * @param array $menuArray index should be like e.g. $menu['techname']
	 * @return string anchor tag
	 * @package files/adm/classes
	 * @author d_heusingf
	 * @date 18.01.2006 15:02:52
	 */
	private function createTreeAnchorTag($menuArray) {
		$src = ($menuArray['icon_src']!='') ? '/'.$menuArray['icon_src'] : '/gfx/silk/folder.png';
		$folderImg = $this->createImageTag(
			array (
				'src' => $src, // Filename
				'width' => 16,
				'height' => 16,
				'alt' => '', // ALT text/ description
				'style' => 'margin-right:3px;'
			));
		return $this->createAnchorTag(
			'/'.$menuArray['techname'].'/'
			,$folderImg.$menuArray['categoryname']
			,0,null,0
			,$menuArray['categorylink_title']
			,' style="margin-left:'.$menuArray['level'].'em; border:0px; text-decoration:none;"');
	}

	/**
	 * Creates the whole pane of the menu_move section
	 *
	 * @return String rendered move menu table
	 * @author ahe
	 * @date 28.10.2006 21:07:17
	 * @package htdocs/classes/categories
	 */
	public function createTreeMove() {
		$allMenues = $this->getMenuFullTree();

		$imgLft = $this->createMoveIcon('Left','arrow_left.png');
		$imgRgt = $this->createMoveIcon('Right','arrow_right.png');
		$imgUp = $this->createMoveIcon('Up','arrow_up.png');
		$imgDwn = $this->createMoveIcon('Down','arrow_down.png');
		for ($i = 0; $i < count($allMenues); $i++) {

			// create anchor tag
			$anchorTag = $this->createTreeAnchorTag($allMenues[$i]);

			$anchorLeft =
				$this->createAnchorTag(
					'/'.$this->getTechname().'/moveleft/'.$allMenues[$i]['techname'],$imgLft,
					0,null,0,null,' style="text-decoration:none"');
			$anchorUp =
				$this->createAnchorTag(
					'/'.$this->getTechname().'/moveup/'.$allMenues[$i]['techname'],$imgUp,
					0,null,0,null,' style="text-decoration:none"');
			$anchorDown =
				$this->createAnchorTag(
					'/'.$this->getTechname().'/movedown/'.$allMenues[$i]['techname'],$imgDwn,
					0,null,0,null,' style="text-decoration:none"');
			$anchorRight =
				$this->createAnchorTag(
					'/'.$this->getTechname().'/moveright/'.$allMenues[$i]['techname'],$imgRgt,
					0,null,0,null,' style="text-decoration:none"');

			$tableArray[$i] = array(
				'categoryname' => $anchorTag,
				'move_direction' => $anchorLeft.$anchorUp.$anchorDown.$anchorRight,
			);
		}
		$htmlTableObj = new HTMLTable('menu_move_table');
		$htmlTableObj->setTranslationPrefix('cat.');
		$htmlTableObj->setData($tableArray);
		return $htmlTableObj->render(
			Factory::getObject('Dictionary')->getTrans('cat.h.cat_move'),
			'cat_id');
	}

}
?>