<?php
/**
 * This script will be used to retrieve information about lot size and alternate routings when
 * an order is dragged from planned to firm and dropped on the weekly schedule.
 * It will be called using Ajax from the javascript function: movePlannedToWeekly().
 */
require_once 'model/FirmOrders.php';
require_once 'model/WorkCenter.php';
require_once '../common/DB_Table.php';
require_once '../common/DB_Select.php';

use polarbev\DB_Select as DB_Select;
use polarbev\DB_Table as DB_Table;

// $conn = DB2_Adapter::getInstance();

$logger = new Zend\Log\Logger;
$writer = new Zend\Log\Writer\Stream('logs/general.log');
$logger->addWriter($writer);

$item = $_REQUEST['itemNo'];
$facility = $_REQUEST['facility'];
$work_ctr = $_REQUEST['work_ctr'];

$altRoutes = FirmOrders::getProductAlternateRoutings(
							$logger, $item, $facility, $work_ctr);

$select = new DB_Select();
$select->columns = "CIC.ICLOTS as Lot_Size, CIC.ICIOQ as Incr_Order_Size ";
$select->from = 'CIC';
$select->andWhere('CIC.ICPROD = ?', $item);
$select->andWhere('CIC.ICFAC = ?', $facility);

$db = new DB_Table();
$cicRow = $db->fetchRow($select->toString(), $select->parms);

if (is_array($cicRow)) {
	$returnValues = array(
		'altRoutes' => $altRoutes,
		'hasAltRoutes' => count($altRoutes) > 0,
		'route' => $work_ctr,
		'lotSize' => $cicRow['LOT_SIZE'],
		'incrOrderQty' => $cicRow['INCR_ORDER_SIZE']
	);
} else {
	$returnValues = array();
}

echo json_encode($returnValues, JSON_FORCE_OBJECT);

