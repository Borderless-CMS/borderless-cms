          <div id="styleswitcher">
            <form action="" method="get">
              <h2><span>StyleSwitcher</span></h2>
              <div><label for="css">Seitenlayout &auml;ndern
                <select name="css" id="css">
<?php
$handle=opendir(BASEPATH.'inc/css/');
while ($file = readdir ($handle)) {
  if ($file != '.' && $file != '..')
  {
    echo '                  <option value="'.$file.'"';
    if($_SESSION['cssfile'] == $file) echo ' selected="selected"';
    echo '>'.$file.'</option>',"\n";
  }
}
closedir($handle);
?>
                </select></label>
                 <input type="submit" value="ausw&auml;hlen" title="Layout ausw&auml;hlen" />
              </div>
            </form>
          </div>
          <!-- styleswitcher end -->
