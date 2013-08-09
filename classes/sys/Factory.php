<?php /*
+----------------------------------------------------------------------------+
| B O R D E R L E S S   C M S                                                |
+----------------------------------------------------------------------------+
| (c) Copyright 2004 - 2006                                                  |
|      by goldstift (aheusingfeld@borderlesscms.de)                          |
+----------------------------------------------------------------------------+
*/
if(!defined('BORDERLESS')) exit;

/**
 * Factory class of bordeRleSS cms
 *
 * created on 29.07.2005 23:23:14 by ahe
 * @author ahe
 */
class Factory
{
	private static $uniqueInstance = null;
	private static $objectArr = array();

	/**
	 * creates a new factory object
	 *
	 * @author ahe
	 * @access protected
	 */
	protected function __contruct() {}

	/**
	 * Returns the reference on the factory object. If none exists one is created
	 *
	 * @return object returns the reference on the factory object
	 * @access public
	 * @author ahe
	 */
	public static function getInstance()
	{
		if(self::$uniqueInstance === null)
		{
			self::$uniqueInstance = new Factory;
		}
		return self::$uniqueInstance;
	}

	/**
	 * Returns the requested object if the according file exists in matching
	 * array. Preserves that there is only one single instance instantiated.
	 *
	 * @param string $p_sClassName the name of the class to be instantiated
	 * @param array $p_aParameters array holding all the parameters for run
	 * @return object in best case it returns the requested object
	 * @author ahe
	 * @access public
	 */
	static function getObject($p_sClassName, $p_aParameters=null)
	{
		if(array_key_exists($p_sClassName,$GLOBALS['bcms_classes']) )
		{
			// preserve single instance!
			if(!array_key_exists($p_sClassName,self::$objectArr)) {
				self::$objectArr[$p_sClassName] = new $p_sClassName();
			}

			return self::$objectArr[$p_sClassName];
		} else {
			throw new Exception (
				'object could not be instantiated! Requested: '.$p_sClassName
			);
		}
	}

	/**
	 * Returns the requested object if the according file exists
	 * in matching array. Preserves that the existing instance is
	 * being reinstantiated.
	 *
	 * @param string $p_sClassName the name of the class to be instantiated
	 * @param array $p_aParameters array holding all the parameters for run
	 * @return object in best case it returns the requested object
	 * @author ahe
	 * @access public
	 */
	public static function reinstantiateObject($p_sClassName, $p_aParameters=null)
	{
		if(array_key_exists($p_sClassName,$GLOBALS['bcms_classes']) )
		{
			self::$objectArr[$p_sClassName] = new $p_sClassName();
			return self::$objectArr[$p_sClassName];
		} else {
			throw new Exception (
				'object could not be instantiated! Requested: '.$p_sClassName
			);
		}
	}

}
?>