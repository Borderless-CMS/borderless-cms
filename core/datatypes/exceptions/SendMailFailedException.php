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
 * @date 14.10.2006
 * @since 0.13
 * @author ahe <aheusingfeld@borderlessscms.de>
 * @class SendMailFailedException
 * @ingroup datatypes
 * @package datatypes
 */
class SendMailFailedException extends BcmsException {

	/**
	 * Is used to notify the user whenever mail transfer fails.
	 *
	 * @param String $to - the mail address of the receiver
	 * @param String $subject - subject of the mail
	 * @return SendMailFailedException
	 * @author ahe
	 */
	public function __construct($to, $subject){
		/* im Fehlerfall zum Senden einer AdminMail auffordern
		*  Diese   wird nicht automatisch gesendet, da davon ausgegangen
		* wird, dass der allgemeine Mailversand gestoert ist!
		*/
		$message = 'ACHTUNG: E-Mail-Versand fehlgeschlagen!<br />
			Bitte kontaktieren Sie den <a href="mailto:'.BcmsConfig::getInstance()->adm_email
		.'?subject=Mailsend_Error:%20'.$to.' - Subject: '.$subject.'">Systemadminstrator</a>.'; // @todo Use dictionary!
		parent::__construct($message, BcmsSystem::SEVERITY_CRITICAL);
	}
}
?>
