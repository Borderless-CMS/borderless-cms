<?php
class History_DAL extends DataAbstractionLayer {

	public $col = array(

		// unique row ID
		'history_id' => array(
			'type'    => 'integer',
			'require' => true,
			'qf_label' => 'History-ID',
			'qf_type' => 'none'
		),
		'version' => array(
			'type'    => 'float',
			'qf_label' => 'Versionsnummer'
		),
		'content_id' => array(
			'type'    => 'integer',
			'require' => true,
			'qf_label' => 'Content-ID',
			'qf_type' => 'none'
		),
		'fk_cat' => array(
			'type'    => 'integer',
			'require' => true,
			'qf_label' => 'Rubrik in der dieser Artikel erscheinen soll',
			'qf_type' => 'select'
		),
		'fk_editor_id' => array(
			'type'    => 'integer',
			'require' => true,
			'qf_label' => 'Autor'
		),
		'editdate' => array(
			'type'    => 'timestamp',
			'require' => true,
			'qf_label' => 'Erstellungsdatum'
		),
		'language' => array(
			'type'    => 'varchar',
			'size' => 5,
			'require' => true,
			'qf_label' => 'Sprache des Artikels',
			'qf_type'    => 'select'
		),
		'heading' => array(
			'type'    => 'varchar',
			'size'    => 80,
			'require' => true,
			'qf_label' => 'Artikeltitel für Listenansicht',
			'qf_rules' => array(
				'maxlength' => array(
					'Der Inhalt darf maximal 80 Zeichen lang sein!',
					80
				)
			),
			'qf_client' => true
		),
		'techname' => array(
			'type'    => 'varchar',
			'size'    => 80,
			'require' => true
		),
		'description' => array(
			'type'    => 'clob',
			'require' => true,
			'qf_label' => 'Zusammenfassung für Listenansicht',
			'qf_type' => 'textarea',
			'qf_attrs'  => array(
				'rows' => 2,
				'cols' => 30
			 )
		),
		'prev_img_id' => array(
			'type'    => 'integer',
			'qf_label' => 'Bild für Listenansicht'
		),
		'prev_img_float' => array(
			'type'    => 'varchar',
			'size'	=>	10,
			'qf_label' => 'Ausrichtung des Bildes in Listenansicht',
			'qf_type' => 'select',
			'qf_vals' => array(
				'none' => 'Text unter dem Bild',
				'lft' => 'Bild links vom Text',
				'rgt' => 'Bild rechts vom Text'
			)
		),
		'layout_id' => array(
			'type'    => 'integer',
			'require' => true,
			'qf_type'    => 'select',
			'qf_label' => 'Artikelstruktur/ Layout des Artikels'
		),
		'contenttext' => array(
			'type'    => 'clob',
			'require' => true,
			'qf_label' => 'Inhalt',
			'qf_type' => 'hidden'
		),
		'publish_begin' => array(
			'type'    => 'timestamp',
			'require' => true,
			'qf_label' => 'Veröffentlichungsbeginn'
		),
		'publish_end' => array(
			'type'    => 'timestamp',
			'require' => true,
			'qf_label' => 'Veröffentlichungsende'
		),

		'status' => array(
			'type'    => 'integer',
			'require' => true,
			'qf_label' => 'Status'
		),

		'meta_keywords' => array(
			'type'    => 'varchar',
			'size'    => 255,
			'qf_label' => 'Schlüsselworte zu diesem Artikel (für Suchmaschinen)',
			'qf_rules' => array(
				'maxlength' => array(
					'Der Inhalt darf maximal 255 Zeichen lang sein!',
					255
				)
			)
		),
		'redirect_url' => array(
			'type'    => 'varchar',
			'size'    => 255,
			'qf_label' => 'Adresse (URL) für Weiterleitung (falls gewünscht)',
			'qf_rules' => array(
				'maxlength' => array(
					'Der Inhalt darf maximal 255 Zeichen lang sein!',
					255
				)
			)
		)
	);

	public $idx = array(
		'history_id' => array(
			'type' => 'unique',
			'cols' => 'content_id'
		),
		'fk_section' => array(
			'type' => 'unique',
			'cols' => array('content_id','version')
		)
	);

	public $sql = array(
		'list_everything' => array(
			'select' => '*',
			'fetchmode' => DB_FETCHMODE_ASSOC
		)
	);

	public $uneditableElements = array (
		'history_id',
		'content_id',
		'fk_editor_id',
		'editdate',
		'techname');


   /*
	* Declaration of methods
	*/
	public function __construct(){
		// call contructor of father class
		parent::__construct($GLOBALS['db'],BcmsConfig::getInstance()->getTablename('history'));
	}

