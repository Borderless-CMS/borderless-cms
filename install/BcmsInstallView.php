<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

include('inc/smarty/libs/Smarty.class.php');

/**
 * Borderless CMS Installer class. Cares about install and upgrade process.
 *
 * @since 0.13.162
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @date 13.03.2007
 * @class BcmsInstallView
 * @ingroup installer
 * @package installer
 */
class BcmsInstallView extends Smarty {
	
	public function __construct() {
		parent::__construct();
		
		$this->compile_check = true;
		$this->config_dir = BASEPATH.'inc/config/';
		$this->template_dir = BASEPATH.'install/';
		$this->compile_dir = BASEPATH.'templates_c/';
		$this->cache_dir = BASEPATH.'cache/';
	}
	
	/**
	 * sets values to form fields
	 * 
	 * @param array $data - associative array with form field => value
	 * @since 0.13.179
	 * @author ahe <aheusingfeld@borderlessscms.de>
 	 * @date 13.03.2007
	 */
	public function setFormData($data){
		$form_fields = $this->getFormFieldDefinitions($data); 
		$this->assign('form_fields',$form_fields);
	}
	
	/**
	 * displays (really means output!!!) the installer view
	 * @param String $processName - Heading of current process
	 * @param integer $step - number of installation step/ page (default=1)
	 * @return void
	 * @since 0.13.179
	 * @author ahe <aheusingfeld@borderlessscms.de>
 	 * @date 13.03.2007
	 */
	public function display($processName, $step=1){
		$page['title'] = 'Borderless CMS '.BCMS_VERSION.' - ' . $processName . ' - Step ' 
									. $step;
		$page['cssUrl'] = 'installer.css';
		$page['metaAuthor'] = '';
		$page['metaDescription'] = '';
		$page['metaKeywords'] = '';
		$page['robots'] = 'noindex, nofollow';
		$this->assign('page',$page);
		$this->assign('welcome',$page['title']);
		$legends = array('system' => 'System settings', 'db' => 'Database settings');
		$this->assign('legends', $legends);
		
		parent::display('install_view.tpl');
	}
	
	/**
	 * defines form fields for installation config form
	 *
	 * @param array $values - Example: $values['fieldname']['value'] = 'some value'
	 * @return array - $fields['fieldname'] = array('label' => ..., 'default' => ..., 'comment' => ..., 'value' => ...)
	 * @author ahe
	 * @date 15.05.2007
	 * @since 0.13.170 
	 */
	private function getFormFieldDefinitions($values) {
		$form_fields=array(
		    'dbType'=>array(
		        'label'     =>'Database type',
		        'default'   => $this->get_template_vars('supported_db_types'),
		        'comment'   =>''
		    ),
		    'dbServer'=>array(
		        'default'   =>'localhost',
		        'label'     =>' Hostname (for Database Server)',
		        'comment'   =>''
		    ),
		    'dbUser'=>array(
		        'default'   =>'root',
		        'label'     =>'Username (for Database)',
		        'comment'   =>''
		    ),
		    'dbPass'=>array(
		        'default'   =>'',
		        'label'     =>'Password (for Database) ',
		        'comment'   =>''
		    ),
		    'dbDatabase'=>array(
		        'default'   =>'bcms',
		        'label'     =>'Name of database',
		        'comment'   =>''
		    ),
		    'adm_email'=>array(
		        'default'   => '',
		        'label'     => 'Email of Tech-Admin (e.g. for DB errors)',
		        'comment'   => ''
		    ),
		    'table_prefix'=>array(
		        'default'   => '',
		        'label'     => 'Prefix for all db tables (e.g. "bcms_")',
		        'comment'   => ''
		    ),
		    'siteUrl'=>array(
		        'default'   => $_SERVER['SERVER_NAME'],
		        'label'     => 'URL of your site',
		        'comment'   => 'Example: "www.my-site.com/bcms-subfolder". No starting "http://"! No trailing slash!',
		    ),
		    'offlineMessage' => array(
		        'default'   => '',
		        'label'     => 'Message that shall be shown when system is "down" for maintenance',
		        'comment'   => ''
		    ),
		    'user_admin_password' => array(
		        'default'   => '',
		        'label'     => 'Borderless CMS admin password',
		        'comment'   => ''
		    ),
		    'confirm_backup_self_accountable' => array(
		        'default'   => 'on',
		        'label'     => 'By clicking this, I confirm that some of the db data may be overwritten.',
		        'comment'   => ''
		    ),
		);
		foreach ($values as $field => $value) {
			$form_fields[$field]['value'] = $value;
		}
		return $form_fields;
	}

}

?>