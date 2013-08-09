<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * Father Exception of all BcmsExceptions. This is done to separate general Exceptions
 * from those defined and thrown by Borderless CMS.
 *
 * @date 03.04.2007
 * @since 0.13.89
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @class BcmsException
 * @ingroup datatypes
 * @package datatypes
 */
class BcmsException extends Exception {

	//class Exception
	//{
	//   protected $message = 'Unknown exception';  // Ausnahmemitteilung
	//   protected $code = 0;                        // Benutzerdefinierter Code
	//   protected $file;                            // Quelldateiname der Ausnahme
	//   protected $line;                            // Quelldateizeile der Ausnahme
	//
	//   function __construct($message = null, $code = 0);
	//
	//   final function getMessage();                // Mitteilung der Ausnahme
	//   final function getCode();                  // Code der Ausnahme
	//   final function getFile();                  // Quelldateiname
	//   final function getLine();                  // Quelldateizeile
	//   final function getTrace();                  // Array mit Ablaufverfolgung
	//   final function getTraceAsString();          // Formatierter String mit
	//                                               //  Ablaufverfolgung
	//
	//   /* Überschreibbar */
	//   function __toString();                      // Formatierter String für
	//                                                 //  Ausgabe
	//}

	public function __construct($message, $code = 0){
		parent::__construct($message, $code);
	}
}
?>
