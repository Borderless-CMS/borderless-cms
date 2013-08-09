<?php /*
<!--esi file="../../header.txt"-->
+----------------------------------------------------------------------------+
| B O R D E R L E S S   C M S                                                |
+----------------------------------------------------------------------------+
| (c) Copyright 2004-2005                                                    |
|      by goldstift (mail@goldstift.de) - www.goldstift.de                   |
+----------------------------------------------------------------------------+
// BORDERLESS: prevents execution of php scripts separetly from the main file
<!--/esi file-->
*/
if(!defined('BORDERLESS')) exit;

class cContent
{
    // primitive vars
    protected $dbContentTable;
    protected $dbCommentTable;
    protected $contentPerPage;
    private $currentArticle = null;
    protected $listOffset;
    public $currentId = 0; // URGENT change this to private ASAP!!!
    protected $float_translation = array( // TODO get this from classification!
        'lft' => 'left',
        'rgt'=>'right',
        'none'=>'none');

    // objects
    protected $dbObj;
    protected $dateObject;
    protected $dictObj;
    protected $manager;
    protected $parser;
    protected $userManager;

/*** METHODS ***/

    function __construct($manager)
    {
        $config = BcmsConfig::getInstance();
        $this->dbContentTable = $config->getTablename('articles');
        $this->dbCommentTable = $config->getTablename('comments');
        $this->contentPerPage = $config->content_per_page;
        $this->dateObject = new cDate();
        $this->manager = $manager;
        $this->dalObj = $this->manager->getArticleDalObj();
        $this->dbObj = $GLOBALS['db'];
        $this->dictObj = PluginManager::getPlgInstance('Dictionary');
        $this->parser = BcmsFactory::getInstanceOf('Parser');
        $this->userManager = PluginManager::getPlgInstance('UserManager');
    }

/* +++ BEGINN  C O N T E N T V E R W A L T U N G +++ */

    function deleteContent($contentID, $temp=0)
    {
        /* ACHTUNG:
        * Hier muss noch geprueft werden, ob das aktuelle Menue fuer jeden
        *  schreibbar ist.
        * Ansonsten muss die Methode checkMenuRight aufgerufen werden.
        */
        $plgCatConfig = $this->manager->getPlgCatConfig();
        if (!$this->userManager->hasRight($plgCatConfig['del_right'])
        || $temp)
        {
            return BcmsSystem::raiseNoAccessRightNotice(
                'deleteContent()',__FILE__, __LINE__);
        }

        $sql='DELETE FROM '.$this->dbContentTable.' WHERE (content_id = '.$contentID.')';
        // TODO ausserdem noch history eintraege loeschen
        return $this->dbObj->query($sql);
    }

    public function getArticleActions(){
        return array(
            0 => array('status', $this->dictObj->getTrans('changeStatus'), 'ChangeStatus'),
//			1 => array('delete', $this->dictObj->getTrans('delete'), 'Delete'),
            1 => array('edit', $this->dictObj->getTrans('edit'), 'Edit')
        );
    }

    public function getHistoryActions(){
        return array(
            0 => array('sync', $this->dictObj->getTrans('setVersionActive'), 'Sync'),
            1 => array('delete', $this->dictObj->getTrans('delete'), 'Delete')
        );
    }

/* +++ ENDE  C O N T E N T V E R W A L T U N G +++ */


/* +++ BEGINN  C O M M E N T V E R W A L T U N G +++ */
    /**
     schreibt ein Kommentar in die CommentTabelle
    */
    function addComment($heading,$comment,$contID,$commStatus,$author)
    {
        if (!$this->userManager->hasRight('COMMENT_WRITE'))
            return BcmsSystem::raiseNoAccessRightNotice(
                'addComment()',__FILE__, __LINE__);

        if(!isset($author)) $author = '---';
        $heading = $this->parser->prepDbStrng($heading);
        $comment = $this->parser->prepDbStrng($comment);
        $author = $this->parser->prepDbStrng($author);
        $remoteAddr = $this->parser->getServerParameter('REMOTE_ADDR');
        $remoteAddr = $this->parser->prepDbStrng($remoteAddr);

        $sql = 'INSERT INTO '.$this->dbCommentTable
            .' (fk_content, fk_author, heading,`contenttext`, created, status, '
            .'author, ip_address) '
            .'VALUES '
            .'('.$contID.', '.$this->userManager->getLogic()->getUserID().', '
            .$heading.','.$comment.', NOW(), '.$commStatus.', '.$author.', '.
            $remoteAddr.')';
        $result = $this->dbObj->query($sql);

        if($result instanceof PEAR_ERROR) {
            $msg = $this->dictObj->getTrans('comm.insert_failed');
            return BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_INSERT,
                BcmsSystem::SEVERITY_ERROR, 'addComment()',
                __FILE__,__LINE__,$msg);
        } else {

            /* fetch succes message from db */
            $msg = $this->dictObj->getTrans('comm.thx4Comment')
                .' <a href="show/'.$contID.'">'
                .$this->dictObj->getTrans('backToArticle').'</a>';
             BcmsSystem::raiseNotice($msg, BcmsSystem::LOGTYPE_INSERT,
                BcmsSystem::SEVERITY_INFO,'addComment()'
                   ,__FILE__, __LINE__);
        }
    }


    function deleteComment($commentID)
    {
        if (!$this->userManager->hasRight('COMMENT_DELETE'))
            return BcmsSystem::raiseNoAccessRightNotice(
                'deleteComment()',__FILE__, __LINE__);

        $sql='DELETE FROM '.$this->dbCommentTable.' WHERE (comment_id = '.$commentID.')';
        return $this->dbObj->query($sql);
    }
