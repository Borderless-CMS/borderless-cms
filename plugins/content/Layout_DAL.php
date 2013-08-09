<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * DataAbstraction class for handling article layout data
 *  
 * @todo document this properly
 *
 * @since 0.9
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @date22.09.2005
 * @class Layout_DAL
 * @ingroup content
 * @package content
 */
 class Layout_DAL extends DataAbstractionLayer {

	public $col = array(

		// unique row ID
		'layout_id' => array(
			'type'    => 'integer',
			'require' => true,
			'qf_label' => 'Layout-ID'
		),
		'layoutname' => array(
			'type'    => 'varchar',
			'size'    => 100,
			'qf_label' => 'Layoutname',
			'qf_rules' => array(
				'maxlength' => array(
					'Der Inhalt darf maximal 80 Zeichen lang sein!',
					80
				)
			)
		),
		'filename' => array(
			'type'    => 'varchar',
			'size'    => 100,
			'qf_label' => 'Dateiname (ohne .slt.php)',
			'qf_rules' => array(
				'maxlength' => array(
					'Der Inhalt darf maximal 80 Zeichen lang sein!',
					80
				)
			)
		),
		'description' => array(
			'type'    => 'clob',
			'qf_label' => 'Beschreibung'
		)
	);

	public $idx = array(
		'layout_id' => array(
			'type' => 'unique',
			'cols' => 'layout_id'
		)
	);

	public $sql = array(
		'listall' => array(
			'select' => 'layout_id, layoutname, filename, description',
			'order'  => 'layoutname ASC',
			'fetchmode' => DB_FETCHMODE_ASSOC
			),
			'list_all_fields' => array(),
		'availLayouts' => array()
		);
	public $uneditableElements = array ();
	protected $primaryKeyColumnName = 'layout_id';

	// needed for use of Singleton
	protected static $uniqueInstance = null;


	/* METHOD DEFINITION */

	/**
	 * Get the instance of this class.
	 *
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 * @date 14.10.2006 23:12:41
	 * @since 0.12
	 * @return Layout_DAL
	 */
	public static function getInstance(){
		if(self::$uniqueInstance==null)
			self::$uniqueInstance = new Layout_DAL();
		return self::$uniqueInstance;
	}

	protected function __construct() {
		parent::__construct($GLOBALS['db'],	BcmsConfig::getInstance()->getTablename('layoutpresets'));
		$this->sql['availLayouts'] = array(
				'select' => "lo.layout_id, lo.layoutname, lo.filename",
				'from' => BcmsConfig::getInstance()->getTablename('menu_layout_zo').' as lm_zo, '.
						$this->table.' as lo ',
				'where' => ' lo.layout_id = lm_zo.fk_layout',
				'order' => ' lo.layoutname ASC',
				'fetchmode' => DB_FETCHMODE_ASSOC
		);

		$from_laf = BcmsConfig::getInstance()->getTablename('layout_fieldtype_zo').' as lf_zo,';
		$join_laf = BcmsConfig::getInstance()->getTablename('fieldtypes').' as ftypes ';
		$this->sql['list_all_fields'] = array(
				'select' => 'lf_zo.layout_id, ' .
					'lf_zo.fieldtype_id, lf_zo.ordering_num, lf_zo.preset_value, ' .
					'lf_zo.tech_title, lf_zo.readonly, lf_zo.required, ' .
					'lf_zo.rules, ftypes.form_tag',
				'from' => $from_laf,
				'join' => $join_laf,
				'where' => ' lf_zo.fieldtype_id = ftypes.fieldtype_id',
				'order' => ' lf_zo.ordering_num ASC'
		);
	}

	public function checkSpecialFields(&$p_aCols, $func) {
	}

	/**
	 * fetches the IDs, and names of the available layouts for the current menu
	 */
	public function getAvailableLayouts($cat_id=0)
	{
		if(empty($cat_id)){
			$cat_id = $_SESSION['m_id'];
		}
		foreach ($this->select('availLayouts','lm_zo.fk_cat = '.$cat_id) as $value) {
			$new_array[$value['layout_id']] = $value['layoutname'];
		}
		return $new_array;
	}

	/**
	 * reads the data of the specified layout from the according file in the filesystem
	 *
	 * @return array - array(layout_string with placeholders, layout_css)
	 */
	public function getLayoutDataFromFS($p_iLayoutID)
	{
		if(is_numeric($p_iLayoutID)) {
			$layoutData = $this->select('listall','layout_id = '.$p_iLayoutID);
			require 'layouts/'.$layoutData[0]['filename'].'.slt.php';
			return (array($layout_string, $layout_css));
		} else
			return null;
	}

	public function getLayoutFields($p_iCurrLayout)
	{
		$this->sql['list_all_fields']['fetchmode'] = DB_FETCHMODE_ASSOC;
		return $this->select('list_all_fields', 'lf_zo.layout_id = '.$p_iCurrLayout);
	}

	public function getObject($id) {
		return $this->select('listall','layout_id = '.$id);
	}
}
?>