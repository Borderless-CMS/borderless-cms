<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * Generates xhtml code for a table
 * ATTENTION: You have to use the following call order of the methods
 *
 * Example:
 * <code from="plugins/dictionary/Dictionary.php">
 * 		$tableObj = new HTMLTable('dict_table');
        $tableObj->setTranslationPrefix('dict.');
		$tableObj->setActions($this->actions);
		$tableObj->setBounds('page',null,$this->dalObj->getNumberOfEntries());
		$limit = $tableObj->getListLimit();
		$offset = $tableObj->getListOffset();

		// prepare searching
		list($searchphrase,$offset,$limit) = $tableObj->setSearchBehaviour(true);
		$trans = $this->dalObj->getList($offset,$limit,null,$searchphrase);
		$tableObj->setData($trans);
		unset($trans);

		return $tableObj->render($this->dalObj->getTrans('dict.heading'),
				'dict_id',true);
 * </code>
 * @todo transfer this howto into online docomentation!
 *
 * @since 0.10
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @class HTMLTable
 * @ingroup gui
 * @package gui
 */
class HTMLTable
{

	private $table = array(
		'id'	=> null,
		'class'	=> null,
		'attributes' => null
	);
	private $formId = null;
	private $searchEnabled = false;
	private $searchphrase = null;
	private $actions = array();
	private $dataset = array();
	private $keys = array();
	private $translatedKeys = array();
	private $caption = null;
	private $translationPrefix = '';
	private $keysTranslated = false;
	private $parserObj = null;
	private $guiObj = null;
	private $offset = 0;
	private $offsetVarname=null;
	private $limit = null;
	private $noOfAllRecords=0;

	public function __construct($id, $class=null, $attributes=null) {
		$this->table = array(
			'id'	=> $id,
			'class'	=> 'sortable '.$class,
			'attributes' => $attributes
		);
		$this->parserObj = BcmsSystem::getParser();
		$this->guiObj = BcmsFactory::getInstanceOf('GuiUtility');
		$this->limit = $this->setListLimit(0);
		$this->formId = 'table_action_choose_form_'.$this->table['id'];
	}

	/**
	 * Set offset varname, limit and number of all records in one effort
	 *
	 * @param String $offsetVarname name of the GET var holding the offset value
	 * @param int limit a integer value; number of elements on a page
	 * @param int $noOfAllRecords number of all records in current table
	 * @author ahe
	 * @date 24.11.2006 22:19:47
	 * @since 0.14.172
	 */
	public function setBounds($offsetVarname,$limit,$noOfAllRecords){
		$this->setListLimit($limit);
		$this->setListOffsetByVarname($offsetVarname);
		$this->setNumberOfAllRecords($noOfAllRecords);
	}

	/**
	 * Get caption of current table
	 *
	 * @return String
	 * @author ahe
	 * @date 24.11.2006 22:19:31
	 * @since 0.14.172
	 */
	public function getCaption(){
		return $this->caption;
	}

	/**
	 * Set caption of current table
	 *
	 * @param $caption - String
	 * @author ahe
	 * @date 24.11.2006 22:19:47
	 * @since 0.14.172
	 */
	public function setCaption($caption){
		$this->caption = $caption;
	}

	/**
	 * Get caption of current table
	 *
	 * @return String
	 * @author ahe
	 * @date 24.11.2006 22:19:31
	 * @since 0.14.172
	 */
	public function getNumberOfAllRecords(){
		return $this->noOfAllRecords;
	}

	/**
	 * Set number of all records of current table
	 *
	 * @param $noOfAllRecords - String
	 * @author ahe
	 * @date 24.11.2006 22:19:47
	 * @since 0.14.172
	 */
	public function setNumberOfAllRecords($noOfAllRecords){
		$this->noOfAllRecords = $noOfAllRecords;
	}

