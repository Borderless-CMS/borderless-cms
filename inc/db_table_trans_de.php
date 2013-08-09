<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * @file db_table_trans_de.php
 * Includes Translation for DB_Table error messages in german
 * 
 * \bug URGENT Continue translation AND use dictionary!
 * @date 24.06.2006
 * @author ahe <aheusingfeld@borderlesscms.de>
 * @ingroup db
 * @package db
 */
$GLOBALS['_DB_TABLE']['error'] = array(
    DB_TABLE_ERR_NOT_DB_OBJECT       => 'Erster Parameter muss ein DB/MDB2 Objekt sein',
    DB_TABLE_ERR_PHPTYPE             => 'DB/MDB2 phptype (oder dbsyntax) nicht unterstützt',
    DB_TABLE_ERR_SQL_UNDEF           => 'Select key ist nicht in der Map enthalten',
    DB_TABLE_ERR_INS_COL_NOMAP       => 'Folgende Spalte ist der Datenbank unbekannt',
    DB_TABLE_ERR_INS_COL_REQUIRED    => 'Pflichtfeld nicht gesetzt. Inhalt darf muss gesetzt werden und darf nicht leer sein. Feldname:',
    DB_TABLE_ERR_INS_DATA_INVALID    => 'Ungültiger Inhalt in Feld ',
    DB_TABLE_ERR_UPD_COL_NOMAP       => 'Folgende Spalte ist der Datenbank unbekannt',
    DB_TABLE_ERR_UPD_COL_REQUIRED    => 'Pflichtfeld nicht gesetzt. Inhalt darf muss gesetzt werden und darf nicht leer sein. Feldname:',
    DB_TABLE_ERR_UPD_DATA_INVALID    => 'Ungültiger Inhalt in Feld',
    DB_TABLE_ERR_CREATE_FLAG         => 'Create flag not valid',
    DB_TABLE_ERR_IDX_NO_COLS         => 'Keine Spalten für den Index angegeben',
    DB_TABLE_ERR_IDX_COL_UNDEF       => 'Spalten ist nicht in der Map des Index',
    DB_TABLE_ERR_IDX_TYPE            => 'Typ ist ungültig für Index',
    DB_TABLE_ERR_DECLARE_STRING      => 'String column declaration not valid',
    DB_TABLE_ERR_DECLARE_DECIMAL     => 'Decimal column declaration not valid',
    DB_TABLE_ERR_DECLARE_TYPE        => 'Spaltentyp nicht gültig',
    DB_TABLE_ERR_VALIDATE_TYPE       => 'Kann unbekannten Typ für diese Spalte nicht validieren',
    DB_TABLE_ERR_DECLARE_COLNAME     => 'Spaltenname ungültig',
    DB_TABLE_ERR_DECLARE_IDXNAME     => 'Indexname ungültig',
    DB_TABLE_ERR_DECLARE_TYPE        => 'Spaltentyp ungültig',
    DB_TABLE_ERR_IDX_COL_CLOB        => 'CLOB column not allowed for index',
    DB_TABLE_ERR_DECLARE_STRLEN      => 'Column name too long, 30 char max',
    DB_TABLE_ERR_IDX_STRLEN          => 'Index name too long, 30 char max',
    DB_TABLE_ERR_TABLE_STRLEN        => 'Table name too long, 30 char max',
    DB_TABLE_ERR_SEQ_STRLEN          => 'Sequence name too long, 30 char max',
    DB_TABLE_ERR_VER_TABLE_MISSING   => 'Verification failed: table does not exist',
    DB_TABLE_ERR_VER_COLUMN_MISSING  => 'Verification failed: column does not exist',
    DB_TABLE_ERR_VER_COLUMN_TYPE     => 'Verification failed: wrong column type',
    DB_TABLE_ERR_NO_COLS             => 'Column definition array may not be empty',
    DB_TABLE_ERR_VER_IDX_MISSING     => 'Verification failed: index does not exist',
    DB_TABLE_ERR_VER_IDX_COL_MISSING => 'Verification failed: index does not contain all specified cols',
    DB_TABLE_ERR_CREATE_PHPTYPE      => 'Creation mode is not supported for this phptype',
    DB_TABLE_ERR_DECLARE_PRIMARY     => 'Only one primary key is allowed',
    DB_TABLE_ERR_DECLARE_PRIM_SQLITE => 'SQLite does not support primary keys',
    DB_TABLE_ERR_ALTER_TABLE_IMPOS   => 'Alter table failed: changing the field type not possible',
    DB_TABLE_ERR_ALTER_INDEX_IMPOS   => 'Alter table failed: changing the index/constraint not possible'
);
$GLOBALS['_DB_TABLE']['qf_rules'] = array(
  'required'  => 'Das Feld %s ist ein Pflichtfeld. Es darf nicht leer sein.',
  'numeric'   => 'Das Feld %s darf nur Ziffern enthalten.',
  'maxlength' => 'Das Feld %s darf maximal %d Zeichen lang sein.'
);
$GLOBALS['_DB_TABLE']['qf_JsWarnings'] = array(
  'prefix' => 'ACHTUNG: Ungültige Feldinhalte!',
  'postfix'   => 'Bitte korrigieren Sie Ihre Eingaben.'
);
?>