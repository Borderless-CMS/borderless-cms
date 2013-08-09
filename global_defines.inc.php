<?php
/**
 * @file global_defines.inc.php
 * makes all defines for the application like BCMS_VERSION, BASEPATH and
 * disabling compatibility with Zend Engine 1
 *
 * @author ahe <aheusingfeld@borderlesscms.de>
 * @since 0.13.153
 * @ingroup _main
 */
if (version_compare(phpversion(), '5.1', '<')) {
	die('Borderless CMS requires PHP 5.1 or greater');
}

/** Disable register globals!!! */
ini_set('zend.register_globals', 'off');
/** Disable compatibility with Zend Engine 1 */
ini_set('zend.ze1_compatibility_mode', 0);
/** prevents execution of php scripts separetly from the main file */
define('BORDERLESS','defined');
/** the svn revision number of the current deployed code copy */
define('BCMS_REVISION',187);
/** The full version number of this BCMS code copy */
define('BCMS_VERSION','(v0.13.'.BCMS_REVISION.')');
/** Basepath is used whenever a fully qualified path is needed. This is helpful e.g. for open_basedir restrictions! */
define('BASEPATH',dirname(__FILE__).'/');
?>