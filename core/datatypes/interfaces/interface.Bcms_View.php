<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * @todo NOT IMPLEMENT YET!!!
 * @todo document this!
 * 
 * Automatic generated with ArgoUML 0.22 on 02.11.2006, 21:20:06
 *
 * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
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
 * @class classes_interfaces_Bcms_View
 * @ingroup datatypes
 * @package datatypes
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