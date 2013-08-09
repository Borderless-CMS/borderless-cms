<?php
require 'content/Article_DAL.php';
require 'content/History_DAL.php';
require 'content/Content.php';
require 'content/ContentConfig_DAL.php';
/**
 * Contains ContentManager class
 *
 * @module ContentManager.php
 * @author ahe <aheusingfeld@borderlesscms.de>
 * @package plugins/content
 */

class ContentManager extends AbstractManager {

	// the versionstring is needed e.g. for getHashcode()
	protected $versionstring = '0.9';
	protected $modname = 'ContentManager';
  	protected $articleDalObj = null;
  	protected $dalObj = null;
  	protected $configDalObj = null;
  	protected $logicObj = null;
  	private static $modArray = null;
  	private $initCalled = false;
  	private $plgCatConfig = null;

	public function __construct() {
		$this->init($_SESSION['m_id']);
	}

	public function init($catId){
		if(!$this->initCalled) {
			BcmsConfig::getInstance()->setTablename('comments', 'comments');
			BcmsConfig::getInstance()->setTablename('history', 'history');
			BcmsConfig::getInstance()->setTablename('articles', 'plg_articles');
			BcmsConfig::getInstance()->setTablename('content_config', 'plg_art_cat_conf');

			$this->articleDalObj = new Article_DAL();
			$this->dalObj = new History_DAL();
			$this->configDalObj = ContentConfig_DAL::getInstance();
			$this->logicObj = new cContent($this);
			$this->modArray = PluginManager::getInstance()->getCurrentMainPlugin();
			$this->logicObj->init($this->modArray['func']);
			$this->plgCatConfig = $this->configDalObj->getObject($catId);
			$this->plgCatConfig['sort_direction'] =
				($this->plgCatConfig['sort_direction'])==41 ? 'ASC' : 'DESC'; // URGENT use classifications!
			$this->initCalled = true;
		}
	}


	public function getPlgCatConfig() { return $this->plgCatConfig; }

	public function getArticleDalObj() { return $this->articleDalObj; }

	public function main($menuId){
		echo $this->logicObj->createEditContentMenu();
		switch ($this->modArray['func']) {
			case 'config':
				return $this->printCategoryConfigForm($menuId);
			case 'list':
				return $this->logicObj->showContentList();
			case 'listall':
				return $this->logicObj->getCompleteList();
			case 'single':
				return $this->logicObj->showSingleArticle();
			case 'edit_article':
				if( !isset($_POST['abort_action'])
				  && !isset($_POST['article_edit_submit']))
				{
				  return $this->logicObj->editArticle($_SESSION['mod']['oid']);
				} // if article is submitted the "break is missed" and the
				  //  "show" function will be executed!
			case 'show':
				return $this->logicObj->showArticle($_SESSION['mod']['oid']);
			case 'sync':
			case 'history':
				return $this->logicObj->showArticle(
					$_SESSION['mod']['oid'],true);
			case 'version':
				return $this->logicObj->showArticleVersion(
					$_SESSION['mod']['oid']);
			case 'write':
				return $this->logicObj->write();
		}
	}

	public function checkTransactions($menuId=0){

		// check for redirection
		$location = $this->getLogic()->checkRedirectPresent();
		if(is_string($location)){
			header('Location: '.$location, false);
		}

		if(isset($_POST['submit_deletion'])) {
			$deleteIds = preg_grep('/^elemid_(\d+)$/',array_keys($_POST));
			$where = '';
			foreach ($deleteIds as $key => $value) {
				if($where != '') $where .= ' OR ';
				$where .='history_id='.intval($_POST[$value]);
			}
			$error = $this->dalObj->delete($where);
			if($error instanceof PEAR_ERROR){ // URGENT overwrite delete()-method and implement error handling!
				return BcmsSystem::raiseError($error, BcmsSystem::LOGTYPE_DELETE,
				BcmsSystem::SEVERITY_ERROR, 'checkTransactions()'
					,__FILE__, __LINE__);
			}
		}

		if(isset($_POST['table_action_select_history_table'])){
			$i = -1;
			$actionFound = false;
			$actions = $this->logicObj->getHistoryActions();
			while($i < sizeof($actions) && !$actionFound){
				$i++;
				if($_POST['table_action_select_history_table'] == $actions[$i][0])
					$actionFound=true;
			}
			$methodName = 'check'.$actions[$i][2].'History';
			if(method_exists($this, $methodName))
				return $this->$methodName();
		}
		
		
		if(isset($_POST['table_action_select_article_table'])
//			&& !isset($_POST['action_chosen_article_table'])
			){
			$i = -1;
			$actionFound = false;
			$actions = $this->logicObj->getArticleActions();
			while($i < sizeof($actions) && !$actionFound){
				$i++;
				if($_POST['table_action_select_article_table'] == $actions[$i][0])
					$actionFound=true;
			}
			$methodName = 'check'.$actions[$i][2].'Article';
			echo $methodName;
			if(method_exists($this, $methodName))
				return $this->$methodName();
		}
		if(!empty($_POST['cat_config_submit']))
			return $this->checkCategoryConfigSubmitted();

		$this->checkForCommentAdded();
	}

