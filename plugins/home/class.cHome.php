<?php
/**
* IMPORTANT: There are only those articles shown which are
* located in a menu that is marked as "viewable==1"
* (Comments are not restricted)!
*/
class cHome {
	/* If the selection form shall not be printed reduce the number of
	*  array elements to 1. This one will automatically be selected and
	*  the form won't be printed!
	*
	*  When you delete or add an array element, be sure to also delete
	*  or add the according function in the "class.cHome.php" file!!!
	*/
	private $sel_latest_type_array =
		array('latestarticles','latestcomments','mostviewedarticles'
		,'mostcommentedarticles');

	private $sel_latest_names_array =
		array('Neueste Beitr&auml;ge','Neueste Kommentare'
		,'Meistbesuchte Artikel','Meistkommentierte Artikel');


	function __construct() {
		$this->articleDAL = PluginManager::getPlgInstance('ContentManager')->getArticleDalObj();
	}

	/**
	* Controller-function for "latestcontent" output.
	*
	* @access public
	*/
	public function latestContent()
	{
		if((PluginManager::getPlgInstance('UserManager')->getLogic()->isLoggedIn())
			|| !(BcmsConfig::getInstance()->showTop5UserOnly) )
		{
			echo '          <div id="latestContent">',"\n";
			echo '            <h2 class="menuheader"><span>Top '
				.BcmsConfig::getInstance()->no_of_latestentries.'</span></h2>'."\n";

			$latestType = $this->getLatestType();
			// selection form is only printed if there is more than 1 type selectable
			if(count($this->sel_latest_type_array)>1)
				$this->printLatestForm($latestType);

			$this->printLatestContent($latestType);
			echo "          </div>  <!-- /latestContent -->\n";
		}
	}

	/**
	* Prints out the <form> for the type selection of the latest articles.
	*
	* @access public
	* @return
	*/
	function printLatestForm($latestType)
	{
		echo '            <form id="latestForm" action="'.BcmsFactory::getInstanceOf('Parser')->getServerParameter('SCRIPT_URL')
						.'" method="post"'
						.' enctype="application/x-www-form-urlencoded">'
						.'

							<div id="latest_type">
				<label for="sel_latest_type">Welche Top'
				.BcmsConfig::getInstance()->no_of_latestentries
				.' m&ouml;chten Sie sehen?
								<select name="sel_latest_type" id="sel_latest_type" onchange="submit()">',"\n";
		for($i=0;$i<count($this->sel_latest_type_array);$i++)
		{
			echo "                    <option value=\"".$this->sel_latest_type_array[$i]."\"";
			if($latestType == $this->sel_latest_type_array[$i])
				echo " selected=\"selected\"";
			echo ">".$this->sel_latest_names_array[$i]."</option>\n";
		}
		echo '                </select></label>',"\n",'              </div>
						</form>',"\n";
	}

