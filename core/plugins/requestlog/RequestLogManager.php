<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

require 'core/plugins/requestlog/RequestLog_DAL.php';
/**
 * RequestLogManager handles request data. The most important data for each user
 * request is stored in the db. If the user runs on an error and uses the "send
 * bug report" form, his last 3 request are send with the bug report mail.
 * This will help the developers to fix bugs much quicker.
 *
 * @author ahe <aheusingfeld@borderlesscms.de>
 * @since 0.13
 * @date 16.12.2006 09:53:17
 * @class RequestLogManager
 * @ingroup requestlog
 * @package requestlog
 */
class RequestLogManager extends AbstractManager {

	// the versionstring is needed e.g. for getHashcode()
	protected $versionstring = '0.1';
	protected $modname = 'RequestLogManager';
	protected $dalObj = null;
	protected $categoryName = '';


	/**
	 *
	 *
	 * @author ahe
	 * @date 22.11.2005 20:07:42
	 */
	public function __construct() {
		BcmsConfig::getInstance()->setTablename('requestlog', 'request_log');

		$this->dalObj = RequestLog_DAL::getInstance();
		$this->categoryName = $this->dalObj->getPluginsCatName();
	}

	/**
	 *
	 *
	 * @author ahe
	 * @date 22.11.2005 20:07:42
	 */
	public function init($menuID) {
		$this->actions = array(
			0 => array('edit', BcmsSystem::getDictionaryManager()->getTrans('edit'), 'Edit'),
			1 => array('delete', BcmsSystem::getDictionaryManager()->getTrans('delete'), 'Delete')
		);
	}

	public function main($menuId){
		$myModArray = PluginManager::getInstance()->getCurrentMainPlugin();
		switch ($myModArray['func']) {
			case 'bug':
				return $this->createReportBugDialog();
			default:
				if(isset($_POST['table_action_select_requestlog_table'])){
					$i = -1;
					$actionFound = false;
					while($i < sizeof($this->actions) && !$actionFound){
						$i++;
						if($_POST['table_action_select_requestlog_table'] == $this->actions[$i][0])
							$actionFound=true;
					}
					$methodName = 'create'.$this->actions[$i][2].'Dialog';
					return $this->$methodName();
				}
				// ...else print general table overview
				return $this->printGeneralConfigForm();
		}
	}

	public function getReportBugLink() {
		$anchor = BcmsFactory::getInstanceOf('GuiUtility')->createAnchorTag(
			'/'.$this->categoryName.'/bug',
			BcmsSystem::getDictionaryManager()->getTrans('ReportBug'),
			0, null, 0,
			BcmsSystem::getDictionaryManager()->getTrans('ReportBug'),
			' class="report_bug_link"'
		);
		return BcmsFactory::getInstanceOf('GuiUtility')->createDivWithText(
			'class="report_bug_link"', null, $anchor);
	}

	public function getCss($menuId=0){
			return null;
	}

	/**
	 * returns the current menu's name to be added to teh page title
	 *
	 * @return string the current menu's name
	 * @author ahe
	 * @date 01.05.2006 00:20:33
	 */
	public function getPageTitle() {
		return null;
	}

	/**
	 * returns the MetaDescription of the current menu
	 *
	 * @return string
	 * @author ahe
	 * @date 01.05.2006 00:21:56
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
	 */
	public function getMetaKeywords() {
		return null;
	}

	/**
	 * Ist in diesem Modul nicht vorhanden!
	 *
	 * @author ahe
	 * @date 22.11.2005 20:07:42
	 */
	public function printCategoryConfigForm($menuId) {
		return null;
	}

	/**
	 *
	 *
	 * @author ahe
	 * @date 22.11.2005 20:07:42
	 */
	public function printGeneralConfigForm() {

		// get table and surround it with a scrollpane div-tag
		$retStr = $this->createRequestLogTable();
		return $retStr;
	}

	private function createRequestLogTable() {
        if( !BcmsSystem::getUserManager()->hasRight('request_view_list') )
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createRequestLogTable()',__FILE__, __LINE__);

		$tableObj = new HTMLTable('requestlog_table');
        $tableObj->setTranslationPrefix('requestlog.');
		$tableObj->setActions($this->actions);
		$tableObj->setBounds('page',null,$this->dalObj->getNumberOfEntries());
		$limit = $tableObj->getListLimit();
		$offset = $tableObj->getListOffset();

		// prepare searching
		list($searchphrase,$offset,$limit) = $tableObj->setSearchBehaviour(true);
		$requestlog_vars = $this->dalObj->getList($offset,$limit,null,$searchphrase);
		$tableObj->setData($requestlog_vars);
		unset($requestlog_vars);
		return $tableObj->render(
				BcmsSystem::getDictionaryManager()->getTrans('requestlog.heading'),
				'requestlog_id',
				true);
	}

