<?php
/**
 *
 *
 * @module BcmsArticle.php
 * @author ahe <aheusingfeld@borderless-cms.de>
 * @package content
 * @version $Id$
 */
class BcmsArticle extends SingleObjectPattern {

	/**
	 *
	 *
	 * @param int id content_id
	 * @author ahe
	 * @date 23.11.2005 15:13:44
	 */
	public function __construct($id=0) {
		if($id>0) {
			$refArticleData =PluginManager::getPlgInstance('ContentManager')->getArticleDalObj();
			$objectDataRecord = $refArticleData->getObject($id);
			$this->setObjectDataWithArray($objectDataRecord[0]);
		}
	}

	public function __get($memberName)
	{
		return $this->get($memberName);
	}

	public function __set($memberName, $value)
	{
		$this->set($memberName, $value);
	}

/* "normal" methods follow here */

	public function setObjectDataWithArray($objectData){
		$this->virtualMembers = $objectData;
	}
}
?>