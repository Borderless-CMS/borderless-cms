<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
* Creates javascript to hide and unhide the whole menu section.
*
* \bug TODO doesn't work in IE at the moment!!!
*
* @file menu_hide.inc.php
* @ingroup categories
*/

$link = '<a href="javascript:moveMenu(\'mainmenu\',this);' .
		'moveMenu(\'usermenu\',this);moveMenu(\'adminmenu\',this);" ' .
		'id="showHideMenu" title="Men&uuml;s ein-/ausblenden">' .
		'<img src="' . BcmsConfig::getInstance()->completeSiteUrl .
		'/inc/gfx/silk/arrow_refresh.png" width="16" height="16" ' .
		'alt="'.BcmsSystem::getDictionaryManager()->getTrans('show_hide_menues').'" /></a>';
// \bug URGENT the javascript blend out does not work correctly in IE6
$jscode = '
    <script type="text/javascript">/*<![CDATA[*/

	    function moveMenu(menuid,source)
	    {
	        var element = this.document.getElementById(menuid);
			if(element==null) return;

			if(element.style.display=="none") {
				element.removeAttribute("style");
				this.document.getElementById("menusection").removeAttribute("style");
				this.document.getElementById("allcontent").removeAttribute("style");

			} else {
				element.style.display="none";
				resizeElement(\'allcontent\',\'95%\');
				resizeElement(\'menusection\',\'4%\');
			}
	    }
	    function resizeElement(menuid,size) {
	      var element = this.document.getElementById(menuid);
			element.style.width=size;
	    }
	 /*]]>*/</script>
';
?>
