<?php
/**
 * sets the include path according to the given system os
 */
if(!defined('BORDERLESS')) exit;
// set general cahrset for whole site. This is overwritten in init_part1.inc.php
mb_internal_encoding('UTF-8');
if(stripos($_SERVER['SERVER_SOFTWARE'],'win')
 || stripos($_SERVER['SystemRoot'],'win')
 || stripos($_SERVER['SERVER_SIGNATURE'],'win') )
{
	set_include_path('.;..;classes;includes;plugins;pear;');
} else {
	set_include_path('./:../:./classes:./plugins:./pear:./includes:');
}
$includeRoot = 'includes';

?>
