<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * @todo document this
 * 
 * @since 0.11
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @class BcmsArticle
 * @ingroup content
 * @package content
 */
class BcmsArticle extends BcmsObject {

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