	/**
	 * <strong>ATTENTION:</strong> Uses setListLimit() to preserve a valid limit
	 *
	 * @since 0.13.143
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 */
	public function getListLimit(){
		return $this->setListLimit($this->limit);
	}

	/**
	 * Sets the limit for returned records. Specifiy -1 if system standard limit
	 * shall be used.
	 *
	 * @since 0.13.143
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 */
	public function setListLimit($limit){
		if($limit==null) $limit = $this->parserObj->getListLimit();
		return $this->limit = $limit;
	}

	/**
	 * Initializes offset with system default value, if offset has not yet been
	 * set.
	 *
	 * @return int - the currently defined list offset
	 * @since 0.13.143
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 */
	public function getListOffset(){
		if($this->offset<0) // initialize with std. values
			$this->setListOffsetByVarname();
		return $this->offset;
	}

	/**
	 * Sets environment parameters for searching
	 *
	 * @param boolean switch - shall search beaviour switched on or off
	 * @return Array - array($searchphrase,$offset,$limit)
	 * @author ahe
	 * @date 16.12.2006 22:29:44
	 * @since 0.13.178
	 */
	public function setSearchBehaviour($switchOn){
		$this->searchEnabled = $switchOn;
		if($switchOn){
			$this->searchphrase = $this->getSearchPhrase();
			// if searchphrase is specified, don't limit list!
			if(!empty($this->searchphrase)) {
				$this->limit = null;
				$this->offset= null;
				$this->setSearchPhrase($this->searchphrase);
			}
		}
		return array($this->searchphrase,$this->offset,$this->limit);
	}

	/**
	 * Set params for search
	 *
	 * @param String searchphrase - the string to be searched for
	 * @author ahe
	 * @date 16.12.2006 00:32:02
	 * @since 0.13.176
	 */
	public function setSearchPhrase($searchphrase)
	{
		$this->searchphrase = $searchphrase;
	}

	/**
	 * Set the current offset by specifying the name of the GET-variable holding
	 * the current page number.
	 * ATTENTION: setListLimit() has to be used first!!!
	 *
	 * @param String $varName - name of the GET variable; default='page'
	 * @since 0.13.143
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 */
	public function setListOffsetByVarname($varName='page'){
		$this->offsetVarname = $varName;
		$this->offset = $this->parserObj->getListOffset($varName,$this->getListLimit());
	}

	/**
	 * Method to set, whether the array keys of the set data have already
	 * been translated?
	 *
	 * @param boolean translated array keys of the set data already translated?
	 * @since 0.13.143
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 */
	public function setKeysAlreadyTranslated($translated=false){
		$this->keysTranslated = $translated;
	}

	/**
	 * expects $data in format array( nr => array('fieldname' => 'value'))
	 * INFO: The translationPrefix will not be used for arraykeys starting with
	 * '-'. That way you can specify keys where prefix shall not be used!
	 *
	 * @param array $data - associative array with column names as keys and
	 * record data as values
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 */
	public function setData($data) {
		$this->dataset = $data;
		if(!empty($this->dataset)){
			$this->keys = array_keys($this->dataset[0]);
		} else {
			$this->keys = array();
		}
	}

	/**
	 * takes a string as prefix
	 *
	 * @param String prefix the prefix for the column translations
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 */
	public function setTranslationPrefix($prefix) {
		$this->translationPrefix = $prefix;
	}

	/**
	 * expects $actions in format array( 0 => array(actionname, translation,
	 * type)
	 * Example:
	 * $actions = array(
	 * 		0	 => array('edit', 'editieren', 'single'),
	 * 		1	 => array('delete', 'l�schen', 'multi')
	 * );
	 *
	 * @@todo replace parameter with Action object
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 */
	public function setActions($actions) {
		$this->actions = $actions;
	}

/** BEGINNING of LOGIC SECTION **/

