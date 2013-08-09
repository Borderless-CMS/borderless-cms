<?php
/**
* Regelt saemtliche Datumsumwandlungen
* 
*/
class cDate
{
 	protected $iYear;
 	protected $iMonth;
 	protected $iDay;
 	protected $iHour;
 	protected $iMin;
 	protected $iSec;

  function splitDate($p_sDateString = null)
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
  function getDateAsInt($p_sDate)
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
  function getDateAsStdDate($p_sDateInt)
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

  function getDateAsRSSDate($p_sDateInt=null)
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