	public function getForm($p_sFormName, $p_sSubmitButtonName,$p_sSubmitButtonText
		,$columns = null, $array_name = null, $args = array(),
		$clientValidate = null, $formFilters = null) {

		$this->addLanguageList();
		$this->addMenuList();
	    $this->setLayoutList();
	    $this->col['status']['qf_vals'] = BcmsConfig::getInstance()->getTranslatedStatusList();
		$this->col['prev_img_id']['qf_vals'] =
			PluginManager::getPlgInstance('ObjectManager')->getObjectList4Select();
		return parent::getForm($p_sFormName, $p_sSubmitButtonName,$p_sSubmitButtonText
			,$columns, $array_name, $args,$clientValidate, $formFilters);
	}

  /**
   * Adds the values to the selectbox element
   * @param integer $category_id  The id of the category (default is current
   * category)
   */
	public function setLayoutList($category_id=0) {
		$refLayout = Factory::getObject('Layout_DAL');
    	$availLayouts = $refLayout->getAvailableLayouts(intval($category_id));
	    $this->col['layout_id']['qf_vals'] = $availLayouts;
	}

	/**
	 * Setzt die Liste der verfuegbaren Sprachen
	 * TODO use classifications
	 */
	protected function addLanguageList() {
		$this->sql['lang'] = array(
				'select' => 'class.name, dict.'
					.BcmsConfig::getInstance()->langKey.' as lang',
				'from' => BcmsConfig::getInstance()->getTablename('classification').' as class, '.
					BcmsConfig::getInstance()->getTablename('dict').' as dict, '.
					BcmsConfig::getInstance()->getTablename('systemschluessel').' as sk ',
				'where' => ' class.fk_syskey = sk.id_schluessel AND '.
					'class.fk_dict = dict.dict_id AND '.
					'sk.schluesseltyp = \'language\' ',
				'order' => ' lang ASC'
		);
		$lang_arr = $this->select('lang');

		for ($index = 0; $index < count($lang_arr); $index++) {
			$lang[$lang_arr[$index][0]] = $lang_arr[$index][1];
		}
		$this->col['language']['qf_vals'] = $lang;
	}

  protected function addMenuList()
  {
    // get menu tree
    $allMenuesSys = PluginManager::getPlgInstance('CategoryManager')->getLogic()->getMenuTreeList('__system__');
    $allMenuesMain = PluginManager::getPlgInstance('CategoryManager')->getLogic()->getMenuTreeList('__main__');
	$allMenues = array_merge($allMenuesSys,$allMenuesMain);
    for ($i = 0; $i < count($allMenues); $i++) {
      // add indent in front of menu names
      $spaces = '';
      for ($k = 0; $k < ($allMenues[$i]['level']-1)*3; $k++) {
        $spaces .= '&nbsp;';
      }
      $menues[$allMenues[$i]['cat_id']] =
        $spaces.$allMenues[$i]['categoryname'];
    }
    $this->col['fk_cat']['qf_vals'] = $menues;
  }

	/**
	 * handles filtering or values of special fields for insert or update
	 * @author ahe
	 */
	public function checkSpecialFields(&$p_aCols, $func) {
			if($func=='insert') {
				$_SESSION['current_article_data']['historyID'] = $this->nextID();
			}

			$parser = BcmsFactory::getInstanceOf('Parser');
			$p_aCols['techname'] = $parser->filterTechName($p_aCols['heading']);
			$p_aCols['techname'] = mb_substr($p_aCols['techname'],0,
				$this->col['techname']['size']);
			$p_aCols['history_id'] = $_SESSION['current_article_data']['historyID'];
			$p_aCols['fk_editor_id'] = PluginManager::getPlgInstance('UserManager')->getLogic()->getUserID();
			$p_aCols['editdate'] = date('YmdHis');
			if(is_numeric($_SESSION['current_article_data']['contentID']))
				$contId = $_SESSION['current_article_data']['contentID'];
			else
				$contId = 0;
			$p_aCols['content_id'] = $contId;
	}

