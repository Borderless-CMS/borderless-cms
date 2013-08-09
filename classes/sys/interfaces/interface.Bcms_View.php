<?php

error_reporting(E_ALL);

/**
 * BordeRleSS_cms - classes\interfaces\interface.Bcms_View.php
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
// section -64--88--14-33-5141416e:10e00ce2df2:-8000:00000000000008BA-includes begin
// section -64--88--14-33-5141416e:10e00ce2df2:-8000:00000000000008BA-includes end

/* user defined constants */
// section -64--88--14-33-5141416e:10e00ce2df2:-8000:00000000000008BA-constants begin
// section -64--88--14-33-5141416e:10e00ce2df2:-8000:00000000000008BA-constants end

/**
 * Short description of class classes_interfaces_Bcms_View
 *
 * @access public
 * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
 * @package classes
 * @subpackage interfaces
 */
interface classes_interfaces_Bcms_View
{
    // --- OPERATIONS ---

    /**
     * Short description of method dataFromUi
     *
     * @access public
     * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
     * @return classes_void
     */
    public function dataFromUi();

    /**
     * Short description of method dataToUi
     *
     * @access public
     * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
     * @return classes_void
     */
    public function dataToUi();

    /**
     * Short description of method initWorkArea
     *
     * @access public
     * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
     * @return classes_boolean
     */
    public function initWorkArea();

} /* end of interface classes_interfaces_Bcms_View */

?>