	protected function createCheckAllJavaScript(){
return '<script type="text/javascript"><!--
var checkAll = true;

function deActivateAll (tableId, startnum,endnum) {
	var currentForm = this.document.getElementById(tableId);
  	endnum = (endnum>0) ? endnum : currentForm.length;
  	for (i = startnum; i < endnum; i++) {
    	currentForm.elements[i].checked = checkAll
  	}
  	checkAll = (checkAll) ? false : true;
}//--></script>'."\n";
	}

	private function translateHeaders($keycolumn=null){
		if(!$this->keysTranslated){
			$prefix = $this->translationPrefix;
			for ($i = 0; $i < sizeof($this->keys); $i++) {
				if($this->keys[$i]==$keycolumn) continue;
				if(mb_substr($this->keys[$i],0,1)=='-')
					$this->translatedKeys[$i] =
						BcmsSystem::getDictionaryManager()->getTrans(mb_substr($this->keys[$i],1));
				else
					$this->translatedKeys[$i] = BcmsSystem::getDictionaryManager()->getTrans($prefix.$this->keys[$i]);
			}
		} else {
			$this->translatedKeys = $this->keys;
		}

	}

	public function render($heading, $keycolumn, $createForm=false) {
		if(count($this->dataset)<1) {
			$msg = 'Tabelle "'.$heading.'" ist leer!'; // @todo use dictionary
			BcmsSystem::raiseNotice($msg, BcmsSystem::LOGTYPE_CHECK,
					BcmsSystem::SEVERITY_INFO, 'render()',
					__FILE__, __LINE__);
		}
		// if searchphrase present, display all results on one page
		if(!empty($this->searchphrase))
		{
			$this->setBounds('xxx',count($this->dataset), count($this->dataset));
		}
		$this->translateHeaders($keycolumn);

		$this->setCaption($heading);
		$tableFoot = $this->createTableFoot($keycolumn,$createForm);
		$tableHead = $this->createTableHeader($keycolumn,$createForm);

		$tableBody = $this->createTableBody($keycolumn,$createForm);
		$tableContent = $tableHead.$tableFoot.$tableBody;

		// create the table tag
		$attrib = ' id="'.$this->table['id'].'"';
		if($this->table['class']!=null)
			$attrib .= ' class="'.$this->table['class'].'"';
		if($this->table['attributes']!=null)
			$attrib .= $this->table['attributes'];

		$table =
			$this->guiObj->fillTemplate('table_tpl',array($attrib, $tableContent, null))."\n";

		$div_attrib =
			' id="div_'.$this->table['id'].'" class="table_surrounder"';
		$div_surrounder =
			$this->guiObj->fillTemplate('div_tpl',array($div_attrib, $table, null))."\n";
		$div_surrounder = '<script type="text/javascript" src="'
							.BcmsConfig::getInstance()->completeSiteUrl
							.'/inc/sortable.js"></script>'."\n".$div_surrounder;
		if($createForm) {
			$retValue = $div_surrounder;
			$js = $this->createCheckAllJavaScript();

			$retValue = $this->guiObj->fillTemplate('form_tpl', array(
					$this->formId,
					BcmsSystem::getParser()->getServerParameter('REQUEST_URI')
					,'post','application/x-www-form-urlencoded'
					,$retValue,'class="table_action"'));
			$retValue .= $js;
		} else {
			$retValue = $div_surrounder;
		}
		if($this->searchEnabled){
			$retValue = $this->guiObj->createSearchForm($this->table['id'],
							$this->searchphrase).$retValue;
		}
		return $retValue;
	}

