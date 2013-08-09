<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * This is a customer exit for changing the default templates of
 * PEARs HTML_QuickForm to a more XHTML and WCAG friendly style.
 *
 * @date 13.09.2005
 * @file quickform_templates.inc.php
 * @author ahe <aheusingfeld@borderlesscms.de>
 * @ingroup gui
 */

   /**
	* Header Template string
	* @var      string
	* @access   private
	*/
	$this->_headerTemplate =
		"\n\t".'<div class="qfHeader">'."\n" .
		"\t\t".'<h4 class="qfHeader">{header}</h4>'."\n" .
		"\t".'</div>';

   /**
	* Element template string
	* @var      string
	* @access   private
	*/
	$this->_elementTemplate =
		"\n\t".'<div class="qfElement">'."\n" .
		"\t\t".'<label class="qfLabel"><span class="qfLabelText">{label}' .
		'<!-- BEGIN required --><acronym title="'
		.BcmsSystem::getDictionaryManager()->getTrans('qf.requiredTT').'">*</acronym>' .
		'<!-- END required --></span>'."\n".
		"\t\t\t".'<!-- BEGIN error --><span class="qfError">{error}</span><!-- END error -->'."\n" .
		"\t\t\t".'<span class="qfLabelValue">{element}</span></label>   <!-- /.qfLabel -->'."\n" .
		"\t".'</div>   <!-- /.qfElement -->';

   /**
	* Form template string
	* @var      string
	* @access   private
	*/
	$this->_formTemplate =
		"\n".'<form{attributes}>'."\n" .
		'<div class="qfSurrounder">'."\n" .
		'{hidden}<div  class="qfBase">'."\n" .
		'{content}'."\n" .
		'</div>   <!-- /.qfBase --> '."\n" .
		'</div>'."\n" .
		'</form>';

   /**
	* Required Note template string
	* @var      string
	* @access   private
	*/
	$this->_requiredNoteTemplate =
		"\n\t".'<div class="qfRequiredNote">'."\n" .
		"\t\t".'<span class="qfRequiredNote">* '
		.BcmsSystem::getDictionaryManager()->getTrans('qf.required').'</span>'."\n" .
		"\t</div>";

   /**
	* Array containing the templates for customised elements
	* @var      array
	* @access   private
	*/
	$this->_templates = array();

   /**
	* Array containing the templates for group wraps.
	*
	* These templates are wrapped around group elements and groups' own
	* templates wrap around them. This is set by setGroupTemplate().
	*
	* @var      array
	* @access   private
	*/
	$this->_groupWraps = array();

   /**
	* Array containing the templates for elements within groups
	* @var      array
	* @access   private
	*/
	$this->_groupTemplates = array();

   /**
	* True if we are inside a group
	* @var      bool
	* @access   private
	*/
	$this->_inGroup = false;

   /**
	* Array with HTML generated for group elements
	* @var      array
	* @access   private
	*/
	$this->_groupElements = array();

   /**
	* Template for an element inside a group
	* @var      string
	* @access   private
	*/
	$this->_groupElementTemplate = '';

   /**
	* HTML that wraps around the group elements
	* @var      string
	* @access   private
	*/
	$this->_groupWrap = '';

   /**
	* HTML for the current group
	* @var      string
	* @access   private
	*/
	$this->_groupTemplate = '';

?>