	private function checkCategoryConfigSubmitted(){
		if(isset($_POST['new_record'])){
			$func = 'insert';
			$where = null;
		} else {
			$func = 'update';
			$where = 'cat_id='.$_SESSION['m_id'];
		}
		return $this->makeCheck($this->configDalObj, 'cat_config_submit',
			$_POST,$func,$where);
	}

	/**
	 * Checks whether a
	 *
	 * @param enclosing_method_arguments
	 * @return return_type
	 * @author ahe
	 * @date 20.10.2006 21:34:47
	 * @package htdocs/plugins/content
	 */
	protected function checkForCommentAdded(){
		if(!isset($_POST['comm_action'])) return;

		if(!PluginManager::getPlgInstance('UserManager')->hasRight('COMMENT_WRITE'))
		 	return BcmsSystem::raiseNoAccessRightNotice(
				'POST[comm_action]',__FILE__, __LINE__);

		$this->logicObj->addComment($_POST['comm_heading'],$_POST['comm_text'],
			$_SESSION['mod']['oid'], $GLOBALS['ARTICLE_STATUS']['published'], // TODO use classifications for status!
			$_POST['comm_author']);
	 	return BcmsSystem::raiseNotice('comment "'.$_POST['comm_heading']
	 	  	.'"added by '.$userObj->getUsername, BcmsSystem::LOGTYPE_CHECK,
			BcmsSystem::SEVERITY_DEBUG,'POST[comm_action]'
		 	  	,__FILE__, __LINE__);
	}

	public function getCss($menuId=0){
		switch ($this->modArray['func']) {
			case 'edit_article':
			case 'write':
				return 'div#formatting_info dl { display:block; margin-left:2em; margin-bottom:2em;}'
					."\n".'div#formatting_info dt { display:block; margin-top:1em;}'
					.$this->logicObj->getCSS();
			default:
				return $this->logicObj->getCSS(); // TODO replace with own interpretation
		}

	}

	/**
	 * returns the current menu's name to be added to teh page title
	 *
	 * @return string the current menu's name
	 * @author ahe
	 * @date 01.05.2006 00:20:33
	 * @package htdocs/plugins/content
	 */
	public function getPageTitle() {

		switch ($this->modArray['func']) {
			case 'sync':
			case 'list':
				return '';
			case 'listall':
				$dictDAL = Factory::getObject('Dictionary')->getModel();
				return $dictDAL->getTrans('articlesurvey');
			case 'history':
			case 'version':
				$dictDAL = Factory::getObject('Dictionary')->getModel();
				$articleData = $this->logicObj->getCurrentArticleData();
				return $dictDAL->getTrans('articleHistoryOf').' "'.$articleData['heading'].'"';
			case 'write':
				if( !isset($_POST['abort_action'])
				  && !isset($_POST['article_edit_submit']))
				{
				$dictDAL = Factory::getObject('Dictionary')->getModel();
				return $dictDAL->getTrans('cont.WriteArticle');
				} // if article is submitted the "break is missed" and the
				  //  "show" function will be executed!
			case 'edit_article':
				if( !isset($_POST['abort_action'])
				  && !isset($_POST['article_edit_submit']))
				{
				return Factory::getObject('Dictionary')->getTrans('cont.EditArticle');
				} // if article is submitted the "break is missed" and the
				  //  "show" function will be executed!
			case 'single':
			case 'show':
			default:
				$articleData = $this->logicObj->getCurrentArticleData();
				return $articleData['heading'];
		}
	}

