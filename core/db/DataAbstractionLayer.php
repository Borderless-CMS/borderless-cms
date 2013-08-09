<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

require_once 'inc/pear/DB/Table.php';
include 'inc/db_table_trans_de.php';

/**
 * Abstract super class for all DAL classes
 *
 * @since 0.7
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @class DataAbstractionLayer
 * @ingroup db
 * @package db
 */
abstract class DataAbstractionLayer extends DB_Table {
	// ausprobieren, ob dies auch protected gesetzt werden kann! Konsequenzen?
	public $uneditableElements;
	protected $elementsToFreeze;
	protected $primaryKeyColumnName;
	public $auto_inc_col;

	/**
	 *
	 * Constructor.
	 *
	 * If there is an error on instantiation, $this->error will be
	 * populated with the PEAR_Error.
	 *
	 * @access public
	 *
	 * @param object &$db A PEAR DB/MDB2 object.
	 *
	 * @param string $table The table name to connect to in the database.
	 *
	 * @param mixed $create The automatic table creation mode to pursue:
	 * - boolean false to not attempt creation
	 * - 'safe' to create the table only if it does not exist
	 * - 'drop' to drop any existing table with the same name and re-create it
	 * - 'verify' to check whether the table exists, whether all the columns
	 *   exist, whether the columns have the right type, and whether the indexes
	 *   exist and have the right type
	 * - 'alter' does the same as 'safe' if the table does not exist; if it
	 *   exists, a verification for columns existence, the column types, the
	 *   indexes existence, and the indexes types will be performed and the
	 *   table schema will be modified if needed
	 *
	 * @return object DB_Table
	 *
	 * @todo also sets the current primary key column to DB_Table's auto_inc_col.
	 * This is a temporary workaround. In future all derived classes will have to be modified.
	 *
	 * @since 05.03.2007 16:05:22
	 */
	protected function __construct($db,$tablename,$method=false) {
		$this->auto_inc_col = $this->primaryKeyColumnName;
		parent::__construct($db,$tablename,$method);
	}


	/**
	 * Returns the name of the primary key column
	 *
	 * @return String - the name of the primary key column
	 * @author ahe
	 * @date 16.12.2006 09:58:08
	 */
	protected function getPrimaryKeyColumnName() {
		return $this->primaryKeyColumnName;
	}

	/**
	 * Asks Dictionary for translation of all columns in current DALs
	 *  $this->cols array.
	 *
	 * @param String prefix - optional; prefix used to categorize default trans
	 * in dictionary
	 * @author ahe
	 * @date 16.12.2006 20:49:38
	 */
	public function addLabels($prefix=null) {
		$fieldnames = array_keys($this->col);
		foreach($fieldnames as $key) {
			$trans = null;
			$trans = BcmsSystem::getDictionaryManager()->getTrans($prefix.$key);
			if($trans==null) $trans=$key;
			$this->col[$key]['qf_label'] = $trans;
		}
	}

	/**
	 * Gets the number of records in the current table
	 *
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 * @return int
	 * @access public
	 * @since 0.12
	 * @date 26.09.2006
	 */
	public function getNumberOfEntries($where=null) {
		$this->sql['no_of_entries'] = array(
        	'select' => 'count(*)'
        );
		$result = $this->select('no_of_entries',$where);
		return (int) $result[0][0];
	}

	/**
	 * builds a where String according to the given data
	 *
	 * @param $where - expected to be like array('columname1' => 'LIKE',
	 * 'columname2' => '=')
	 * @param $searchphrase - the phrase to be searched for
	 * @param tablePrefix - optional prefix for searchtable; like 'dict1.'
	 * @return String - the where represented as string
	 * @author ahe
	 * @date 28.11.2006 01:04:16
	 */
	protected function buildWhereString($searchphrase,$tablePrefix=null){
		$whereString=null;
		$where = $this->getSearchableFieldsArray();

		// throw exception for incorrect implementation
		if(is_null($where) && !empty($searchphrase))
		{
			throw new Exception('ATTENTION: Searchstring specified but Your_' .
					'DAL::getSearchableFieldsArray() has not been implemented!');
		}

		if(!is_null($where) && !empty($searchphrase)){
			$searchphrase = str_replace('*','%',$searchphrase);
			$searchphrase = str_replace('?','_',$searchphrase);
			$searchphrase = BcmsSystem::getParser()->prepDbStrng($searchphrase);
			foreach ($where as $key => $value) {
				if(!empty($whereString)) $whereString .= ' OR ';
				$whereString .= $tablePrefix.$key.' '.$value.' '.$searchphrase;
			}
		}
		return $whereString;
	}

	/**
	 * Returns an array with column name and search method for column.
	 *
	 * <strong>example</strong>
	 * <code>
	 * 	array(
	 *		'column1' => 'LIKE',
	 *      'column2' => '='
     *  );
	 * </code>
	 *
	 * @return String[] - columnname => searchmethod
	 * @author ahe
	 * @date 16.12.2006 01:48:48
	 */
	protected function getSearchableFieldsArray()
	{
		return null;
	}

