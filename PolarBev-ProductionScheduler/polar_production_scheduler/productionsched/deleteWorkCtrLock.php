<?php
// ini_set("display_errors", 1);
/**
 * This script will be used to delete a lock on a particular production line
 * when a user logs out or clicks "start over" while working on that produciton line.
 */

require_once 'model/WorkCenter.php';
require_once '../common/DB_Table.php';
require_once '../common/DB_Select.php';
require_once '../common/util.php';

session_start();

$response = array();

use polarbev\DB_Select as DB_Select;
use polarbev\DB_Table as DB_Table;

$facility = $_REQUEST['facility'];
$work_ctr = $_REQUEST['work_ctr'];

$data = array (
	'facility' => $facility,
	'work_ctr' => $work_ctr
);

$wc = new WorkCenter();

$wc->deleteWorkCtrLock($data);

// pre_dump ($response);
// pre_dump ($data);
