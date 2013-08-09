<?php
if (!defined('BORDERLESS'))	exit;
/**
 * BordeRleSS_cms - classes\forms\class.StandardForm.php
 *
 * $Id$
 *
 * This file is part of BordeRleSS_cms.
 *
 * Automatic generated with ArgoUML 0.22 on 02.11.2006, 22:07:19
 *
 * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
 * @package classes
 * @subpackage forms
 */

/**
 * include classes_interfaces_Bcms_View
 *
 * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
 */
require_once('classes/interfaces/interface.Bcms_View.php');

/* user defined includes */
// section -64--88--14-33--6331f96a:10eaa483163:-8000:0000000000000CF6-includes begin
// section -64--88--14-33--6331f96a:10eaa483163:-8000:0000000000000CF6-includes end

/* user defined constants */
// section -64--88--14-33--6331f96a:10eaa483163:-8000:0000000000000CF6-constants begin
// section -64--88--14-33--6331f96a:10eaa483163:-8000:0000000000000CF6-constants end

/**
 * Short description of class classes_forms_StandardForm
 *
 * @abstract
 * @access public
 * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
 * @package classes
 * @subpackage forms
 */
abstract class classes_forms_StandardForm
        implements classes_interfaces_Bcms_View
{
    // --- ATTRIBUTES ---

    /**
     * Short description of attribute formId
     *
     * @access private
     * @var int
     */
    private $formId = 0;

    /**
     * Short description of attribute formManager
     *
     * @access protected
     * @var FormManager
     */
    protected $formManager = null;

    /**
     * Short description of attribute formFields
     *
     * @access protected
     * @var String
     */
    protected $formFields = null;

    /**
     * Short description of attribute formObj
     *
     * @access protected
     * @var QuickForm
     */
    protected $formObj = null;

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access protected
     * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
     * @param int
     * @return classes_void
     */
    protected function __construct($formId)
    {
        // section -64--88--14-33--6331f96a:10eaa483163:-8000:0000000000000D23 begin
        // section -64--88--14-33--6331f96a:10eaa483163:-8000:0000000000000D23 end
    }

    /**
     * Short description of method getFormId
     *
     * @access public
     * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
     * @return classes_int
     */
    public function getFormId()
    {
        // section -64--88--14-33--6331f96a:10eaa483163:-8000:0000000000000D26 begin
        return (int) $this->formId;
        // section -64--88--14-33--6331f96a:10eaa483163:-8000:0000000000000D26 end
    }

    /**
     * Short description of method setFormId
     *
     * @access public
     * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
     * @param int
     * @return classes_void
     */
    public function setFormId($formId)
    {
        // section -64--88--14-33--6331f96a:10eaa483163:-8000:0000000000000CFB begin
        // section -64--88--14-33--6331f96a:10eaa483163:-8000:0000000000000CFB end
    }

    /**
     * Short description of method loadFormFromDb
     *
     * @access protected
     * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
     * @return classes_void
     */
    protected function loadFormFromDb()
    {
        // section -64--88--14-33--6331f96a:10eaa483163:-8000:0000000000000D00 begin
        if($GLOBALS['db']==null) throw new Exception('No database connection present!');
        	
        $sql = '';
        // section -64--88--14-33--6331f96a:10eaa483163:-8000:0000000000000D00 end
    }

    /**
     * Short description of method dataFromUi
     *
     * @access public
     * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
     * @return classes_void
     */
    public function dataFromUi()
    {
        // section -64--88--14-33--6331f96a:10eaa483163:-8000:0000000000000D02 begin
        // section -64--88--14-33--6331f96a:10eaa483163:-8000:0000000000000D02 end
    }

    /**
     * Short description of method dataToUi
     *
     * @access public
     * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
     * @return classes_void
     */
    public function dataToUi()
    {
        // section -64--88--14-33--6331f96a:10eaa483163:-8000:0000000000000D04 begin
        // section -64--88--14-33--6331f96a:10eaa483163:-8000:0000000000000D04 end
    }

    /**
     * Short description of method initWorkArea
     *
     * @access public
     * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
     * @return classes_boolean
     */
    public function initWorkArea()
    {
        $returnValue = (bool) false;

        // section -64--88--14-33--6331f96a:10eaa483163:-8000:0000000000000D06 begin
        // section -64--88--14-33--6331f96a:10eaa483163:-8000:0000000000000D06 end

        return (bool) $returnValue;
    }

    /**
     * Short description of method createStdBtnPanel
     *
     * @access protected
     * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
     * @return java_lang_String
     */
    protected function createStdBtnPanel()
    {
        $returnValue = null;

        // section -64--88--14-33--6331f96a:10eaa483163:-8000:0000000000000D08 begin
        // section -64--88--14-33--6331f96a:10eaa483163:-8000:0000000000000D08 end

        return $returnValue;
    }

    /**
     * Short description of method setStdBtns
     *
     * @access public
     * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
     * @param ButtonList
     * @return classes_void
     */
    public function setStdBtns( classes_datatypes_ButtonList $buttons)
    {
        // section -64--88--14-33--6331f96a:10eaa483163:-8000:0000000000000D0A begin
        // section -64--88--14-33--6331f96a:10eaa483163:-8000:0000000000000D0A end
    }

    /**
     * Short description of method getStdBtns
     *
     * @access public
     * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
     * @return classes_datatypes_ButtonList
     */
    public function getStdBtns()
    {
        $returnValue = null;

        // section -64--88--14-33--6331f96a:10eaa483163:-8000:0000000000000D0D begin
        // section -64--88--14-33--6331f96a:10eaa483163:-8000:0000000000000D0D end

        return $returnValue;
    }

    /**
     * Short description of method loadFormObject
     *
     * @access protected
     * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
     * @return classes_void
     */
    protected function loadFormObject()
    {
        // section -64--88--14-33--6331f96a:10eaa483163:-8000:0000000000000D0F begin
        // section -64--88--14-33--6331f96a:10eaa483163:-8000:0000000000000D0F end
    }

    /**
     * Short description of method createInstance
     *
     * @access public
     * @author Alexander Heusingfeld, <aheusingfeld@borderlesscms.de>
     * @return classes_void
     */
    abstract public static function createInstance();

} /* end of abstract class classes_forms_StandardForm */

?>