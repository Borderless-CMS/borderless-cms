<?php
/**
 * Created on 14.10.2006
 *
 */
class UnknownClassException extends Exception {

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

	public function __construct($classname, $code = 0){
		$message = 'Unknown class: Class file is not registered in this system! ' .
				'Requested class: '.$classname; // TODO Use dictionary
		parent::__construct($message, $code);
	}	
}
?>
