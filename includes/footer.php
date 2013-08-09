<?php
/*
 * Created on 03.01.2006
 * TODO use dictionary for these texts!!!
 */
?>
      <div id="footer">
        <div id="copyright"><span><?php
		echo $refBcmsConfig->copyright_linktext;
		?></span></div>  <!-- /copyright -->
<?php
$parser = BcmsFactory::getInstanceOf('Parser');
$gui = Factory::getObject('GuiUtility');
$dict = Factory::getObject('Dictionary');
$reportbug = PluginManager::getPlgInstance('RequestLogManager')->getReportBugLink();
$viewrss = $gui->createDivWithText('class="rss_link"',
	null, $gui->createAnchorTag('/rss.php', 'RSS'));
$recommend_link = 'mailto:somebody@youraddressbook.tld?subject='
	.rawurlencode($dict->getTrans('recommend_subject')).'&amp;body='
	.rawurlencode($parser->getServerParameter('SCRIPT_URI'));
$recommend = $gui->createDivWithText('class="recommend_link"',
	null, $gui->createAnchorTag($recommend_link,
	$dict->getTrans('recommend_site')));
echo $gui->createDivWithText('id="site_features"',
	null, $reportbug.$viewrss.$recommend);
?>
        <div id="skipContent">
          <a href="#shortcuts" id="shortcuts" name="shortcuts" class="unsichtbar" accesskey="z"></a>
          <dl title="accessibility options">
            <dt><a href="#top" accesskey="1" title="Dieser Link bringt Sie immer an den Anfang der aktuellen Seite">zum Anfang</a></dt>
            <dd>Dieser Link bringt Sie immer an den Anfang der aktuellen Seite <span class="unsichtbar">, </span></dd>

            <dt><a href="#mmenu" title="Dieser Link bringt Sie zum Hauptmen&uuml;">Hauptmen&uuml;</a></dt>
            <dd>Dieser Link bringt Sie zum Hauptmen&uuml; <span class="unsichtbar">, </span></dd>

            <dt><a href="#umenu" accesskey="5" title="Dieser Link bringt Sie direkt zum Benutzermen&uuml;">Benutzermen&uuml;</a></dt>
            <dd>Dieser Link bringt Sie direkt zum Benutzermen&uuml; <span class="unsichtbar">, </span></dd>

            <dt><a href="#optcomp" title="Dieser Link bringt Sie direkt zum Bereich mit optionalen Komponenten">Optionale Komponenten</a></dt>
            <dd>Dieser Link bringt Sie direkt zum Bereich mit optionalen Komponenten</dd>
          </dl>
        </div>
<?php
  if($refBcmsConfig->showValidInfos==1)  {
?>
        <dl id="valid_xhtml">
          <dt><a id="val_xhtml" href="http://validator.w3.org/check?uri=http://<?php
	echo $parser->getServerParameter('HTTP_HOST')
		.$parser->getServerParameter('REQUEST_URI');
	  ?>;No200=1" title="validate this xhtml document"><span>XHTML</span></a></dt>
          <dd>Pr&uuml;fen Sie den XHTML-Code unserer Seite auf Validit&auml;t und Standardkonformit&auml;t <span class="unsichtbar">, </span></dd>

          <dt><a id="val_css" href="http://jigsaw.w3.org/css-validator/check/referer" title="validate our CSS file"><span>CSS</span></a></dt>
          <dd>Pr&uuml;fen Sie das CSS (Cascading Style Sheet) unserer Seite auf Validit&auml;t und Standardkonformit&auml;t <span class="unsichtbar">, </span></dd>

          <dt><a id="val_aaa" href="http://webxact.watchfire.com/ScanForm.aspx?ScanURL=http://<?php
	echo $parser->getServerParameter('HTTP_HOST')
		.$parser->getServerParameter('REDIRECT_URL')
		?>" title="validate the accessibility of this document"><span>WAI-AAA</span></a></dt>
          <dd>Pr&uuml;fen Sie die Accessibility (Zug&auml;nglichkeit) unser Seite <span class="unsichtbar">, </span></dd>

          <dt><a id="val_rss" href="http://feedvalidator.org/check.cgi?url=http://<?php
		echo $refBcmsConfig->siteUrl;
		?>/rss.php" title="validate our RSS feed"><span>RSS</span></a></dt>
          <dd>Pr&uuml;fen Sie unser RSS-Feed auf Validit&auml;t und Standardkonformit&auml;t <span class="unsichtbar">, </span></dd>

          <dt><a id="to_borderless" href="http://www.borderless-cms.de/" title="link to BordeRleSS cms homepage"><span>BordeRleSS cms <?=BCMS_VERSION?></span></a></dt>
          <dd>Link zur Homepage von Borderless CMS</dd>
        </dl>
<?php
  }
// Ausgabe der Errorhandling-Variable
if(!empty($_SESSION['system_msg']))	echo BcmsSystem::getSystemMessages();
?>
      </div>  <!-- /footer -->
