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

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

require_once '../common/vgs_utilities.php';
require_once '../model/Project_Master.php';
require_once '../model/VGS_Mail.php';
require_once 'Zend/Loader/Autoloader.php';

// $sec = new Security();
// $sec->checkPermissionByCategory('PROJ', 'UPDATE');

$conn = VGS_DB_Conn_Singleton::getInstance();
$proj = new Project_Master($conn);

$select = new VGS_DB_Select();
$select->from = 'PROJECT_MASTER';
$select->andWhere("PRJ_COMPLETION_DATE < CURRENT DATE");
$select->andWhere("PRJ_COMPLETION_DATE > '0001-01-01'");
$select->andWhere("PRJ_STATUS = 'P'");
$proj->execListQuery($select->toString());

$projects_processed_count = 0;
$projects_closed_count = 0;
$projects_cancelled_count = 0;
$ineligibleProjects = array();
$projectsClosed = array();
$projectsCancelled = array();

while ($row = db2_fetch_assoc($proj->stmt)) {
	$open_wo_count = Project_Master::getWOCountForProject($conn, $row['PRJ_NUM'],
				"WO_STATUS in ('PND', 'CMP')");
	if ($open_wo_count > 0) {
		/** If open or completed W/Os found, the project is inelgible for close.
		 * Save array of ineligible projects to add to email */
		$row['open_wo_count'] = $open_wo_count;
		$ineligibleProjects[$row['PRJ_NUM']] = $row;
	} else {
		$prj_WO_count = Project_Master::getWOCountForProject($conn, $row['PRJ_NUM']);
		$prj_WOs_notCnl = Project_Master::getWOCountForProject($conn, $row['PRJ_NUM'],
				"WO_STATUS <> 'CNL'");
		if ($prj_WO_count == 0 || $prj_WOs_notCnl == 0) {
			/** If either no WOs for this project, or only cancelled W/Os,
			 * change project status to cancelled.
			 */
			changeProjectStatus($row['PRJ_NUM'], 'X');
			$projectsCancelled[$row['PRJ_NUM']] = $row;
			$projects_cancelled_count++;
		} else {
			// Changed project status to closed.
			changeProjectStatus($row['PRJ_NUM'], 'C');
			$projectsClosed[$row['PRJ_NUM']] = $row;
			$projects_closed_count++;
		}

	}
	$projects_processed_count++;
}

$project_ineligible_count = count($ineligibleProjects);
if ($project_ineligible_count > 0) {
	sendEmail($ineligibleProjects, $projectsClosed, $projectsCancelled);
}

echo "$projects_processed_count projects were processed." .
	 "<br />$projects_closed_count were automatically closed. " .
	 "<br />$project_ineligible_count projects were ineligible for auto-close." .
	 "<br />$projects_cancelled_count projects were automatically cancelled.";

db2_close($conn);

//---------------------------------------------------------------------
function changeProjectStatus($projNum, $status) {
	$projData['PRJ_NUM'] = $projNum;
	$projData['PRJ_STATUS'] = $status;
	$objProj = new Project_Master($conn);
//	$blnResult = $objProj->autoUpdateRecord($projData);
	return $blnResult;
}

//---------------------------------------------------------------------
function sendEmail( $projectsNotClosed, $projectsClosed, $projectsCancelled ) {
	Zend_Loader_Autoloader::getInstance();

	$notClosedCount = count($projectsNotClosed);
	$bodyText = "The following $notClosedCount projects have reached their " .
			"completion date, but currently have open " .
			"or completed work orders which need to be closed " .
			"before the project can be closed:\n";

	foreach ($projectsNotClosed as $proj) {
		$bodyText .= "\n{$proj['PRJ_NUM']} - {$proj['PRJ_DESCRIPTION']} " .
					"({$proj['open_wo_count']} workorders need to be closed)";
	}

	$autoClosedCount = count($projectsClosed);
	$bodyText .= "\n\n--------------------------------\n".
		"The following $autoClosedCount projects have reached " .
			"their completion date, and were closed successfully:\n";

	foreach ($projectsClosed as $proj) {
		$bodyText .= "\n{$proj['PRJ_NUM']} - {$proj['PRJ_DESCRIPTION']}";
	}

	$autoCancelledCount = count($projectsCancelled);
	$bodyText .= "\n\n--------------------------------\n".
		"The following $autoCancelledCount projects have reached their completion date, " .
			"and were cancelled because they had either no work orders " .
			"OR only cancelled work orders:\n";

	foreach ($projectsCancelled as $proj) {
		$bodyText .= "\n{$proj['PRJ_NUM']} - {$proj['PRJ_DESCRIPTION']}";
	}

	$from = 'reports@vermontgas.com';
	$fromName = 'Batch Job Scheduler';
// 	$toList = 'cleforce@vermontgas.com, bgray@vermontgas.com';
	$toList = 'johnv@jvalance.com, johnv@div1sys.com';
	$subject = "Projects Auto-Close Report";

	$mail = new VGS_Mail();
	$mail->sendHtmlMail($from, $fromName, $toList, $subject, $bodyText, 'text');

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

}

