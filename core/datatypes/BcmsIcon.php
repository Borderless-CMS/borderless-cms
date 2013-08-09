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
 * @todo finish this class!
 * @date 30.09.2006
 * @since 0.12
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @class BcmsIcon
 * @ingroup datatypes
 * @package datatypes
 */
class BcmsIcon extends BcmsObject {
	private $image_url = null;
	private $marginArray = array();
	private $default_trans = null;
	const SMALL = 11;
	const MEDIUM = 16;
	const LARGE = 20;
	
	/**
	 * Constructor
	 *
	 * @param String $url the relative url to the icons image
	 * @param short $size One of the constants of this class
	 * @param String $default_trans default translation, business key for dictionary
	 * @return Icon 
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 * @date 30.09.2006 22:23:38
	 * @throws Exception Due to InvalidSize or NoArray provided
	 */
	public function __construct($url,$size,$default_trans){
		return $this->__construct($url,$size,$default_trans,array(2,2,2,2));
	}
	
	/**
	 * Constructor
	 *
	 * @param String $url the relative url to the icons image
	 * @param short $size One of the constants of this class
	 * @param String $default_trans default translation, business key for dictionary
	 * @param short[] $marginArray the four short numbers for the margin (top,right,bottom,left)
	 * @return Icon 
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 * @date 30.09.2006 22:23:38
	 * @throws Exception Due to InvalidSize or NoArray provided
	 */
	public function __construct($url, $size, $default_trans, $marginArray){
		if($size!=self::SMALL && $size!=self::MEDIUM && $size!=self::LARGE)
			throw Exception('Invalid variable "size"! Usage of constants in Icon class is mandatory!');
		if(!is_array($marginArray))
			throw Exception('Invalid variable "marginArray"! Usage of constants in Icon class is mandatory!');
		$this->image_url = $url;
		$this->size = $size;
		$this->marginArray = $marginArray;
		$this->default_trans = $default_trans;
	}
	
	public function getUrl() {
		return $this->image_url; 
	}
	
	/**
	 * Creates and returns the image tag of this icon instance
	 *
	 * @return String html image tag of this icon
	 * @author ahe <aheusingfeld@borderlesscms.de>
	 * @date 01.10.2006 00:06:30
	 */
	public function getImage(){
		$guiObj = BcmsFactory::getInstanceOf('GuiUtility');
		
		if(empty($this->default_trans)) 
			$trans = '';
		else
			$trans = BcmsSystem::getDictionaryManager()->getTrans($this->default_trans);
			 
		$theImgDataArray = array(
			'src' => $this->getUrl(), // Filename
			'width' => $this->size,
			'style' => 'margin:'
				.$this->marginArray[0].'px '
				.$this->marginArray[1].'px '
				.$this->marginArray[2].'px '
				.$this->marginArray[3].'px;',
			'alt' => $trans,
			'title' => $trans
		);
		return $guiObj->createImageTag($theImgDataArray);
	}
}
?>