/* +++ ENDE  C O M M E N T V E R W A L T U N G +++ */



/* +++ HILFSFUNKTIONEN FUER CONTENT- UND COMMENTAUSGABE +++ */

    /* Gibt die Anzahl der Hits zum entsprechenden Artikel
    */
    function getContentHits($contID)
    {
    $sql = 'SELECT hits FROM '.$this->dbContentTable
         .' WHERE (content_id = '.$contID.')';
    $result = $this->dbObj->query($sql);
    $erg = $result->fetchRow();
    $result->free();
    return ($erg[0]);
    }

    /* Erhoeht die Anzahl der Hits des entsprechenden Artikels um 1
    */
    function updateContentHits($contID)
    {
        $hits = $this->getContentHits($contID);
        // Falls es nocht nicht ausgefuehrt wurde, werden die Hits erhoeht
        $sql = 'UPDATE '.$this->dbContentTable.' SET hits = '.($hits+1)
            .' WHERE (content_id = '.$contID.')';
        // Wenn TransaktionsSQL vorhanden, fuehre Update nicht durch!
        if(BcmsSystem::checkTransaction($sql)) return false;

        $result = $this->dbObj->query($sql);
        if($result instanceof PEAR_ERROR) {
            return BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_UPDATE,
                BcmsSystem::SEVERITY_ERROR, 'updateContentHits()',
                __FILE__,__LINE__);
        }

        /* Bei erfolgreichem Update wird zusaetzlich ein Datensatz im TransactionTable
        angelegt, damit Mehrfachausfuehrungen innerhalb kurzer Zeit (default
        120 Sek. -> BcmsSystem::TRANSACTION_LIFETIME) nicht moeglich sind.
        So soll gewaehrleistet werden, dass die "Hits" einigermassen
        realistisch sind und nicht bei jedem Klick erhoeht werden.
        */
        $sql4NextCheck = 'UPDATE '.$this->dbContentTable.' SET hits = '.($hits+2)
            .' WHERE (content_id = '.$contID.')';
        BcmsSystem::addTransaction($sql4NextCheck);
        return $result;
    }

    /**
    *
    *
    * @param $content_id
    * @access public
    * @return
    * @author goldstift
    */
    function getNoOfComments($content_id,$comment_id=0)
    {
        $query  = 'SELECT comment_id FROM '.$this->dbCommentTable.'
            WHERE (fk_content='.$content_id.') and (status='
            .$GLOBALS['ARTICLE_STATUS']['published'].')';// TODO use classifications for status!
        if($comment_id>0) $query .= ' and (fk_comment = '.$comment_id.')';
        $result=$this->dbObj->query($query);
        $numRows =  $result->numRows();
        $result->free();
        return $numRows;
    }

/* ENDE DER "DATENBESCHAFFUNGS"-FUNKTIONEN */

/* BEGINN DER AUSGABE_FUNKTIONEN */

    private function createContentChangePageToolbar()
    {
        // add "turn the page"-navigation
        return Factory::getObject('GuiUtility')->getPageControlField(
            'page',
            $this->contentPerPage,
            sizeof($this->getContentList(false))
        );
    }

    /**
     *  Gibt das Kommentar-Formular aus
     */
    private function createCommentForm($contID=null,$indent = 14){
    // if user has no right to write a comment he does not see the form
        if(!$this->userManager->hasRight('COMMENT_WRITE')) return null;


        $allCommentsSQL  = 'SELECT comm.comment_id, comm.heading
             FROM '.$this->dbCommentTable.' as comm
             WHERE (fk_content='.$contID.')
                 AND (comm.status='.$GLOBALS['ARTICLE_STATUS']['published'] // TODO use classifications for status!
                 .') ORDER BY comm.created ASC';
        $result = $this->dbObj->query($allCommentsSQL);
         $numrows = $result->numRows();
         $row = array();
        if (!($result instanceof DB_ERROR) && $numrows>0) {
            for ($i = 0; $i < $numrows; $i++) {
                $row[] = $result->fetchRow(DB_FETCHMODE_ASSOC,$i);
            }
            $result->free();
        }

        for ($i = 0; $i < sizeof($row); $i++) {
            $commentToArray[$row[$i]['comment_id']] = $row[$i]['heading'];
        }

        $commentForm = new cForm();
        $commentForm->addHeader('commForm',
            $this->parser->getServerParameter('REQUEST_URI'),$indent);
          $retStr = '<div id="commentForm">'."\n"
            .'<fieldset class="commentform"><legend>'
            .$this->dictObj->getTrans('h.CommentForm')
            .'</legend>'."\n";

        // Namensfeld nur anzeigen, wenn Benutzer nicht angemeldet ist
        if(!$this->userManager->getLogic()->isLoggedIn()) {
            $commentForm->addElement('text', 'comm_author'
            , '', $indent+2, 'Name:');
        }
        $commentForm->addElement('text', 'comm_heading'
        , '', $indent+2, '&Uuml;berschrift:');
// TODO Use HTML_Quickform so that CommentOnComment can be realized
//        $commentForm->addElement('select', 'comm_on_comm'
//        , $commentToArray, $indent+2, 'Kommentar zu:');
        $commentForm->addElement('textarea', 'comm_text'
        , '', $indent+2, 'Inhalt:');
        $commentForm->addElement('submit', 'comm_action'
            , $this->dictObj->getTrans('submit')
            , $indent+2);
        $commentForm->addBottom($indent);
        $retStr .= $commentForm->printForm();
        $retStr .= '</fieldset></div>  <!-- /commentForm -->'."\n";
        return $retStr;
    }

/* +++ ENDE DER HILFSFUNKTIONEN FUER CONTENT- UND COMMENTAUSGABE +++ */

    /**
     * calls the class cArticleForm and creates a dialog with this form
     *
     * @param $p_iArticleId holds the id of the article to be edited
     * @return mixed
     */
    public function editArticle($p_iArticleId)
    {
        $plgCatConfig = $this->manager->getPlgCatConfig();
        // determine editor_id of current article
        $editor = 0;
        if($this->currentArticle!=null) {
            if(isset($this->currentArticle['fk_creator'])){
                $editor = $this->currentArticle['fk_creator'];
            } else {
                $editor = $this->currentArticle['fk_editor_id'];
            }
        }
        // check access
        if ( !$this->userManager->hasRight($plgCatConfig['edit_right'])
            && !$this->userManager->hasRight($plgCatConfig['add_right'])
            && !( $this->userManager->hasRight($plgCatConfig['edit_own_right'])
                   && $editor==$this->userManager->getLogic()->getUserID())
        ) {
            // if access denied, send error message
            return BcmsSystem::raiseNoAccessRightNotice(
                'editArticle()',__FILE__, __LINE__);
        }
        unset($plgCatConfig);

        return Factory::getObject('cArticleLayout')->createForm($this,$p_iArticleId); // TODO change when ArticleLayout-Plugin is build
    }

    public function getContentList($withLimitAndOffset=true) {
        $plgCatConfig = $this->manager->getPlgCatConfig();
        $sort_direction = $plgCatConfig['sort_direction'];
        $content_order_by = $plgCatConfig['content_order_by'];
        if($withLimitAndOffset) {
            return $this->dalObj->getArticleListByCategory(
                $_SESSION['m_id'], $GLOBALS['ARTICLE_STATUS']['published'] // TODO use classifications for status!
                , true
                , $content_order_by
                , $sort_direction
                , $this->contentPerPage
                , $this->listOffset
            );
        } else {
            return $this->dalObj->getArticleListByCategory(
                $_SESSION['m_id'], $GLOBALS['ARTICLE_STATUS']['published'] // TODO use classifications for status!
                , true
                , $content_order_by
                , $sort_direction
            );
        }
    }

/* +++ CONTENT- und COMMENT-AUSGABE +++ */

    /**
    * Gibt die Liste der zum aktuellen Menue gehoerenden Beitraege aus.
    * Die Anzahl der Beitraege in dieser Liste wird begrenz durch $content_per_page.
    *
    * @author goldstift
    */
    public function getCss($menuId=0) {
        $layoutId = 0;

        if(isset($_POST['layout_id'])) {
            // will be used whilst article generation
            $layoutId = intval($_POST['layout_id']);
        } elseif(isset($_SESSION['current_article_data'])
            && is_numeric($_SESSION['current_article_data']['layout_id']))
        {
            // will be used whilst article generation
            $layoutId = $_SESSION['current_article_data']['layout_id'];
        } elseif($this->currentId>0) {
            $layoutId = $this->currentArticle['layout_id'];
        }

        if($layoutId>0) { // if layout_id is set...
            $refLayout = Factory::getObject('Layout_DAL');// TODO change when ArticleLayout-Plugin is build
            $articleLayoutData = $refLayout->getLayoutDataFromFS($layoutId);
            // ... and print it
            return $articleLayoutData[1];
        } else {
            return '';
        }
    }


    /**
     * initializes class
     *
     * @param string $modus maybe 'single', 'list', 'show', 'edit', 'del'
     */
    public function init($modus, $id=null) {

        $id = ($id!=null && $id!='' && is_numeric($id)) ? $id : $_SESSION['mod']['oid'];
        switch ($modus) {
            case 'single':
                $row  = $this->getContentList();
                if(array_key_exists(0,$row))
                    $this->currentId = intval($row[0]['content_id']);
                else
                    $this->currentId = 0;
                break;
            case 'edit_article':
            case 'show':
            case 'history':
            default:
                $this->currentId = $id;
                break;
            case 'list':
                $this->currentId = 0;
                break;
        }
        if($this->currentId>0 && $this->currentId != null && $modus!='history') {
            $this->currentArticle = $this->dalObj->getObject($this->currentId);
        } elseif($modus=='history') {
            $this->currentArticle = $this->manager->getModel()->getObject($this->currentId,true);
        } else {
            $this->currentArticle = null;
        }
        $_SESSION['mod']['oid'] = $this->currentId;
        $this->listOffset = $this->getContentListOffset();
    }

    /**
     * Checks whether redirect_url is present and valid url and returns it
     *
     * @return String - null or url of redirect location
     * @author ahe
     * @date 13.12.2006 22:51:24
     * @package htdocs/plugins/content
     */
    public function checkRedirectPresent()
    {
        $location = null;
        if(!empty($this->currentArticle)
            && !empty($this->currentArticle['redirect_url'])
            && $this->parser->is_url($this->currentArticle['redirect_url'])
            )
        {
            $location = $this->currentArticle['redirect_url'];
        }
        return $location;
    }

    public function getCurrentArticleData() {
        if($this->currentArticle != null)
            return $this->currentArticle;
        else // if articleData is not set, return empty array for compatibility
            return array(
                'heading' => null,
                'description' => null,
                'meta_keywords' => null,
                );
    }

    private function getContentListOffset() {
        $cp = $this->contentPerPage;
        return $this->parser->getListOffset('page',$cp);
    }

    /**
     * This was needed because control chars like \n were displayed as \n and
     * not as e.g. a line feed
     */
    private function convertControlChars($sString) {
        $retVal = str_replace('\r',"\r",$sString);
        $retVal = str_replace('\n',"\n",$retVal);
        $retVal = nl2br($retVal);
        return $retVal;
    }

    public function showSingleArticle()
    {
        if( !$this->checkRights('view') ) {
            return BcmsSystem::raiseNoAccessRightNotice(
                'showSingleArticle()',__FILE__, __LINE__);
        }
        if($this->currentId<=0 ) {
            return BcmsSystem::raiseDictionaryNotice('noArticles',
                BcmsSystem::LOGTYPE_CHECK, BcmsSystem::SEVERITY_INFO,
                'checkForDeletion()', __FILE__, __LINE__);
        }

        return $this->showArticle($this->currentId);
    }

private function createContentListEntry($result, $j) {
    $baseIndent = 14;
    $previewDivCss = '';
    $objWidth = null;
    $objHeight = null;
    $gui = Factory::getObject('GuiUtility');

    $retStr = '        <!-- Contentlist-Output -->
        <div id="contentlist'.($j+1).'" class="contentlist"';
    $placeholderImg = '';
    if(!empty($result['prev_img_id']))
    {
        $objFloat = $this->float_translation[$result['prev_img_float']];
        $placeholderImg = PluginManager::getPlgInstance('ObjectManager')->getObjectsSmallImage(
                    $result['prev_img_id'],
                    false,
                    'float:'.$objFloat);
    }
    $retStr .= '>'."\n";

    $appointment_link = $gui->createAnchorTag(
         'show/'.$result['content_id'],
        $result['heading'],0,null,0,$result['heading'].
        '... '.$this->dictObj->getTrans('more_w_brackets'));

    $heading = $gui->createHeading(3,'<dfn class="unsichtbar">'
        .($j+1).'.</dfn> '.$appointment_link
        ,$baseIndent
        ,'heading'
        ,$this->dictObj->getTrans('sr.Article'));
    $description = $this->convertControlChars($result['description']);
    $description = $gui->createDivWithText(
        'class="contentlist_description"',null,
        stripslashes($description).' ... ');


    $retStr .= $gui->createDivWithText(
        'class="contentlist_preview"'.$previewDivCss
        ,null
        ,"\n".$placeholderImg.$heading.$description."\n"
        ,$baseIndent+2)."\n";

    $retStr .= $this->createContentInfo(
        $baseIndent+2
        ,$result['content_id']
        ,'contentlist_info'
        ,$result['username']
        ,$result['email']
        ,$result['publish_begin']
        ,$result['version']
        ,$result['status']
        ,$result['hits']
        ,$result['publish_end']
        ,$result['created']);

    $retStr .= '	</div>  <!-- /.contentlist -->'."\n";
    return $retStr;
}

    function showContentList()
    {
        if( !$this->checkRights('view') )
            return BcmsSystem::raiseNoAccessRightNotice(
                'showContentList()',__FILE__, __LINE__);

        $row  = $this->getContentList();
        $retStr = '';
        if(count($row) > 0)
        {
            for($j=0;$j<count($row);$j++)
            {
                $retStr .= $this->createContentListEntry($row[$j], $j);
            } // for-loop

            $retStr .= $this->createContentChangePageToolbar();
        } else {
            $retStr .= $this->showNoArticlesInfo();
        }
        return $retStr;
    }

    private function showNoArticlesInfo() {
        if( (BcmsConfig::getInstance()->showEmptyCategory==1))
        {
            $retStr = '<div id="contentlist1" class="contentlist">'.
                $this->dictObj->getTrans('noArticles').'</div>';
            if($this->listOffset>0)
                $retStr .= $this->createContentChangePageToolbar();
            return $retStr;
        }
    }

    /**
     * Lists all content objects according to filter
     */
    public function getCompleteList()
    {
//		if(isset($_POST['table_action_select_article_table'])
//			&& !isset($_POST['dialog_submit'])){ // AHE: The field 'dialog_submit' must be added to each dialog
        $this->actions = $this->getArticleActions();
        // TODO move this back into ContentManager and make performListAction() "protected" again!
        $dialog = $this->manager->performListAction('article_table');
        if($dialog!=null) return $dialog;
        // ...else print general table overview

        if( !$this->userManager->hasViewRight() )
            return BcmsSystem::raiseNoAccessRightNotice(
                'getCompleteList()',__FILE__, __LINE__);

        $tableObj = new HTMLTable('article_table');
        $tableObj->setTranslationPrefix('cont.');
        $tableObj->setActions($this->getArticleActions());
        $tableObj->setBounds('page',null,$this->dalObj->getNumberOfEntries());
        $limit = $tableObj->getListLimit();
        $offset = $tableObj->getListOffset();

        // prepare searching
        list($searchphrase,$offset,$limit) = $tableObj->setSearchBehaviour(true);

        $articles = $this->dalObj->getAllArticlesList(null,null,null,$limit,$offset,$searchphrase);
        $articles = $this->prepareArticleListValues($articles);
        $tableObj->setData($articles);
        unset($articles);

        $plgCatConfig = $this->manager->getPlgCatConfig();
           $showForm=$this->userManager->hasRight($plgCatConfig['edit_right']);
        return $tableObj->render($this->dictObj->getTrans('articlesurvey'),
                                    'content_id', $showForm);
    }

    /**
     * Prepares values of specified array for list view
     *
     * @param Array articles - array containing data for list view
     * @return Array - contains same as input array but with prepared values
     * @author ahe
     * @date 16.12.2006 02:04:23
     * @package htdocs/plugins/content
     */
    private function prepareArticleListValues($articles) {
        for ($i = 0; $i < count($articles); $i++) {
            $h_id = '';
            foreach($articles[$i] as $key => $value) {
                if($key == 'heading') {
                    $value = Factory::getObject('GuiUtility')->createAnchorTag('show/'.$h_id
                        ,$value);
                }
                if($key == 'author') {
                    $value = Factory::getObject('GuiUtility')->createAuthorName($value);
                }
                if($key == 'status') {
                    $value = $this->dictObj->getStatusTrans($value);
                }
                $articleArr[$i][$key] = $value;
                if($key == 'content_id')
                    $h_id = $value;
            }
        }
        return $articleArr;
    }

    private function createContentInfo($indent,$content_id,$cssClassName,$author,$mail,$pubbegin,$version, $status,$hits=null,$pubend=null,$created=null,$history=false)
    {
        $gui = Factory::getObject('GuiUtility');
            // authorname ausgeben
        $retStr = $gui->createAuthorName($author,$indent+2);

        // create version link
        $version_link = $gui->createAnchorTag(
            'version/'.$content_id,'Version:',0,null,
            0,$this->dictObj->getTrans('articleHistory'));
        if($history) $version_link = 'Version:'; // TODO Use dictionary here!
        $retStr .= $gui->createDivWithInnerSpan('class="version"'
            ,$this->dictObj->getTrans('sr.Version')
            ,$version_link.' '.$version,$indent+2);

        // print creation date
        if($created!=null) {
            $retStr .= $gui->createDivWithInnerSpan(' class="created"'
            ,$this->dictObj->getTrans('sr.CreationDate')
            ,$this->dateObject->getDateAsStdDate($created)
            ,$indent+2);
        }

        // print publication start date
        if($pubbegin!=null) {
            $retStr .= $gui->createDivWithInnerSpan(' class="pubbegin"'
                ,$this->dictObj->getTrans('sr.PublishBegin')
                ,$this->dateObject->getDateAsStdDate($pubbegin)
                ,$indent+2);
        }

        // print publication end date
        if($pubend!=null) {
            $retStr .= $gui->createDivWithInnerSpan(' class="pubend"'
                ,$this->dictObj->getTrans('sr.PublishEnd')
                ,$this->dateObject->getDateAsStdDate($pubend)
                ,$indent+2);
        }
        $retStr .= '<div class="sec_row">'."\n";
        // print number of hits
        if($hits!=null) {
            $retStr .= $gui->createDivWithInnerSpan(' class="hits"'
            ,$this->dictObj->getTrans('sr.Hits')
            ,$hits.' '.$this->dictObj->getTrans('sr.Hits')
            ,$indent+2);
        }

        if(PluginManager::getPlgInstance('CategoryManager')->getLogic()->isCommentable())
        {
            // print no of comments on article
            $no_of_comments = $this->getNoOfComments($content_id).' '
            .$this->dictObj->getTrans('comments');
            // ... print span.author without link...
            $retStr .= $gui->createDivWithInnerSpan(' class="no_of_comments"'
                            ,$this->dictObj->getTrans('sr.NoOfComment')
                            ,$no_of_comments,$indent+2);
        }
        $retStr .= '</div>  <!-- /sec_row -->'."\n";

        return $gui->createDivWithText(' style="clear:both" class="'.$cssClassName.'"',null,"\n".$retStr,$indent);
    }

    /**
     * Creates the menu for the content plugin.
     * <strong>ATTENTION:</strong> Does not check any rights!
     *
     * @return String the created menu
     * @author ahe
     * @date 05.10.2006 23:44:53
     * @package htdocs/plugins/content
     */
    public function createEditContentMenu() {
        if($_SESSION['mod']['func']!='single'
            && $_SESSION['mod']['func']!='list'
            && $_SESSION['mod']['func']!='show'
        ){
            return null;
        }

        $plgCatConfig = $this->manager->getPlgCatConfig();
        if(!is_array($plgCatConfig) || empty($plgCatConfig)){
            return null;
        }

        $currentCat = PluginManager::getPlgInstance('CategoryManager')->getLogic()->getTechname();

        $retString = '';
        // determine editor_id of current article
        $editor = 0;
        if($this->currentArticle!=null) {
            if(isset($this->currentArticle['fk_creator'])){
                $editor = $this->currentArticle['fk_creator'];
            } else {
                $editor = $this->currentArticle['fk_editor_id'];
            }
        }
        // check right for edit article and create link
        if( ($_SESSION['mod']['func']=='show' || $_SESSION['mod']['func']=='single')
            &&
            ($this->userManager->hasRight($plgCatConfig['edit_right'])
             || ( $this->userManager->hasRight($plgCatConfig['edit_own_right'])
                   && $editor==$this->userManager->getLogic()->getUserID()) )
            && $this->currentId>0) // category not empty
        {
            $retString .= '<li><a id="edit_article_link" href="'
                .'/'.$currentCat.'/edit_article/'
                .$_SESSION['mod']['oid'].'" accesskey="w">'
                .$this->dictObj->getTrans('cont.EditArticle')
                 .'</a></li>';
        }

        // check right for add article and create link
        if($this->userManager->hasRight($plgCatConfig['add_right'])){
            $retString .= '<li><a id="write_article_link" href="/'
                .$currentCat.'/write" accesskey="w">'
                .$this->dictObj->getTrans('cont.WriteArticle')
                 .'</a></li>';
        }
        $retString = '<ul class="action_menu" style="position:relative;top:-4px;z-index:9999;">'
            .$retString.'
             </ul>';
        return $retString;
    }

    /**
     * TODO PARAM History is unnecessary!!!
     */
    private function createArticlesNavigation($currArticle,$history=false) {
        if($currArticle==null) return false;
        $plgCatConfig = $this->manager->getPlgCatConfig();
        $std_where = ' AND fk_cat = '.$currArticle['fk_cat'].' AND ' .
            'content_id<>'.$currArticle['content_id']
            .' AND status >= '.$GLOBALS['ARTICLE_STATUS']['published'] // TODO use classifications for status!
            .' AND (\''.date('Y-m-d H:i:s')
            .'\' BETWEEN publish_begin AND publish_end)';

        $predecessor_where = $plgCatConfig['content_order_by'].' < \''
            .$currArticle[$plgCatConfig['content_order_by']].'\''.
            $std_where;
        $successor_where = $plgCatConfig['content_order_by'].' >= \''.
            $currArticle[$plgCatConfig['content_order_by']].'\''.
            $std_where;

        $successor = $this->dalObj->select('nextAndPrevious',
            $successor_where,
            $plgCatConfig['content_order_by'].' ASC',0,1);
        $predecessor = $this->dalObj->select('nextAndPrevious',
            $predecessor_where,
            $plgCatConfig['content_order_by'].' DESC',0,3);

		// if sort direction is descendend, turn around links
        if($plgCatConfig['sort_direction']==42) { // TODO use classifications
            $predecessor = $successor;
            $successor = $predecessor;
        }
        if(sizeof($predecessor)>0) {
            $predecessor_link = '&laquo; '.Factory::getObject('GuiUtility')->createAnchorTag(
                 'show/'.$predecessor[0]['content_id'],
                $predecessor[0]['heading'],0,null,0,
                $this->dictObj->getTrans('back').' ' .
                $this->dictObj->getTrans('to_next_article')
                .': '.$predecessor[0]['heading'].')');
        } else $predecessor_link = null;
        if(sizeof($successor)>0) {
            $successor_link = Factory::getObject('GuiUtility')->createAnchorTag(
                 'show/'.$successor[0]['content_id'],
                $successor[0]['heading'],0,null,0,
                $this->dictObj->getTrans('continue').' ' .
                $this->dictObj->getTrans('to_next_article')
                .': '.$successor[0]['heading'])
                .' &raquo;';
        } else $successor_link = null;
        if($predecessor_link!=null || $successor_link!=null){
	        $linkString = $predecessor_link.' | '.$successor_link;
	        $navi = Factory::getObject('GuiUtility')->fillTemplate('div_tpl',
            			array(' id="article_navi"',$linkString));
        } else $navi = null;

		// only add topLink if the category is commentable. In that case the
		// comment form and comments will still have to be printed after
		// the article navigation. Then printing out a topLink makes sense.
		if(PluginManager::getPlgInstance('CategoryManager')->getLogic()->isCommentable())
		{
	        $topLink = Factory::getObject('GuiUtility')->getToTopAnchorDiv(14);
	        $navi = $topLink.$navi;
		}
			return $navi;
    }

    /**
    * Schreibt einen Beitrag anhand der uebergebenen contentID
    * @author goldstift
    * @version 1.0
    */
    function showArticle($contID,$history=false)
    {
        if( !$this->checkRights('view') )
            return BcmsSystem::raiseNoAccessRightNotice(
                'showArticle()',__FILE__, __LINE__);

        $baseIndent = 14;
        $currArticle = $this->currentArticle;
        $refLayout = Factory::getObject('Layout_DAL');// TODO change when ArticleLayout-Plugin is build
        $articleLayoutData =
            $refLayout->getLayoutDataFromFS($currArticle['layout_id']);

        echo '          <!-- Content-Output -->',"\n";
        // unserialize content array from DB
        $contenttext = unserialize($currArticle['contenttext']);
        $index = 0;
        if($contenttext)
        {
            foreach($contenttext as $name => $value) {
                if( (mb_substr($name,0,4)=='bild')
                    && (mb_substr($name,-4)!='text'))
                {
                    $value = PluginManager::getPlgInstance('ObjectManager')->getObjectsSmallImage($value);
                } elseif(strstr($name,'heading')){ // heading should not contain <p>
                    $value = stripslashes($value);
                } else {
                    $value = $this->parser->parseTagsByAllRegex($value);
                    $value = $this->parser->addParagraphs(stripslashes($value));
                }

                // replace the placeholder with the elements HTML
                $articleLayoutData[0] = str_replace('%%'.($index+1).'%%',
                    $value,$articleLayoutData[0]);
                $index++;
            }
        }
        echo '<div id="article_preview_surrounder">',"\n";
        if(is_string($articleLayoutData[0]))
        echo $articleLayoutData[0];
        echo "\n",'</div>';

        if($history) {
            $authorId = $currArticle['fk_editor_id'];
            $creationDate = $currArticle['editdate'];
        } else {
            $authorId = $currArticle['fk_creator'];
            $creationDate = $currArticle['created'];
        }
        $author = $this->userManager->getLogic()->getUserNameFromDB($authorId);
        // create div with content-info
        echo $this->createContentInfo($baseIndent+2
            ,$contID,'story_info',$author
            ,null
            ,null
            ,$currArticle['version']
            ,$currArticle['status']
            ,$currArticle['hits']
            ,null
            ,$creationDate
            ,$history
        );

        echo $this->createArticlesNavigation($currArticle,$history);

        /* comments shall not be shown, if
         * - menu is not commentable
         * - article is history_entry
         */
        if(!$history && PluginManager::getPlgInstance('CategoryManager')->getLogic()->isCommentable())
            echo $this->getCommentsHtml($contID);
        // Hits hochzaehlen
        $this->updateContentHits($contID);

    }


    /**
    * Zeigt die Versionsliste zu einem Beitrag anhand der uebergebenen contentID
    * @author goldstift
    * @version 1.0
    */
    function showArticleVersion($contID)
    {
        // TODO move this back into ContentManager and make performListAction() "protected" again!
        $this->actions = $this->getHistoryActions();
        $dialog = $this->manager->performListAction('history_table');
        if($dialog!=null) return $dialog;
        // ...else print general table overview

        if( !$this->checkRights('view') )
            return BcmsSystem::raiseNoAccessRightNotice(
                'showArticleVersion()',__FILE__, __LINE__);

        $baseIndent = 14;
        $logged_in = PluginManager::getPlgInstance('UserManager')->getLogic()->isLoggedIn();

        $tableObj = new HTMLTable('history_table');
        $tableObj->setTranslationPrefix('cont.');
        $tableObj->setActions($this->getHistoryActions());
        $tableObj->setBounds('page',null,$this->manager->getModel()->getNumberOfEntries('content_id='.$contID));
        $limit = $tableObj->getListLimit();
        $offset = $tableObj->getListOffset();

        $history = $this->manager->getModel()->getArticleHistory($contID,
            $logged_in, $offset,$limit);
        $heading = $history[0]['heading'];

        // re-edit column values
        for ($i = 0; $i < count($history); $i++) {
            $h_id = '';
            foreach($history[$i] as $key => $value) {
                if($key == 'heading') {
                    $value = Factory::getObject('GuiUtility')->createAnchorTag(
                        'history/'.$h_id,$value);
                }
                if($key == 'username') {
                    $value = Factory::getObject('GuiUtility')->createAuthorName($value);
                }
                if($key == 'status') {
                    $value = $this->dictObj->getStatusTrans($value);
                }
                $historyArr[$i][$key] = $value;
                if($key == 'history_id')
                    $h_id = $value;
            }
        }
        unset($history);
        $tableObj->setData($historyArr);
        unset($historyArr);

        $showForm=false;
        if($this->userManager->hasRight('history_edit')) $showForm=true;

        $retStr = $tableObj->render($this->dictObj->getTrans('articleHistoryOf')
            .'"'.$heading.'"', 'history_id',$showForm);
        $retStr .= Factory::getObject('GuiUtility')->createAnchorTag(
                 '/'.PluginManager::getPlgInstance('CategoryManager')->getLogic()->getTechname().
                '/show/'.$contID
                 ,$this->dictObj->getTrans('back').' ('
                 .$heading.')');
        return $retStr;
    }

    function printSubComments($p_iContent_id, $p_iFK_Comment=0, $level=0)
    {
        $sql = 'SELECT comment.comment_id, comment.fk_comment, ' .
                ' comment.heading, comment.created, user.username' .
                ' FROM '.$this->dbCommentTable.' AS comment' .
                ' INNER JOIN '.BcmsConfig::getInstance()->getTablename('user').' AS user' .
                ' ON comment.fk_author = user.user_id'.
                ' WHERE comment.fk_content = '.$p_iContent_id.
                ' AND comment.fk_comment = '.$p_iFK_Comment.
                ' AND comment.status = '.$GLOBALS['ARTICLE_STATUS']['published']; // TODO use classifications for status!

        // URGENT The following won't work with PEAR_DB!!!
        $row = $this->dbObj->fetch_array($this->dbObj->query($sql),1);

        $gui = Factory::getObject('GuiUtility');
        $retStr = '';
        for($j=0;$j<count($row);$j++)
        {
            // put dboutput into readable vars
            $comment_id = $row[$j]['comment_id'];
            $fk_comment = $row[$j]['fk_comment'];
            $commentheader = $row[$j]['heading'];
            $datum = $row[$j]['created'];
            $author = $row[$j]['username'];

            $retStr .= '
            <div class="comment_level"'.$level.'">'."\n";
            $retStr .= $gui->createDivWithText(' class="heading"'
                ,$this->dictObj->getTrans('sr.Article')
                ,$gui->createAnchorTag('show/'
                .$p_iContent_id, $commentheader),14);

            // authorname ausgeben
            $retStr .= $gui->createAuthorName($author,16);


            // print creation date
            $retStr .= $gui->createDivWithText(' class="date"',$this->dictObj->getTrans('sr.PublishBegin')
            ,$this->dateObject->getDateAsStdDate($datum),16);
            if($this->getNoOfComments($p_iContent_id, $comment_id)>0)
            {
                $retStr .= $this->printSubComments($p_iContent_id, $comment_id, ($level+1));
            }
            $retStr .= '</div>'."\n";
        }
    }

    /**
    * returns out all comments to a article depending on it's contentID
    *
    * @access public
    * @return String - all comments represented in an HTML-String
    */
    function getCommentsHtml($contID)
    {
        $oddEven = array('even', 'odd');
        $indent = 10;
        $returnString = '';
        $gui = Factory::getObject('GuiUtility');
        $returnString .= $gui->createSpaces($indent)."<a name=\"a_comments\" id=\"a_comments\"></a>\n";
        $returnString .= $gui->createSpaces($indent)."<div id=\"allcomments\">\n";
        $returnString .= $gui->createHeading(3,$this->dictObj->getTrans('h.CommentHeader')
                                ,$indent+2,'commentheader');

        /* fetch sql from database, parse it and fire it back */
        $allCommentsSQL  = 'SELECT
                 comm.comment_id,
                 comm.heading,
                 comm.contenttext,
                 comm.created,
                 comm.author,
                 author.username as auth_name,
                 author.email as auth_email
             FROM
                 '.$this->dbCommentTable.' as comm,
                 '.BcmsConfig::getInstance()->getTablename('user').' as author
             WHERE comm.fk_author=author.user_id AND
                 (fk_content='.$contID.')
                 AND (comm.status='.$GLOBALS['ARTICLE_STATUS']['published'] // TODO use classifications for status!
                 .') ORDER BY comm.created ASC';
        $result = $this->dbObj->query($allCommentsSQL);
         $numrows = $result->numRows();
         $row = array();
        if (!($result instanceof PEAR_ERROR) && $numrows>0) {
            for ($i = 0; $i < $numrows; $i++) {
                $row[] = $result->fetchRow(DB_FETCHMODE_ASSOC,$i);
            }
            $result->free();
        } else {
            // TODO handle PEAR_ERROR if comments_sql failed
        }

        for($i=0;$numrows>0 && $i<$numrows;$i++)
        {
            $heading = $this->parser->prepareText4Preview($row[$i]['heading']);
            $heading = '<dfn>'.($i+1).'.</dfn> '.$heading;
            $commenttext = $this->parser->prepareText4Preview($row[$i]['contenttext']);
            $auth_name = $this->parser->prepareText4Preview($row[$i]['auth_name']);

            if(!empty($row[$i]['author']) && $row[$i]['author']!='---'){
                $author = $row[$i]['author'];
            } else {
                $author = null;
            }
            $returnString .= $gui->createSpaces($indent+2)
                .'<div id="comment'.($i+1).'" class="comment '.$oddEven[(($i+1)%2)].'">'."\n";
            $returnString .= $gui->createHeading(4,$heading,$indent+4,'commentheader');
            $returnString .= $gui->createDivWithText(' class="comment_text"'
                                ,$this->dictObj->getTrans('sr.Comment')
                ,$commenttext,$indent+4);
            $returnString .= $gui->createSpaces($indent+4)
                                .'<div class="comment_info">'."\n";
            $returnString .= $gui->createAuthorName($auth_name
                                ,$indent+6,null,null,$author);

            $returnString .= $gui->createDivWithInnerSpan(
                'class="date"',$this->dictObj->getTrans('sr.Creationdate')
                ,$this->dateObject->getDateAsStdDate($row[$i]['created']),$indent+6);
            $returnString .= $gui->createSpaces($indent+4).'</div>  <!-- /comment_info -->'."\n";
            $returnString .= $gui->createSpaces($indent+2).'</div>  <!-- /#comment'.($i+1)." -->\n";
        }
        $returnString .= $gui->createSpaces($indent).'</div>  <!-- /#allcomments -->'."\n";
        $returnString .= $this->createCommentForm($contID);
        return $returnString;
    }

    public function write()
    {
        if( !$this->checkRights('write') )
            return BcmsSystem::raiseNoAccessRightNotice('write()',__FILE__, __LINE__);

        return Factory::getObject('cArticleLayout')->createForm($this);// TODO change when ArticleLayout-Plugin is build
    }

    /**
    * Hier wird geprueft, ob der aktuelle Benutzer die benoetigten
    * Zugriffsrechte fuer das aktuelle Menue hat
    *
    * URGENT Move this into System class
    * @param  string $type can be "write" or "view"
    * @author ahe
    * @access protected
    * @package content
    */
    protected function checkRights($type, $m_id=null) {
        // URGENT ACHTUNG: Die beiden Attribute heissen
        $checkAbility = $type.'able4all';
        $m_id = ($m_id!=null) ? $m_id : $_SESSION['m_id'];
        return true; // URGENT This checkRights() is checking NOTHING!!!
    }

}

?>