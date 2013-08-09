<?php
if (!defined('BORDERLESS'))	exit;
/**
 * BordeRleSS_cms - classes\forms\class.StdMaintenanceForm.php
 *
 * $Id$
 *
 * This file is part of BordeRleSS_cms.
 *
 * Automatic generated with ArgoUML 0.22 on 02.11.2006, 21:42:41
 *
 * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
 */

/**
 * include classes_forms_StandardForm
 *
 * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
 */
require_once('classes/forms/class.StandardForm.php');

/**
 * include classes_interfaces_ActionListener
 *
 * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
 */
require_once('classes/interfaces/interface.ActionListener.php');

/* user defined includes */
// section -64--88--14-33-5141416e:10e00ce2df2:-8000:00000000000008DB-includes begin
// section -64--88--14-33-5141416e:10e00ce2df2:-8000:00000000000008DB-includes end

/* user defined constants */
// section -64--88--14-33-5141416e:10e00ce2df2:-8000:00000000000008DB-constants begin
// section -64--88--14-33-5141416e:10e00ce2df2:-8000:00000000000008DB-constants end

/**
 * Short description of class classes_forms_StdMaintenanceForm
 *
 * @abstract
 * @access public
 * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
 * @class classes_forms_StdMaintenanceForm
 * @ingroup gui
 * @package gui
 */
abstract class classes_forms_StdMaintenanceForm
    extends classes_forms_StandardForm
        implements classes_interfaces_ActionListener
{
    // --- ATTRIBUTES ---

    /**
     * Short description of attribute menuButtonList
     *
     * @access private
     * @var ButtonList
     */
    private $menuButtonList = null;

    // --- OPERATIONS ---

    /**
     * Short description of method performAction
     *
     * @access public
     * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
     * @param BcmsAction
     * @return classes_void
     */
    public function performAction( classes_datatypes_BcmsAction $action)
    {
        // section -64--88--14-33-5141416e:10e00ce2df2:-8000:0000000000000A0A begin
        // section -64--88--14-33-5141416e:10e00ce2df2:-8000:0000000000000A0A end
    }

    /**
     * Short description of method createMenuBar
     *
     * @access public
     * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
     * @return classes_void
     */
    public function createMenuBar()
    {
        // section -64--88--14-33-5141416e:10e00ce2df2:-8000:00000000000008E3 begin
        // section -64--88--14-33-5141416e:10e00ce2df2:-8000:00000000000008E3 end
    }

    /**
     * Short description of method setMenuBtns
     *
     * @access public
     * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
     * @param ButtonList
     * @return classes_void
     */
    public function setMenuBtns( classes_datatypes_ButtonList $btnList)
    {
        // section -64--88--14-33-5141416e:10e00ce2df2:-8000:00000000000008E5 begin
        // section -64--88--14-33-5141416e:10e00ce2df2:-8000:00000000000008E5 end
    }

    /**
     * Short description of method getMenuBtns
     *
     * @access public
     * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
     * @return classes_datatypes_ButtonList
     */
    public function getMenuBtns()
    {
        $returnValue = null;

        // section -64--88--14-33-5141416e:10e00ce2df2:-8000:00000000000008E8 begin
        // section -64--88--14-33-5141416e:10e00ce2df2:-8000:00000000000008E8 end

        return $returnValue;
    }

    /**
     * Short description of method performAction
     *
     * @access public
     * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
     * @param void
     * @return classes_void
     */
    public function performAction( classes_void $Action)
    {
        // section -64--88--14-33-5141416e:10e00ce2df2:-8000:00000000000008EA begin
        // section -64--88--14-33-5141416e:10e00ce2df2:-8000:00000000000008EA end
    }

    /**
     * Short description of method createInstance
     *
     * @abstract
     * @access public
     * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
     * @return classes_void
     */
    public static abstract function createInstance();

} /* end of abstract class classes_forms_StdMaintenanceForm */

?>