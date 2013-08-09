<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * Contains global arrays $bcms_classes and $bcms_templates
 *
 * @file classnames.php
 * @author ahe <aheusingfeld@borderless-cms.de>
 * @ingroup init
 */

/**
 * In $GLOBALS['bcms_classes'] the path to every class of the cms is given.
 * This file may be supplemented automatically by new plugins
 *
 * @author ahe <aheusingfeld@borderless-cms.de>
 */
$GLOBALS['bcms_classes'] = array(
// PEAR classes
	'DB'		=> 'pear/DB.php',
// DB
	'DataAbstractionLayer' => 'db/DataAbstractionLayer.php',

// datatypes
	//interfaces
		'Singleton' => 'datatypes/interfaces/interface.Singleton.php',
		'Controller' => 'datatypes/interfaces/interface.Controller.php',
		'Bcms_View' => 'datatypes/interfaces/interface.Bcms_View.php',
		'ActionListener' => 'datatypes/interfaces/interface.ActionListener.php',
	// exceptions
		'BcmsException' => 'datatypes/exceptions/BcmsException.php',
		'MissingConfigFileException' => 'datatypes/exceptions/MissingConfigFileException.php',
		'PearDbErrorException' => 'datatypes/exceptions/PearDbErrorException.php',
		'ProtectedConstructorException' => 'datatypes/exceptions/ProtectedConstructorException.php',
		'UnknownClassException' => 'datatypes/exceptions/UnknownClassException.php',
		'SendMailFailedException' => 'datatypes/exceptions/SendMailFailedException.php',
	'BcmsAction' => 'datatypes/BcmsAction.php',
	'BcmsIcon' => 'datatypes/BcmsIcon.php',
	'BcmsList' => 'datatypes/BcmsList.php',
	'BcmsObject' => 'datatypes/BcmsObject.php',
	// alte Klassen --> unbedingt ueberarbeiten!!!
	'cDate' 	=> 'datatypes/cDate.php',
// general
// gui
	'cForm' 		=> 'gui/class.cForm.php',
	'HTMLTable' => 'gui/HTMLTable.php',
	'GuiUtility' 			=> 'gui/GuiUtility.php',
// plugins
	'AbstractManager'	=> 'plugins/AbstractManager.php',
	'PluginManager'		=> 'plugins/PluginManager.php',
	'CategoryManager' => 'plugins/categories/CategoryManager.php',
	'Dictionary' => 'plugins/dictionary/Dictionary.php',
	'UserManager' => 'plugins/user/UserManager.php',
	'UserSession' => 'plugins/user/UserSession.php',
	'RightManager' => 'plugins/user/RightManager.php',
	'UserManager' => 'plugins/user/UserManager.php',
	// sys
	'BcmsConfig' 	=> 'sys/BcmsConfig.php',
	'BcmsSystem' => 'sys/BcmsSystem.php',
	'BcmsFactory' 	=> 'sys/BcmsFactory.php',
	'Parser' => 'sys/Parser.php',
	'SingleObjectPattern' => 'sys/SingleObjectPattern.php'
);



/* ***** TEMPLATES ***** */

$GLOBALS['bcms_templates'] = array(
// paragraph templates
'div_tpl' => '<div %%1%%>%%2%%</div>  <!-- /%%1%% -->'."\n",
'span_tpl' => '<span %%2%%>%%1%%</span>'."\n",
'clearDiv_tpl' => '<div %%1%% style="clear:both">%%2%%</div>  <!-- /%%1%% -->'."\n",
'p_tpl' => '<p %%1%%>%%2%%</p>'."\n",

// table templates
'table_tpl' => '%%3%%<table %%1%%>'."\n".'%%3%%%%2%%'."\n".'%%3%%</table>  <!-- /%%1%% -->'."\n",
'tr_tpl' => '%%3%%<tr %%1%%>'."\n".'%%2%%'."\n".'%%3%%</tr>'."\n",
'td_tpl' => '<td %%1%%>%%2%%</td>'."\n",
'th_tpl' => '<th %%1%%>%%2%%</th>'."\n",

// form templates
'fieldset_tpl' => '%%4%%<fieldset %%1%%>'."\n".'%%4%%<legend>%%2%%</legend>'
	."\n".'%%4%%%%3%%'."\n".'%%4%%</fieldset>'."\n",
'label_tpl' => '<label for="%%1%%" %%3%%>%%2%%</label>'."\n",
'input_tpl' => '<input type="%%1%%" name="%%2%%" id="%%2%%" value="%%3%%" %%4%%/>'."\n",
'select_tpl' => '%%3%%<select %%1%%>'."\n".'%%2%%</select>'."\n",
'option_tpl' => '%%3%%<option %%1%%>%%2%%</option>'."\n",
'form_tpl' => '
      <form id="%%1%%" action="%%2%%"
method="%%3%%" enctype="%%4%%" %%6%%>
%%5%%
      </form>  <!-- /%%1%% -->
',
'object_form_tpl' => '
      <h%%1%% %%2%%>%%3%%</h%%1%%>
      <form id="%%4%%" action="%%5%%"
method="%%6%%" enctype="%%7%%" %%9%%>
%%8%%
      </form>  <!-- /%%4%% -->
',
'img_tpl' => '<img %%1%% />'
);
?>