	public function getForm($p_sFormName, $p_sSubmitButtonName,$p_sSubmitButtonText
		,$columns = null, $array_name = null, $args = array(),
		$clientValidate = null, $formFilters = null) {

		$form =& parent::getForm($columns, $array_name,
		array('formName' => $p_sFormName, 'trackSubmit'=>true),
		$clientValidate, $formFilters); // note the "=&" -- very important
			$form->addElement('submit', $p_sSubmitButtonName, $p_sSubmitButtonText);
			$element = $form->getElement($p_sSubmitButtonName);
			$element->setLabel('&nbsp;');
			$form->addElement('submit', 'abort_action',
			BcmsSystem::getDictionaryManager()->getTrans('cancel'));
			$element = $form->getElement('abort_action');
			$element->setLabel('&nbsp;');
			$form->addElement('reset', 'reset_values',
			BcmsSystem::getDictionaryManager()->getTrans('reset'));
			$element = $form->getElement('reset_values');
			$element->setLabel('&nbsp;');

			// if there are elements which shall just be shown, freeze them
			if(count($this->elementsToFreeze)>0)
			$form->freeze($this->elementsToFreeze);

			// remove elements from form which contents shall be given by the system
			for ($index = 0; $index < count($this->uneditableElements); $index++) {
			$form->removeElement($this->uneditableElements[$index]);
		}
		return $form;
	}

	/**
	 *
	 *
	 * @param 	int 	$offset - optional;
	 * @param 	int 	$limit - optional;
	 * @param 	String 	$where - optional;
	 * @param 	String 	$searchphrase - optional;
	 * @param 	String 	$translationPrefix - optional; Prefix if used in dictionary
	 * @param 	String 	$generalSqlPrefix - optional; Prefix/ table alias for columns like 'dict.'
	 * @param 	String 	$internalSqlName - optional;
	 * @return 	array	resulting list as an associative array
	 * @access protected
	 * @author ahe
	 * @date 16.12.2006 23:19:37
	 */
	protected function getList($offset=null, $limit=null, $where=null, $order_by=null, $order_dir=null,$searchphrase=null,$translationPrefix=null,$generalSqlPrefix=null,$internalSqlName=null)
	{
		$this->addLabels($translationPrefix);

		if(!empty($order_by) && !empty($order_dir)) {
			$this->sql[$internalSqlName]['order'] = $order_by.' '.$order_dir;
		}

		if(!empty($limit)) {
			$this->sql[$internalSqlName]['limit'] = $limit;
			$this->sql[$internalSqlName]['offset'] = $offset;
		}

		// prepare where string
		$whereString = $this->buildWhereString($searchphrase,$generalSqlPrefix);
		if(empty($where)){
			$where = $whereString;
		} else if(!empty($whereString)){
			$where .= ' AND ('.$whereString.')';
		}
		$rows = $this->select($internalSqlName,$where,null,$offset,$limit);
		return $this->prepareResultForTableView($rows);
	}

    /**
	 * prepare values in specified array for display in table view.
	 * ATTENTION: This is a hook method to implement special handling of result array
	 *
	 * @param array rows - associative array with column => value
	 * @return array - rows with prepared values
	 * @author ahe
	 * @date 16.12.2006 23:11:28
	 * @since 0.13.180
	 */
    protected function prepareResultForTableView($rows) {
    	return $rows;
	}

	public function checkForAction($p_sNameOfSubmitButton,$dataArray,$func='insert',$p_sWhere=null){
		// check whether form was send
		if(isset($_POST[$p_sNameOfSubmitButton])) {
			// extract array to usable cols and values
			foreach ($dataArray as $key => $value) {
				if( ($key != $p_sNameOfSubmitButton)
					&& (!in_array($key,$this->uneditableElements))
					&& (substr($key,0,5) != '_qf__')  // ignore quickform field!
					&& (substr($key,0,2) != '__') ) // ignore quickform field!
				{
					$cols[$key] = $value;
				}
			}
	    return $this->performAction($cols,$func,$p_sWhere);
		}
		return null;

	}

	/**
	 * Perform specified action with specified cols array
	 *
	 * @param Array $cols - mapping of db cols to values
	 * @param String $func - 'update' or 'insert'
	 * @return mixed - PEAR_Result or boolean on error
	 * @access protected
	 * @author ahe
	 * @since 0.14.49
	 * @date 20.03.2007 00:08:55
	 */
	public function performAction($cols,$func,$p_sWhere=null) {
		// handle/ set values for special form fields
		$this->checkSpecialFields($cols,$func);
		if($func=='update') {
			$result = $this->$func($cols,$p_sWhere);
			$logtype = BcmsSystem::LOGTYPE_UPDATE;
//			$this->update()
		} else {
			// $this->insert();
			$result = $this->$func($cols);
			$logtype = BcmsSystem::LOGTYPE_INSERT;
		}

	    if($result instanceof PEAR_ERROR) {
	    	return BcmsSystem::raiseError($result,$logtype,
			BcmsSystem::SEVERITY_ERROR, 'checkForAction()',
	    		__FILE__, __LINE__, $result->message);
	    }
	    return $result;
	}



