<?php
/***       include definitions      ***/
require_once 'objects/File_DAL.php';
require_once 'objects/FileConfig_DAL.php';

/**
 * Contains ObjectManager class
 *
 * @module ObjectManager.php
 * @author ahe <aheusingfeld@borderlesscms.de>
 * @package object
 * @version $Id$
 */
class ObjectManager extends AbstractManager {

	// the versionstring is needed e.g. for getHashcode()
	protected $versionstring = '0.31';
	protected $moduleName = 'ObjectManager';
	protected $dalObj = null;  // saves File_DAL class reference
	private $objectData = array();
	private $postfixForSmall = '_small';
	private $pm = null; // PluginManager
	private $validImageFiletypes = array('image/png', 'image/jpeg', 'image/gif');
  	protected $configDalObj = null;
  	protected $logicObj = null;
  	private $initCalled = false;
  	private $plgCatConfig = null;


  /**
   *
   *
   * @author ahe
   * @date 22.11.2005 20:07:42
   * @package object
   * @project bcms
   */
	public function __construct() {
		if($this->initCalled) return;

		BcmsConfig::getInstance()->setTablename('objects', 'plg_objects');
		BcmsConfig::getInstance()->setTablename('file_config', 'plg_file_cat_conf');
		$this->dalObj = File_DAL::getInstance();
		$this->configDalObj = FileConfig_DAL::getInstance();
		$this->modArray = PluginManager::getInstance()->getCurrentMainPlugin();
		$this->plgCatConfig = $this->configDalObj->getObject($_SESSION['m_id']);
		$this->plgCatConfig['sort_direction'] =
			($this->plgCatConfig['sort_direction'])==41 ? 'ASC' : 'DESC'; // URGENT use classifications!
		$this->gui = Factory::getObject('GuiUtility');
		$this->actions = array(
			0 => array('edit', Factory::getObject('Dictionary')->getTrans('edit'), 'Edit'),
			1 => array('delete', Factory::getObject('Dictionary')->getTrans('delete'), 'Delete'),
			2 => array('change_preview_size', Factory::getObject('Dictionary')->getTrans('om.change_preview_size'), 'ChangePreviewSize')
		);
		$this->initCalled = true;
	}

  /**
   *
   *
   * @author ahe
   * @date 22.11.2005 20:07:42
   * @package object
   * @project bcms
   */
	public function init($menuID) {
		// assure that handling with filenames works
		$parser = BcmsFactory::getInstanceOf('Parser');
		if($parser->getGetParameter('oname')!=null)
		{
			$result = $this->dalObj->getObjectIdByWhere('object_filename LIKE '
				.$parser->prepDbStrng($_SESSION['mod']['oname'].'%'));
			if(!empty($result))	$_SESSION['mod']['oid'] = $result['object_id'];
		}

	}

	public function getCss($menuId=0){
		return '';
	}

	/**
	 * returns the current menu's name to be added to teh page title
	 *
	 * @return string the current menu's name
	 * @author ahe
	 * @date 01.05.2006 00:20:33
	 * @package htdocs/plugins/objects
	 */
	public function getPageTitle() { return null; }

	/**
	 * returns the MetaDescription of the current menu
	 *
	 * @return string
	 * @author ahe
	 * @date 01.05.2006 00:21:56
	 * @package htdocs/plugins/objects
	 */
	public function getMetaDescription() { return null; }

	/**
	 * returns the MetaKeywords of the current menu
	 *
	 * @return string
	 * @author ahe
	 * @date 01.05.2006 00:23:41
	 * @package htdocs/plugins/objects
	 */
	public function getMetaKeywords() { return null; }

	public function main($menuId) {
		$retVal = $this->createActionMenuAtTop();

		switch ($this->modArray['func']) {
			case 'config':
				return $this->printCategoryConfigForm($menuId);
			case 'insert':
				return $retVal.$this->insertObject();
			case 'show':
				return $retVal.$this->showImageDetails($_SESSION['mod']['oid']);
			case 'list':
			default:
				return $retVal.$this->getList();
		}
	}

