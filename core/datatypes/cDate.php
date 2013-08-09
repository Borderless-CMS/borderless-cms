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
 * @since 0.6
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @class cDate
 * @ingroup datatypes
 * @package datatypes
 */
class cDate
{
 	protected $iYear;
 	protected $iMonth;
 	protected $iDay;
 	protected $iHour;
 	protected $iMin;
 	protected $iSec;

  protected function splitDate($p_sDateString = null)
  {
    // Wenn Parameterlaenge ungleich 19, dann handelt es sich nicht um einen Timestampwert
    if(strlen($p_sDateString)!=19)
    {
      return false;
    } else {
      $this->iYear = mb_substr($p_sDateString,0,4);
      $this->iMonth = mb_substr($p_sDateString,5,2);
      $this->iDay = mb_substr($p_sDateString,8,2);
      $this->iHour = mb_substr($p_sDateString,11,2);
      $this->iMin = mb_substr($p_sDateString,14,2);
      $this->iSec = mb_substr($p_sDateString,17,2);
      return true;
    }  
  }

  /**
  * Zieht aus dem uebergebenen "DATETIME"-String alle Zahlwerte heraus und
  * haengt sie hintereinander. Ergebnis: Datum als Zahl ohne Trennzeichen.
  *
  * @param $thisDate
  * @access public
  * @return integer
  */
  public function getDateAsInt($p_sDate)
  { 
    if($this->splitDate($p_sDate))
    {
      $retDate = $this->iYear;
      $retDate .= $this->iMonth;
      $retDate .= $this->iDay;
      $retDate .= $this->iHour;
      $retDate .= $this->iMin;
      $retDate .= $this->iSec;
      return $retDate;
    } else {
      return false;
    }    
  }
  
  /**
  * Wandelt den uebergebenen "DATETIME"-String aus dem amerikanischen Datumsformat
  * in das europaeische/ deutsche Format mit Punkten um.
  *
  * @param $thisDateInt
  * @access public
  * @return string
  */
  public function getDateAsStdDate($p_sDateInt)
  {
    $retDate = BcmsConfig::getInstance()->date_format;
    $thisDateInt = $this->splitDate($p_sDateInt);
    
    $retDate = str_replace('d',$this->iDay,$retDate);
    $retDate = str_replace('m',$this->iMonth,$retDate);
    $retDate = str_replace('Y',$this->iYear,$retDate);
    $retDate = str_replace('H',$this->iHour,$retDate);
    $retDate = str_replace('i',$this->iMin,$retDate);
    $retDate = str_replace('s',$this->iSec,$retDate);
    return $retDate;
  }

  public function getDateAsRSSDate($p_sDateInt=null)
  {
	if($p_sDateInt==null)
	{ 
    	return date('r',time());
	} 
	else {
    
	    $thisDateInt = $this->splitDate($p_sDateInt);
	    return date('r',mktime($this->iHour,$this->iMin,$this->iSec
	    	,$this->iMonth,$this->iDay,$this->iYear));
	} 
  }
}
?>