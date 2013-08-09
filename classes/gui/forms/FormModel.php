<?php

// basic classes
class FormModel 
// extends BcmsDAO 
{
	protected $fieldtypeTable;
	protected $formruleTable;
	protected $formgroupsTable;
	protected $formsTable;
	protected $formfieldsTable;

	public function __construct(){
        if($GLOBALS['db']==null) throw new Exception('No database connection present!');
		$this->fieldtypeTable;
		$this->formruleTable;
		$this->formgroupsTable;
		$this->formsTable;
		$this->formfieldsTable;
	}

}
?>