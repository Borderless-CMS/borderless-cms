<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * Short description of class classes_interfaces_Bcms_View
 *
 * @since 0.13.182
 * @author ahe <aheusingfeld@borderlesscms.de>
 * @class BcmsView
 * @ingroup datatypes
 * @package datatypes
 */
abstract class BcmsView extends Smarty {

    /**
     * Short description of method dataFromUi
     *
     * @access public
     * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
     * @return void
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