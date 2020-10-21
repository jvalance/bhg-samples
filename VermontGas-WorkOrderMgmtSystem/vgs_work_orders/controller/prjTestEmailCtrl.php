<?php
/*======================================================================
  >> This will be run once a month as a scheduled job.
  >> It will search for projects with completion date before today,
     with a status of 'P' (Project), and it will attempt to close
     these automatically.
  >> Only projects with no pending or completed work orders will be
     automatically cancelled.
  >> If active w/os exist for a project, a notification email will be
     sent to operations staff to close these.
/*======================================================================*/
// TODO: Implement security/login strategy for batch processes.

// Override security/login - temporary measure
session_start();
$_SESSION['current_user'] = 'JVALANCE';

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../common/vgs_utilities.php';
require_once '../model/Project_Master.php';
require_once '../model/VGS_Mail.php';
require_once 'Zend/Loader/Autoloader.php';

	Zend_Loader_Autoloader::getInstance();

	$bodyText = "TESTING 1, 2, 3.........\n";


	$from = 'reports@vermontgas.com';
	$fromName = 'Batch Job Scheduler';
// 	$toList = 'cleforce@vermontgas.com, bgray@vermontgas.com';
	$toList = 'jvalance2@vermontgas.com';
	$subject = "Projects Auto-Close Report";

// 	$mail = new VGS_Mail();
// 	$mail->sendHtmlMail($from, $fromName, $toList, $subject, $bodyText, 'text');

	$file = __FILE__;
	pre_dump('<h2>$file:</h2>'. $file);

	$uri = $_SERVER['REQUEST_URI'];
	pre_dump('<h2>$uri:</h2>'. $uri);

	$url = "http".(!empty($_SERVER['HTTPS'])?"s":"").
	"://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
	pre_dump('<h2>$url:</h2>'. $url);

	$pos = strrpos($url,'/');
	$base_url = substr($url,0,$pos);
	pre_dump('<h2>$base_url:</h2>'. $base_url);

	pre_dump('<h2>$_SERVER:</h2>');
	ary_dump($_SERVER);
	pre_dump('<h2>$_ENV:</h2>');
	ary_dump($_ENV);
// 	$mail = new Zend_Mail();
// 	$mail->setFrom('reports@vermontgas.com', 'Batch Job Scheduler');

// 	$toArray = explode(',', $toList);
// 	foreach ($toArray as $to) {
// 		$mail->addTo($to);
// 	}
// 	$mail->addTo('cleforce@vermontgas.com');
// 	$mail->addTo('bgray@vermontgas.com');
// 	$mail->setSubject("Projects Auto-Close Report");
// 	$mail->setBodyText($bodyText, NULL, Zend_Mime::ENCODING_BASE64);
// 	try {
// 		$mail->send();
// 	} catch (Exception $e) {
// 		pre_dump($e);
// 		pre_dump($mail);
// 		exit;
// 	}


