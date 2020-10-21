<?php
// ini_set("display_errors", 1);
/**
 * This script will be used to place a lock on a particular production line
 * when a user logs in to that production line.
 * It will be called using Ajax from the javascript function: validateInputs() in prodSchedSelect.php.
 */
require_once 'model/WorkCenter.php';
require_once '../common/DB_Table.php';
require_once '../common/DB_Select.php';
require_once '../common/util.php';

session_start();

use polarbev\DB_Select as DB_Select;
use polarbev\DB_Table as DB_Table;

$data = array (
	'facility' => $_REQUEST['facility'],
	'work_ctr' => $_REQUEST['work_ctr']
);

$wc = new WorkCenter();

$response = $wc->createUserLock($data);

echo json_encode($response,JSON_FORCE_OBJECT);

// pre_dump ($response);
// pre_dump ($data);
