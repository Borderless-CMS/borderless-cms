<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

require 'content/BcmsArticle.php';
require 'content/Article_DAL.php';
require 'content/History_DAL.php';
require 'content/Content.php';
require 'content/ContentConfig_DAL.php';
require 'content/Layout_DAL.php';
require 'content/class.cArticleLayout.php';
require 'content/LatestContent.php';

/**
 * Contains ContentManager class
 *
 * @since 0.8
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @date 2006-01-27
 * @class ContentManager
 * @ingroup content
 * @package content
 */
class ContentManager extends AbstractManager {

	// the versionstring is needed e.g. for getHashcode()
	protected $versionstring = '0.11';
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
			BcmsConfig::getInstance()->setTablename('layoutpresets', 'layoutpresets');

			$this->configDalObj = ContentConfig_DAL::getInstance();
			$this->plgCatConfig = $this->configDalObj->getObject($catId);
			$this->plgCatConfig['sort_direction'] =
				($this->plgCatConfig['sort_direction'])==41 ? 'ASC' : 'DESC'; // \bug URGENT use classifications!
			$this->plgCatConfig['comments_sort_direction'] =
				($this->plgCatConfig['comments_sort_direction'])==41 ? 'ASC' : 'DESC'; // \bug URGENT use classifications!
			$this->articleDalObj = new Article_DAL();
			$this->dalObj = new History_DAL();
			$this->logicObj = new cContent($this);
			$this->modArray = PluginManager::getInstance()->getCurrentMainPlugin();
			$this->logicObj->init($this->modArray['func']);
			$this->initCalled = true;
		}
	}

	public function getArticleActions(){
		return array(
		0 => array('status', PluginManager::getPlgInstance('Dictionary')->getTrans('changeStatus'), 'ChangeStatus'),
		//			1 => array('delete', PluginManager::getPlgInstance('Dictionary')->getTrans('delete'), 'Delete'),
		1 => array('edit', PluginManager::getPlgInstance('Dictionary')->getTrans('edit'), 'Edit')
		);
	}

	public function getHistoryActions(){
		return array(
		0 => array('sync', PluginManager::getPlgInstance('Dictionary')->getTrans('setVersionActive'), 'Sync'),
		1 => array('delete', PluginManager::getPlgInstance('Dictionary')->getTrans('delete'), 'Delete')
		);
	}

	public function getPlgCatConfig() { return $this->plgCatConfig; }

	public function getArticleDalObj() { return $this->articleDalObj; }

	public function getDalObj() { return $this->dalObj; }

	public function main($menuId){
		echo $this->logicObj->createEditContentMenu();
		switch ($this->modArray['func']) {
			case 'config':
				return $this->printCategoryConfigForm($menuId);
			case 'list':
				return $this->logicObj->showContentList();
			case 'listall':
				return $this->getCompleteList();
			case 'single':
				return $this->logicObj->showSingleArticle();
			case 'edit_article':
				if( !isset($_POST['abort_action'])
				  && !isset($_POST['article_edit_submit']))
				{
				  return $this->logicObj->editArticle($_SESSION['mod']['oid']);
				}

				// if article is submitted the "break is missed" and the
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

		// check whether content editing/ writing process has been aborted
		if(isset($_POST['abort_action'])) {
			// in that case, remove all remaining session data for article editing...
			unset($_SESSION['current_article_data']);
			session_unregister('current_article_data');
			// ... and redirect to article show phase
			header('Location: ../show/'.$_SESSION['mod']['oid'],true);
		}

		if(isset($_POST['submit_deletion'])) {
			$deleteIds = preg_grep('/^elemid_(\d+)$/',array_keys($_POST));
			$where = '';
			foreach ($deleteIds as $key => $value) {
				if($where != '') $where .= ' OR ';
				$where .='history_id='.intval($_POST[$value]);
			}
			$error = $this->dalObj->delete($where);
			if($error instanceof PEAR_ERROR){ // \bug URGENT overwrite delete()-method and implement error handling!
				return BcmsSystem::raiseError($error, BcmsSystem::LOGTYPE_DELETE,
				BcmsSystem::SEVERITY_ERROR, 'checkTransactions()'
					,__FILE__, __LINE__);
			}
		}

		if(isset($_POST['table_action_select_history_table'])){
			$i = -1;
			$actionFound = false;
			$this->actions = $this->getHistoryActions();
			while($i < sizeof($this->actions) && !$actionFound){
				$i++;
				if($_POST['table_action_select_history_table'] == $this->actions[$i][0])
					$actionFound=true;
			}
			$methodName = 'check'.$this->actions[$i][2].'History';
			if(method_exists($this, $methodName))
				return $this->$methodName();
		}


		if(isset($_POST['table_action_select_article_table'])
//			&& !isset($_POST['action_chosen_article_table'])
			){
			$i = -1;
			$actionFound = false;
			$this->actions = $this->getArticleActions();
			while($i < sizeof($this->actions) && !$actionFound){
				$i++;
				if($_POST['table_action_select_article_table'] == $this->actions[$i][0])
					$actionFound=true;
			}
			$methodName = 'check'.$this->actions[$i][2].'Article';
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
	 * Checks whether a comment shall be added
	 *
	 * @param enclosing_method_arguments
	 * @return return_type
	 * @author ahe
	 * @date 20.10.2006 21:34:47
	 */
	protected function checkForCommentAdded(){
		if(!isset($_POST['comm_action'])) return;

		if(!BcmsSystem::getUserManager()->hasRight('COMMENT_WRITE'))
		 	return BcmsSystem::raiseNoAccessRightNotice(
				'POST[comm_action]',__FILE__, __LINE__);

		$author = empty($_POST['comm_author']) ? null : $_POST['comm_author'];
		if($this->logicObj->addComment($_POST['comm_heading'],$_POST['comm_text'],
			$_SESSION['mod']['oid'], $GLOBALS['ARTICLE_STATUS']['published'], // @todo use classifications for status!
			$author))
		{
		 	return BcmsSystem::raiseNotice('comment "'.$_POST['comm_heading']
		 	  	.'"added by '.$userObj->getUsername, BcmsSystem::LOGTYPE_CHECK,
				BcmsSystem::SEVERITY_DEBUG,'POST[comm_action]'
		 	  	,__FILE__, __LINE__);
		}
	}

	public function getCss($menuId=0){
		switch ($this->modArray['func']) {
			case 'edit_article':
			case 'write':
				return 'div#formatting_info dl { display:block; margin-left:2em; margin-bottom:2em;}'
					."\n".'div#formatting_info dt { display:block; margin-top:1em;}'
					.$this->logicObj->getCSS();
			default:
				return $this->logicObj->getCSS(); // @todo replace with own interpretation
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

		switch ($this->modArray['func']) {
			case 'sync':
			case 'list':
				return '';
			case 'listall':
				$dictDAL = BcmsSystem::getDictionaryManager()->getModel();
				return $dictDAL->getTrans('articlesurvey');
			case 'history':
			case 'version':
				$dictDAL = BcmsSystem::getDictionaryManager()->getModel();
				$articleData = $this->logicObj->getCurrentArticleData();
				return $dictDAL->getTrans('articleHistoryOf').' "'.$articleData['heading'].'"';
			case 'write':
				if( !isset($_POST['abort_action'])
				  && !isset($_POST['article_edit_submit']))
				{
				$dictDAL = BcmsSystem::getDictionaryManager()->getModel();
				return $dictDAL->getTrans('cont.WriteArticle');
				} // if article is submitted the "break is missed" and the
				  //  "show" function will be executed!
			case 'edit_article':
				if( !isset($_POST['abort_action'])
				  && !isset($_POST['article_edit_submit']))
				{
				return BcmsSystem::getDictionaryManager()->getTrans('cont.EditArticle');
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
	 * @return String
	 * @author ahe
	 * @date 01.05.2006 00:21:56
	 */
	public function getMetaDescription() {
		$articleData = $this->logicObj->getCurrentArticleData();
		return $articleData['description'];
	}

	/**
	 * returns the MetaKeywords of the current menu
	 *
	 * @return String
	 * @author ahe
	 * @date 01.05.2006 00:23:41
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
		if(!isset($_POST['content_id'])) return null;
		$id=intval($_POST['content_id']);
		$status = intval($_POST['status']);
		$error = $this->articleDalObj->update(array('status_id'=>$status),'content_id = '.$id);
		if($error instanceof PEAR_ERROR){
			return BcmsSystem::raiseError($error, BcmsSystem::LOGTYPE_SELECT,
				BcmsSystem::SEVERITY_ERROR, 'checkChangeStatusArticle()'
				,__FILE__, __LINE__);
		}
		return true;
	}

	protected function createChangeStatusDialog(){
        if( !BcmsSystem::getUserManager()->hasRight($this->plgCatConfig['change_status_right']) ) // @todo plg_cat_conf-edit_right should actually be checked here!
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createChangeStatusArticleDialog()',__FILE__, __LINE__);

		$result = HTMLTable::getAffectedIds();
		$id=$result[0];
		$articleObj = $this->articleDalObj->getObject($id);
		$parser = BcmsSystem::getParser();
		$articleObj= $parser->stripArrayFieldsInverse($articleObj,array('content_id','status_id'));
		$form = $this->articleDalObj->getForm('articleeditstatusform','editArticleStatus'
			,BcmsSystem::getDictionaryManager()->getTrans('save'),$articleObj);
		$form->addElement('hidden','table_action_select_article_table','status_id');
		$form->addElement('hidden','dialog_submit','1');
		return $form->toHTML();
	}

	protected function createDeleteHistoryDialog(){
		// @todo check right for delete history action!
		include_once 'pear/HTML/QuickForm.php';
		$result = HTMLTable::getAffectedIds();
		$parser = BcmsSystem::getParser();
		$retStr = '';
		$action_url = $parser->getServerParameter('REDIRECT_URL');
		$form = new HTML_QuickForm('delete_elements','post',$action_url);
		for ($i = 0; $i < sizeof($result); $i++) {
			$id=$result[$i];
			$element = $form->addElement('hidden', 'elemid_'.$i, $id);
			if($retStr!='') $retStr .= ', ';
			$retStr .= $id;
		}
		$heading = BcmsSystem::getDictionaryManager()->getTrans('dict.h.deleteEntries');
		$question =	BcmsSystem::getDictionaryManager()->getTrans('really_delete');
		$retStr = $question."<br/>\n".$retStr;

		$element = $form->addElement('submit','submit_deletion',
			BcmsSystem::getDictionaryManager()->getTrans('submit'), 'id="submit_deletion"');
		$element->setLabel('&nbsp;');
		$element = $form->addElement('submit', 'abort_action',
			BcmsSystem::getDictionaryManager()->getTrans('cancel'), 'id="abort_action"');
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
			,BcmsSystem::getDictionaryManager()->getTrans('save'), $cols);
		if(count($cols)<1){
		  $form->addElement('hidden', 'new_record');
		}
		$heading =
			BcmsFactory::getInstanceOf('GuiUtility')->createHeading(3,
				BcmsSystem::getDictionaryManager()->getTrans(
				'h.category_plugin_config'));
		return $heading.$form->toHtml();
	}

	/**
	 * Lists all content objects according to filter
	 * @return String - rendered html
	 */
	protected function getCompleteList()
	{
		if(isset($_POST['table_action_select_article_table'])
			&& !isset($_POST['dialog_submit']))
		{ // AHE: The field 'dialog_submit' must be added to each dialog
				$dialog = $this->performListAction('article_table');
				if($dialog!=null) return $dialog;
		}
		// ...else print general table overview

		if( !BcmsSystem::getUserManager()->hasViewRight() )
			return BcmsSystem::raiseNoAccessRightNotice('getCompleteList()',__FILE__, __LINE__);

		$tableObj = new HTMLTable('article_table');
		$tableObj->setTranslationPrefix('cont.');
		$tableObj->setActions($this->getArticleActions());
		$tableObj->setBounds('page',null,$this->articleDalObj->getNumberOfEntries());
		$limit = $tableObj->getListLimit();
		$offset = $tableObj->getListOffset();

		// prepare searching
		list($searchphrase,$offset,$limit) = $tableObj->setSearchBehaviour(true);

		$articles = $this->articleDalObj->getAllArticlesList(null,null,null,$limit,$offset,$searchphrase);
		$articles = $this->prepareArticleListValues($articles);
		$tableObj->setData($articles);
		unset($articles);

		$showForm=BcmsSystem::getUserManager()->hasRight($this->plgCatConfig['edit_right']);
		return $tableObj->render(PluginManager::getPlgInstance('Dictionary')->getTrans('articlesurvey'),
		'content_id', $showForm);
	}

	/**
	 * Prepares values of specified array for list view
	 *
	 * @param Array articles - array containing data for list view
	 * @return Array - contains same as input array but with prepared values
	 * @author ahe
	 * @date 16.12.2006 02:04:23
	 */
	protected function prepareArticleListValues($articles) {
		for ($i = 0; $i < count($articles); $i++) {
			$h_id = '';
			foreach($articles[$i] as $key => $value) {
				if($key == 'heading') {
					$value = BcmsFactory::getInstanceOf('GuiUtility')->createAnchorTag('show/'.$h_id
					,$value);
				}
				if($key == 'author') {
					$value = BcmsFactory::getInstanceOf('GuiUtility')->createAuthorName($value);
				}
				if($key == 'status_id') {
					$value = PluginManager::getPlgInstance('Dictionary')->getStatusTrans($value);
				}
				$articleArr[$i][$key] = $value;
				if($key == 'content_id')
				$h_id = $value;
			}
		}
		return $articleArr;
	}

}
?>