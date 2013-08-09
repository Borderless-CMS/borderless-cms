<?php
/**
 * Diverse methods to
 *
 * Created on 10.09.2005 13:07:31 by ahe
 * Filename: Parser.php
 * @package htdocs/classes/system
 */
 class Parser {

	private $maxSpecialCharLength = 7; // used in CutUnfinishedSpecialChars()
	private $getVars = array(null);
	private $postVars = array(null);
	private $serverVars = array(null);
	private $configInst = null;
	private static $isRunFetchAdditionalGetParameters = false;

 	public function __construct() {
	    $this->configInst = BcmsConfig::getInstance();
		// first fetch all real $_GET-variables ...
 		foreach($_GET as $key => $value) {
			// URGENT the following line causes apache to crash when get-par ?func= is submitted!
			$this->getVars[$this->filter($key)] = $this->filter($value);
		}
		// ... and afterwards fetch all additional "GET"-vars
//		echo ' Aufruf  ';
//		$this->fetchAdditionalGetParameters();
 	}

/******* BEGIN OF  general helper methods             ********/

	/**
	 * Uses regular expression to validate the specified url string
	 *
	 * @param String url_string - possible url to be checked
	 * @return boolean - whether param matched the validation regex or not
	 * @author ahe
	 * @date 13.12.2006 23:13:41
	 * @package htdocs/classes/sys
	 */
	public function is_url($url_string)
	{
		return preg_match(
			'/(http|ftp|https):\/\/([\w-]+\.)+(\/[\w- .\/?%&=]*)?/',
			$url_string);
	}

	/**
	* Return text or if the result row contains more than one field, even return
	* second field
	*
	* @param string $text subject string that shall be parsed
	* @param mixed $param_array All vars that shall be parsed into the text
	* @param string $needle regular expression or null
	* @access public
	* @return string parsed text
	* @author goldstift
	*/
	public function parse($text,$param_array,$needle=null){
		for($i=0;$i<count($param_array);++$i) {
			$ndl = ($needle==null) ? '/%%'.($i+1).'%%/' : $needle;
			// if param is not a number it is prepared for sql-statement as a string
			if(!is_numeric($param_array[$i]))
				$param_array[$i] = $this->prepDbStrng($param_array[$i]);
			$text = preg_replace($ndl, $param_array[$i], $text);
	    }
		return ($text);
	}

	/**
	 * Parses wiki text like formatting into corresponding xhtml tags. Does
	 * ignore <code>-tags (performance reason). Therefore use
	 * parseTagsByAllRegex()
	 *
	 * @param String text to be parsed
	 * @author ahe
	 * @date 29.06.2006 01:06:54
	 * @package htdocs/classes/system
	 */
	public function parseTagsByRegex($text) {
//		$text = $this->parseEmphacisedAndStrongByRegex(stripslashes($text));
		$text = stripslashes($text);
		$text = $this->parseEmphacisedByRegex($text);
		$text = $this->parseStrongByRegex($text);
		$text = $this->parseImagesByRegex($text);
		$text = $this->parseImgTag1ByRegex($text);
		$text = $this->parseImgTag2ByRegex($text);
		$text = $this->parseFileTagThumbByRegex($text);
		$text = $this->parseLinksByRegex($text);
		$text = $this->parseCiteByRegex($text);
		$text = $this->parseQuoteByRegex($text);
		$text = $this->parseH5ByRegex($text);
		$text = $this->parseH4ByRegex($text);
//		$text = $this->parseUlByRegex($text); TODO uncomment if ready to fix bugs
//		$text = $this->parseOlByRegex($text);
		return $text;
	}

	public function parseTagsByAllRegex($text) {
		$text = $this->parseTagsByRegex($text);
		$text = $this->parseCodeByRegex($text);
		return $text;
	}

	public function parseStrongByRegex($text) {
		$pattern = '/\*\*(.+?)\*\*/s';
		$replace = '<strong>$1</strong>';
		return preg_replace($pattern,$replace,$text);
	}

	public function parseUlByRegex($text) {
		$pattern = '/\*[\s]?(.+)[^\s]/';
		$replace = '<li>$1</li>';
		$text = preg_replace($pattern,$replace,$text);

		$pattern = '/(<li>.*<\/li>)/';
//		preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);
//		print_r($matches);
		$replace = '<ul>$1</ul>';
		return preg_replace($pattern,$replace,$text);
	}

	public function parseOlByRegex($text) {
		$pattern = '/\#{1}\s?(.+)\\n/';
		$replace = '<li>$1</li>'."\n";
		$text = preg_replace($pattern,$replace,$text);
		$pattern = '/[^l\>\s](\<li\>.*\<\/li\>)/';
		$replace = '<ol>$1</ol>';
		return preg_replace($pattern,$replace,$text);
	}

	public function parseEmphacisedByRegex($text) {
		$pattern = '/\'\'(.+?)\'\'/';
		$replace = '<em>$1</em>';
		return preg_replace($pattern,$replace,$text);
	}

	public function parseEmphacisedAndStrongByRegex($text) {
		$pattern = '/\'\'\'\'\'(.+?)\'\'\'\'\'/';
		$replace = '<strong class="italic">$1</strong>';
		return preg_replace($pattern,$replace,$text);
	}

	public function parseCiteByRegex($text) {
		$pattern = '/\?\?(.+?)\?\?/s';
		$replace = '<cite>$1<span class="cite_end">&nbsp;</span></cite>';
		return preg_replace($pattern,$replace,$text);
	}

	public function parseQuoteByRegex($text) {
		$pattern = '/:::(.+?):::/s';
		$replace = '<q>$1<span class="quote_end">&nbsp;</span></q>';
		return preg_replace($pattern,$replace,$text);
	}

	public function parseCodeByRegex($text) {
		$pattern = '/\[\[code\]\[(.*?)\]\]/s';
		preg_match_all($pattern,$text,$matches);
		$matchArray = $matches[1];
		foreach ($matchArray as $value) {
			$text_repl = str_replace('<','&lt;',$value);
			$text_repl = str_replace('>','&gt;',$text_repl);
			$text = str_replace($value,$text_repl,$text);
		}
		$replace = '<code>$1</code>';
		return preg_replace($pattern,$replace,$text);
	}

	public function parseH4ByRegex($text) {
		$pattern = '/==(.+?)==[\\r\\n]*/';
		$replace = '<h4>$1</h4>'."\n";
		return preg_replace($pattern,$replace,$text);
	}

	public function parseH5ByRegex($text) {
		$pattern = '/===(.+?)===(\\r\\n)*/';
		$replace = '<h5>$1</h5>'."\n";
		return preg_replace($pattern,$replace,$text);
	}

	public function parseLinksByRegex($text) {
		$pattern1 = '/[^\[|^\]|^\"](http[s]?:\/\/[^\s]+)/';
		$pattern2 = '/\[([^\s]+) (.+?)\]/';
		$replace1 = '<a href="$1">$1</a>';
		$replace2 = '<a href="$1">$2</a>';
		$text = preg_replace($pattern1,$replace1,$text);
		return preg_replace($pattern2,$replace2,$text);
	}

	/**
	 *
	 * @deprecated use {@link #parseImgTag3ByRegex()} instead
	 *
	 * @param String text - string the pattern shall be replaced with an image tag
	 * @return String - the input string with pattern replaced by img-tag
	 * @author ahe
	 * @date 30.12.2006 23:22:19
	 * @package htdocs/classes/sys
	 */
	public function parseImagesByRegex($text) {
		$pattern = '/\[\[img\]([^\s]+)\|(.+?)\]/';
		$replace = '<img src="$1" alt="$2" title="$2" />';
		return preg_replace($pattern,$replace,$text);
	}

//	/**
//	 * Searches and replaces pattern [[IMG:URL|Description with spaces|style-attributes|additional attributes]]
//	 * <em>Example:</em>
//	 * <code>
//	 * [[IMG:/files/get/imgname|alternative text for my image|float:left;margin:1em|longdesc="/files/show/imgname"]]
//	 * </code>
//	 *
//	 * @param String text - string the pattern shall be replaced with an image tag
//	 * @return String - the input string with pattern replaced by img-tag
//	 * @author ahe
//	 * @date 30.12.2006 23:22:19
//	 * @package htdocs/classes/sys
//	 */
//	public function parseImgTag1ByRegex($text) {
//		$pattern = '/\[\[IMG:(.+?)\|(.*?)\|(.*?)\|(.*?)\]\]/';
//		$replace = '<img src="$1" alt="$2" title="$2" style="font-size:inherit;$3" $4 />';
//		return preg_replace($pattern,$replace,$text);
//	}
//
	/**
	 * Searches and replaces pattern [[IMG:URL|Description with spaces|style-attributes]]
	 * <em>Example:</em>
	 * <code>
	 * [[IMG:/files/get/imgname|alternative text for my image|float:left;margin:1em]]
	 * </code>
	 *
	 * @param String text - string the pattern shall be replaced with an image tag
	 * @return String - the input string with pattern replaced by img-tag
	 * @author ahe
	 * @date 30.12.2006 23:22:19
	 * @package htdocs/classes/sys
	 */
	public function parseImgTag1ByRegex($text) {
		$pattern = '/\[\[IMG:(.+?)\|(.*?)\|(.*?)\]\]/';
		$replace = '<img src="$1" alt="$2" title="$2" style="font-size:inherit;$3" />';
		return preg_replace($pattern,$replace,$text);
	}

	/**
	 * Searches and replaces pattern [[IMG:URL|Description with spaces]]
	 * <em>Example:</em>
	 * <code>
	 * [[IMG:/files/get/imgname|alternative text for my image]]
	 * </code>
	 *
	 * @param String text - string the pattern shall be replaced with an image tag
	 * @return String - the input string with pattern replaced by img-tag
	 * @author ahe
	 * @date 30.12.2006 23:22:19
	 * @package htdocs/classes/sys
	 */
	public function parseImgTag2ByRegex($text) {
		$pattern = '/\[\[IMG:(.+?)\|(.*?)\]\]/';
		$replace = '<img src="$1" alt="$2" title="$2" />';
		return preg_replace($pattern,$replace,$text);
	}

	/**
	 * Searches and replaces pattern [[IMG:URL|Description with spaces]]
	 * <em>Example:</em>
	 * <code>
	 * [[IMG:/files/get/imgname|alternative text for my image]]
	 * </code>
	 *
	 * @param String text - string the pattern shall be replaced with an image tag
	 * @return String - the input string with pattern replaced by img-tag
	 * @author ahe
	 * @date 30.12.2006 23:22:19
	 * @package htdocs/classes/sys
	 */
	public function parseFileTagThumbByRegex($text) {
		$pattern='/\[\[FILETHUMB:(.+?)\|(.+?)\]\]/';
		return preg_replace_callback(
			$pattern,
			array(PluginManager::getPlgInstance('ObjectManager'),'createFilesThumbTag') ,
			$text);
	}

	/**
	 * adds <p>-tags, <br/>-tags to the given (string-)value according to the
	 * line breaks. Also replaces '  ' with ' &nbsp;'.
	 *
	 * @param string p_sValue the string to be parsed
	 * @param string p_sAttributes optional attributes for <p>-tag
	 * @return string
	 * @author ahe
	 * @date 01.12.2005 22:56:11
	 */
	public function addParagraphs($p_sValue, $p_sAttributes=null)
 	{
 		// split text by carriage return
 		$paragraphs = explode("<br />\r\n<br />\r\n",nl2br($p_sValue));
    	$retString = '';
		for ($i=0; $i<count($paragraphs); $i++) {
			if(count($paragraphs)>0
			  && mb_strlen($paragraphs[$i])>1
// TODO This has been replace by the following 2 lines. Check whether these are sufficient
//			  && mb_substr($paragraphs[$i],0,1)!='<'
			  && mb_substr($paragraphs[$i],0,1)!='<div'
			  && mb_substr($paragraphs[$i],0,1)!='<h'
			  && mb_substr($paragraphs[$i],0,1)!='<p'
			){
				$retString .= '<p '.$p_sAttributes.'>'.$paragraphs[$i].'</p>'."\n";
			} else {
				$retString .= $paragraphs[$i]."\n";
			}
		}
		$retString = str_replace("  ",' &nbsp;',$retString);
		return $retString;
 	}


	/**
	 * strips given fields from given array and returns remaining array
	 *
	 * @param array $array subject array from which fields shall be stripped
	 * @param array $fields array containing keys to be stripped from $array
	 * @return array array with remaining elements
	 * @author ahe
	 * @date 01.05.2006 16:26:10
	 */
	public function stripArrayFields($array,$fields) {
		foreach($array as $key => $value) {
			if(!in_array($key,$fields)) $retArray[$key] = $value;
		}
		return $retArray;
	}

	/**
	 * strips all fields from given array except the given ones and returns
	 * the remaining array
	 *
	 * @param array $array subject array from which fields shall be stripped
	 * @param array $fields array containing keys to be stripped from $array
	 * @return array array with remaining elements
	 * @author ahe
	 * @date 01.05.2006 16:26:10
	 */
	public function stripArrayFieldsInverse($array,$fields) {
		$retArray = array();
		foreach($array as $key => $value) {
			if(in_array($key,$fields)) {
				$retArray[$key] = $value;
			}
		}
		return $retArray;
	}

/******* END OF    general helper methods             ********/

/******* BEGIN OF  Parsing of global variables        ********/

	/**
	 * searches local arrays of $_POST, $_SERVER or $_GET for given $name.
	 * If no entry is found, the GLOBAL dependants are considered, parsed and
	 * the values safed in local arrays.
	 * Use specific 'get...() ' methods to get their values!
	 *
	 * ATTENTION: This is done for security purpose. External vars are filtered
	 * in one place! Nevertheless special filtering e.g. prepDbStrng() has to be
	 * done when sending data to subsystems like databases or other servers!!
	 *
	 * @author ahe
	 * @date 02.05.2006 22:12:21
	 * @package htdocs/classes/system
	 */
	private function fetchAndParseGlobalParameter($global_array,&$array,$name) {
		if(array_key_exists($name, $global_array)) {
			$array[$this->filter($name)] =
				$this->filter($global_array[$name]);
		}
	}

	/**
	 * takes $_SERVER['REQUEST_URI'] explodes it by ? and parses all
	 * following parameters into an error. ATTENTION: There is NO filtering!!!
	 *
	 * @author ahe
	 */
	public function fetchAdditionalGetParameters() {
	    // if method has already been run, quit here
	    if(self::$isRunFetchAdditionalGetParameters) return false;

	    $parameters = explode('?', $_SERVER['REQUEST_URI']);

	    // if explode results in only 1 element, quit here!
	    if(count($parameters)<2) return false;

	    $parameters = explode('&', $parameters[1]);
	    for ($i = 0; $i < count($parameters); $i++) {
		    $parts = explode('=', $parameters[$i]);
			if(!isset($this->getVars[$this->filter($parts[0])])) {
				$this->getVars[$this->filter($parts[0])] = $this->filter($parts[1]);
			} else {
				$msg = 'Der in der Adressleiste angegebene Parameter "'.$this->filter($parts[0])
					.'" ist reserviert! Ihr Wert: '.$this->filter($parts[1]); // TODO Use dictionary!
				BcmsSystem::raiseNotice($msg, BcmsSystem::LOGTYPE_CHECK,
					BcmsSystem::SEVERITY_WARNING,
					'fetchAdditionalGetParameters()',__FILE__, __LINE__);
			}
		}
		self::$isRunFetchAdditionalGetParameters = true;
		return true;
	}

	/**
	 * searches filtered $_GET-vars for $name and returns it. returns null if
	 * not found
	 *
	 * @param string $name name of $_GET-variable
	 * @return mixed value of $_GET-var OR null!
	 * @author ahe
	 * @date 02.05.2006 22:08:33
	 * @package htdocs/classes/system
	 */
	public function getGetParameter($name) {
		return $this->getFromLocalArray($_GET,$this->getVars,$name);
	}

	/**
	 * searches filtered $_POST-vars for $name and returns it. returns null if
	 * not found
	 *
	 * @param string $name name of $_POST-variable
	 * @return mixed value of $_POST-var OR null!
	 * @author ahe
	 * @date 02.05.2006 22:08:33
	 * @package htdocs/classes/system
	 */
	public function getPostParameter($name) {
		return $this->getFromLocalArray($_POST,$this->postVars,$name);
	}

	/**
	 * searches filtered $_SERVER-vars for $name and returns it. returns null if
	 * not found
	 *
	 * @param string $name name of $_SERVER-variable
	 * @return mixed value of $_SERVER-var OR null!
	 * @author ahe
	 * @date 02.05.2006 22:08:33
	 * @package htdocs/classes/system
	 */
	public function getServerParameter($name) {
		return $this->getFromLocalArray($_SERVER,$this->serverVars,$name);
	}

	/**
	 * searches instance array for $name and returns it. returns null if not
	 * found
	 *
	 * @param array $array reference to array
	 * @param string $name name of $_GET-variable
	 * @return mixed value of $_GET-var OR null!
	 * @author ahe
	 * @date 02.05.2006 22:08:33
	 * @package htdocs/classes/system
	 */
	private function getFromLocalArray($global_array, &$array, $name) {
		if(! array_key_exists($this->filter($name), $array)) {
			$this->fetchAndParseGlobalParameter($global_array,$array,$name);
		}
		if(array_key_exists($this->filter($name),$array))
			return $array[$this->filter($name)];
		else
			return null;
	}

/******* END OF    Parsing of global variables        ********/
/******* BEGIN OF  filter methods for diverse content ********/

	/**
	 * strips bad words, strips tags, converts htmlentities and converts
	 * quote_entities (double quotes) to single quotes
	 *
	 * @param string string to be filtered
	 * @return string the filtered string
	 * @author ahe
	 * @date 18.05.2006 19:51:19
	 * @package htdocs/classes/system
	 */
	public function filter($p_sText) {
		$p_sText = $this->filterTags($p_sText);
		$p_sText = $this->filterMetaInfo($p_sText);
		$p_sText = $this->revertLineBreaks($p_sText);
		return $p_sText;
	}

	/**
	 * used in header
	 *
	 * @param string p_sText meta description string
	 * @return string filtered input string
	 * @author ahe
	 * @date 01.05.2006 16:20:27
	 */
	public function filterMetaDescription($p_sText) {
		$p_sText = $this->filter($p_sText);
	  	$p_sText = $this->filter_badwords($p_sText);
		$p_sText = $this->htmlEntitiyDecode($p_sText);
		$p_sText = stripcslashes($p_sText);
		return $this->cutTextWithSpecialChars($p_sText
			,$this->configInst->meta_desc_length);
	}

	public function filterPageTitle($p_sText) {
		$p_sText = $this->filter($p_sText);
		$p_sText = stripcslashes($p_sText);
		return $p_sText;
	}

	/**
	 * calls filterTags(), filterMetaInfo, replaces " " with - (urlencode
	 * error) and afterwards sets string to lower
	 *
	 * @param string $sInput
	 * @return string filtered input string
	 * @author ahe
	 * @date 01.05.2006 16:25:13
	 * @package htdocs/classes/system
	 */
	public function filterTechName($sInput)
	{
		$sInput = $this->filterTags($sInput);
		$sInput = str_replace(' ','_',$sInput);
		$sInput = $this->stripSpecialChars($sInput);
    	return mb_strtolower($sInput);
	}

	/**
	 * used in init_get_transactions.inc.php
	 */
	public function prepDbStrng($p_sText) {
	  	$p_sText = $this->filterTags($p_sText);
	    $p_sText = $this->revertLineBreaks($p_sText);
	    $p_sText = $this->htmlEntitiyDecode($p_sText);
	    $p_sText = $GLOBALS['db']->quoteSmart($p_sText); // TODO methode in DAL einarbeiten
	    return $p_sText;
	}

	/**
	 * used in init_get_transactions.inc.php
	 *
	 * @param enclosing_method_arguments
	 * @author ahe
	 * @date 01.05.2006 16:14:40
	 * @package htdocs/classes/system
	 */
	public function convStrToCharOnly($p_sText)
	{
		// URGENT convStrToCharOnly() muss noch geschrieben werden!!
		return $p_sText;
	}

	/**
	 * used in cHome->printlatestcontent()
	 *
	 * @param string $p_sText string to be filtered
	 * @param int $length - (optional) length to which string shall be cutted
	 * @return string filtered input string
	 * @author ahe
	 * @date 01.05.2006 17:00:48
	 * @package htdocs/classes/system
	 */
	public function prepareText4Preview($p_sText,$length=null)
	{
		$p_sText = $this->filter($p_sText);
//		$p_sText = $this->filterTags($p_sText);
	  	$p_sText = $this->filter_badwords($p_sText);
	  	if($length!=null) {
			$p_sText = $this->cutTextWithSpecialChars($p_sText,$length);
	  	}
//		$p_sText = $this->revertLineBreaks($p_sText);
		$p_sText = stripslashes($p_sText);
//		$p_sText = $this->filterMetaInfo($p_sText);
		$p_sText = $this->htmlEntitiyDecode($p_sText);
		$p_sText = nl2br($p_sText);
		return $p_sText;
	}

	/**
	 * converts htmlentities
	 *
	 * @param string string to be filtered
	 * @return String - the parsed input string
	 * @author ahe
	 * @date 01.05.2006 16:51:19
	 * @package htdocs/classes/system
	 */
	public function htmlentities($p_sText) {
		$charset = $this->configInst->metaCharset;
		$charset = empty($charset) ? 'UTF-8' : $charset;
		return htmlentities($p_sText,ENT_QUOTES,$charset);
	}

/********************************** OLD FUNCTIONS **************************/

	private function htmlEntitiyDecode($p_sText) {
		$charset = $this->configInst->metaCharset;
		$charset = empty($charset) ? 'UTF-8' : $charset;
		return html_entity_decode($p_sText,ENT_QUOTES,$charset);
	}

	private function filterTags($p_sText) {
	  	$p_sText = strip_tags($p_sText, $this->configInst->allowed_tags);
		return $p_sText;
	}

	private function stripSpecialChars($sInput) {
		$retStr = '';
		$pattern = BcmsSystem::getTechnameRegex();
		for ($i = 0; $i < mb_strlen($sInput); $i++) {
			$char = mb_substr($sInput,$i,1);
			if(preg_match($pattern,$char))	$retStr .= $char;
		}
		return $retStr;
	}

	/**
	 * converts htmlentities, converts quote_entities to single quotes
	 *
	 * @param string string to be filtered
	 * @author ahe
	 * @date 01.05.2006 16:51:19
	 * @package htdocs/classes/system
	 */
	private function filterMetaInfo($p_sText) {
		$p_sText = $this->htmlentities($p_sText);
		$p_sText = $this->remove_ampersent($p_sText);
		$p_sText = str_replace('&quot;','\'',$p_sText);
		return $p_sText;
	}

	/**
	 *  Specialchars zurueck konvertieren
	 *
	 *  ACHTUNG: Die nachfolgenden Zeilen sollen sicherstellen, dass alle vorhandenen
	 *    Sonderzeichen in HTML-schreibweise auch korrekt angezeigt werden.
	 *    Hier entstand ein Problem, da ausnahmslos alle & zu &amp; konvertiert wurden,
	 *    auch die der Sonderzeichen. Dies wird hier nun rueckgaengig gemacht.
	 * @param string inp holds the string in which the ampersents shall be removed
	*/
	private function remove_ampersent($inp)
	{
	    /*
	     * IDEE: Ich suche nach dem ersten auftauchen von "&amp" und dann nach dem folgenden
	     *        ersten Auftauchen eines Semikolons. Diese Positionen merke ich mir.
	     *        Diesen Ausschnitt des Originaltextes speichere ich in einen $tempstring.
	     *        Dort ersetze ich das &amp; durch html_entity_decode().
	     *        Dann speichere ich die L�nge meines "neuen" Strings zwischen und wende
	     *        html_entity_decode() nochmals an. Handelte es sich hierbei um ein gueltiges
	     *        html entity ist mein String nun k�rzer als vorher. Dies kann ich pr�fen.
	     *        falls ja, wird im Originalstring der Teil durch den Textstring ersetzt.
	     *       Nun beginnt die Suche von vorne.
	     */
	    $temp_str = explode('&amp;',$inp);
	    $inp = "";

	    for($i=0;$i<count($temp_str);$i++) {
	      // Suche nach erstem vorkommenden Semikolon...
	      // Speichere seine Position
	      $first_sem_pos = mb_strpos($temp_str[$i],";");

	      $sPossibleEntity = "&".mb_substr($temp_str[$i],0,$first_sem_pos);
	      $iLengthBefore = mb_strlen($sPossibleEntity);
	      $sPossibleEntity = $this->htmlEntitiyDecode($sPossibleEntity);
	      $iLengthAfter = mb_strlen($sPossibleEntity);
	      if($iLengthBefore > $iLengthAfter) {
	        // falls ja, war dies vorher ein HTML-Specialchar...
	        $inp .= "&".$temp_str[$i];
	      } else {
	        // falls nicht, wandle & in Specialchar, wenn schon zweiter Durchlauf!
	        if($i > 0) {
	          $inp .= '&amp;'.$temp_str[$i];
	        } else {
	          $inp .= $temp_str[$i];
	        }
	      }

	    } // end for-loop
	  return $inp;
	}

	  /**
	   * replaces the badwords (defined in config) from the given string with
	   * the config-defined replaceword.
	   * Does not change the parameter input string!
	   *
	   */
	  private function filter_badwords($inp)
	  {
	    // prepare badword list
	    $bword = explode(',',$this->configInst->badwords);
		$rpw = $this->configInst->badwords_replace;
	    // replace all badwords found
	    for($i = 0; $i < count($bword); $i++){
	        $inp = str_replace($bword[$i], $rpw, $inp);
	        $inp = str_replace(mb_strtolower($bword[$i]), $rpw, $inp);
	        $inp = str_replace(mb_strtoupper($bword[$i]), $rpw, $inp);
	        $inp = str_replace(ucfirst($bword[$i]), $rpw, $inp);
	        $inp = str_replace(ucwords($bword[$i]), $rpw, $inp);
	    }
		return $inp;
	  }

	private function revertLineBreaks($text) {
		$text = str_replace('\n',"\n",$text);
		$text = str_replace('\r',"\r",$text);
		return $text;
	}

	/**
	 * searches for incomplete special chars and html tags at the end of the
	 * text
	 *
	 * @param string $p_sText text that has already been cutted off!!!
	 * @author ahe
	 * @date 01.05.2006 16:38:34
	 * @package htdocs/classes/system
	 */
	private function cutTextWithSpecialChars($p_sText,$length) {
		$p_sText = mb_substr($p_sText,0,$length);
		$text_ending = mb_substr($p_sText,-($this->maxSpecialCharLength));
		$first_sem_pos = mb_strpos($text_ending,'&');
		if($first_sem_pos===false){
			$first_sem_pos = mb_strpos($text_ending,'<');
		}
		$offset = ($first_sem_pos===false) ? mb_strlen($p_sText) : -($this->maxSpecialCharLength-$first_sem_pos);
		return mb_substr($p_sText,0,$offset);
	}

	/**
	 * DO NOT USE DIRECTLY!!! Use HTMLTable::getListOffset instead
	 * Used by HTMLTable::getListOffset()
	 *
	 * @param $varname - name of the get variable holding the current page
	 * number
	 * @return integer - the offset in records
	 * @author ahe
	 * @date 22.11.2006 22:29:49
	 * @package _deployed/classes/sys
	 */
	public function getListOffset($pageVarName,$limit=null){
		if($limit==null) $limit = intval($this->getListLimit());
		return (intval($this->getGetParameter($pageVarName)) * $limit);
	}
	public function getListLimit(){
		return intval($this->configInst->max_list_entries);
	}
}

?>