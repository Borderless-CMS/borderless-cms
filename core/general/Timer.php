<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

  /**
   * Funktionen um Zeit zu messen.
   *
   * @author S. Gaffga <webmaster@gaffga.de>, ahe <aheusingfeld@borderless-cms.de>
   *
   * Beispiel:
   *  <code>
   *    $myTimer = new Timer();
   *    $myTimer->StartTimer('myTimerName');
   *    ... zu messende Operation ...
   *    $myTimer->StopTimer('myTimerName');
   *    $timeperiod = $myTimer->GetDuration("dauer");
   *    echo 'The operation took ' . $timeperiod . ' ms';
   *  </code>
   * @class Timer
   * @ingroup general
   * @package general
   */
class Timer
{

    private $timer = array();

	/**
	 * Startet einen neuen Timer unter dem angegebenen Namen.
	 *
	 * @param string $key Name des Timers.
	 *
	 * @access public
	 */
	public function StartTimer($key)
	{
	  	$this->timer[$key . "_start"] = $this->TimerGetTicks();
	}

  /**
   * Stoppt den angegebenen Timer.
   *
   * @param string $key Name des Timers.
   *
   * @access public
   */
  public function StopTimer($key)
  {
    $this->timer[$key . "_stop"] = $this->TimerGetTicks();
  }

  /**
   * Liefert die Laufzeit des angegebenen Timers in Millisekunden. Bevor diese
   * Funktion aufgerufen werden kann mu� {@link timerStop()} aufgerufen werden.
   *
   * @param string $key Name des Timers.
   * @return double Laufzeit des Timers in Millisekunden.
   *
   * @access public
   */
  public function GetDuration($key)
  {
    $d = $this->timer[$key . "_stop"] - $this->timer[$key . "_start"];
    $d = intval($d * 1000);
    return $d / 1000.0;
  }

  /**
   * Liefert die Ticks nach denen die Zeit gemessen wird in Millisekunden.
   *
   * @return double Die Ticks in Millisekunden
   *
   * @access protected
   */
  public function TimerGetTicks()
  {
    $a = gettimeofday();
    $t = $a["sec"] + $a["usec"] / 1000000.0;
    return $t;
  }

}
?>