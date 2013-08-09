<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * @file upgrade_statements.inc.php
 * Inside of this file all sql statements are collected needed to upgrade a database
 * from one version to another. This is being included by Installer::upgrade()
 *
 * @since 0.13.153
 * @ingroup install
 */

//... perform sql statements for version
//if($fromRevision < 116){
//	// 2007-04-17 AHE: inserted translation of Content plgCatConfig's "hide_comments_on_show" field
//	$sqls[] = 'INSERT INTO '.$this->getTblName('dict').
//		' (deftrans, type_classify_id, de, en) VALUES ('.
//		$this->db->quoteSmart('cont.hide_comments_on_show').', '.
//		$this->db->quoteSmart(35).', '.
//		$this->db->quoteSmart('Sollen die Kommentare in der Artikelstandardansicht ausgeblendet werden?').', '.
//		$this->db->quoteSmart('generally turn off optional plugins section').', '.
//		$this->db->quoteSmart('Shall comments be hidden in article\\\'s standard view?').');';
//}

if($fromRevision < 171){
	// 2007-05-16 AHE: insert "imprintCategoryName" and "imprintCategoryName" into config table
	$sqls[] = 'INSERT INTO '.$this->getTblName('config').
		' (fk_section, var_name, var_value, var_description, var_type, editable) VALUES ('.
		$this->db->quoteSmart(24).', '.
		$this->db->quoteSmart('imprintCategoryName').', '.
		$this->db->quoteSmart('impressum').', '.
		$this->db->quoteSmart('Name of imprint section. Used in meta header information on each page.').
		', '.$this->db->quoteSmart(46).', '.$this->db->quoteSmart(1).');';
	$sqls[] = 'INSERT INTO '.$this->getTblName('config').
		' (fk_section, var_name, var_value, var_description, var_type, editable) VALUES ('.
		$this->db->quoteSmart(24).', '.
		$this->db->quoteSmart('contactCategoryName').', '.
		$this->db->quoteSmart('kontakt').', '.
		$this->db->quoteSmart('Name of contact section. Used in meta header information on each page.').
		', '.$this->db->quoteSmart(46).', '.$this->db->quoteSmart(1).');';
	// 2007-05-16 AHE: update according sequence
	$sqls[] = 'UPDATE '.$this->getTblName('config__seq').' SET id=id+2;';
}
if($fromRevision < 187){
	// 2007-05-16 AHE: changed column names of old table "last_transactions" (ONLY FOR OLD MYSQL DB)
	$sqls[] = 'ALTER TABLE '.$this->getTblName('last_transactions').
		' CHANGE `sql` `sql_stmt` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,' .
		' CHANGE `timestamp` `timestmp` INT( 14 ) NOT NULL DEFAULT \'0\';';
	$sqls[] = ' ALTER TABLE '.$this->getTblName('classification').' CHANGE `name` `classify_name` VARCHAR( 50 )'.
				'CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL';
	$sqls[] = 'ALTER TABLE '.$this->getTblName('comment').
			' CHANGE `status` `status_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT \'0\'';
	$sqls[] = 'ALTER TABLE '.$this->getTblName('history').
			' CHANGE `language` `lang` VARCHAR( 5 ) CHARACTER SET utf8 COLLATE '.
			' utf8_general_ci NOT NULL DEFAULT \'de\', '.
			' CHANGE `status` `status_id` INT( 11 ) NOT NULL DEFAULT \'0\'';

	$sqls[] = 'ALTER TABLE '.$this->getTblName('content').
			' CHANGE `language` `lang` VARCHAR( 5 ) CHARACTER SET utf8 COLLATE '.
			' utf8_general_ci NOT NULL DEFAULT \'de\', '.
			' CHANGE `status` `status_id` INT( 11 ) NOT NULL DEFAULT \'0\'';
	$sqls[] = 'ALTER TABLE '.$this->getTblName('content').' ADD INDEX created ( created )';
	$sqls[] = 'ALTER TABLE '.$this->getTblName('content').' DROP INDEX for_top5, '.
			'ADD INDEX `for_top5` ( fk_creator, fk_cat, status_id, publish_begin, publish_end  )';
	$sqls[] = 'ALTER TABLE '.$this->getTblName('history').' ADD INDEX ( content_id, status )';

// TODO create techname in plg_articles table	->filterTechName($contentfields['heading']);

	$sqls[] = 'ALTER TABLE '.$this->getTblName('menu').' ADD INDEX root_id ( root_id ) ';
	$sqls[] = 'ALTER TABLE '.$this->getTblName('menu').
			' CHANGE `status` `status_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT \'0\', '.
			' CHANGE `type` `fk_type_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT \'0\'';

	$sqls[] = 'ALTER TABLE '.$this->getTblName('systemschluessel').' ADD UNIQUE SCHLUESSELTYP ( SCHLUESSELTYP ) ';
	$sqls[] = 'ALTER TABLE '.$this->getTblName('plugins').' CHANGE name plg_name VARCHAR( 40 ) '.
		'CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL';
	$sqls[] = 'ALTER TABLE '.$this->getTblName('requestlog').' CHANGE `session` session_id VARCHAR( 40 ) '.
		'CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL';
	$sqls[] = 'ALTER TABLE '.$this->getTblName('syslog').
		' CHANGE `timestamp` `timestmp` datetime NOT NULL default \'0000-00-00 00:00:00\');';
	$sqls[] = 'ALTER TABLE '.$this->getTblName('classification').' DROP INDEX `number_syskey` , '.
		'ADD UNIQUE number_syskey ( fk_syskey , number )';
	$sqls[] = 'ALTER TABLE '.$this->getTblName('usersessions').' ADD UNIQUE (`hash_val`)';
}
?>