  /**
   * Returns form for category dependent plugin configuration
   *
   * @author ahe
   * @date 22.11.2005 20:07:42
   * @package object
   * @project bcms
   */
  public function printCategoryConfigForm($catId) {
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

  /**
   *
   *
   * @author ahe
   * @date 22.11.2005 20:07:42
   * @package object
   * @project bcms
   */
  public function printGeneralConfigForm() {
	return '...printGeneralConfigForm() aufgerufen...';
  }

  /**
   *
   *
   * @author ahe
   * @date 22.11.2005 20:07:42
   * @package object
   * @project bcms
   */
	public function checkTransactions($menuId=0) {
		if(isset($_POST['upload_object']))
			return $this->checkForUpload();

		if(isset($_POST['object_phase4']))
			return $this->checkForResize();

		if(isset($_POST['submit_deletion']))
			return $this->checkForDeletion();

		if(isset($_POST['cat_config_submit']))
			return $this->checkCategoryConfigSubmitted();

		// check for edit
		return $this->makeCheck($this->dalObj,
			'editObject',
			$_POST,
			'update',
			'object_id='.intval($_POST['object_id']));
	}

/* *** BEGIN OF  "CHECK TRANSACTION METHODS" - SECTION *** */

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

	private function checkForUpload() {
		if(!$file = $this->saveUploadedFile()) return false;
		$parser = BcmsFactory::getInstanceOf('Parser');
		list($width, $height, $type) = getimagesize($this->getUploadFolder().$file);
		$data = array(
			'object_id' => $this->dalObj->nextId(),
			'object_filename' => $file,
			'object_smallimage_filename' => $this->createSmallImageFilename($file),
			'object_folder' => $this->getUploadFolder(),
			'object_type' => image_type_to_mime_type($type),
			'object_width' => $width,
			'object_height' => $height,
			'object_importdate' => date('YmdHis'),
			'object_import_user' => PluginManager::getPlgInstance('UserManager')->getLogic()->getUserID()
		);
		$resultOk = $this->dalObj->checkForAction('upload_object',$data);
		if(!$resultOk) return false;
		return BcmsSystem::raiseNotice('Objekt erfolgreich hochgeladen!',
				BcmsSystem::LOGTYPE_INSERT,	BcmsSystem::SEVERITY_INFO,
				'checkForUpload()',	__FILE__,__LINE__); // TODO Use dictionary here!
	}

	private function checkForResize() {
		$parser = BcmsFactory::getInstanceOf('Parser');
		if(isset($_POST['object_id']))
			$objectId = $parser->getPostParameter('object_id');
		if(!is_numeric($objectId))
			throw new Exception('ObjektId ist keine Zahl! ID: '.$objectId);

		$values = $this->getObjectValuesById($objectId);
		$filename = $values['object_folder'].$values['object_smallimage_filename'];
		$oid = $values['object_id'];
		unset($values);
		if(is_file($filename)){
			$newValues = array();
			list($newValues['object_width'], $newValues['object_height']) = getimagesize($filename);
			$this->dalObj->update($newValues,'object_id='.$oid);
		}

	}

	private function checkForDeletion() {
		$deleteIds = HTMLTable::getAffectedIds();
		$files = $this->getFilenamesArrayByIds($deleteIds);
		$deletedAll = true;
		foreach ($files as $file) {
			if(file_exists($file)) {
				if(!@unlink(BASEPATH.'/'.$file)) {
					$msg = ' Löschen von '.$file.' fehlgeschlagen!'; // TODO use dictionary!
					BcmsSystem::raiseNotice($msg, BcmsSystem::LOGTYPE_CHECK,
						BcmsSystem::SEVERITY_WARNING, 'checkForDeletion()',
						__FILE__, __LINE__);
					$deletedAll = false;
				}
			}
		}
		if($deletedAll)
			return $this->checkForDeleteTransaction('object_id',__FILE__,__LINE__);
		else
			return false;
	}

/* *** END OF  "CHECK TRANSACTION METHODS" - SECTION *** */

/* *** BEGIN OF "PUBLIC HELPER METHODS" - SECTION *** */
	/**
	 * Takes an array with a match and returns the according html-tag
	 *
	 * @see Parser#parseFileTagThumbByRegex()
	 * @param enclosing_method_arguments
	 * @return return_type
	 * @author ahe
	 * @date 31.12.2006 23:52:15
	 * @package htdocs/plugins/objects
	 */
	public function createFilesThumbTag($matches)
	{
		$parser = BcmsFactory::getInstanceOf('Parser');
		$result = $this->dalObj->getObjectIdByWhere('object_filename LIKE '
			.$parser->prepDbStrng($matches[1].'%'));
		if(empty($result)) return null;

		return $this->getObjectsSmallImage($result['object_id'],true
				,$matches[2]);
	}

	/**
	 * Takes a filename and strips extension and path. Returns only the
	 * filename.
	 *
	 * @param String $file the filename with path and extension
	 * @return String the filename
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 * @date 04.10.2006 23:04:31
	 * @package htdocs/plugins/objects
	 */
	public function getFilenameWithoutExtension($file){
		$pieces = explode('.',$file);
		if(count($pieces)>2)
			throw Exception('Filename must not contain more than one \'.\' ' .
					'(dot)! The dot is used to get the file extension.');
		$pieces = explode('/',$pieces[0]);
		return $pieces[count($pieces)-1];

	}

  /**
   * Benutzt ein File_DAL-Objekt um die Liste der vorhandenen Objekte zu
   * holen
   * @author ahe
   *
   */
	public function getObjectList4Select()
	{
		$objList = $this->dalObj->getObjectList('list_by_shortdesc');
		// Leerzeile oben anstellen
		$objects[0] = '&nbsp;';
		for ($i = 0; $i < count($objList); $i++) {
			$maxLength = BcmsConfig::getInstance()->select_field_max_no_of_chars;
			$desc = $objList[$i]['object_shortdesc'];
			$info = ' ('.$objList[$i]['object_filename'].' - '
				.$objList[$i]['object_width'].'x'.$objList[$i]['object_height'].'px)';
			$maxDescLength= $maxLength - mb_strlen($info);
			if(mb_strlen($desc)>$maxDescLength)
			{
				$desc = mb_substr($objList[$i]['object_shortdesc'],0,$maxDescLength-4).'... ';
			}
			// add indent in front of menu names
			$objects[$objList[$i]['object_id']] =
				$desc
				.$info;
		}
		return $objects;
	}

	/**
	 * URGENT refactor this object list as soon as article form is
	 * being refactored.
	 *
	 * creates the list of objects for ARTICLE FORM
	 *
	 * @param boolean addFormElements optional (default=false)
	 * @return return_type
	 * @author ahe
	 * @date 25.11.2005 09:55:54
	 */
	public function getObjectList($addFormElements=false) {
		$allObjects = $this->dalObj->getObjectList();
		$clearer = $this->gui->fillTemplate('clearDiv_tpl'
			,array('class="clearer"','&nbsp;'));
		$attributes = array(
			0 => array('desc','sr.Desc','longdesc'),
			1 => array('author','sr.Author','author'),
			2 => array('created','sr.CreationDate','created'),
			3 => array('importdate','importedDate','importdate'),
			4 => array('importuser','importedBy','import_user')
			);
		$objectList = null;

		foreach ($allObjects as $key =>$value) {
			$objSrc = '';
			$objSrc .= $this->getObjectsSmallImage(0,true
				,'float:left; margin:5px 5px 5px 5px',$value);

			$objSrc .= $this->gui->createHeading(
				'3 id="object_filename'.$key.'"',
				Factory::getObject('Dictionary')->getTrans('om.Object').': '
				.$value['object_filename']);

			for ($i = 0; $i < sizeof($attributes); $i++) {
				$objSrc .= $this->gui->fillTemplate('p_tpl'
					,array('id="object_'.$attributes[$i][0].$key.'"'
					,'<span>'.Factory::getObject('Dictionary')->getTrans($attributes[$i][1]).':'
					.'</span> '.$value['object_'.$attributes[$i][2]]));
			}

			if($addFormElements) {
				$objSrc .= $this->gui->fillTemplate('input_tpl'
					, array('submit', 'object_chosen___'.$value['object_id']
					, $value['object_filename']
					.' '.Factory::getObject('Dictionary')->getTrans('choose'), null));

			} // end_if
			$objectList .= $this->gui->fillTemplate('div_tpl',array('class="objListItem"'
				,$objSrc.$clearer));
		} //end_for

		// insert a hidden form field holding the article's form field index
		if($addFormElements) {
			$objectList .= $this->gui->fillTemplate('input_tpl'
				, array('hidden', 'article_update_image'
				, $_POST['image_index'], null));
		}

		$divObjectList = $this->gui->fillTemplate('div_tpl',
			array('id="object_list"',$objectList));

		if($addFormElements) {
			return $this->gui->fillTemplate('object_form_tpl', array('3',
				'id="object_list_heading"',
				Factory::getObject('Dictionary')->getTrans('om.objList'),
				'object_choose_form',
				BcmsFactory::getInstanceOf('Parser')->getServerParameter('SCRIPT_URI'),'post'
				,'application/x-www-form-urlencoded',$divObjectList,null));
		} else {
			return $divObjectList;
		}
	}

  /**
   * Fetches object information by id and generates an html img-tag
   *
   * @param array internData this can be an object record
   * @author ahe
   * @return string html img-tag
   */
  public function getObjectValuesById($objectId, $internData=null)
  {
	if(is_array($internData)) {
	  $objData = $internData;
	} else {
	  // fetch new recordset from database
	  $objData = $this->dalObj->getObject($objectId);
	}
	$objData['src'] = '/'.$objData['object_folder']
		.$objData['object_smallimage_filename'];
	return $objData;
  }

  /**
   * Fetches object information by id and generates an html img-tag
   *
   * @param array internData this can be an object record
   * @author ahe
   * @return string html img-tag
   */
	public function getObjectsSmallImage($objectId, $createAnchorTag=true, $additionalStyleInfo=null, $internData=null,$newWidth=0)
	{
		if(is_array($internData)) {
		  $objData = $internData;
		} else {
		  // fetch new recordset from database
		  $objData = $this->dalObj->getObject($objectId);
		  $this->objectData = $objData;
		}
		if($newWidth>0 && $objData['object_width']>0) {
			$newHeight = ceil($newWidth / $objData['object_width']*$objData['object_height']);
		}
		$width=($newWidth>0) ? $newWidth : $objData['object_width'];
		$height=($newWidth>0) ? $newHeight : $objData['object_height'];

		// strip quotation marks from image description
		$shortDesc = str_replace( array('"','\''), '', $objData['object_shortdesc']);
		if($objData['object_smallimage_filename']==''
			|| !is_file($objData['object_folder'].$objData['object_smallimage_filename']))
		{
			$theImgDataArray = $this->getPreviewImageSrcByType(
				$objData['object_type'],
				$objData['object_filename']);
		} else {
			$theImgDataArray = array(
			  'src' => '/'.$objData['object_folder']
				.$objData['object_smallimage_filename'], // Filename
				'style' =>''.$additionalStyleInfo, // style
			    'alt' => $shortDesc,  // ALT text/ description
				'title' => $shortDesc,  // title
				'width' => $width,
				'height' => $height
			);
		}
		$retValue = $this->gui->createImageTag($theImgDataArray);
		if($createAnchorTag) {
		  $retValue =
			$this->gui->createAnchorTag('/'.$this->dalObj->getPluginsCatName()
			.'/show/'.$objData['object_filename'],
			$retValue, 0, null, 0,
			$shortDesc.' - Link &ouml;ffnet Detailinformationen'); // TODO Use dictionary!
		}
		return $retValue;
	}

/* *** END OF "PUBLIC HELPER METHODS" - SECTION *** */


/* *** BEGIN OF "INTERNAL PROCESSING METHODS" - SECTION *** */

	/**
	 * @return String - html representation of file table
	 */
	private function getList(){
        if( !PluginManager::getPlgInstance('UserManager')->hasViewRight() )
		    return BcmsSystem::raiseNoAccessRightNotice(
				'getList()',__FILE__, __LINE__);

		$dialog = $this->performListAction('object_table');
		if($dialog!=null) return $dialog;
		// ...else print general table overview

		// get turn page vars
		$tableObj = new HTMLTable('object_table');
        $tableObj->setTranslationPrefix('om.');
		$tableObj->setActions($this->actions);
		$limit = $this->plgCatConfig['files_per_page'];
		if(empty($limit) || !is_numeric($limit))
		{
			$limit = BcmsConfig::getInstance()->max_object_list_entries;
		}
		$tableObj->setBounds('page',$limit,$this->dalObj->getNumberOfEntries());
		$offset = $tableObj->getListOffset();

		// prepare searching
		list($searchphrase,$offset,$limit) = $tableObj->setSearchBehaviour(true);
		$objects = $this->dalObj->getList($offset,$limit,null,$searchphrase);
		$obj = $this->rearrangeTableColumns($objects);
		unset($objects);
		$tableObj->setData($obj);
		unset($obj);
		$retStr = $tableObj->render(
			Factory::getObject('Dictionary')->getTrans('om.h.list'),
			'object_id',true);

		return $retStr;
	}

	private function showImageDetails($id) {
        if( !PluginManager::getPlgInstance('UserManager')->hasRight($this->plgCatConfig['view_details_right']) )
		    return BcmsSystem::raiseNoAccessRightNotice(
				'getList()',__FILE__, __LINE__);

		$attributes = array(
			0 => array('shortdesc','om.object_shortdesc','shortdesc'),
			1 => array('longdesc','om.object_longdesc','longdesc'),
			2 => array('author','om.object_author','author'),
			3 => array('created','om.object_created','created'),
			4 => array('type','om.object_type','type'),
			5 => array('importdate','om.object_importdate','importdate'),
			6 => array('importuser','om.object_import_user','import_user')
			);
		$objSrc = '';
		// the following method loads the object data that's why it's called first
		$objSrc .= $this->getObjectsSmallImage($id,true
			,'float:left; margin:5px 5px 5px 5px');

		$objSrc = $this->gui->createHeading(
			'3 id="object_filename"',
			Factory::getObject('Dictionary')->getTrans('om.Object').': '
			.$this->objectData['object_filename']).$objSrc;

		for ($i = 0; $i < sizeof($attributes); $i++) {
			$objSrc .= $this->gui->fillTemplate('p_tpl'
				,array('id="object_'.$attributes[$i][0].'"'
				,'<span>'.Factory::getObject('Dictionary')->getTrans($attributes[$i][1]).':'
				.'</span> '.$this->objectData['object_'.$attributes[$i][2]]));
		}
		return $objSrc;
	}

	protected function insertObject() {
        if( !PluginManager::getPlgInstance('UserManager')->hasRight($this->plgCatConfig['add_right']) )
		    return BcmsSystem::raiseNoAccessRightNotice(
				'insertObject()',__FILE__, __LINE__);

		// Object phase 1
		$retStr = '<p>'
			.Factory::getObject('Dictionary')->getTrans('om.insert_notice')
			.'</p>'."\n".'<p>';
		$retStr .= $this->gui->fillTemplate('label_tpl',
			array('search_object',
			Factory::getObject('Dictionary')->getTrans('om.choose_object').': ',null));
		$retStr .= $this->gui->fillTemplate('input_tpl',
			array('file','search_object','',null));
		$retStr .= '</p>'."\n".'<p>';
		$retStr .= $this->gui->fillTemplate('label_tpl',
			array('object_save_as',
			Factory::getObject('Dictionary')->getTrans('om.save_as').': ',null));
		$retStr .= $this->gui->fillTemplate('input_tpl',
			array('text','object_save_as','',null));
		$retStr .= '</p>';

		$retStr = $this->gui->fillTemplate('fieldset_tpl',
			array('id="object_upload_fieldset"',
			Factory::getObject('Dictionary')->getTrans('om.upload'),
			$retStr
			,null,null));

		$retStr .= $this->gui->fillTemplate('input_tpl',
			array('hidden','MAX_FILE_SIZE','2000000',null)); // TODO Find another way to send max_file_size!
		$retStr .= $this->gui->fillTemplate('input_tpl',
			array('submit','upload_object',
			Factory::getObject('Dictionary')->getTrans('continue'),null));
		$formContent = $retStr;
		$formString = $this->gui->fillTemplate('object_form_tpl', array('2',
			'id="object_upload_heading"',
			Factory::getObject('Dictionary')->getTrans('om.upload'),
			'object_upload_form',BcmsFactory::getInstanceOf('Parser')->getServerParameter(
			'SCRIPT_URI'),'post','multipart/form-data',$formContent,null,null));
		return $formString;
	}

/* *** END OF "INTERNAL PROCESSING METHODS" - SECTION *** */

/* *** BEGIN OF "INTERNAL DIALOG METHODS" - SECTION *** */
	protected function createChangePreviewSizeDialog(){
		$result = HTMLTable::getAffectedIds();
		$id=$result[0];
		if(!$this->isObjectAnImage($id))
			return 'FEHLER: Ausgew&auml;hltes Objekt ist KEIN Bild!'; // TODO use dictionary

		$file = $this->dalObj->getObjectFilenameById($id);

		if(!file_exists($file)) {
			$msg = Factory::getObject('Dictionary')->getTrans('om.file_not_exists')
				.' - ORIGINAL: '.$file;
			return BcmsSystem::raiseNotice($msg, BcmsSystem::LOGTYPE_CHECK,
				BcmsSystem::SEVERITY_ERROR, 'createChangePreviewSizeDialog()'
				,__FILE__, __LINE__);
		}

		// create smaller preview image
		// TODO die folgende Methode muesste eigentlich das Formular zurueckgeben und nicht den neuen Dateinamen!
		$newfilename = $this->createImagesizeDialog($file
			,3,Factory::getObject('Dictionary')->getTrans('om.workSmallImgSize'),$id);

		return $this->createPhasefinishedForm($file,4,$newfilename,$id);
	}

	protected function createEditDialog(){
		$result = HTMLTable::getAffectedIds();
		$id = $result[0];
		$element = $this->dalObj->getObject($id);

        if( !PluginManager::getPlgInstance('UserManager')->hasRight($this->plgCatConfig['edit_right'])
        	&& !(PluginManager::getPlgInstance('UserManager')->hasRight($this->plgCatConfig['edit_own_right'])
				&& $element['object_import_user_id'] == PluginManager::getPlgInstance('UserManager')->getLogic()->getUserID()
				)
        ){
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createEditDialog()',__FILE__, __LINE__);
        }

		$objImgTag = $this->getObjectsSmallImage($id,false,null,$element);
		$this->dalObj->addLabels('om.');
		$form = $this->dalObj->getForm('objecteditform','editObject',
			Factory::getObject('Dictionary')->getTrans('submit'),$element);
		return $objImgTag.$form->toHTML();
	}

	protected function createDeleteDialog(){
        if( !PluginManager::getPlgInstance('UserManager')->hasRight($this->plgCatConfig['del_right']) )
		    return BcmsSystem::raiseNoAccessRightNotice(
				'createDeleteDialog()',__FILE__, __LINE__);

		$heading = Factory::getObject('Dictionary')->getTrans('om.h.deleteEntries');
		$result = HTMLTable::getAffectedIds();
		$additionalInfo = '<p>'.$this->createDeleteFilesNotice($result).'</p>';
		return $this->createDeletionConfirmFormForHTML_TableForms($heading, $additionalInfo);
	}

	private function createDeleteFilesNotice($deleteIds) {
		$files = $this->getFilenamesArrayByIds($deleteIds);
		$msg = Factory::getObject('Dictionary')->getTrans('om.delete_following_files');
		foreach ($files as $file) {
			$msg .= '<br/>'."\n".$file;
		}
		return $msg;
	}

	private function createImagesizeDialog($file,$phase,$heading,$id) {
		// set file permissions
		@chmod($file,0666);
		$newfilename = $this->createSmallImageFilename($file);

		list($width, $height, $type, $attr) = getimagesize($file);
		$current_ratio = 0.1;

		// calculate ratio if small image already exists
		if(is_file($newfilename) && file_exists($newfilename))
		{
			list($small_width) = getimagesize($newfilename);

			$current_ratio = (1/$width*$small_width);
		}

		if($type){
			// try to get resize ratio from POST ... else set default
			if(BcmsFactory::getInstanceOf('Parser')->getPostParameter('image_size_ratio')) {
				$ratio = BcmsFactory::getInstanceOf('Parser')->getPostParameter('image_size_ratio');
			} else {
				$ratio = $current_ratio;
			}
			$new_width = round($ratio*$width,0);
			$new_height = round($ratio*$height,0);
			echo $this->createChangeImageSizeForm($file,$ratio,$phase,$heading,$id);

			echo Factory::getObject('Dictionary')->getTrans('om.original_size')
				.$width.'x'.$height.'px';
			$newfilename = $this->createSmallImage($file,$new_width,$new_height);
			$fileWoExt = $this->getFilenameWithoutExtension($file);
			echo '<div><p>'.Factory::getObject('Dictionary')->getTrans('om.new_image')
			.$newfilename.'</p>
				<p><img src="getthumb/'.$fileWoExt.'" alt="'.
				Factory::getObject('Dictionary')->getTrans('om.resized_image').'" /></p>
				</div>'."\n";
			return $newfilename;
		}

		return false;
	}

	private function createChangeImageSizeForm($p_sFilename,$default_new_ratio,$phase,$heading,$id) {
		$formStr = '';
		$formStr .= '<p>'.$this->gui->fillTemplate('label_tpl'
			, array('image_size_ratio'
			,Factory::getObject('Dictionary')->getTrans('om.ratio'),null));
		$formStr .= $this->gui->fillTemplate('input_tpl'
			, array('input','image_size_ratio',$default_new_ratio,null))
			.'</p>'."\n";
		$formStr .= $this->gui->fillTemplate('input_tpl'
		  , array('hidden','resize_filename',$p_sFilename,null));
		$formStr .= $this->gui->fillTemplate('input_tpl'
			, array('hidden','table_action_select_object_table'
			,$_POST['table_action_select_object_table'],null));
		$formStr .= $this->gui->fillTemplate('input_tpl'
		  , array('hidden','elemid_'.$id,'On',null));

		$formStr = $this->gui->fillTemplate('fieldset_tpl'
			, array('id="object_resize_fieldset"'
			,Factory::getObject('Dictionary')->getTrans('om.workObjectSize')
			,$formStr,null,null));
		$formStr .= $this->gui->fillTemplate('input_tpl'
			, array('submit','object_phase'.$phase
			,Factory::getObject('Dictionary')->getTrans('om.changeImgSize'),null));
		$formString = $this->gui->fillTemplate('object_form_tpl'
			, array('2','id="object_resize_heading"',$heading,'object_resize_form'
			,BcmsFactory::getInstanceOf('Parser')->getServerParameter('SCRIPT_URI'),'post'
			,'application/x-www-form-urlencoded',$formStr,null,null));

		return $formString;
	}

  private function createPhasefinishedForm($p_sFilename, $phase, $p_sNewFilename=null, $id=null) {
	$formContent = $this->gui->fillTemplate('input_tpl'
		, array('submit','object_phase'.$phase
		,Factory::getObject('Dictionary')->getTrans('continue'),null));
	$formContent .= $this->gui->fillTemplate('input_tpl'
	  , array('hidden','filename',$p_sFilename,null));
	$formContent .= $this->gui->fillTemplate('input_tpl'
	  , array('hidden','object_id',$id,null));
	$formContent .= $this->gui->fillTemplate('input_tpl'
	  , array('hidden','phase_finished','true',null));
	if($p_sNewFilename!=null) {
		$formContent .= $this->gui->fillTemplate('input_tpl'
			, array('hidden','new_filename',$p_sNewFilename,null));
	}
	$formString = $this->gui->fillTemplate('object_form_tpl'
		, array('2','id="object_phase2_heading"'
	  ,Factory::getObject('Dictionary')->getTrans('om.ImageSizeOk')
		,'object_phase2_form'
		,BcmsFactory::getInstanceOf('Parser')->getServerParameter('SCRIPT_URI')
		,'post','application/x-www-form-urlencoded',$formContent,null,null));

	  return $formString;
  }

/* *** BEGIN OF "INTERNAL HELPER METHODS" - SECTION *** */

	private function isObjectAnImage($id) {
		$type = $this->dalObj->getObjectTypeById($id);
		return (in_array($type,$this->validImageFiletypes));
	}

	/**
	 * Takes the object array and creates a new array with the preview image as
	 * first column, a link to the objects detail page, etc.
	 *
	 * @param String[][] $objects the result array of the db-query
	 * @return String[][] the rearranged array with additional data
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 * @date 04.10.2006 22:44:12
	 * @package htdocs/plugins/objects
	 */
	private function rearrangeTableColumns($objects){
		$obj = array();
		$keys = @array_keys($objects[0]);
		for ($i = 0; $i < sizeof($objects); $i++) {
			$objects[$i]['object_filename'] = $this->gui->createAnchorTag(
					'show/'.$objects[$i]['object_id'],
					$objects[$i]['object_filename']
			);
			$objects[$i]['object_import_user'] =
				$this->gui->createAuthorName($objects[$i]['object_import_user'],16);
			// put image in first column (use -image for semi-automatic translation in HTMLTable class)
			$obj[$i]['-image'] =
				$this->getObjectsSmallImage($objects[$i]['object_id'],false,null,null,50);

			foreach($keys as $key){
				$obj[$i][$key]= $objects[$i][$key];
			}
		}
		return $obj;
	}

  /**
   *
   *
   * @return string
   * @author ahe
   * @date 22.11.2005 20:47:44
   */
  private function getUploadFolder() {
	if(substr(BcmsConfig::getInstance()->upload_dir,0,3)==='../') {
	  $folder = substr(BcmsConfig::getInstance()->upload_dir.'',3);
	} else {
	  $folder = BcmsConfig::getInstance()->upload_dir;
	}
	return $folder;
  }

	private function saveUploadedFile ($move=true) {
		$parser = BcmsFactory::getInstanceOf('Parser');
		if(isset($_POST['object_save_as']) && !empty($_POST['object_save_as'])
			&& is_string($_POST['object_save_as'])) {
			$file = $parser->getPostParameter('object_save_as');
		} else {
			$file = $_FILES['search_object']['name'];
		}
		if(!$this->checkFilename($file)) return false;
		$folder = $this->getUploadFolder();
		if(!file_exists($folder.$file)) { // TODO moves file only if not already uploaded - currently no overwrite
			if (!move_uploaded_file($_FILES['search_object']['tmp_name']
				, $folder.$file))
			{
				$msg = Factory::getObject('Dictionary')->getTrans('om.upload_failed')
					.' - IMG: '.$folder.$file;
				return BcmsSystem::raiseNotice($msg, BcmsSystem::LOGTYPE_INSERT,
				BcmsSystem::SEVERITY_FAILURE, 'saveUploadedFile()'
					,__FILE__, __LINE__);
			}
		} else {
			$msg = Factory::getObject('Dictionary')->getTrans('om.file_already_exists')
				.' '.$folder.$file;
			return BcmsSystem::raiseNotice($msg, BcmsSystem::LOGTYPE_INSERT,
				BcmsSystem::SEVERITY_FAILURE, 'saveUploadedFile()'
				,__FILE__, __LINE__);
		}
		return $file;
	}

	private function checkFilename($file) {
		if(!preg_match('/^[a-zA-Z0-9_-]{4,}\.[a-zA-Z]{1,5}$/',$file)) {
			return BcmsSystem::raiseDictionaryNotice('filename_no_special_chars',
				BcmsSystem::LOGTYPE_CHECK,	BcmsSystem::SEVERITY_ERROR,
				'checkFilename()',	__FILE__,__LINE__);
		}
		return true;
	}

	/**
	 * creates a new image out of the given file and the width and height values and
	 * saves it to the filesystem. The new image will be the same filetype as the
	 * original.
	 *
	 * @author ahe
	 * @date 26.10.2005 21:28:22
	 * @param
	 * @return string the filename of the new file
	 * @access public
	 */
	private function createSmallImage($file,$width,$height, $p_sJPGQuality=80, $p_sNewFilename='') {
		$p_sNewFilename = $this->createSmallImageFilename($file,$p_sNewFilename);
		// save created image to FS
		if($this->resizeImg($file,$width,$height,$p_sNewFilename, $p_sJPGQuality))
			return $p_sNewFilename;
		else
			return false;
	}

	private function createSmallImageFilename($file,$p_sNewFilename=''){
		// generate new filename
		if($p_sNewFilename=='') {
			$p_sNewFilename = '';
			$imgArray = explode('.',$file);
			$postfix_length = mb_strlen($imgArray[count($imgArray)-1]);
			$p_sNewFilename = mb_substr($file,0,-($postfix_length+1));
			$p_sNewFilename .= $this->postfixForSmall.'.'.$imgArray[count($imgArray)-1];
		}
		return $p_sNewFilename;
	}

	private function resizeImg($p_sFilename, $p_iNewWidth, $p_iNewHeight,$p_sNewFilename, $p_sJPGQuality)
	{
		if( !file_exists($p_sFilename) ) die('No such file');

		// TODO the following is hardcode to prevent memory leaks!
		if($p_iNewWidth>800) {
			echo '<br/><strong>ACHTUNG: Bild ist breiter als 800px. Breite wurde auf 800px reduziert!</strong><br/>';
			$p_iNewHeight=$p_iNewHeight*800/$p_iNewWidth;
			$p_iNewWidth=800;
		}

		list($width, $height, $type, $attr) = getimagesize($p_sFilename);
		switch( image_type_to_mime_type($type) ){
			case 'image/png' :
				$src_id = imagecreatefrompng($p_sFilename);
				$image_p = $this->resampleImage($src_id,$p_iNewWidth, $p_iNewHeight, $width, $height);
	            $returnValue = imagepng($image_p,$p_sNewFilename);
				break;
			case 'image/jpeg':
				$src_id = imagecreatefromjpeg($p_sFilename);
				$image_p = $this->resampleImage($src_id,$p_iNewWidth, $p_iNewHeight, $width, $height);
	            $returnValue = imagejpeg($image_p,$p_sNewFilename,$p_sJPGQuality);
				break;
			case 'image/gif' :
				$old_id = imagecreatefromgif($p_sFilename);
				$src_id = imagecreatetruecolor($width,$height);
				imagecopy($src_id,$old_id,0,0,0,0,$width,$height);
				imagedestroy($old_id);
				$image_p = $this->resampleImage($src_id,$p_iNewWidth, $p_iNewHeight, $width, $height);
	            $returnValue = imagegif($image_p,$p_sNewFilename);
    	        break;
        }
		imagedestroy($image_p);
		sleep(1);
		return $returnValue;
	}

	/**
	 * copyresamples given ressource/ image to a truecolorimage of given size
	 *
	 * @param ressource $src_id the original image
	 * @param int $p_iNewWidth of the new image
	 * @param int $p_iNewHeight of the new image
	 * @param int $width of the original image
	 * @param int $height of the original image
	 * @return ressource the newly created image ressource
	 */
	private function resampleImage($src_id,$p_iNewWidth, $p_iNewHeight, $width, $height) {
		// Resample; IMPORTANT: Use truecolor function for resampling!
		$newimage = imagecreatetruecolor($p_iNewWidth, $p_iNewHeight);
		imagecopyresampled($newimage, $src_id, 0, 0, 0, 0,
			$p_iNewWidth, $p_iNewHeight, $width, $height);
		imagedestroy($src_id);
		return $newimage;
	}

	private function getPreviewImageSrcByType($type,$filename) {
		// TODO use external xml here
		if(($type == 'application/msword'
			|| stristr(mb_substr($filename,-4),'.doc')
			|| stristr(mb_substr($filename,-4),'.dot') ) )
		{
			$imgFilename = 'page_white_word.png';

		} elseif(($type == 'application/msexcel'
			|| stristr(mb_substr($filename,-4),'.xls')
			|| stristr(mb_substr($filename,-4),'.xlt') ))
		{
			$imgFilename = 'page_white_excel.png';

		} elseif(($type == 'application/mspowerpoint'
			|| stristr(mb_substr($filename,-4),'.ppt') ))
		{
			$imgFilename = 'page_white_powerpoint.png';

		} elseif(($type == 'application/acrobat'
			|| stristr(mb_substr($filename,-4),'.pdf') ))
		{
			$imgFilename = 'page_white_acrobat.png';

		} elseif((stristr(mb_substr($filename,-4),'.zip')
			|| stristr(mb_substr($filename,-4),'.gz')
			|| stristr(mb_substr($filename,-4),'.tgz') ))
		{
			$imgFilename = 'page_white_compressed.png';

		} elseif((stristr(mb_substr($filename,-4),'.odt')
			|| stristr(mb_substr($filename,-4),'.ods')
			|| stristr(mb_substr($filename,-4),'.txt') ) )
		{
			$imgFilename = 'page_white_text.png';

		} else {
			$imgFilename = 'page_white.png';
		}

		return array(
				'src' => '/gfx/silk/'.$imgFilename,
				'width' => 16,
				'height' => 16,
				'alt' => $filename,
				'style' => 'margin:5px; border:0px; text-decoration:none;'
			);
	}

	/**
	 * Creates an array of the Filenames related to the object_ids in the given
	 * array. The filenames array contains original filename and thumbnail
	 * filename!
	 *
	 * @param int[] $deleteIds id array of HTMLTable::getAffectedIds()
	 * @return String[] filenames
	 * @author ahe
	 * @date 04.10.2006 23:30:50
	 * @package htdocs/plugins/objects
	 */
	private function getFilenamesArrayByIds($deleteIds) {
		$files = array();
		foreach ($deleteIds as $value) {
			$files[] = $this->dalObj->getObjectFilenameById($value);
		}
		foreach ($files as $key => $file) {
			$smallfile = $this->buildThumbnailFilename($file);
			$files[] = $smallfile;
		}
		return $files;
	}

	private function buildThumbnailFilename($file){
		$pieces = explode('.',$file);
		$postfix = $pieces[sizeof($pieces)-1];
		$negaPostfixLen = (mb_strlen($postfix)+1)*(-1);
		return mb_substr($file,0,$negaPostfixLen)
			.$this->postfixForSmall.'.'.$postfix;
	}

	private function createActionMenuAtTop(){
		$insert = $this->gui->createAnchorTag('/'.$this->dalObj->getPluginsCatName()
			.'/insert','Datei hinzufügen'); // TODO Use dictionary!
		$list = $this->gui->createAnchorTag('/'.$this->dalObj->getPluginsCatName()
			.'/','Dateiliste'); // TODO Use dictionary!

		return $this->gui->fillTemplate('div_tpl',array('class="application_menu"'
			,'<ul>' .
			'<li>'.$insert.'</li>'
			.'<li>'.$list.'</li></ul>',null));
	}

/* *** END OF "INTERNAL HELPER METHODS" - SECTION *** */
}
?>