	/**
	 * syncronises a content record with the history record given by the history_id param
	 *
	 * @author ahe
	 * @date 21.10.2005 22:22:20
	 * @param int history_id the identifier of the history_table record to be transferred to the content table
	 * @param int p_iContentID optional the identifier of the content to be updated with the history values
	 * @return
	 * @access
	 */
	public function syncContent($history_id, $p_iContentID=0)
	{
		// get the history record of the article to be synced
		$history_record =
			$this->select('list_everything', 'history_id = '.$history_id);
		$values = array(
			'fk_cat' => $history_record[0]['fk_cat'],
			'heading' => $history_record[0]['heading'],
			'contenttext' => $history_record[0]['contenttext'],
			'fk_creator' => $history_record[0]['fk_editor_id'],
			'created' => $history_record[0]['editdate'],
			'description' => $history_record[0]['description'],
			'publish_begin' => $history_record[0]['publish_begin'],
			'publish_end' => $history_record[0]['publish_end'],
			'version' => $history_record[0]['version'],
			'language' => $history_record[0]['language'],
			'layout_id' => $history_record[0]['layout_id'],
			'status' => $history_record[0]['status'],
			'prev_img_id' => $history_record[0]['prev_img_id'],
			'prev_img_float' => $history_record[0]['prev_img_float'],
			'redirect_url' => $history_record[0]['redirect_url'],
			'meta_keywords' => $history_record[0]['meta_keywords'],
			'techname' => $history_record[0]['techname']
			// TODO DYNAMIC PROBLEM: make this dynamic! NO MATTER WHAT!!!
		);
		$content=new Article_DAL();
		// check whether content_id is set
		if($p_iContentID==0) {

			$values['hits'] = 0;
			$error=$content->insert($values,'content_id');
			if($error instanceof PEAR_ERROR){
				$msg = 'Einfügen des Beitrags war nicht erfolgreich!'; // TODO Use dictionary here!
				return BcmsSystem::raiseError($error, BcmsSystem::LOGTYPE_INSERT,
				BcmsSystem::SEVERITY_ERROR, 'syncContent()'
					,__FILE__, __LINE__,$msg);
			} else
				$p_iContentID = $error;

			$error=$this->update(array('content_id' => $p_iContentID)
				, 'history_id = '.$history_id);

			if($error instanceof PEAR_ERROR) {
				// if history-table update fails, exit hard
				$msg = 'FEHLER: Aktualisierung der Historien-Tabelle war nicht erfolgreich!'; // TODO Use dictionary here!
				return BcmsSystem::raiseError($error, BcmsSystem::LOGTYPE_UPDATE,
				BcmsSystem::SEVERITY_ERROR, 'syncContent()'
					,__FILE__, __LINE__,$msg);
			}
		} else {
			$error=$content->update($values,'content_id='.$p_iContentID);
			if($error instanceof PEAR_ERROR) {
				// if history-table update fails, exit hard
				$msg = 'FEHLER: Aktualisierung des Artikels war nicht erfolgreich!'; // TODO Use dictionary here!
				return BcmsSystem::raiseError($error, BcmsSystem::LOGTYPE_UPDATE,
				BcmsSystem::SEVERITY_ERROR, 'syncContent()'
					,__FILE__, __LINE__,$msg);
			}
		}
		$msg = 'Artikel "'.$history_record[0]['heading']
			.'" in Version '.$history_record[0]['version']
			.' erfolgreich übernommen!';// TODO Use dictionary here!
		BcmsSystem::raiseNotice($msg, BcmsSystem::LOGTYPE_UPDATE,
				BcmsSystem::SEVERITY_INFO, 'syncContent()',__FILE__, __LINE__);
		return true;
 	}

	public function getObject($id,$sendArray=false) {
		$this->sql['list_everything']['fetchmode'] = DB_FETCHMODE_ASSOC;
		$this->sql['list_everything']['order'] = 'history_id DESC';
// TODO Inkonsistentes Verhalten! Entweder geben alle ein Objekt zur�ck oder gar keiner!
		$dataArray = $this->select('list_everything',' history_id = '.$id);
		if($sendArray) return $dataArray[0];
		$refArticle = new BcmsArticle();
		$refArticle->setObjectDataWithArray($dataArray[0]);
		return $refArticle;
	}

	public function getObjectBySectionId($id) {
		$this->sql['list_everything']['fetchmode'] = DB_FETCHMODE_ASSOC;
		$this->sql['list_everything']['order'] = ' history_id DESC';
		$this->sql['list_everything']['where'] = ' (status >= '.$GLOBALS['ARTICLE_STATUS']['published'].') '; // TODO use classifications for status!
		$refArticle = new BcmsArticle();
		$dataArray = $this->select('list_everything', 'content_id = '.$id);
		$refArticle->setObjectDataWithArray($dataArray[0]);
		return $refArticle;
	}

	public function getArticleHistory($articleId, $isLoggedIn, $offset=null,$limit=null) {
	    $this->sql['articleHistory'] = array(
	    	'select' => 'h.history_id, h.version as \'-sr.Version\', h.heading, h.editdate as created, ' .
	    			'user.username, class.name as status ',
			'from' => $this->table.' as h, '.
					BcmsConfig::getInstance()->getTablename('user').' as user, ' .
					BcmsConfig::getInstance()->getTablename('classification').' as class, ' .
					BcmsConfig::getInstance()->getTablename('systemschluessel').' as syskey ',
			'where' => ' h.fk_editor_id = user.user_id AND' .
					' class.number = h.status AND' .
					' class.fk_syskey = syskey.id_schluessel AND' .
					' syskey.schluesseltyp = \'status\'',
			'order' => 'version DESC',
			'fetchmode' => DB_FETCHMODE_ASSOC
	    );
	    if(!$isLoggedIn) $where = ' AND (h.status>='.$GLOBALS['ARTICLE_STATUS']['published'].')';// TODO use classifications for status!
		return $this->select('articleHistory',' h.content_id ='.$articleId.$where,
			null,$offset, $limit);
	}
}
?>