<?php
// ini_set("display_errors", 1);
/**
 * This script will be used to update a lock on a particular production line
 * when a user makes a change to a production line (preventing timeout while working).
 */

require_once 'model/WorkCenter.php';
require_once '../common/DB_Table.php';
require_once '../common/DB_Select.php';
require_once '../common/util.php';

session_start();

$response = array();

use polarbev\DB_Select as DB_Select;
use polarbev\DB_Table as DB_Table;

$logger = new Zend\Log\Logger;
$writer = new Zend\Log\Writer\Stream('logs/updateUserLock.log');
$logger->addWriter($writer);

$logger->info("updateUserLock.php " . date('M d, Y h:i:s'));

$facility = $_REQUEST['facility'];
$work_ctr = $_REQUEST['work_ctr'];

$data = array (
	'facility' => $facility,
	'work_ctr' => $work_ctr
);
pre_dump($data);
exit;

$wc = new WorkCenter();

$wc->updateUserLock($data);

// pre_dump ($response);
// pre_dump ($data);
