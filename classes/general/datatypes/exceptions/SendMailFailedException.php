<?php
/**
 * Created on 14.10.2006
 *
 */
class SendMailFailedException extends Exception {

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

	public function __construct($username, $subject){
		/* im Fehlerfall zum Senden einer AdminMail auffordern
		*  Diese   wird nicht automatisch gesendet, da davon ausgegangen
		* wird, dass der allgemeine Mailversand gestoert ist!
		*/
		$message = 'ACHTUNG: E-Mail-Versand fehlgeschlagen!<br />
			Bitte kontaktieren Sie den <a href="mailto:'.BcmsConfig::getInstance()->adm_email
			.'?subject=Mailsend_Error:%20'.$username.' - Subject: '.$subject.'">Systemadminstrator</a>.'; // TODO Use dictionary!
		parent::__construct($message, BcmsSystem::SEVERITY_CRITICAL);
	}
}
?>
