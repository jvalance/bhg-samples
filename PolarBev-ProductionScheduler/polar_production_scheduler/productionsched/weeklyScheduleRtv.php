<?php
require_once 'model/FirmOrders.php';
require_once 'model/WorkCenter.php';
require_once 'model/JQGrid_Paginator.php';

date_default_timezone_set('America/New_York');

$logger = new Zend\Log\Logger;
$writer = new Zend\Log\Writer\Stream('logs/weeklyScheduleRtvDebug.log');
$logger->addWriter($writer);

$debug = ($_REQUEST['debug'] == '1');
if ($debug) {
	$logger->debug("=================================");
	$logger->debug(var_export($_REQUEST,true));
}

$filters = array();
$filters['facility'] = $_REQUEST['facility'];
$filters['work_ctr'] = $_REQUEST['work_ctr'];
$sched_date = $_REQUEST['weekly_from_date'];
$wkOrdno = 0;

// Define the response data structure (array $weekly) to be returned as JSON
$weekly = array();

// Retrieve daily capacity for this work center
$wrkCtrObj = new WorkCenter();
$wrkCtrRec = $wrkCtrObj->getWorkCenterRec($_REQUEST['work_ctr']);
//$capacity = (int)$wrkCtrRec['WSHFT'] * (float)$wrkCtrRec['WSHRS'];
$weekly['workcenter']['wcNum'] = $wrkCtrRec['WWRKC'];
$weekly['workcenter']['numShifts'] = (int)$wrkCtrRec['WSHFT'];
$weekly['workcenter']['hrsPerShift'] = (float)$wrkCtrRec['WSHRS'];
$weekly['workcenter']['description'] = $wrkCtrRec['WDESC'];
$weekly['workcenter']['facility'] = $filters['facility'];


// Load 12 days worth of production schedule
for ($i = 1; $i <= 12; $i++) {

	$firmOrders = new FirmOrders();
	$selectFirmOrds = new polarbev\DB_Select();

	$shopOrders = new FirmOrders();
	$selectShopOrds = new polarbev\DB_Select();

	$filters['sched_date'] = $sched_date;
	$firmOrders->getFirmOrders_KFP($selectFirmOrds, $filters);
	$shopOrders->getShopOrders_FSO($selectShopOrds, $filters);
	$query =  $selectFirmOrds->toString() . ' UNION ALL '
			. $selectShopOrds->toString() . ' order by Due_Date, SEQNO';

// 	pre_dump($query);

	// Run the actual query for the bar charts
	$parms = array_merge($selectFirmOrds->parms, $selectShopOrds->parms);

// 	pre_dump($parms);
// exit;

	if ($debug) $logger->debug($query);
	if ($debug) $logger->debug(var_export($parms,true));

	$firmOrders->execListQuery($query, $parms);

	$row_count = 0;
	$dateVar = convertDateFormat($sched_date, 'Ymd', 'Y-m-d');
	$weekly[$dateVar] = array();
	$seqNo = 0;

	while ( $row = db2_fetch_assoc($firmOrders->stmt) ) {
		$dateObj = new DateTime($row['DUE_DATE']);
		$dueDate = $dateObj->format('Y-m-d');
		$ordno = $row['ORDNO'];
		if ($ordno == 0) {
			$ordno = 'F'.$wkOrdno++;
		}
		$qty = number_format($row['PLAN_QTY'],0,'.','');
		$seqNo += 10;

		$altRoutes = FirmOrders::getProductAlternateRoutings(
					$logger, trim($row['ITEM_NUMBER']), $filters['facility'], $filters['work_ctr']);
		if (count($altRoutes) > 0 && $debug)
			$logger->debug("**** alternate Routings for {$row['ITEM_NUMBER']}, {$filters['facility']}, {$filters['work_ctr']}: \n------------------\n"
				. var_export($altRoutes,true));

		$weekly["$dueDate"][$ordno] =
			array('orderno' => trim($ordno),
				'qty' => trim($qty),
				'origQty' => trim($qty),
				'hours' => $row['HOURS'],
				'seqno' => $seqNo,
				'origSeqno' => $row['SEQNO'],
				'type' => $row['ORDER_TYPE'],
				'origType' => $row['ORDER_TYPE'],
				'hoursAlpha' => (float) $row['HOURS']>0? ' @ ' . trim($row['HOURS']).' Hrs':'',
				'itemno' => trim($row['ITEM_NUMBER']),
				'itemdesc' => trim($row['ITEM_DESC']),
				'duedate' => trim(date('M d, Y', strtotime($row['DUE_DATE']))),
				'dateYMD' => $dueDate,
				'originalDateYMD' => $dueDate,
				'reschedule' => trim(formatDate($row['RESCHEDULE'])),
				'lotSize' => $row['LOT_SIZE'],
				'incrOrderQty' => $row['INCR_ORDER_SIZE'],
				'altRoutes' => $altRoutes,
				'hasAltRoutes' => count($altRoutes) > 0,
				'route' => $filters['work_ctr'],
				'inProcFlag' => $row['IN_PROC_FLAG']
			);
	}

	$dateObj2 = new DateTime($dateVar);
	$dateObj2->add(new DateInterval('P1D')); // add 1 day to date
	$sched_date = $dateObj2->format('Ymd');
}

echo json_encode($weekly, JSON_FORCE_OBJECT);

?>