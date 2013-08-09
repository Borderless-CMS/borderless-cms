<?
  /**
   * Funktionen um Zeit zu messen.
   *
   * @author S. Gaffga, ahe <aheusingfeld@borderless-cms.de>
   * @package tools
   *
   * Beispiel:
   *
   *  <?
   *    timerStart("dauer");
   *    ... zu messende Operation ...
   *    timerStop("dauer");
   *    $dauer = timerGetDuration("dauer");
   *    echo "Die Operation dauerte $dauer ms <br>\n";
   *  ?>   *
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
  function StartTimer($key)
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
  function StopTimer($key)
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
  function GetDuration($key)
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
  function TimerGetTicks()
  {
    $a = gettimeofday();

    $t = $a["sec"] + $a["usec"] / 1000000.0;

    return $t;
  }

}
?>