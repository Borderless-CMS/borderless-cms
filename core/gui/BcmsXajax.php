<?php
require('xajax/xajax.inc.php');
/**
 * Test class to test behaviour of xajax
 * ATTENTION: Not implemented yet! This will probably never go into productional use!
 * 
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @date 10.10.2006
 * @class BcmsXajax
 * @ingroup _main
 */
class BcmsXajax extends xajax {
	
	public function printFormActionDisabler($formId){
		echo $this->getFormActionDisabler($formId);
	}
	
	public function getFormActionDisabler($formId){
		return '<script type="text/javascript">' .
			'document.getElementById(\''.$formId.'\').action="javascript:void(null)";' .
			'</script>';
	}

	public function getWaitMessageScript($elementId){
		return '<script type="text/javascript"><!--
            xajax.loadingFunction = 
                function(){'             	
                	.'xajax.$(\''.$elementId.'\').innerHTML="Loading...";'
                	.'xajax.$(\''.$elementId.'\').disabled=true;'
                .'};
            function hideLoadingMessage(){'
            	.'xajax.$(\''.$elementId.'\').innerHTML ="Finished!";'
                .'xajax.$(\''.$elementId.'\').disabled=false;'
            .'}
            xajax.doneLoadingFunction = hideLoadingMessage;
        // --></script>';
	}
	
}
?>
