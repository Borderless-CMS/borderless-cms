<?php
/**
* In dieser Datei werden die SystemVariablen aus der Datenbank geladen
* TODO use classifications!
*/
$langKey = BcmsConfig::getInstance()->langKey;
$sql = 'SELECT class.name, class.number ' .
	'FROM '.BcmsConfig::getInstance()->getTablename('classification').' AS class, '.
	BcmsConfig::getInstance()->getTablename('systemschluessel').' AS syskey ' .
	'WHERE ' .
	'class.fk_syskey = syskey.id_schluessel AND syskey.schluesseltyp = \'status\''
	.' ORDER BY class.number DESC';
$result=$db->query($sql);
if (!($result instanceof PEAR_ERROR) && $result->numRows()>0) {
 	$numrows = $result->numRows();
	for ($i = 0; $i < $numrows; $i++) {
		$conf_array = $result->fetchRow(DB_FETCHMODE_ASSOC,$i);
		$GLOBALS['ARTICLE_STATUS'][$conf_array['name']] = $conf_array['number'];
	}
	$result->free();
}
?>