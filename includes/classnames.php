<?php /*
+----------------------------------------------------------------------------+
| B O R D E R L E S S   C M S                                                |
+----------------------------------------------------------------------------+
| (c) Copyright 2004 - 2006                                                  |
|      by goldstift (aheusingfeld@borderlesscms.de)                          |
+----------------------------------------------------------------------------+
*/
if(!defined('BORDERLESS')) exit;

/**
 * In $GLOBALS['bcms_classes'] the path to every class of the cms is given.
 * This file may be supplemented automatically by new plugins
 *
 * @module classnames.php
 * @author ahe <aheusingfeld@borderless-cms.de>
 * @package init
 * @version $Id$
 */
$GLOBALS['bcms_classes'] = array(
// PEAR classes
	'DB'		=> 'pear/DB.php',
// DB
	'DataAbstractionLayer' => 'db/DataAbstractionLayer.php',

// datatypes
	// exceptions
		'ProtectedConstructorException' => 'general/datatypes/exceptions/ProtectedConstructorException.php',
		'UnknownClassException' => 'general/datatypes/exceptions/UnknownClassException.php',
		'SendMailFailedException' => 'general/datatypes/exceptions/SendMailFailedException.php',
	'BcmsAction' => 'general/datatypes/BcmsAction.php',
	'BcmsIcon' => 'general/datatypes/BcmsIcon.php',
	'BcmsList' => 'general/datatypes/BcmsList.php',
	'BcmsObject' => 'general/datatypes/BcmsObject.php',
	// alte Klassen --> unbedingt ueberarbeiten!!!
	'cDate' 	=> 'general/datatypes/cDate.php',

// gui
	'cForm' 		=> 'gui/class.cForm.php',
	'HTMLTable' => 'gui/HTMLTable.php',
	'GuiUtility' 			=> 'gui/GuiUtility.php',
// plugins
	'AbstractManager'	=> 'plugins/AbstractManager.php',
	'PluginManager'		=> 'plugins/PluginManager.php',
// sys
	'BcmsConfig' 	=> 'sys/BcmsConfig.php',
	'BcmsSystem' => 'sys/BcmsSystem.php',
	'BcmsFactory' 	=> 'sys/BcmsFactory.php',
	'Parser' => 'sys/Parser.php',
//	'Timer' => 'sys/Timer.php',
	'SingleObjectPattern' => 'sys/SingleObjectPattern.php',
	//interfaces
		'Singleton' => 'sys/interfaces/interface.Singleton.php',
		'Controller' => 'sys/interfaces/interface.Controller.php',
		'Bcms_View' => 'sys/interfaces/interface.Bcms_View.php',
		'ActionListener' => 'sys/interfaces/interface.ActionListener.php',
	// alte Klassen --> unbedingt ueberarbeiten!!!
	'Factory' 	=> 'sys/Factory.php',
	'cHome'		=> 'plugins/home/class.cHome.php',

/* --------- automatically added by plugins --------- */
	'CategoryManager' => 'general/categories/CategoryManager.php',
	'Dictionary' => 'plugins/dictionary/Dictionary.php',
// content
	'BcmsArticle'		=> 'plugins/content/BcmsArticle.php',
	'Layout_DAL'		=> 'plugins/content/Layout_DAL.php',
	// alte Klassen --> unbedingt ueberarbeiten!!!
	'cArticleLayout'	=> 'plugins/content/class.cArticleLayout.php',
	'ContentManager' => 'plugins/content/ContentManager.php',
// users
	'cGroup' => 'plugins/user/class.cGroup.php',
	'UserSession' => 'plugins/user/UserSession.php',
	'RightManager' => 'plugins/user/RightManager.php',
	'UserManager' => 'plugins/user/UserManager.php'
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