	private function createReportBugDialog()
	{
        if( !BcmsSystem::getUserManager()->hasRight('request_submit_bug') )
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createReportBugDialog()',__FILE__, __LINE__);

		$heading = BcmsSystem::getDictionaryManager()->getTrans('requestlog.h.report_bug');
		$heading = BcmsFactory::getInstanceOf('GuiUtility')->createHeading(3,$heading);
		include_once 'HTML/QuickForm.php';
		$action_url = BcmsSystem::getParser()->getServerParameter('REDIRECT_URL');
		$form = new HTML_QuickForm('report_bug','post',$action_url);
		$form->addElement('textarea','report_bug_msg',
					BcmsSystem::getDictionaryManager()->getTrans('requestlog.h.bug_desc'));

		// ...and at last the submit button
		$form->addElement('submit','report_bug_submit',
					BcmsSystem::getDictionaryManager()->getTrans('submit'));

		return $heading.$form->toHTML();
	}

	protected function createEditDialog(){
        if( !BcmsSystem::getUserManager()->hasRight('request_view_entry') ) // @todo build plugin_cat_config for ConfigManager
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createEditDialog()',__FILE__, __LINE__);

		$result = HTMLTable::getAffectedIds();
		$id=$result[0];
		$this->dalObj->addLabels('requestlog.');
		$heading = BcmsSystem::getDictionaryManager()->getTrans('requestlog.h.edit_entry');
		$heading = BcmsFactory::getInstanceOf('GuiUtility')->createHeading(3,$heading);
		$recordData= $this->dalObj->getObject($id);
		foreach ($recordData[0] as $key => $value) {
			$record[$key] = BcmsSystem::getParser()->filter($value);
		}
		$form = $this->dalObj->getForm('requestlogEditform','editRequestLogElement'
			,BcmsSystem::getDictionaryManager()->getTrans('submit'),$record);
		return $heading.$form->toHTML();
	}

	protected function createDeleteDialog(){
        if( !BcmsSystem::getUserManager()->hasDeleteRight() )
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createDeleteDialog()',__FILE__, __LINE__);

		$heading = BcmsSystem::getDictionaryManager()->getTrans('requestlog.h.deleteEntries');
		return $this->createDeletionConfirmFormForHTML_TableForms($heading);
	}

	/**
	 *
	 *
	 * @author ahe
	 * @date 22.11.2005 20:07:42
	 */
	public function checkTransactions($id=0) {
		if(array_key_exists('submit_deletion', $_POST)){
			return $this->checkForDeleteTransaction('requestlog_id',__FILE__,__LINE__);
		}

		if(array_key_exists('report_bug_submit', $_POST)){
			return $this->sendBugReport();
		}

		// check for edit
		if(array_key_exists('requestlog_id', $_POST)){
			return $this->makeCheck($this->dalObj,
				'editRequestLogElement',
				$_POST,
				'update',
				'requestlog_id='.intval($_POST['requestlog_id'])
			);
		}

		return $this->makeCheck($this->dalObj,'go_requestlog_action');
	}

	/**
	 * Collects information on last three request and sends it with submitted
	 * text via email to bug-support.
	 *
	 * @return boolean - mail sending successful?
	 * @author ahe
	 * @date 20.12.2006 00:00:44
	 */
	private function sendBugReport() {
		$parser = BcmsSystem::getParser();

		$from = BcmsConfig::getInstance()->webmasterEmail;
		$to   = BcmsConfig::getInstance()->bugreportEmail;
		$subject = BcmsSystem::getDictionaryManager()->getTrans('requestlog.bug_mail_subject')
					.BcmsConfig::getInstance()->siteUrl;

		$text = BcmsSystem::getDictionaryManager()->getTrans('requestlog.bug_mail_beginning');
		if(array_key_exists('report_bug_msg',$_POST)){
			// get user's message
			$text .= $parser->getPostParameter('report_bug_msg')."\n\n";
		}
		// get username
		$text .= BcmsSystem::getUserManager()->getLogic()->getUserName()
					.' ('.$parser->getServerParameter('REMOTE_ADDR').")\n";
		// get uri
		$text .= $parser->getServerParameter('REQUEST_URI')."\n";
		// strip potentially malicious chars
		$text = $parser->filterPageTitle($text);

		// get array with last requests
		$data = $this->dalObj->getListForBugReport();
		// concat array to message text
		$text .= "\n\n ====== DATA ============\n\n";
		for ($i = 0; $i < sizeof($data); $i++) {
			$text .= "\nTimestamp: ".$data[$i]['request_date'];
			$text .= "\nURI: ".$data[$i]['uri'];
			$text .= "\n\n\$_POST:\n".
				$parser->filterPageTitle(print_r($data[$i]['post'],true));
			$text .= "\n\n\$_GET:\n".
				$parser->filterPageTitle(print_r($data[$i]['get'],true));
			$text .= "\n\n\$_SESSION:\n".
				$parser->filterPageTitle(print_r($data[$i]['session'],true));
			$text .= "\n".'========== END OF RECORD #'.($i+1).' ============'."\n\n\n";
		}
		if(BcmsSystem::sendmail_to_address($to, $from,$subject, $text))
		{
			return BcmsSystem::raiseNotice('Der von Ihnen gemeldete Fehler ' .
					'wird nun unserem Entwickler-Team übermittelt. ' .
					'Vielen Dank für Ihre Hilfe.',
				BcmsSystem::LOGTYPE_INSERT, BcmsSystem::SEVERITY_INFO,
				'createEditMenuForm()',__FILE__, __LINE__);
		}
		return false;
	}
}
?>
