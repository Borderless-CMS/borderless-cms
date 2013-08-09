<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 * 
 * @date 10.07.2005
 * @since 0.9
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @class cForm
 * @ingroup gui
 * @deprecated
 */
class cForm extends GuiUtility
 {
 	private $currForm = null;

 	function cForm()
 	{
 	}

 	/**
  * Builds a div holding a form field and the according label into a string
  * Used @ printContentList and printContentArticle
  *
  * @param string $p_sType optional, default=text can be text, hidden, radio, checkbox
  * @param string $p_sInputNameAndDivCssId the name of the form field and the id of the surrounding div tag
  * @param string $p_sValue optional, default=null holds the preset value of the form field
  * @param integer $p_iNumOfSpaces optional, default value 0
  * @param boolean $p_sLabelText Holds the label text information
  * @param boolean $p_bReadOnly shall the form element be readonly?
  * @param boolean $p_bChecked shall the form element be checked?
  * @access private
  * @return void
 	 */
 	public function addElement($p_sType, $p_sInputNameAndDivCssId, $p_sValue, $p_iNumOfSpaces = 0, $p_sLabelText=null, $p_bReadOnly=false, $p_bChecked=false)
 	{
 		switch($p_sType)
 		{
 			case "text":
 			case "checkbox":
 			case "hidden":
 			case "button":
 			case "reset":
 			case "submit":
			case "radio":
				$this->CreateInputWithLabel($p_sInputNameAndDivCssId, $p_sValue, $p_sType
					, $p_iNumOfSpaces, $p_sLabelText, $p_bReadOnly, $p_bChecked);
				break;
 			case "textarea":
				$this->CreateTextareaWithLabel($p_sInputNameAndDivCssId, $p_sValue
					, $p_iNumOfSpaces, $p_sLabelText, $p_bReadOnly);
				break;
 			case "select":
				break;
 		}
 	}

	public function printForm()
	{
		return $this->currForm;
	}

	public function addText($p_sText)
	{
		$this->currForm .= $p_sText;
	}

  /**
  * Builds a div holding a form field and the according label into a string
  * Optional parameters are the number of spaces for text-indent and the label text
  * can be given.
  * Used @ printContentList and printContentArticle
  *
  * @param string $p_sInputNameAndDivCssId the name of the form field and the id of the surrounding div tag
  * @param string $p_sValue optional, default=null holds the preset value of the form field
  * @param string $p_sType optional, default=text can be text, hidden, radio, checkbox
  * @param string $p_sLabelText Holds the label text information
  * @param integer $p_iNumOfSpaces optional, default value 0
  * @access private
  * @return void
  */
  private function CreateInputWithLabel($p_sInputNameAndDivCssId, $p_sValue, $p_sType="text", $p_iNumOfSpaces = 0, $p_sLabelText=null, $p_bReadOnly=false, $p_bChecked=false)
  {

    $this->currForm .= $this->createSpaces($p_iNumOfSpaces); // Spaces
    $this->currForm .= "<div id=\"".$p_sInputNameAndDivCssId."\">\n";

    if($p_sLabelText != null)
    {
      $this->currForm .= $this->createSpaces($p_iNumOfSpaces+2); // Spaces
      $this->currForm .= "<label for=\"".$p_sInputNameAndDivCssId."\">".$p_sLabelText."</label>\n";
    }

    $this->currForm .= $this->createSpaces($p_iNumOfSpaces+2); // Spaces
    $this->currForm .= "<input name=\"".$p_sInputNameAndDivCssId."\" type=\"".$p_sType."\" value=\"".$p_sValue."\"";
    if($p_bChecked) $this->currForm .= " checked=\"checked\"";
    if($p_bReadOnly) $this->currForm .= " readonly";
    $this->currForm .= " />\n";

    $this->currForm .= $this->createSpaces($p_iNumOfSpaces); // Spaces
    $this->currForm .= "</div>  <!-- /".$p_sInputNameAndDivCssId." -->\n";
  }

  /**
  * Builds a div holding a form field and the according label into a string
  * Optional parameters are the number of spaces for text-indent and the label text
  * can be given.
  * Used @ printContentList and printContentArticle
  *
  * @param string $p_sInputNameAndDivCssId the name of the form field and the id of the surrounding div tag
  * @param string $p_sValue optional, default=null holds the preset value of the form field
  * @param string $p_sLabelText Holds the label text information
  * @param integer $p_iNumOfSpaces optional, default value 0
  * @access private
  * @return string
  */
  private function CreateTextareaWithLabel($p_sInputNameAndDivCssId, $p_sValue, $p_iNumOfSpaces = 0, $p_sLabelText=null, $p_bReadOnly=false, $p_iRows=8, $p_iCols=25)
  {
    $this->currForm .= $this->createSpaces($p_iNumOfSpaces); // Spaces
    $this->currForm .= "<div id=\"".$p_sInputNameAndDivCssId."\">\n";

    if($p_sLabelText != null)
    {
      $this->currForm .= $this->createSpaces($p_iNumOfSpaces+2); // Spaces
      $this->currForm .= "<label for=\"".$p_sInputNameAndDivCssId."\">".$p_sLabelText."</label>\n";
    }

    $this->currForm .= $this->createSpaces($p_iNumOfSpaces+2); // Spaces
    $this->currForm .= "<textarea name=\"".$p_sInputNameAndDivCssId."\"" .
    		" rows=\"".$p_iRows."\" cols=\"".$p_iCols."\" ";
    if($p_bReadOnly) $this->currForm .= " readonly=\"readonly\"";
	$this->currForm .= ">".$p_sValue."</textarea>\n";

    $this->currForm .= $this->createSpaces($p_iNumOfSpaces); // Spaces
    $this->currForm .= "</div>  <!-- /".$p_sInputNameAndDivCssId." -->\n";
  }

  /**
   * Creates a string including a html form start-tag.
   * @param integer $target DEPRECATED! (actually boolean) Was used to insert target to links which is not xhtml strict
   */
  public function addHeader($p_sFormNameAndCssId,$p_sAction, $p_iNumOfSpaces=0, $p_sEnctype=null)
  {
	if($p_sEnctype===null) $p_sEnctype = BcmsConfig::getInstance()->default_form_enctype;

	$this->currForm .= $this->createSpaces($p_iNumOfSpaces);
	$this->currForm .= "<form id=\"".$p_sFormNameAndCssId."\" action=\""
		.$p_sAction."\" method=\"post\" enctype=\"".$p_sEnctype."\">\n";
  }

  public function addBottom($p_iNumOfSpaces=0)
  {
	$this->currForm .= $this->createSpaces($p_iNumOfSpaces);
	$this->currForm .= "</form>\n";
  }
 }
?>