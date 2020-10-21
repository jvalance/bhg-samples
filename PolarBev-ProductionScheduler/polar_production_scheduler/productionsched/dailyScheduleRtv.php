<?php
require_once 'model/FirmOrders.php';
require_once 'model/JQGrid_Paginator.php';
require_once 'common/autoloader.php';

$debug = ($_REQUEST['debug'] == '1');
echo "Is DEBUG?? = '$debug' <p>";

if ($debug) {
	$dsr_writer = new Zend_Log_Writer_Stream('logs/dailyScheduleRtvDebug.log');
	$dsr_logger = new Zend_Log($dsr_writer);
	$dsr_logger->debug("=================================");
}

$filters = array();
$filters['facility'] = $_REQUEST['facility'];
$filters['sched_date'] = $_REQUEST['from_date'];
$filters['work_ctr'] = $_REQUEST['work_ctr'];

$firmOrders = new FirmOrders();
$selectFirmOrds = new polarbev\DB_Select();
$firmOrders->getFirmOrders_KFP($selectFirmOrds, $filters);

$shopOrders = new FirmOrders();
$selectShopOrds = new polarbev\DB_Select();
$shopOrders->getShopOrders_FSO($selectShopOrds, $filters);

$totalRowCount =
	$firmOrders->getRowCount($selectFirmOrds) + $shopOrders->getRowCount($selectShopOrds);
if ($debug) $dsr_logger->debug("totalRowCount = $totalRowCount");

$pager = new JQGrid_Paginator($totalRowCount);

$query = $selectFirmOrds->toString() . ' UNION ALL ' . $selectShopOrds->toString() . ' order by Due_Date, SEQNO';
if ($debug) $dsr_logger->debug($query);

// the actual query for the grid data
$parms = array_merge($selectFirmOrds->parms, $selectShopOrds->parms);
if ($debug) $dsr_logger->debug(var_export($parms, true));

$firmOrders->execScrollableListQuery_String($query, $parms);

// prepare json response data
$response->page = $pager->pageToView;
$response->total = $pager->numberOfPages;
$response->records = $totalRowCount;

// initialize looping variables
$row_count = 0;
$rowNumber = $pager->startRow+1;

if ($debug) $dsr_logger->debug(var_export($pager, true));

while ( $row = db2_fetch_assoc($firmOrders->stmt, $rowNumber++ )) {
	if ($debug) $dsr_logger->debug("rowNumber = $rowNumber; row_count = $row_count");
	if ($row_count++ > $pager->pageSize) break;
 	if ($debug) $dsr_logger->debug(var_export($row, true));
	$response->rows [] = array(
		'id' => $rowNumber,
		'cell' => array (
			$row['SEQNO'],
			$row['ITEM_NUMBER'],
			htmlentities($row['ITEM_DESC']),
			number_format($row['PLAN_QTY'],0),
			$row['ORDER_TYPE']
		)
	);
}

echo json_encode ( $response );
?>