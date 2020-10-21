<?php
require_once 'Zend/Loader/Autoloader.php';

class VGS_Mail {
	const SMTP_IP_ADDR = '192.168.11.20';

	function __construct() {
		Zend_Loader_Autoloader::getInstance();
	}
	
	public function sendHtmlMail($from, $fromName, $toList, $subject, $body, $bodyType = 'html') {
		// Oct 2013 JGV - Added parameter bodyType to allow text only emails

		$mail = new Zend_Mail();
		$tr = new Zend_Mail_Transport_Smtp(VGS_Mail::SMTP_IP_ADDR);
		Zend_Mail::setDefaultTransport($tr);

		$mail->setFrom($from, $fromName);
		
		$toArray = explode(',', $toList);
		
		foreach ($toArray as $to) {
			$mail->addTo($to);
		}

		$environment = VGS_DB_Conn_Singleton::getEnvironment();
		if ($environment != VGS_DB_Conn_Singleton::DB_PROD) {
			$subject = "*** $environment *** " . $subject;
			if ($bodyType == 'html') {
				$body = "<span style='color:red'>*** $environment *** : " .
					"Note - This email was generated in the $environment environment.</span><p />" . $body;
			} else {
				$body = "*** Note - This email was generated in the $environment environment. ***\n\n" . $body;
			}			
		}
		$mail->setSubject($subject);
		
		if ($bodyType == 'html') {
			$body = '<body style="background-color: #E2F1FA; font-family:arial; font-size:12pt; color:navy">'
					. $body . '</body>';
			$mail->setBodyHTML($body, NULL, Zend_Mime::ENCODING_BASE64);
		} else {
			$mail->setBodyText($body);
		}
		
		try {
			$mail->send();
		} catch (Exception $e) {
			pre_dump($e);
			pre_dump($mail);
			exit;
		}
		
	}
}

?>