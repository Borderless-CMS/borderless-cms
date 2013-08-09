<?php
require 'Classify_DAL.php';
/**
 * Contains Dictionary class
 *
 * @module Dictionary.php
 * @author ahe <aheusingfeld@borderless-cms.de>
 * @package plugins
 * @version $Id$
 */

class ClassificationManager extends AbstractManager {

	// the versionstring is needed e.g. for getHashcode()
	protected $versionstring = '0.1';
	protected $modname = 'ClassificationManager';
	private $dalObj;

	/**
	 *
	 *
	 * @author ahe
	 * @date 22.11.2005 20:07:42
	 * @package plugins
	 * @project bcms
	 */
	public function __construct() {
		$this->dalObj = new Classify_DAL();
	}

	/**
	 *
	 *
	 * @author ahe
	 * @date 22.11.2005 20:07:42
	 */
	public function init($menuID) {
		$this->checkTransactions();
	}

	public function main($menuId) {
	}

	/**
	 * returns the current menu's name to be added to teh page title
	 *
	 * @return string the current menu's name
	 * @author ahe
	 * @date 01.05.2006 00:20:33
	 * @package htdocs/plugins/classifications
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
	 * @package htdocs/plugins/classifications
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
	 * @package htdocs/plugins/classifications
	 */
	public function getMetaKeywords() {
		return null;
	}

	/**
	 *
	 *
	 * @param
	 * @author ahe
	 * @date 01.05.2006 00:37:13
	 * @package htdocs/plugins/classifications
	 */
	public function getCss($menuId=0) {}

	/**
	 * Ist in diesem Modul nicht vorhanden!
	 *
	 * @author ahe
	 * @date 22.11.2005 20:07:42
	 */
	public function printCategoryConfigForm($menuId=0) {
		return null;
	}

	/**
	 *
	 *
	 * @author ahe
	 * @date 22.11.2005 20:07:42
	 */
	public function printGeneralConfigForm() {
		$retStr = $this->createDictTable();
		$form =& $this->dictDalObj->getForm('dict_form','go_dict_action'
			,'abschicken');
		$retStr .= PluginManager::getPlgInstance('CategoryManager')->getLogic()->fillTemplate('fieldset_tpl'
					,array('id="dictionary"','&Uuml;bersetzung hinzuf&uuml;gen'
					,$form->toHtml(),null));
		echo $retStr;
	}

	private function createDictTable() {
		$this->dictDalObj->sql['listallcolumns']['fetchmode'] = DB_FETCHMODE_ASSOC;
		$trans = $this->dictDalObj->select('listallcolumns');

		// get maximum string length
		$maxLen = BcmsConfig::getInstance()->dict_max_trans_length;
		for ($i = 0; $i < count($trans); $i++) {
			foreach($trans[$i] as $key => $value) {
				// cut string if necessary
				if(mb_strlen($value)>$maxLen)
					$trans[$i][$key] = mb_substr($value,0,($maxLen-3)).'...';
			}
		}
		$tableObj = new HTMLTable('dict_table');
		$tableObj->setData($trans);
		return $tableObj->render();
	}

	/**
	 *
	 *
	 * @author ahe
	 * @date 22.11.2005 20:07:42
	 */
	public function checkTransactions() {
	}
}
?>
