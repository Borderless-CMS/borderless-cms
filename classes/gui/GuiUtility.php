<?php
/*
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
if (!defined('BORDERLESS'))	exit;
/**
* This class contains all functions for gui output
*/
class GuiUtility extends BcmsObject {
	// the versionstring is needed e.g. for getHashcode()
	protected $versionstring = '0.49';
	protected $dbObject;
	private $parserObj = null;
	private $searchTextFieldId = 'searchtext_';
	private $configInstance = null;

	function __construct(& $dbObject=null) {
		$this->parserObj = BcmsFactory::getInstanceOf('Parser');
		$this->configInstance = BcmsConfig::getInstance();
		$this->dictObj = Factory::getObject('Dictionary');
	}


	public function getPageControlField($getVarName='logpage', $noOfElementsOnPage=0,$noOfAllElements=0)
	{
		if(!isset($this->parserObj))
			$this->parserObj = BcmsFactory::getInstanceOf('Parser');

		$noOfElementsOnPage = ($noOfElementsOnPage==0) ? $this->configInstance->max_list_entries : $noOfElementsOnPage;
		switch ($noOfElementsOnPage) {
			case 0:
				$noOfElementsOnPage = $this->configInstance->max_list_entries;
				break;
			case -1:
				$noOfElementsOnPage = $noOfAllElements;
				break;
			default:
				break;
		}
		if($this->parserObj->getGetParameter($getVarName)!=null
			&& intval($this->parserObj->getGetParameter($getVarName))>1)
		{
			$pageBack = intval($this->parserObj->getGetParameter($getVarName))-1;
		} else {
			$pageBack = 0;
		}
		if($this->parserObj->getGetParameter($getVarName)!=null){
			$pageNext = intval($this->parserObj->getGetParameter($getVarName))+1;
		} else {
			$pageNext = 1;
		}
		$fromNum = ($noOfElementsOnPage*($pageNext-1))+1;
		$toNum = ($noOfElementsOnPage*$pageNext);
		$toNum = ($toNum>$noOfAllElements) ? $noOfAllElements : $toNum;
		$leftAnchor = ($pageBack==0 && $fromNum==1) ? '&laquo;' : $this->createAnchorTag(
								'?'.$getVarName.'='.$pageBack
								,'&laquo;',0,'&lt;',0,
								$this->dictObj->getTrans('back')
								);
		$rightAnchor= ($toNum ==$noOfAllElements) ? '&raquo;' : $this->createAnchorTag(
								'?'.$getVarName.'='.$pageNext
								,'&raquo;',0,'&gt;',0,
								$this->dictObj->getTrans('further')
								);

		return $this->createDivWithText('class="prevNextArticle"',null,
			$leftAnchor.' '.$this->dictObj->getTrans('entries')
        	.' '.$fromNum.'-'.$toNum
        	.' '.$this->dictObj->getTrans('of').' '.$noOfAllElements.' '.
				$rightAnchor,10);
	}

	/**
	 * Creates a search form using the specified parameters.
	 * Returns null if setSearchEnabled() has not been set true
	 *
	 * @param $tableId - an id to make the fields unique on a page with multiple
	 * search forms
	 * @return String - the xhtml representation of the whole form
	 * @author ahe
	 * @date 28.11.2006 00:12:08
	 * @package _deployed/classes/gui
	 */
	public function createSearchForm($tableId, $presetValue=null)
	{
		$searchLabel = $this->fillTemplate('label_tpl',
				array($this->searchTextFieldId.$tableId,
				$this->dictObj->getTrans('searchphrase'),
				null));
		$searchField = $this->fillTemplate('input_tpl',
				array('text',
				$this->searchTextFieldId.$tableId,$presetValue,
				null));
		$submit = $this->fillTemplate('input_tpl',
				array('submit', 'submit_search_'.$tableId,
				$this->dictObj->getTrans('search'),
				null))."\n";
		$searchFieldset = $this->fillTemplate('fieldset_tpl',
				array('id="'.$this->searchTextFieldId.$tableId.'_fieldset"',
				$this->dictObj->getTrans('search_table_entries'),
				$searchLabel.$searchField.$submit,null));
		$retValue = $this->fillTemplate('form_tpl', array(
				'searchForm_'.$tableId,
				BcmsFactory::getInstanceOf('Parser')->getServerParameter('REQUEST_URI'),
				'post','application/x-www-form-urlencoded',
				$searchFieldset,
				'class="elementsOnPageForm"'));
		return $retValue;
	}

