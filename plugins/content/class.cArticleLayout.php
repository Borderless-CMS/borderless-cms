<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * Handles and creates article write and edit form
 *
 * @since 0.9
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @class cArticleLayout
 * @ingroup content
 * @package content
 */
 class cArticleLayout extends Layout_DAL {

  public $uneditableElements = array ();
  private $contentObj = null;


/* METHOD DEFINITION */

  public function __construct() {
	parent::__construct($GLOBALS['db'],	BcmsConfig::getInstance()->getTablename('layoutpresets'));
  }

   protected function parseFieldsIntoLayoutForm($p_sLayout,$p_aFields, $submit_button_name)
   {
	 include_once 'HTML/QuickForm.php';
	 $form = new HTML_QuickForm('article_content_form');
	$formArray = $form->toArray();

	$p_sLayout = '<form  id="form_contenttext" name="form_contenttext" '
	  .'action="'.BcmsSystem::getParser()->getServerParameter('SCRIPT_URI').'" method="post" enctype="'
	  .BcmsConfig::getInstance()->default_form_enctype
	  ."\">\n".$p_sLayout;
	for ($index = 0; $index < count($p_aFields); $index++) {
	  if($p_aFields[$index]['form_tag'] != 'img')
	  {
		$element = $this->createFormElement($form, $p_aFields[$index]);
		$formElement = $element->toHtml();
	  } else {
	  	$tech_title = $p_aFields[$index]['tech_title'];
		// the image shall be displayed...
		$imageID =
		  is_numeric($_SESSION['current_article_data']['content'][$tech_title]) ? $_SESSION['current_article_data']['content'][$tech_title] : $p_aFields[$index]['preset_value'];
		$bcmsObject = PluginManager::getPlgInstance('FileManager');
		$imgTag = $bcmsObject->getObjectsSmallImage($imageID);

		// ... and a hidden form field with the index number
		$p_aFields[$index]['form_tag'] = 'hidden';
		$p_aFields[$index]['preset_value'] = $imageID; // save imageID
		$element = $this->createFormElement($form, $p_aFields[$index]);

		$ImgIndexElement =
		  $form->addElement('hidden', 'image_index'
		  , $index, null);
		// ...and an submit button to chose another image
		$chooseImgElement =
		  $form->addElement('submit', 'article_choose_image'
		  , 'Bild waehlen'
		  , 'title="Ein anderes Bild aus der Liste waehlen"');

		$imageElement = $imgTag."\n".$element->toHtml()
		  .$ImgIndexElement->toHtml().$chooseImgElement->toHtml();
		$formElement = $imageElement;
	  }
	  // replace the placeholder with the elements HTML
	  $p_sLayout = str_replace('%%'.($index+1).'%%',
		$formElement,$p_sLayout);
	}
	// ...and at last the submit button
	$element = $form->addElement('submit','article_update_preview', BcmsSystem::getDictionaryManager()->getTrans('Preview'));
	$p_sLayout .= $element->toHtml();

	$form->addElement('submit', 'abort_action', BcmsSystem::getDictionaryManager()->getTrans('cancel'));
	$element = $form->getElement('abort_action');
	$element->setLabel('&nbsp;');
	$p_sLayout .= $element->toHtml();
	
	$element = $form->addElement('submit',$submit_button_name, BcmsSystem::getDictionaryManager()->getTrans('continue'));
	$p_sLayout .= $element->toHtml()."\n".'</form>';
	
	return $p_sLayout;
   }

	private function createFormElement(&$form, $p_aFields) {
	  	$tech_title = $p_aFields['tech_title'];
		$element = $form->addElement($p_aFields['form_tag'],$tech_title,
		$tech_title, 'title='.$tech_title);

		// check whether the form field is given in the article_data
		if(!empty($_SESSION['current_article_data']['content'][$tech_title]) ) 
		{
			// ... if so set this to the current formfields value
			$element->setValue(stripslashes(
				$_SESSION['current_article_data']['content'][$tech_title]));
		} else {
		  // ... else take the default value
		  $element->setValue($p_aFields['preset_value']);
		}
		if($p_aFields['readonly']==1) $element->freeze();
		return $element;
	}

   protected function filterArticleDataFromPost($p_aFields2Filter)
   {
	 foreach ($_POST as $key => $value) {
	  if(!in_array($key,$p_aFields2Filter)) $new_array[$key] = $value;
	}
	if(isset($new_array))
		 return array('contenttext' => serialize($new_array));
	else
		return array('contenttext' => null);
   }

	private function phase2(&$p_refArticle, $submit_button_name) {
		if(!array_key_exists('current_article_data',$_SESSION)) {
			return BcmsSystem::raiseError('Formulardaten von Phase1 verloren! Bitte vermeiden '.
				' Sie während der Erstellung von Beiträgen einen Wechsel zu anderen '. // @todo use dictionary!
				' Seiten dieses Angebots.','phase2()',__FILE__, __LINE__);
		}
		// add the form elements for the current layout
		$layoutData =
			$this->getLayoutDataFromFS($_SESSION['current_article_data']['layout_id']);
		$layout = $layoutData[0]; // Achtung: Das CSS wird hier noch nicht geholt!
		$lfields = $this->getLayoutFields($_SESSION['current_article_data']['layout_id']);
			
		// \bug URGENT check where the following comes from
		if(empty($_SESSION['current_article_data']['layout_id']))
			throw new Exception('$_SESSION does not contain layout_id!!!');

		$lfields = $this->setChosenImageToArray($lfields);
		$lfields = $this->presetValuesFromFirstPage($lfields);
		echo BcmsFactory::getInstanceOf('GuiUtility')->createHeading(3,'Phase2: Artikel schreiben'); // @todo use dictionary

		$guide_text = BcmsSystem::getDictionaryManager()->getTrans('write_article_info2');
		echo '<div id="guide_text">'.stripcslashes($guide_text).'</div>';
		
		$editor_hints = BcmsSystem::getDictionaryManager()->getTrans('editor_syntax_hints');
		echo '<div id="formatting_info">'.stripcslashes($editor_hints).'</div>';

		// print out the article preview
		echo '<div id="article_preview_surrounder" class="floatclear">',"\n";
		echo $this->parseFieldsIntoLayoutForm($layout, $lfields, $submit_button_name);
		echo "\n",'</div>',"\n";
	}

	private function setChosenImageToArray($lfields){
		if(isset($_POST['article_update_image'])) {
		  $index = intval($_POST['article_update_image']);
		  $imageId = 0;
		  // split array keys of POST array by seperator '___'
		  foreach(array_keys($_POST) as $key => $value) {
			if(strpos($value,'___')) $possible_image_id = explode('___',$value);
			if(isset($possible_image_id[1]) && is_numeric($possible_image_id[1])) {
				$imageId = $possible_image_id[1];
			}
		  }
		  if(is_array($_SESSION['current_article_data']['content'])) {
			  $_SESSION['current_article_data']['content'][$lfields[$index]['tech_title']] = $imageId;
		  }
		  $lfields[$index]['preset_value'] = $imageId;
		}
		return $lfields;
	}

	private function presetValuesFromFirstPage($lfields){
		if($lfields[0]['tech_title']=='heading'
			&& !empty($_POST['heading']))
		{
			$lfields[0]['preset_value'] = stripslashes(
				BcmsSystem::getParser()->getPostParameter('heading'));
		}
		$notFound = true;
		$i=0;
		while($notFound && $i<count($lfields)){
			if($lfields[$i]['tech_title']=='fliesstext'
				&& !empty($_POST['description']))
			{
				$lfields[$i]['preset_value'] =
					BcmsSystem::getParser()->getPostParameter('description');
				$notFound=false;
			}
			$i++;
		}
		return $lfields;
	}

	private function phase1(&$p_refArticle, $submit_button_name) {
		echo BcmsSystem::getCategoryManager()->getLogic()->createHeading(3,'Phase1: Artikel-Metainformationen pflegen');
		$guide_text = BcmsSystem::getDictionaryManager()
			->getTrans('write_article_info1');
		echo '<div id="guide_text">'.stripcslashes($guide_text).'</div>';
		$form =& $p_refArticle->getForm('article_form',$submit_button_name
		  ,BcmsSystem::getDictionaryManager()->getTrans('continue'));
		$form->validate(); // validate entries
		$form->display();
	}

  /**
   * Sets preset Values to Formfields/ -cols if a recordset is given
   * 
   * @todo Methode analysieren
   * @param History_DAL $p_refArticle reference to the HistoryData-Object
   * @param History $p_refHistory reference to a BcmsArticle Object holding
   * History values (optional)
   * @return int content_id
   * @author ahe
   * @date 15.11.2005 22:48:17
   */
  private function setArticlePresetValues(&$p_refArticle, $p_refHistory=null) {
	// check whether
	if($p_refHistory == null) {
	  $p_refArticle->col['version']['qf_type'] = 'hidden';
	  $p_refArticle->col['version']['qf_setvalue'] = '1.0';
	  $p_refArticle->col['publish_begin']['qf_setvalue'] = date('Y-m-d H:i:s');
	  $p_refArticle->col['publish_end']['qf_setvalue'] =
			BcmsConfig::getInstance()->PublishEndDate;
	  $p_refArticle->col['fk_cat']['qf_setvalue'] = $_SESSION['m_id'];
	  return 0;
	} else {
		$p_refArticle->col['version']['qf_setvalue'] = (floatval($p_refHistory['version'])+1).'.0'; // \bug URGENT die evtl. vorhandenen Versionen in der History werden hier nicht beruecksichtigt!
		$p_refArticle->col['publish_begin']['qf_setvalue'] = $p_refHistory['publish_begin'];
	  $p_refArticle->col['publish_end']['qf_setvalue'] = $p_refHistory['publish_end'];
	  $p_refArticle->col['fk_cat']['qf_setvalue'] = $p_refHistory['fk_cat'];
	  $p_refArticle->col['lang']['qf_setvalue'] = $p_refHistory['lang'];
	  $p_refArticle->col['heading']['qf_setvalue'] = stripslashes($p_refHistory['heading']);
	  $p_refArticle->col['description']['qf_setvalue'] = stripslashes($p_refHistory['description']);
	  $p_refArticle->col['layout_id']['qf_setvalue'] = $p_refHistory['layout_id'];
	  $p_refArticle->col['status_id']['qf_setvalue'] = $p_refHistory['status_id'];
	  $p_refArticle->col['prev_img_id']['qf_setvalue'] = $p_refHistory['prev_img_id'];
	  $p_refArticle->col['prev_img_float']['qf_setvalue'] = $p_refHistory['prev_img_float'];
	  $p_refArticle->col['redirect_url']['qf_setvalue'] = $p_refHistory['redirect_url'];
	  $p_refArticle->col['meta_keywords']['qf_setvalue'] = $p_refHistory['meta_keywords'];
	  $p_refArticle->col['techname']['qf_setvalue'] = $p_refHistory['techname'];
	  return $p_refHistory['content_id']; // content_id
	}
  }

   public function createForm($contentObj,$articleId=null) {

		$this->contentObj = $contentObj;
		$histDalObj = PluginManager::getPlgInstance('ContentManager')->getModel();
		$this->updateDatabase($histDalObj);

		if(array_key_exists('current_article_data',$_SESSION)
			&& ($objectId=$_SESSION['current_article_data']['historyID'])>0)
		{
			$refHistoryObj = $histDalObj->getObject($objectId,true);
		} elseif(!empty($articleId)) {
			$artDalObj =PluginManager::getPlgInstance('ContentManager')->getArticleDalObj();
			$refHistoryObj = $artDalObj->getObject($articleId);
		} else {
			$refHistoryObj = null;
		}

		// set preset values to form fields
		$this->setArticlePresetValues($histDalObj, $refHistoryObj);
		$contentId = ($articleId!=null && $articleId!='') ? intval($articleId) : 0;
		// if there is an existing history object, refresh the layoutlist according to the given menu
		if(!empty($refHistoryObj)) $histDalObj->setLayoutList($refHistoryObj['fk_cat'],$this);

		$content = (empty($refHistoryObj)) ? $refHistoryObj : unserialize($refHistoryObj['contenttext']);
		if(!isset($_SESSION['current_article_data']))
		{
		  $_SESSION['current_article_data'] = array('historyID' => 0
			,'contentID' => $contentId
			, 'content' => $content
			);
		}
		if($refHistoryObj!=null && $content!=null){
			$_SESSION['current_article_data']['content'] = $content;
		}
		
		$_SESSION['current_article_data']['contentID'] = $contentId;
		if(isset($_POST['layout_id'])) {
			$curr_LayoutID = intval($_POST['layout_id']);
			$_SESSION['current_article_data']['layout_id'] = $curr_LayoutID;
		} else {
			$curr_LayoutID = ($refHistoryObj!=null) ? $refHistoryObj['layout_id'] : 0;
		}

		// FINISH THE ARTICLE
		// this is done before output because content is then displayed up2date
		if(isset($_POST['update_article'])) {
			// sync to content table
			if($contId=$histDalObj->syncContent(
				$_SESSION['current_article_data']['historyID']
				,$_SESSION['current_article_data']['contentID']))
			{
				// unregister used session var
				session_unregister('current_article_data');
			}
		}

		// ----------- print out forms ----------------
		if( isset($_POST['insert_article'])
			|| isset($_POST['article_update_preview'])
			|| isset($_POST['article_update_image']) )
		{
			// create form
			$this->phase2($histDalObj, 'update_article');

			// show preview
			if(isset($_POST['article_update_preview'])){
				echo BcmsFactory::getInstanceOf('GuiUtility')->createHeading(
					3,'Artikelvorschau',0,'previewtext_heading'); // @todo use dictionary!
				$histId = $_SESSION['current_article_data']['historyID'];
				$this->contentObj->init('history',$histId);
				$this->contentObj->showArticle($histId,true);
			}
		} elseif(isset($_POST['update_article'])) {
			// @todo this is yet the end of the content generation process...
			echo 'Artikel wird erstellt...';
		} elseif(isset($_POST['article_choose_image'])) {
			$objectmanager = PluginManager::getPlgInstance('FileManager');
			echo $objectmanager->getObjectList(true);
		} else {
			$this->phase1($histDalObj, 'insert_article');
		}
	}

	protected function updateDatabase($histDalObj) {
		if(empty($_SESSION['current_article_data'])) return false;
		if($_SESSION['current_article_data']['historyID']>0) {
			// Wenn history_id bereits vorhanden, dann fuehre UPDATE durch! ...
			$dismissField = 'insert_article';
			$dataArray = $_POST;
			if(isset($_POST['article_choose_image'])) {
				$dismissField = 'article_choose_image';
				$dataArray = $this->filterArticleDataFromPost(array(
					$dismissField, 'image_index','article_update_preview'));
			}
			if(isset($_POST['update_article'])) {
				$dismissField = 'update_article';
				$dataArray = $this->filterArticleDataFromPost(array(
					$dismissField, 'image_index','article_update_preview'));
			}
			if(isset($_POST['article_update_preview'])) {
				$dismissField = 'article_update_preview';
				$dataArray = $this->filterArticleDataFromPost(array(
					$dismissField, 'image_index','update_article'));
			}
			$error=$histDalObj->checkForAction($dismissField,$dataArray,'update'
				,'history_id = '.$_SESSION['current_article_data']['historyID']);
			if($error instanceof PEAR_ERROR) {
				return BcmsSystem::raiseError($error,'createForm()'
					,__FILE__, __LINE__);
			}
			return true;
		} else {
			// ...ansonsten erstelle neuen history-Eintrag
			return $histDalObj->checkForAction('insert_article',$_POST);
		}
	}

}
?>