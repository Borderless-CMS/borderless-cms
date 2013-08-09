<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * @file interface.ActionListener.php
 * @todo NOT IMPLEMENT YET!!!
 * @todo document this!
 * 
 * Automatic generated with ArgoUML 0.22 on 02.11.2006, 21:20:06
 * 
 * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
 */


/* user defined includes */
// section -64--88--14-33-5141416e:10e00ce2df2:-8000:0000000000000A08-includes begin
// section -64--88--14-33-5141416e:10e00ce2df2:-8000:0000000000000A08-includes end

/* user defined constants */
// section -64--88--14-33-5141416e:10e00ce2df2:-8000:0000000000000A08-constants begin
// section -64--88--14-33-5141416e:10e00ce2df2:-8000:0000000000000A08-constants end

/**
 * Short description of class classes_interfaces_ActionListener
 *
 * @access public
 * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
 * @class classes_interfaces_ActionListener
 * @ingroup datatypes
 * @package datatypes
 */
interface classes_interfaces_ActionListener
{
    // --- OPERATIONS ---

    /**
     * Short description of method performAction
     *
     * @access public
     * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
     * @param BcmsAction
     * @return classes_void
     */
    public function performAction( classes_datatypes_BcmsAction $action);

} /* end of interface classes_interfaces_ActionListener */

?>