	/**
	 * Gets the search phrase from $_POST according to specified tableId
	 *
	 * @param $tableId - an id to make the fields unique on a page with multiple
	 * search forms
	 * @return String - the search phrase specified already prepared for DB
	 * @author ahe
	 * @date 28.11.2006 00:24:49
	 * @package _deployed/classes/gui
	 */
	public function getSearchPhrase($tableId){
		if(!isset($this->parserObj))
			$this->parserObj = BcmsFactory::getInstanceOf('Parser');

		if(!empty($_POST[$this->searchTextFieldId.$tableId])){
			return $this->parserObj->getPostParameter($this->searchTextFieldId.$tableId);
		} else {
			return null;
		}

	}

	/**
	* schreibt eine uebergebene Anzahl an Leerzeichen, um
	* die korrekte Einrueckung des Codes zu gewaehrleisten.
	* Wird fuer die Einrueckung des HTML-Quellcodes verwendet.
	*
	* @param integer $numOfSpaces count of spaces to be returned
	* @return string returns a string with the requested spaces
	*/
	function createSpaces($numOfSpaces) {
		$spaces = '';
		for ($i = 0; $i < $numOfSpaces; $i ++) $spaces .= ' '; // Spaces
		return $spaces;
	}

	/**
	 *
	 * create an anchor tag with given parameters
	 *
	 * @param integer $ext DEPRECATED! (actually boolean) Was used to insert the
	 * target attribute to the link (not xhtml strict)
	 * @author ahe
	 * @package htdocs/classes/system
	 */
	function createAnchorTag($link, $text, $spaces = 0, $accesskey = null, $ext = 0, $title = null, $optAttrib=null) {
		if(!$ext
			&& !stristr($link,'://')
			&& substr($link,0,1)!='/'
			&& substr($link,0,1)!='?'
			&& substr($link,0,1)!='#'
			&& !stristr($link,'mailto:')
		)
		{
			$prefix = $this->configInstance->completeSiteUrl;
			// rebuild link
			$link = $prefix.'/'.$_SESSION['cur_catname'].'/'.$link;
		}

		$anchorTag = $this->createSpaces($spaces); // Spaces
		$anchorTag .= '<a href="'.$link.'"'.$optAttrib;
		$anchorTag .= ' title="'.$title.'';
		if (isset ($accesskey) && $accesskey!=null && $accesskey != '') {
			$anchorTag .= ' (ALT + '.$accesskey.')';
			$anchorTag .= '" accesskey="'.$accesskey;
		}
		$anchorTag .= '">'.$text.'</a>';
		return $anchorTag;
	}

	public function createAuthorName($author,$num_of_spaces=0,$cssClassName=null,$srDescText=null,$alternativeAuthor=null){

		if($cssClassName == null)
			$optAttribs = ' class="author"';
		else
			$optAttribs = ' class="'.$cssClassName.'"';

		if($srDescText==null)
			$srDescText = $this->dictObj->getTrans('sr.Author');
		$titleText = $this->dictObj->getTrans('showUserProfile')
			.' ('.$author.')';

		$alternativeAuthor = empty($alternativeAuthor) ? $author : $alternativeAuthor;
		// ... print span.author with link...
		return $this->createDivWithInnerSpan($optAttribs, $srDescText,
			$this->createAnchorTag('/user/show/'.$author,$alternativeAuthor,0,
			null,0,$titleText), $num_of_spaces);
	}

	public function createDivWithInnerSpan($optAttribString, $srVarName, $text, $numOfSpaces = 0) {
		return $this->createDivWithText($optAttribString, $srVarName, $text, $numOfSpaces,true);
	}

	/**
	* prints out a div with additional screenreader info, css class definition
	* and text/ content. As optional parameter the number of spaces for text-indent
	* can be given.
	* Used @ printContentList and printContentArticle
	*
	* @param string $cssClassName
	* @param string $srDescript
	* @param string $text
	* @param integer $numOfSpaces optional, default value 0
	* @access public
	* @return
	*/
	function createDivWithText($optAttribString, $srDescript, $text, $numOfSpaces = 0,$innerSpan=false) {
		$srDesc = null;
		$divTitle = null;
		$content = null;
		$retStr = null;
		if($numOfSpaces>0) $retStr .= $this->createSpaces($numOfSpaces);

		/* Screenreader information span */
		if ($srDescript != null) {
			$srDesc .= "\n".$this->createSpaces($numOfSpaces+2);
			$srDesc .= $this->fillTemplate('span_tpl',array($srDescript
				,'class="sr_desc" title="'.$this->dictObj->getTrans('srInfo').'"'));
			$divTitle = 'title="'.$srDescript.'"';
		}
		if($numOfSpaces>0) $content .= "\n".$this->createSpaces($numOfSpaces+2);
		if($innerSpan)
			$content .= $this->fillTemplate('span_tpl',array($text,$divTitle));
		else
			$content .= $text;
		$content .= "\n".$this->createSpaces($numOfSpaces);
		$retStr .= $this->fillTemplate('div_tpl',
			array($optAttribString,$srDesc.$content));
		return $retStr;
	}