	/**
	 * returns the MetaDescription of the current menu
	 *
	 * @return string
	 * @author ahe
	 * @date 01.05.2006 00:21:56
	 * @package htdocs/plugins/content
	 */
	public function getMetaDescription() {
		$articleData = $this->logicObj->getCurrentArticleData();
		return $articleData['description'];
	}

	/**
	 * returns the MetaKeywords of the current menu
	 *
	 * @return string
	 * @author ahe
	 * @date 01.05.2006 00:23:41
	 * @package htdocs/plugins/content
	 */
	public function getMetaKeywords() {
		$articleData = $this->logicObj->getCurrentArticleData();
		return $articleData['meta_keywords'];
	}

	private function checkSyncHistory() {
		$result = HTMLTable::getAffectedIds();
		$id=$result[0];
		$histRecord = $this->dalObj->getObject($id,true);
		return $this->dalObj->syncContent($id,$histRecord['content_id']);
	}

	private function checkChangeStatusArticle() {
		$id=intval($_POST['content_id']);
		$status = intval($_POST['status']);
		$error = $this->articleDalObj->update(array('status'=>$status),'content_id = '.$id);
		if($error instanceof PEAR_ERROR){
			return BcmsSystem::raiseError($error, BcmsSystem::LOGTYPE_SELECT,
				BcmsSystem::SEVERITY_ERROR, 'checkTransactions()'
				,__FILE__, __LINE__);
		}
		return true;
	}

	protected function createChangeStatusArticleDialog(){
        if( !PluginManager::getPlgInstance('UserManager')->hasRight($this->plgCatConfig['change_status_right']) ) // TODO plg_cat_conf-edit_right should actually be checked here!
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createChangeStatusArticleDialog()',__FILE__, __LINE__);

		$result = HTMLTable::getAffectedIds();
		$id=$result[0];
		$articleObj = $this->articleDalObj->getObject($id);
		$parser = BcmsFactory::getInstanceOf('Parser');
		$articleObj= $parser->stripArrayFieldsInverse($articleObj,array('content_id','status'));
		$form = $this->articleDalObj->getForm('articleeditstatusform','editArticleStatus'
			,Factory::getObject('Dictionary')->getTrans('save'),$articleObj);
		$form->addElement('hidden','table_action_select_article_table','status');
		$form->addElement('hidden','dialog_submit','1');
		return $form->toHTML();
	}

	protected function createDeleteHistoryDialog(){
		// TODO check right for delete history action!
		include_once 'pear/HTML/QuickForm.php';
		$result = HTMLTable::getAffectedIds();
		$parser = BcmsFactory::getInstanceOf('Parser');
		$retStr = '';
		$action_url = $parser->getServerParameter('REDIRECT_URL');
		$form = new HTML_QuickForm('delete_elements','post',$action_url);
		for ($i = 0; $i < sizeof($result); $i++) {
			$id=$result[$i];
			$element = $form->addElement('hidden', 'elemid_'.$i, $id);
			if($retStr!='') $retStr .= ', ';
			$retStr .= $id;
		}
		$heading = Factory::getObject('Dictionary')->getTrans('dict.h.deleteEntries');
		$question =	Factory::getObject('Dictionary')->getTrans('really_delete');
		$retStr = $question."<br/>\n".$retStr;

		$element = $form->addElement('submit','submit_deletion',
			Factory::getObject('Dictionary')->getTrans('submit'), 'id="submit_deletion"');
		$element->setLabel('&nbsp;');
		$element = $form->addElement('submit', 'abort_action',
			Factory::getObject('Dictionary')->getTrans('cancel'), 'id="abort_action"');
		$element->setLabel('&nbsp;');
		return $retStr.$form->toHTML();
	}

	/**
	 * redirects to the edit article dialog
	 */
	protected function checkEditArticle(){
		$result = HTMLTable::getAffectedIds();
		$cont_id=$result[0];
		header('Location: edit_article/'.$cont_id,true);
	}

	public function printGeneralConfigForm(){

	}

	public function printCategoryConfigForm($catId){
		$cols = $this->configDalObj->getObject($catId);
		$form = $this->configDalObj->getForm('catconfigform','cat_config_submit'
			,Factory::getObject('Dictionary')->getTrans('save'), $cols);
		if(count($cols)<1){
		  $form->addElement('hidden', 'new_record');
		}
		$heading =
			Factory::getObject('GuiUtility')->createHeading(3,
				Factory::getObject('Dictionary')->getTrans(
				'h.category_plugin_config'));
		return $heading.$form->toHtml();
	}
}
?>