	function printLatestContent($latestType)
	{
		// get resultset

		if(method_exists($this, $latestType))
		{
			$row = $this->$latestType();
		} else {
			$msg = "The \$_POST-var \"sel_latest_type\"" .
				" in cHome holds the manipulated value \"$latestType\"! This is " .
				"not a proper value.\nThe request has been send from " .
				BcmsFactory::getInstanceOf('Parser')->getServerParameter('SCRIPT_URL').":"
				.BcmsFactory::getInstanceOf('Parser')->getServerParameter('REMOTE_PORT')
				."!"; // TODO Use dictionary!
			BcmsSystem::raiseNotice($msg, BcmsSystem::LOGTYPE_SELECT,
				BcmsSystem::SEVERITY_ERROR, 'printLatestContent()',__FILE__, __LINE__);
			die('Invalid Request!');
		}

		$dictObj = Factory::getObject('Dictionary');
		$guiObj = Factory::getObject('GuiUtility');
		$parser = BcmsFactory::getInstanceOf('Parser');
		// print content
		for($j=0;$j<count($row);$j++)
		{
			// get content and author
			$content_id = $row[$j][0];
			$latestheader = $row[$j][1];

			// prevent xhtml conformness even if there's no header!
			if(mb_strlen($latestheader)==0) $latestheader = '&nbsp;';
			$dateObject = new cDate();

			$content = $row[$j][2];
			$datum = $dateObject->getDateAsStdDate($row[$j][3]);
			$author = $row[$j][4];
			$categoryname = $row[$j][5];
			$hits_or_comments = $row[$j][6];
			if($latestType == 'mostcommentedarticles') {
				$h_o_c_text = ' '.$dictObj->getTrans('comments');
			} else {
				$h_o_c_text = ' '.$dictObj->getTrans('sr.Hits');
			}

			echo '            <!-- LatestContent-Output'.($j+1).' -->'."\n";
			echo "            <div id=\"latestcont".($j+1)."\" class=\"latestcont\">\n";
			echo $guiObj->createHeading(3,$latestheader,14,'latestheader');
			echo '              <div class="latestcontent"><span>';
			// Ueberlange Worte (>30Zeichen) werden umgebrochen
			// Datenbank-Output wird nochmals von evtl. Fehlern gereinigt
			echo $parser->prepareText4Preview($content
			,BcmsConfig::getInstance()->preview_length).'...<br />'."\n";
			echo "                </span>";
			$hiddenMore = ' <span class="unsichtbar">('.$latestheader.')</span>';
			echo $guiObj->createAnchorTag('/'.$categoryname.'/show/'.$content_id,
				$dictObj->getTrans('more_w_brackets').$hiddenMore,
				14,null,0,$latestheader),"\n";
?>              </div>  <!-- /latestcontent -->
							<div class="latestinfo">
<?php
			echo $guiObj->createAuthorName($author,16);
			echo $guiObj->createDivWithText(' class="date"',null,$datum,16);
			echo $guiObj->createDivWithText(' class="hits_or_comments"',null,$hits_or_comments.$h_o_c_text,16);
			echo "              </div>  <!-- /latestinfo -->
						</div>  <!-- /latestcont".($j+1)." -->\n\n";
		}
		unset($row, $content, $parser,$guiObj,$hiddenMore,$latestheader,$dictObj);
	}

	/**
	* Liefert den ausgewaehlten TOP5-Typ zurueck (selected_latest_type)
	*
	* @access private
	* @return string
	*/
	function getLatestType()
	{
		// Wenn POST-VAR nicht gesetzt, default-wert zurueckgeben
		if(!isset($_POST['sel_latest_type']))
		{
			return 'latestarticles';
		}
		else
		{
			// der explode verhindert hier das Einschleusen von ganzen Dateinamen oder
			//  Adressen was zu einem Code-Injection-Bug fuehren koennte
			$returnwert = explode(".",
				BcmsFactory::getInstanceOf('Parser')->getPostParameter('sel_latest_type'));
			return $returnwert[0];
		}
	}
/* END OF GENERAL FUNCTIONS */


/* BEGIN OF TYPE-VALUE DEPENDENT (DATA FETCHING) FUNCTIONS */

	/**
	* Liesst das SQL fuer die neuesten Artikel aus der Datenbank und setzt es
	* ab. Das zurueckgelieferte Resultset wird uebergeben.
	*
	* @author goldstift
	*/
	function latestarticles()
	{
		return $this->articleDAL->getLatestArticles();
	}

	/**
	* Liesst das SQL fuer die neuesten Kommentare aus der Datenbank und setzt es
	* ab. Das zurueckgelieferte Resultset wird uebergeben.
	*
	* @author goldstift
	*/
	function latestcomments()
	{
		return $this->articleDAL->getLatestComments();
	}

	/**
	* Liesst das SQL fuer die meistbesuchten Artikel aus der Datenbank und setzt es
	* ab. Das zurueckgelieferte Resultset wird uebergeben.
	*
	* @author goldstift
	*/
	function mostviewedarticles()
	{
		return $this->articleDAL->getMostViewedArticles();
	}

	/**
	* Liesst das SQL fuer die meistkommentierten Artikel aus der Datenbank und setzt es
	* ab. Das zurueckgelieferte Resultset wird uebergeben.
	*
	* @author goldstift
	*/
	function mostcommentedarticles()
	{
		return $this->articleDAL->getMostCommentedArticles();
	}
}
?>