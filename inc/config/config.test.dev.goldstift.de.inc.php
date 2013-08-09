<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
$confVars = array(
	// email of system admin, will be handed out for critical errors
		'adm_email' => 'admin@goldstift.de',
	// For example: mysql, pgsql, mssql, etc. (see /pear/DB/)
		'dbType' => 'mysql',
	// name fo your database server
		'dbServer' => 'localhost',
	// the database Borderless CMS shall be installed to
		'dbDatabase' => 'goldstift',
	// database user; will at least need right to select, insert, update, delete and truncate tables
		'dbUser' => 'goldstift',
	// password of database user
		'dbPass' => 'B1wtKG1lZ79Ppl5uSv-x6',
	// optional prefix for bcms db-tables
		'table_prefix' => 'bcms_',
	// example: 'www.borderlesscms.de' or 'www.borderlesscms.de/subfolder'. Be sure to adjust your /etc/hosts for local testing
		'siteUrl' => 'test.goldstift.de',
	// message that shall be shown when site is down for maintenance
		'offlineMessage' => 'Unsere Internetseite ist wegen Wartungsarbeiten zur Zeit offline. Vielen Dank f&uuml;r Ihr Verst&auml;ndnis.'
);
?>