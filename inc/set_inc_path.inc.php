<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * sets the include path according to the given system os
 *
 * @file set_inc_path.inc.php
 * @author ahe <aheusingfeld@borderlesscms.de>
 * @ingroup init
 */


// set general charset for whole site. This is overwritten in init.inc.php
mb_internal_encoding('UTF-8');
if( (array_key_exists('SERVER_SOFTWARE',$_SERVER) && stripos($_SERVER['SERVER_SOFTWARE'],'win'))
|| (array_key_exists('SystemRoot',$_SERVER) && stripos($_SERVER['SystemRoot'],'win'))
|| (array_key_exists('SERVER_SIGNATURE',$_SERVER) && stripos($_SERVER['SERVER_SIGNATURE'],'win')) )
 {
 	set_include_path(BASEPATH.'core/;'.BASEPATH.'inc/;'.BASEPATH.'plugins/;'.BASEPATH.'inc/pear/;'.BASEPATH.';./;../;');
 } else {
 	set_include_path(BASEPATH.'core/:'.BASEPATH.'inc/:'.BASEPATH.'plugins/:'.BASEPATH.'inc/pear/:'.BASEPATH.':./:../:');
}
$includeRoot = 'inc';

?>
