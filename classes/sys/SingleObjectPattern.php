<?php
/**
 * the abstract class
 *
 * @module SingleObjectPattern.php
 * @author ahe <aheusingfeld@borderless-cms.de>
 * @package packagename
 * @version $Id$
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