	/**
	* creates a heading with additional screenreader info, css class definition
	* and text/ content. As optional parameter the number of spaces for text-indent
	* can be given.
	*
	* @param $cssClassName
	* @param $srVarName
	* @param $text
	* @param integer $numOfSpaces optional, default value 0
	* @access public
	* @return
	*/
	public function createHeading($headingNo, $text, $numOfSpaces = 0, $cssClassName = null, $srVarName = null)
	{
		$returnString = $this->createSpaces($numOfSpaces); // Spaces
		$returnString .= "<h".$headingNo;
		// if classname is given, print it out
		if ($cssClassName != null) $returnString .= ' class="'.$cssClassName.'"';
		$returnString .= '>';

		if ($srVarName != null) {
			/* Screenreader information span */
			$returnString .= '  <span class="sr_desc" '
				.'title="additional screenreader information">'.$srVarName
				.'  </span>  <!-- /sr_desc -->';
		}

		$returnString .= '<span>'.$text.'</span>';
		$returnString .= '</h'.$headingNo.'>  <!-- /'.$cssClassName.' -->'."\n";
		return $returnString;
	}

	/*_______________________I M A G E M E T H O D S________________________ */

	/**
	* Creates a complete ImageTag with the data from the given array.
	* The $ImgNumber is for CSS identification.
	*
	* @param $ImgDataArray
	* @param $ImgNumber
	* @access public
	* @return string
	*/
	public function createImageTag($p_aImgAttributes) {

		$repStr = '';
		foreach ($p_aImgAttributes as $key =>$value) {
			$repStr .= ' ' . $key . '="' . $value .'"';
		}
		if(mb_substr($repStr,0,1)==' ') $repStr = mb_substr($repStr,1);
		return $this->fillTemplate('img_tpl',array($repStr));
	}

	/**
	* Image-Tag fuer ein Menu-Symbol erstellen
	*
	* @param $parID used in link as "...&parVarname=parID"
	* @param $parGfxname the actual name of the image including postfix e.g. ".gif"
	* @param $parVarname used in link as "...&parVarname=parID"
	* @param $parAction_link used as $_GET['action']
	* @param $gfxPath
	* @param $parGfxwidth
	* @param $parImgInfo used in title attribute
	* @param $p_sFloat style float behaviour e.g. "none","left","right"; default="none"
	* @param $p_aMarginArray holds the margin values (top,right,bottom,left); default={0,0,0,0}
	* @access public
	* @return string
	*/
	public static function createActionImg($parID,$parGfxname,$parVarname,$parAction_link,$gfxPath,$parGfxwidth,$parImgInfo,$p_sFloat="none",$p_aMarginArray=array(0,0,0,0), $p_cAccesskey=null)
	{
	$theImgDataArray = array(
			'src' => $gfxPath.$parGfxname, // Filename
			'width' => $parGfxwidth,
			'style' => 'float:'.$p_sFloat.'; margin:'
				.$p_aMarginArray[0].'px '
				.$p_aMarginArray[1].'px '
				.$p_aMarginArray[2].'px '
				.$p_aMarginArray[3].'px;',
			'alt' => $parImgInfo,
			'title' => $parImgInfo
		);
		$theImgContent = $this->createImageTag($theImgDataArray);
		$theImgContent = $this->createAnchorTag($parAction_link.'/'
			.$parID
			, $theImgContent, 0, $p_cAccesskey,0, $parImgInfo);
	return $theImgContent;
	}

	/**
	* Parses the replace strings (array) into the given template
	*
	* @param $p_sTemplate
	* @param $p_aReplaceStrings
	* @access private
	* @return
	*/
	public function fillTemplate($p_sTemplate,$p_aReplaceStrings) {

		$retStr = $GLOBALS['bcms_templates'][$p_sTemplate];
		for ($i = 0; $i < count($p_aReplaceStrings); $i++) {
			$retStr = str_replace('%%'.($i+1).'%%',$p_aReplaceStrings[$i],$retStr);
		}
		return $retStr;
	}

	/**
	 * Creates a &lt;div&gt; with an anchor leading to top inside
	 *
	 * @return string - html code representing the div with the to_top_anchor
	 * @author ahe
	 * @date 19.01.2007 23:41:40
	 * @package htdocs/classes/gui
	 */
	public function getToTopAnchorDiv($numOfSpaces = 0) {
		$to_top_trans = PluginManager::getPlgInstance('Dictionary')->getTrans('to_top');
		$retString = $this->createAnchorTag(
			'#top',$to_top_trans,0,null,0,$to_top_trans);
		return $this->createDivWithText(' class="toTopLink"', null, $retString, $numOfSpaces,false);
	}
}
?>