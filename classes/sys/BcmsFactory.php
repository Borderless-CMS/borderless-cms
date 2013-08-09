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
class BcmsFactory implements Singleton
{
	private static $uniqueInstance = null;
	private static $objectArray = array();

	/**
	 * creates a new factory object
	 *
	 * @author ahe
	 * @access protected
	 */
	protected function __construct() {}

	/**
	 * Returns the reference on the factory object. If none exists one is created
	 *
	 * @return object returns the reference on the factory object
	 * @access public
	 * @author ahe
	 */
	public static function getInstance()
	{
		if(is_null(self::$uniqueInstance)) {
			self::$uniqueInstance = new BcmsFactory();
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
	public static function getInstanceOf($p_sClassName)
	{
		if(array_key_exists($p_sClassName,$GLOBALS['bcms_classes']) )
		{
			if(!isset(self::$objectArray[$p_sClassName]))
				self::$objectArray[$p_sClassName] = self::createNewObject($p_sClassName);
			return self::$objectArray[$p_sClassName];
		} else {
			throw new UnknownClassException($p_sClassName);
		}
	}
	
	/**
	 * Creates a new instance of the requested class 
	 *
	 * @param string $p_sClassName the name of the class to be instantiated
	 * @param array $p_aParameters array holding all the parameters for run
	 * @return object in best case it returns the requested object
	 * @author ahe
	 * @access public
	 */
	public static function createInstanceOf($p_sClassName)
	{
		if(array_key_exists($p_sClassName,$GLOBALS['bcms_classes']) )
		{
			return self::createNewObject($p_sClassName);
		} else {
			throw new UnknownClassException($p_sClassName);
		}
	}
	
	private static function createNewObject($className){
		$reflClass = new ReflectionClass($className);
		return $reflClass->newInstance();
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
			self::callMethod($p_sClassName, $p_aParameters);
			return self::$objectArr[$p_sClassName];
		} else {
			throw new Exception (
				'object could not be instantiated! Requested: '.$p_sClassName
			);
		}
	}

	/**
	 * Should be called from within a constructor. Checks whether instantiation
	 * takes place via BcmsFactory.
	 * This has been build to apply factory instantiation to Singleton objects.
	 *
	 * @param String $classname name of the class which instantiation shall be
	 * checked
	 * @throws ProtectedConstructorException
	 * @author ahe
	 * @date 15.10.2006 00:37:01
	 * @package htdocs/classes/system
	 */
	public static function ensureFactoryInstantiation($classname){
		try{
			throw new Exception();
		} catch(Exception $ex) {
			$traceArr = $ex->getTrace();
			if(sizeof($traceArr)<2 || strpos('BcmsFactory',$traceArr[2]['file']))
				throw new ProtectedConstructorException($classname);
		}
	}
}
?>