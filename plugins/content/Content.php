<?php if(!defined('BORDERLESS')) { header('Location: /',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * Old class. Still contains most logic and view methods
 *
 * @todo separate into ArticleLogic, CommentLogic, HistoryLogic if possible
 * @since 0.4
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @class Content
 * @ingroup content
 * @package content
 */
class cContent
{
    // primitive vars
    protected $dbContentTable;
    protected $dbCommentTable;
    protected $plgCatConfig;
    private $currentArticle = null;
    protected $listOffset;
    public $currentId = 0; // \bug URGENT change this to private ASAP!!!
    protected $float_translation = array( // @todo get this from classification!
        'lft' => 'left',
        'rgt'=>'right',
        'none'=>'none');

    // objects
    protected $dbObj;
    protected $dateObject;
    protected $dictObj;
    protected $manager;
    /**
     * Parser instance
     * @var Parser
     */
    protected $parser;

/*** METHODS ***/

    function __construct($manager)
    {
        $config = BcmsConfig::getInstance();
        $this->dbContentTable = $config->getTablename('articles');
        $this->dbCommentTable = $config->getTablename('comments');
        $this->dateObject = new cDate();
        $this->manager = $manager;
        $this->plgCatConfig = $this->manager->getPlgCatConfig();
        $this->dalObj = $this->manager->getArticleDalObj();
        $this->dbObj = $GLOBALS['db'];
        $this->dictObj = PluginManager::getPlgInstance('Dictionary');
        $this->parser = BcmsSystem::getParser();
    }

/* +++ BEGINN  C O N T E N T V E R W A L T U N G +++ */

    function deleteContent($contentID, $temp=0)
    {
        /* ACHTUNG:
        * Hier muss noch geprueft werden, ob das aktuelle Menue fuer jeden
        *  schreibbar ist.
        * Ansonsten muss die Methode checkMenuRight aufgerufen werden.
        */
        if (!BcmsSystem::getUserManager()->hasRight($this->plgCatConfig['del_right'])
        || $temp)
        {
            return BcmsSystem::raiseNoAccessRightNotice(
                'deleteContent()',__FILE__, __LINE__);
        }

        $sql='DELETE FROM '.$this->dbContentTable.' WHERE (content_id = '.$contentID.')';
        // @todo ausserdem noch history eintraege loeschen
        return $this->dbObj->query($sql);
    }

/* +++ ENDE  C O N T E N T V E R W A L T U N G +++ */


/* +++ BEGINN  C O M M E N T V E R W A L T U N G +++ */
    /**
     schreibt ein Kommentar in die CommentTabelle
    */
    function addComment($heading,$comment,$contID,$commStatus,$author)
    {
        if (!BcmsSystem::getUserManager()->hasRight('COMMENT_WRITE'))
            return BcmsSystem::raiseNoAccessRightNotice(
                'addComment()',__FILE__, __LINE__);

        if(empty($author)) { $author = '---'; }
        if($this->parser->checkContainsBadwords($heading)
        	|| $this->parser->checkContainsBadwords($author)
        	|| $this->parser->checkContainsBadwords($comment))
        {
        	return BcmsSystem::raiseError(
        		'Kommentar enthält unzulässigen Inhalt, bitte korrigieren Sie Ihre Eingabe', // TODO use dictionary!!!
        		BcmsSystem::LOGTYPE_EXCEPTION // TODO use real Exception here
			);
        }
        $heading = $this->parser->prepDbStrng($heading);
        $comment = $this->parser->prepDbStrng($comment);
        $author = $this->parser->prepDbStrng($author);
        $remoteAddr = $this->parser->getServerParameter('REMOTE_ADDR');
        $remoteAddr = $this->parser->prepDbStrng($remoteAddr);

        $sql = 'INSERT INTO '.$this->dbCommentTable
            .' (fk_content, fk_author, heading,`contenttext`, created, status_id, '
            .'author, ip_address) '
            .'VALUES '
            .'('.intval($contID).', '.BcmsSystem::getUserManager()->getUserId().', '
            .$heading.','.$comment.', NOW(), '.$commStatus.', '.$author.', '.
            $remoteAddr.')';
        $result = $this->dbObj->query($sql);

        if(PEAR::isError($result)) {
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
        if (!BcmsSystem::getUserManager()->hasRight('COMMENT_DELETE'))
            return BcmsSystem::raiseNoAccessRightNotice(
                'deleteComment()',__FILE__, __LINE__);

        $sql='DELETE FROM '.$this->dbCommentTable.' WHERE (comment_id = '.intval($commentID).')';
        return $this->dbObj->query($sql);
    }
/* +++ ENDE  C O M M E N T V E R W A L T U N G +++ */



/* +++ HILFSFUNKTIONEN FUER CONTENT- UND COMMENTAUSGABE +++ */

    /* Gibt die Anzahl der Hits zum entsprechenden Artikel
    */
    function getContentHits($contID)
    {
	    $sql = 'SELECT hits FROM '.$this->dbContentTable
	         .' WHERE (content_id = '.intval($contID).')';
	    $result = $this->dbObj->query($sql);
	    if(PEAR::isError($result)) {
            BcmsSystem::raiseError($result, BcmsSystem::LOGTYPE_SELECT,
                BcmsSystem::SEVERITY_ERROR, 'getContentHits()',
                __FILE__,__LINE__);
	    	return 0;
	    } else {
		    $erg = $result->fetchRow();
		    $result->free();
		    return ($erg[0]);
	    }
    }

    /* Erhoeht die Anzahl der Hits des entsprechenden Artikels um 1
    */
    private function updateContentHits($contID)
    {
        $contID = intval($contID);
    	$hits = $this->getContentHits($contID);
        // Falls es nocht nicht ausgefuehrt wurde, werden die Hits erhoeht
        $sql = 'UPDATE '.$this->dbContentTable.' SET hits = '.($hits+1)
            .' WHERE (content_id = '.$contID.')';
        // Wenn TransaktionsSQL vorhanden, fuehre Update nicht durch!
        if(BcmsSystem::checkTransaction($sql)) return false;

        $result = $this->dbObj->query($sql);
        if(PEAR::isError($result)) {
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
    * @param int $content_id
    * @param int $sub_comment_id
    * @access public
    * @return
    * @author goldstift
    */
    private function getNoOfComments($content_id,$sub_comment_id=0)
    {
        $query  = 'SELECT comment_id FROM '.$this->dbCommentTable.'
            WHERE (fk_content='.intval($content_id).') and (status_id='
            .$GLOBALS['ARTICLE_STATUS']['published'].')'; // @todo use classifications for status!
        if($sub_comment_id>0) $query .= ' and (fk_comment = '.intval($sub_comment_id).')';
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
        return BcmsFactory::getInstanceOf('GuiUtility')->getPageControlField(
            'page',
            $this->plgCatConfig['no_of_articles_per_page'],
            sizeof($this->getContentList(false))
        );
    }

    /**
     *  Gibt das Kommentar-Formular aus
     */
    private function createCommentForm($contID=null,$indent = 14){
    // if user has no right to write a comment he does not see the form
        if(!BcmsSystem::getUserManager()->hasRight('COMMENT_WRITE')) return null;


        $allCommentsSQL  = 'SELECT comm.comment_id, comm.heading
             FROM '.$this->dbCommentTable.' as comm
             WHERE (fk_content='.$contID.')
                 AND (comm.status_id='.$GLOBALS['ARTICLE_STATUS']['published'] // @todo use classifications for status!
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
        if(!BcmsSystem::getUserManager()->isLoggedIn()) {
            $commentForm->addElement('text', 'comm_author'
            , '', $indent+2, 'Name:');
        }
        $commentForm->addElement('text', 'comm_heading'
        , '', $indent+2, '&Uuml;berschrift:');
// @todo Use HTML_Quickform so that CommentOnComment can be realized
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
        if ( !BcmsSystem::getUserManager()->hasRight($this->plgCatConfig['edit_right'])
            && !BcmsSystem::getUserManager()->hasRight($this->plgCatConfig['add_right'])
            && !( BcmsSystem::getUserManager()->hasRight($this->plgCatConfig['edit_own_right'])
                   && $editor==BcmsSystem::getUserManager()->getUserId())
        ) {
            // if access denied, send error message
            return BcmsSystem::raiseNoAccessRightNotice(
                'editArticle()',__FILE__, __LINE__);
        }
		$layout = new cArticleLayout();
        return $layout->createForm($this,$p_iArticleId);
    }

    public function getContentList($withLimitAndOffset=true) {
        $sort_direction = $this->plgCatConfig['sort_direction'];
        $content_order_by = $this->plgCatConfig['content_order_by'];
        if($withLimitAndOffset) {
        	return $this->dalObj->getArticleListByCategory(
                $_SESSION['m_id'], $GLOBALS['ARTICLE_STATUS']['published'] // @todo use classifications for status!
                , true
                , $content_order_by
                , $sort_direction
                , $this->plgCatConfig['no_of_articles_per_page']
                , $this->listOffset
            );
        } else {
            return $this->dalObj->getArticleListByCategory(
	            $_SESSION['m_id'], $GLOBALS['ARTICLE_STATUS']['published'] // @todo use classifications for status!
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
        } elseif(array_key_exists('current_article_data',$_SESSION)
            && array_key_exists('layout_id',$_SESSION['current_article_data'])
        	&& is_numeric($_SESSION['current_article_data']['layout_id']))
        {
            // will be used whilst article generation
            $layoutId = $_SESSION['current_article_data']['layout_id'];
        } elseif($this->currentId>0) {
            $layoutId = $this->currentArticle['layout_id'];
        }

        if($layoutId>0) { // if layout_id is set...
            $refLayout = Layout_DAL::getInstance();// @todo change when ArticleLayout-Plugin is build
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
        $cp = $this->plgCatConfig['no_of_articles_per_page'];
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
    $gui = BcmsFactory::getInstanceOf('GuiUtility');
    $retStr = '        <!-- Contentlist-Output -->
        <div id="contentlist'.($j+1).'" class="contentlist"';
    $placeholderImg = '';
    if(!empty($result['prev_img_id']))
    {
        $objFloat = $this->float_translation[$result['prev_img_float']];
        $placeholderImg = PluginManager::getPlgInstance('FileManager')->getObjectsSmallImage(
                    $result['prev_img_id'],
                    false,
                    'float:'.$objFloat);
    }
    $retStr .= '>'."\n";

    $result['heading'] = $this->parser->prepareText4Preview($result['heading']);
    $appointment_link = $gui->createAnchorTag(
         'show/'.$result['content_id'],
        $result['heading'],0,null,0,
        $this->parser->filterPageTitle($result['heading']).
        '... '.$this->dictObj->getTrans('more_w_brackets'));

    $heading = $gui->createHeading(3,'<dfn class="unsichtbar">'
        .($j+1).'.</dfn> '.$appointment_link
        ,$baseIndent
        ,'heading'
        ,$this->dictObj->getTrans('sr.Article'));
    $description = $this->convertControlChars($result['description']);
    $description = $gui->createDivWithText(
        'class="contentlist_description"',null,
        $this->parser->prepareText4Preview($description).' ... ');


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
        ,$result['status_id']
        ,$result['hits']
        ,$result['publish_end']
        ,$result['created']);

    $retStr .= '	</div>  <!-- /.contentlist -->'."\n";
    return $retStr;
}

    function showContentList()
    {
        if( !$this->checkRights('view') ){
            return BcmsSystem::raiseNoAccessRightNotice('showContentList()',__FILE__, __LINE__);
        }

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

    private function createContentInfo($indent,$content_id,$cssClassName,$author,$mail,$pubbegin,$version, $status,$hits=null,$pubend=null,$created=null,$history=false)
    {
        $gui = BcmsFactory::getInstanceOf('GuiUtility');
            // authorname ausgeben
        $retStr = $gui->createAuthorName($author,$indent+2);

        // create version link
        $version_link = $gui->createAnchorTag(
            'version/'.$content_id,'Version:',0,null,
            0,$this->dictObj->getTrans('articleHistory'));
        if($history) $version_link = 'Version:'; // @todo Use dictionary here!
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

        if(BcmsSystem::getCategoryManager()->getLogic()->isCommentable())
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
     */
    public function createEditContentMenu() {
        if($_SESSION['mod']['func']!='single'
            && $_SESSION['mod']['func']!='list'
            && $_SESSION['mod']['func']!='show'
        ){
            return null;
        }

        if(!is_array($this->plgCatConfig) || empty($this->plgCatConfig)){
            return null;
        }

        $currentCat = BcmsSystem::getCategoryManager()->getLogic()->getTechname();

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
            (BcmsSystem::getUserManager()->hasRight($this->plgCatConfig['edit_right'])
             || ( BcmsSystem::getUserManager()->hasRight($this->plgCatConfig['edit_own_right'])
                   && $editor==BcmsSystem::getUserManager()->getUserId()) )
            && $this->currentId>0) // category not empty
        {
            $retString .= '<li><a id="edit_article_link" href="'
                .'/'.$currentCat.'/edit_article/'
                .$_SESSION['mod']['oid'].'" accesskey="w">'
                .$this->dictObj->getTrans('cont.EditArticle')
                 .'</a></li>';
        }

        // check right for add article and create link
        if(BcmsSystem::getUserManager()->hasRight($this->plgCatConfig['add_right'])){
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
	 * Generates links to next and previous articles
	 *
	 * @todo switch first parameter to type BcmsArticle
	 * @param Array $currArticle - array with the current articles record data
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 * @return String - rendered html code
	 */
    private function createArticlesNavigation($currArticle) {
        if($currArticle==null) return false;
        if(empty($this->plgCatConfig)) $this->plgCatConfig = $this->manager->getPlgCatConfig();

        $std_where = ' AND fk_cat = '.$currArticle['fk_cat'].' AND ' .
            'content_id<>'.$currArticle['content_id']
            .' AND status_id >= '.$GLOBALS['ARTICLE_STATUS']['published'] // @todo use classifications for status!
            .' AND (\''.date('Y-m-d H:i:s')
            .'\' BETWEEN publish_begin AND publish_end)';

        $predecessor_where = $this->plgCatConfig['content_order_by'].' < \''
            .$currArticle[$this->plgCatConfig['content_order_by']].'\''.
            $std_where;
        $successor_where = $this->plgCatConfig['content_order_by'].' >= \''.
            $currArticle[$this->plgCatConfig['content_order_by']].'\''.
            $std_where;

        $successor = $this->dalObj->select('nextAndPrevious',
            $successor_where,
            $this->plgCatConfig['content_order_by'].' ASC',0,1);
        $predecessor = $this->dalObj->select('nextAndPrevious',
            $predecessor_where,
            $this->plgCatConfig['content_order_by'].' DESC',0,3);

		// if sort direction is descendend, turn around links
        if($this->plgCatConfig['sort_direction']=='DESC') { // @todo use classifications
            $predecessor = $successor;
            $successor = $predecessor;
        }

        if(sizeof($predecessor)>0) {
        	$predecessor_anchortitle = $this->dictObj->getTrans('back') . ' '
				. $this->dictObj->getTrans('to_next_article')
        		. ': '
				. $this->parser->filterPageTitle($predecessor[0]['heading']);
        	$predecessor_link = $this->dictObj->getTrans('cont.previous_article')
        		. BcmsFactory::getInstanceOf('GuiUtility')->createAnchorTag(
                 	'show/'.$predecessor[0]['content_id'],
                 	$this->parser->prepareText4Preview($predecessor[0]['heading']),
                 	0,null,0,
                 	$predecessor_anchortitle
				);
                $predecessor_link = '<li>'.$predecessor_link.'</li>';
        } else {
        	$predecessor_link = null;
        }
        if(sizeof($successor)>0) {
        	$successor_anchortitle = $this->dictObj->getTrans('continue') . ' '
				. $this->dictObj->getTrans('to_next_article')
        		. ': '
				. $this->parser->filterPageTitle($successor[0]['heading']);
        	$successor_link = $this->dictObj->getTrans('cont.next_article')
            	. BcmsFactory::getInstanceOf('GuiUtility')->createAnchorTag(
            		'show/'.$successor[0]['content_id'],
            		$this->parser->prepareText4Preview($successor[0]['heading']),
            		0,null,0,
            		$successor_anchortitle
            	);
                $successor_link = '<li>'.$successor_link.'</li>';
        } else {
        	$successor_link = null;
        }
        if($predecessor_link!=null || $successor_link!=null){
	        $linkString = '<ul id="article_navi_list">'.$predecessor_link.' '.$successor_link.'</ul>';

			// add topLink only if the category is commentable. In that case the
			// comment form and comments will still have to be printed after
			// the article navigation. Then printing out a topLink makes sense.
			if(BcmsSystem::getCategoryManager()->getLogic()->isCommentable())
			{
		        $topLink = BcmsFactory::getInstanceOf('GuiUtility')->getToTopAnchorDiv(14);
		        $linkString = $topLink.$linkString;
			}
			$navi = BcmsFactory::getInstanceOf('GuiUtility')->fillTemplate('div_tpl',
            			array(' id="article_navi"',$linkString));
        } else {
        	$navi = null;
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
        $articleLayoutData = Layout_DAL::getInstance()->getLayoutDataFromFS($currArticle['layout_id']);// @todo change when ArticleLayout-Plugin is build
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
                    $value = PluginManager::getPlgInstance('FileManager')->getObjectsSmallImage($value);
                } elseif(strstr($name,'heading')){ // heading should not contain <p>
                	$value = $this->parser->prepareText4Preview($value);
                } else {
                    $value = $this->parser->parseTagsByAllRegex($value);
                    $value = $this->parser->addParagraphs($value);
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
        $author = BcmsSystem::getUserManager()->getLogic()->getUserNameFromDB($authorId);
        $hits = (array_key_exists('hits',$currArticle) ? $currArticle['hits'] : null);

        // create div with content-info
        echo $this->createContentInfo(
        	$baseIndent+2
            ,$contID,'story_info'
			,$author
            ,null
            ,null
            ,$currArticle['version']
            ,$currArticle['status_id']
            ,$hits
            ,null
            ,$creationDate
            ,$history
        );

        echo $this->createArticlesNavigation($currArticle);

        /* comments shall not be shown, if
         * - menu is not commentable
         * - article is history_entry
         */
        if(!$history && BcmsSystem::getCategoryManager()->getLogic()->isCommentable())
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
        // @todo move this back into ContentManager and make performListAction() "protected" again!
        $dialog = $this->manager->performListAction('history_table');
        if($dialog!=null) return $dialog;
        // ...else print general table overview

        if( !$this->checkRights('view') )
            return BcmsSystem::raiseNoAccessRightNotice(
                'showArticleVersion()',__FILE__, __LINE__);

        $baseIndent = 14;
        $logged_in = BcmsSystem::getUserManager()->isLoggedIn();

        $tableObj = new HTMLTable('history_table');
        $tableObj->setTranslationPrefix('cont.');
        $tableObj->setActions($this->manager->getHistoryActions());
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
                    $value = BcmsFactory::getInstanceOf('GuiUtility')->createAnchorTag(
                        'history/'.$h_id,$value);
                }
                if($key == 'username') {
                    $value = BcmsFactory::getInstanceOf('GuiUtility')->createAuthorName($value);
                }
                if($key == 'status_id') {
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
        if(BcmsSystem::getUserManager()->hasRight('history_edit')) $showForm=true;

        $retStr = $tableObj->render($this->dictObj->getTrans('articleHistoryOf')
            .'"'.$heading.'"', 'history_id',$showForm);
        $retStr .= BcmsFactory::getInstanceOf('GuiUtility')->createAnchorTag(
                 '/'.BcmsSystem::getCategoryManager()->getLogic()->getTechname().
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
                ' AND comment.status_id = '.$GLOBALS['ARTICLE_STATUS']['published']; // @todo use classifications for status!

        // \bug URGENT The following won't work with PEAR_DB!!!
        $row = $this->dbObj->fetch_array($this->dbObj->query($sql),1);

        $gui = BcmsFactory::getInstanceOf('GuiUtility');
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
        $gui = BcmsFactory::getInstanceOf('GuiUtility');
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
                 AND (comm.status_id='.$GLOBALS['ARTICLE_STATUS']['published'] // @todo use classifications for status!
        	.') ORDER BY comm.created '.$this->plgCatConfig['comments_sort_direction'];
        $result = $this->dbObj->query($allCommentsSQL);
         $numrows = $result->numRows();
         $row = array();
        if (!($result instanceof PEAR_ERROR) && $numrows>0) {
            for ($i = 0; $i < $numrows; $i++) {
                $row[] = $result->fetchRow(DB_FETCHMODE_ASSOC,$i);
            }
            $result->free();
        } else {
            // @todo handle PEAR_ERROR if comments_sql failed
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
		$layout = new cArticleLayout();
        return $layout->createForm($this);
    }

    /**
    * Hier wird geprueft, ob der aktuelle Benutzer die benoetigten
    * Zugriffsrechte fuer das aktuelle Menue hat
    *
    * \bug URGENT Move this into System class
    * @param  string $type can be "write" or "view"
    * @author ahe
    * @access protected
    */
    protected function checkRights($type, $m_id=null) {
        // \bug URGENT ACHTUNG: Die beiden Attribute heissen
        $checkAbility = $type.'able4all';
        $m_id = ($m_id!=null) ? $m_id : $_SESSION['m_id'];
        return true; // \bug URGENT This checkRights() is checking NOTHING!!!
    }

}
?>