<?php

error_reporting(E_ALL);

/**
 * BordeRleSS_cms - classes\interfaces\interface.ActionListener.php
 *
 * $Id$
 *
 * This file is part of BordeRleSS_cms.
 *
 * Automatic generated with ArgoUML 0.22 on 02.11.2006, 21:20:06
 *
 * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
 * @package classes
 * @subpackage interfaces
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

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
 * @package classes
 * @subpackage interfaces
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