	private function createTableHeader($keycolumn,$createForm) {
		if($createForm) {
			$th = '<th>&nbsp;</th>';
		} else {
			$th = '';
		}

		for ($i = 0; $i < count($this->keys); $i++) {
			$attrib = ' id="'.$this->table['id'].'_th'.($i+1).'"';
			if($this->keys[$i] != $keycolumn) {
				$th .= $this->guiObj->fillTemplate('th_tpl',
					array($attrib, $this->translatedKeys[$i]));
			}
		}
		$thTR = 	$this->guiObj->fillTemplate('tr_tpl'
			,array('id="'.$this->table['id'].'_tr1" class="odd_th"', $th, null));
		$caption = (empty($this->caption)) ? '' : '<caption>'.$this->caption.'</caption>'."\n";
		$thTR = $caption.'<thead>'."\n".$thTR.'</thead>'."\n";
		return $thTR;
	}

	/**
	 *
	 *
	 * @param keycolumn - String defining the columnname of the primary key
	 * @return String - <tfoot>-Tag with content
	 * @author ahe
	 * @date 24.11.2006 21:32:16
	 */
	protected function createTableFoot($keycolumn,$createForm){
		$footerContent = '';
		if($createForm) {
			if(count($this->actions)<1)
				throw new Exception('Actions have not yet been set. Please use HTMLTable::setActions()!'); // @todo use dictionary
			$action = $this->createActionSelect();

			// create cell with check box
			$checkbox = '<input type="checkbox" name="'.
				$this->table['id'].'_checkall" id="'.
				$this->table['id'].'_checkall" ' .
				'onChange="deActivateAll(\''.$this->formId.'\',1,0)"/>';
			$attrib = ' id="'.$this->table['id'].'_td_checkbox_checkall'
				.'" class="td_checkbox_checkall"';
			$footerContent .= $this->guiObj->fillTemplate('th_tpl',array($attrib,$checkbox));

		} else {
			$action = '';
		}
		$noOfCols = count($this->keys);
		$spanCheckbox = 1;
		$spanFirst=floor($noOfCols*2/3)-$spanCheckbox;
		$spanSecond=$noOfCols-$spanFirst-$spanCheckbox;

		$footerContent .= $this->guiObj->fillTemplate('td_tpl'
			,array('class="action_select_td" colspan="'.$spanFirst.'"'
			, $action
			))."\n";

		$footerContent .= $this->getPageSwitcherWidget(true,$spanSecond);
		$tr = 	$this->guiObj->fillTemplate('tr_tpl',array(null, $footerContent, null));
		return '<tfoot>'."\n".$tr.'</tfoot>'."\n";
	}

	/**
	 * Creates a "page switcher"-gui element that bases on internal values
	 *
	 * @param boolean createTd - shall the widget be surrounded by a <td>-tag?
	 * @param int colspan - if specified, it has to be >=0; 0 for no colspan
	 * @return String - the widget
	 * @author ahe
	 * @date 24.11.2006 23:41:08
	 */
	private function getPageSwitcherWidget($createTd=false,$colspan=-1){
		if(empty($this->pageSwitcherWidget)){
			$this->pageSwitcherWidget = $this->guiObj->getPageControlField(
				$this->offsetVarname,$this->limit,$this->noOfAllRecords);

		}
		if($createTd && $colspan>=0){
			$colspan = ($colspan>0) ? 'colspan="'.$colspan.'"' : '';
			$returnValue = $this->guiObj->fillTemplate('td_tpl'
				,array('class="turn_page_td" align="right" '.$colspan
				, $this->pageSwitcherWidget
			))."\n";
		} else {
			$returnValue = $this->pageSwitcherWidget;
		}
		return $returnValue;
	}

	/**
	 * processes all table rows and creates the tbody-element/ -tag
	 *
	 * @param keycolumn - String defining the columnname of the primary key
	 * @return String - <tbody>-Tag with content
	 * @author ahe
	 * @date 24.11.2006 21:32:16
	 */
	protected function createTableBody($keycolumn,$createForm){
		// process table rows
		return '<tbody>'."\n"
				.$this->processTableRows($this->table['id'],$keycolumn,$createForm)
				.'</tbody>'."\n";
	}

