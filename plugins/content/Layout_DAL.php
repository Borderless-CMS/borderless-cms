<?php
/**
 * Created on 22.09.2005
 * @author ahe
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
			'order'  => 'layoutname ASC'
		),
		'list_all_fields' => array(),
		'availLayouts' => array()
	);
	public $uneditableElements = array ();


/* METHOD DEFINITION */

	public function __construct() {
		parent::__construct($GLOBALS['db'],
			BcmsConfig::getInstance()->getTablename('layoutpresets'));
		$this->sql['availLayouts'] = array(
				'select' => "lo.layout_id, lo.layoutname, lo.filename",
				'from' => BcmsConfig::getInstance()->getTablename('menu_layout_zo').' as lm_zo, '.
						$this->table.' as lo ',
				'where' => ' lo.layout_id = lm_zo.fk_layout',
				'order' => ' lo.layoutname ASC'
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
		$where = null;
		if(empty($cat_id)){
			$where = 'lm_zo.fk_cat = '.$_SESSION['m_id'];
		}
		foreach ($this->select('availLayouts',$where) as $value) {
			$new_array[$value[0]] = $value[1];

		}
		return $new_array;
	}

	/**
	 * fetches the IDs of the available layouts for the current menu
	 */
	public function getLayoutDataFromFS($p_iLayoutID)
	{
		if(is_numeric($p_iLayoutID)) {
			$layoutData = $this->select('listall','layout_id = '.$p_iLayoutID);
				$layoutfile = 'includes/layouts/'.$layoutData[0][2].'.slt.php';
			require $layoutfile;
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
		$this->sql['listall']['fetchmode'] = DB_FETCHMODE_ASSOC;
		return $this->select('listall','layout_id = '.$id);
	}
}
?>