<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * @todo document this
 * 
 * @since 0.7
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @class SingleObjectPattern
 * @ingroup datatypes
 * @package datatypes
 */
abstract class SingleObjectPattern
{
	protected $virtualMembers = array();

	public function get($memberName)
	{
		if(@array_key_exists($memberName,$this->virtualMembers))
			return $this->virtualMembers[$memberName];
		else
			return null;
	}

	public function set($memberName, $value) {
		$this->virtualMembers[$memberName] = $value;
	}
}
?>