	private function processTableRows($t_id,$keycolumn,$createForm){
		$tr = '';
		for ($i = 0; $i < count($this->dataset); $i++) {
			$td = '';
			if($createForm) {
				// create cell with check box
				$checkbox = '<input type="checkbox" name="elemid_' .
					$this->dataset[$i][$keycolumn].'" id="elemid_' .
					$this->dataset[$i][$keycolumn].'"/>';
				$attrib = ' id="'.$t_id.'_td'.($i+2).'_checkbox'
					.'" class="'.$t_id.'_td'.($i+2).' col'.($i+2).'"';
				$td .= $this->guiObj->fillTemplate('td_tpl',array($attrib,$checkbox));
			}

			// process table cells
			for($k=0; $k<count($this->dataset[$i]); $k++) {
				// create cell
				if($this->keys[$k] != $keycolumn) {
					// create cell attribute string
					$attrib = ' id="'.$t_id.'_td'.($i+2).'_'.($k+1)
						.'" class="'.$t_id.'_td'.($i+2).' col'.($i+2).'"';
					$td .= $this->guiObj->fillTemplate('td_tpl'
						,array($attrib,$this->dataset[$i][$this->keys[$k]]));
				}
			}
			$css= ($i%2==1) ? 'odd' : 'even'; // for line differentiating
			$attr = 'class="'.$t_id.'_tr'.($i+2).' '.$css.'"';
			$tr .= 	$this->guiObj->fillTemplate('tr_tpl',array($attr, $td, null));
		}
		return $tr;
	}

	private function createActionSelect() {
		if(count($this->actions)<1) return '';

		$options = '';
		for ($i = 0; $i < sizeof($this->actions); $i++) {
			$options .= $this->guiObj->fillTemplate('option_tpl'
				,array(
					' value="'.$this->actions[$i][0].'"',
					$this->actions[$i][1],
					null));

		}
		$selectattribs = ' name="table_action_select_'.$this->table['id']
			.'" id="table_action_select_'.$this->table['id'].'"';
		$label = $this->guiObj->fillTemplate('label_tpl'
			,array('table_action_select_'.$this->table['id']
			, BcmsSystem::getDictionaryManager()->getTrans('choose_action')
			, null))."\n";
		$select = $this->guiObj->fillTemplate('select_tpl'
			,array($selectattribs, $options, null))."\n";
		$submit = $this->guiObj->fillTemplate('input_tpl',
			array('submit', 'action_chosen_'.$this->table['id'],
			BcmsSystem::getDictionaryManager()->getTrans('choose'),
			null))."\n";
		$action_div = $this->guiObj->fillTemplate('div_tpl'
			,array('class="action_select_div"'
			, $label.$select.$submit
			))."\n";

		return $action_div;
	}

	public function getSearchPhrase() {
		if(empty($this->table['id']))
			throw new Exception('Class has to be instantiated with a table id!'); // @todo use dictionary

		return $this->guiObj->getSearchPhrase($this->table['id']);
	}

	/**
	 * Strips all send IDs from $_POST-Array and returns them as integer values
	 * in an array.
	 *
	 * @return array containing the ids submitted in POST
	 * @author ahe
	 * @date 10.06.2006 23:40:40
	 */
	public static function getAffectedIds($sendMessage=false){
		$postKeys = array_keys($_POST);
		$elements = preg_grep('/^elemid_\d+$/',$postKeys);
		foreach($elements as $value) {
			$ids[] = intval(substr($value,7));
		}
		if(count($ids)<1){
			 if($sendMessage) self::sendNoneSelectedMsg();
			 return array();
		}
		return $ids;
	}

	private static function sendNoneSelectedMsg(){
	    return BcmsSystem::raiseDictionaryNotice('NoElementSelected',
	 		BcmsSystem::LOGTYPE_CHECK, BcmsSystem::SEVERITY_WARNING,
			'getAffectedIds()',__FILE__, __LINE__);
	}
}
?>