    /**
     * overwrites the parent's method.
     *
     * @return mixed - PEAR_Error or the "id" of the inserted record
     * @author ahe
     */
    public function insert($data, $id_column=null)
    {
    	if($id_column != null) {
			// force a new ID on the data
			$data[$id_column] = $this->nextID();
    	} else {
    		$id_column = $this->primaryKeyColumnName;
    	}
        // auto-validate and insert
        $result = parent::insert($data);
        // check the result of the insert attempt
        if ($result instanceof PEAR_ERROR) {
            // return the error
            return $result;
        } else {
            // return the new ID
            return $data[$id_column];
        }
    }

   /**
    * Selects rows from the table using one of the DB/MDB2 get*() methods.
    * Overwrites the method of the super class adding error handling features.
    *
    * @param string $sqlkey The name of the SQL SELECT to use from the
    * $this->sql property array.
    * @param string $filter Ad-hoc SQL snippet to AND with the default
    * SELECT WHERE clause.
    * @param string $order Ad-hoc SQL snippet to override the default
    * SELECT ORDER BY clause.
    * @param int $start The row number to start listing from in the
    * result set.
    * @param int $count The number of rows to list in the result set.
    * @param array $params Parameters to use in placeholder substitutions (if
    * any).
    * @return mixed An array of records from the table (if anything but
    * 'getOne'), a single value (if 'getOne') or false in case of error.
    *
    * @access public
    * @author ahe <aheusingfeld@borderlesscms.de>
	* @date 05.10.2006 23:15:13
	*/
    function select($sqlkey, $filter = null, $order = null,
        $start = null, $count = null, $params = array())
    {
        $result = parent::select($sqlkey,$filter,$order,$start,$count,$params);
        // check the result of the insert attempt
        if ($result instanceof PEAR_ERROR) {
        	$msg = 'FEHLER: Ein Datenbankfehler ist aufgetreten. Bitte ' .
        			'benachrichtigen Sie Ihren Systemadministrator falls' .
        			'diese Meldung nicht zum ersten mal erscheint.'; // @todo use Dictionary! @see BcmsSystem#128
			return BcmsSystem::raiseError($result,BcmsSystem::LOGTYPE_SELECT,
				BcmsSystem::SEVERITY_ERROR, 'select()'
				,__FILE__, __LINE__,$msg);
        } else {
            // return the new ID
            return $result;
        }
    }

	/**
     * Deletes table rows matching a custom WHERE clause.
     *
     * @access public
     * @param string $where The WHERE clause for the delete command.
     * @return mixed true on success or a PEAR_Error object on failure.
     * @see DB::query()
     * @see MDB2::exec()
	 * @author ahe
	 * @date 20.01.2007 23:11:21
     */
    function delete($where) {
    	$error = parent::delete($where);
		if($error instanceof PEAR_ERROR){
			return BcmsSystem::raiseError($error,BcmsSystem::LOGTYPE_DELETE,
			BcmsSystem::SEVERITY_ERROR, 'delete()'
				,__FILE__, __LINE__);
		}
		return true;
    }

    /**
    *
    * Updates table row(s) matching a custom WHERE clause, after checking
    * against validUpdate().
    *
    * @access public
    *
    * @param array $data An associative array of key-value pairs where
    * the key is the column name and the value is the column value.  These
    * are the columns that will be updated with new values.
    *
    * @param string $where An SQL WHERE clause limiting which records
    * are to be updated.
    *
    * @return mixed Void on success, a PEAR_Error object on failure.
    *
    * @see validUpdate()
    *
    * @see DB::autoExecute()
    *
    * @see MDB2::autoExecute()
    *
    */
    function update($data, $where) {
    	$result = parent::update($data, $where);
        // check the result of the insert attempt
        if ($result instanceof PEAR_ERROR) {
        	$msg = 'Bei der Aktualisierung ist ein Datenbankfehler aufgetreten. Bitte ' .
        			'benachrichtigen Sie Ihren Systemadministrator falls' .
        			'diese Meldung nicht zum ersten mal erscheint.'; // @todo use Dictionary!
			return BcmsSystem::raiseError($result,BcmsSystem::LOGTYPE_UPDATE,
				BcmsSystem::SEVERITY_ERROR, 'update()'
				,__FILE__, __LINE__,$msg);
        } else {
            // return the result
            return $result;
        }
    }

	abstract public function checkSpecialFields(&$p_aCols,$func);

	abstract public function getObject($id);
}
?>