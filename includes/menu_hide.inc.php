<?php /*
* B O R D E R L E S S   C M S
*  (c)  Copyright 2004-2006 by goldstift (mail@goldstift.de / www.goldstift.de)
*
*/
if(!defined('BORDERLESS')) exit;
$link = '<a href="javascript:moveMenu(\'mainmenu\',this);' .
		'moveMenu(\'usermenu\',this);moveMenu(\'adminmenu\',this);" ' .
		'id="showHideMenu" title="Men&uuml;s ein-/ausblenden">' .
		'<img src="/gfx/silk/arrow_refresh.png" width="16" height="16" ' .
		'alt="'.Factory::getObject('Dictionary')->getTrans('show_hide_menues').'" /></a>';
// URGENT the javascript blend out does not work correctly in IE6
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
