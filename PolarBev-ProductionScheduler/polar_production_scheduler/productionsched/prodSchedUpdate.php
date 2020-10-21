<?php
require_once 'model/FirmOrders.php';
require_once 'model/WorkCenter.php';
require_once 'model/Product.php';

use polarbev\Product;
// use polarbev\DB2_Adapter as DB2_Adapter;
use polarbev\DB_Conn as DB_Conn;


function updateWeeklySchedule() {
	$logger = new Zend\Log\Logger;
	$writer = new Zend\Log\Writer\Stream('logs/prodSchedUpdate.log');
	$logger->addWriter($writer);
	$logger->info("================= Entering updateWeeklySchedule() =======================");

	$conn = DB_Conn::getInstance();
// 	$conn = DB2_Adapter::getInstance();
	$objProd = new Product();

	$objFirmOrders = new FirmOrders();
	$objWrkCtr = new WorkCenter();

	// Parse json for weekly schedule into associative array
	$weeklySchedule = json_decode($_POST['jsonWeekly'], true);
 	$logger->info($_POST['jsonWeekly']);

	foreach ($weeklySchedule as $newDate => $daysOrders) :
		if ($newDate == 'workcenter') :
			// First object in the weekly grid is actually work center details, not an order.
			$oldWorkCtr = $daysOrders['wcNum'];
			$facility = $daysOrders['facility'];
		else :
			$newDate = date('Ymd', strtotime($newDate));
			//$logger->info("outer loop: newDate = $newDate");

			foreach ($daysOrders as $orderNo => $orderDetails) :
				$blnOrderUpdated = false; // flag to track if order was updated.

				$itemNo = $orderDetails['itemno'];
				$oldDate = date('Ymd', strtotime($orderDetails['originalDateYMD']));
				$newSeqNo = $orderDetails['seqno'];
				$oldSeqNo = $orderDetails['origSeqno'];
				$newType = $orderDetails['type'];
				$oldType = $orderDetails['origType'];
				$newQty = $orderDetails['qty'];
				$oldQty = $orderDetails['origQty'];
				$newWorkCtr = $orderDetails['route'];
				//$logger->info("inner loop: itemno = $itemNo; oldDate = $oldDate; orderDetails['originalDateYMD'] = {$orderDetails['originalDateYMD']}");

				if (substr($orderNo, 0, 4) =='Firm') {
					// This is an order moved from planned to firm. Update planned order to be firm planned
					$mfgMethRoute = $objWrkCtr->getMfgMethRoutingCode($itemNo, $facility, $newWorkCtr);
				    $objFirmOrders->changePlannedOrderToFirm($conn, $itemNo, $oldDate, $newDate, $newSeqNo,  $newQty, $mfgMethRoute);
					$blnOrderUpdated = true;
				}
				// Update database if data has changed
				if ($oldDate != $newDate
    				|| $oldSeqNo != $newSeqNo
    				|| $oldType  != $newType
    				|| $oldQty   != $newQty
    				|| $oldWorkCtr != $newWorkCtr)
				{
					// Get the routing code to update FPRTEM for the currently selected work center
					$mfgMethRoute = $objWrkCtr->getMfgMethRoutingCode($itemNo, $facility, $newWorkCtr);

					$newValues = array('date'=>$newDate, 'seqno'=>$newSeqNo,
							'qty'=>$newQty, 'type'=>$newType, 'route' => $mfgMethRoute);

					if (substr($orderNo, 0, 1) == 'F') {
						// Firm orders have no order #; use product# + date as key
						$objFirmOrders->updateOrder($conn, $logger, 'KFP', $itemNo, $oldDate, $newValues);
					} else {
						$objFirmOrders->updateOrder($conn, $logger, 'FSO', $orderNo, $oldDate, $newValues);
					}
					$blnOrderUpdated = true;
				}

				if ($blnOrderUpdated) {
					// If any updates took place, update MRP activity flag to Y, for product/facility
					$objProd->updateMRPActivityFlag($itemNo, $facility);
				}

			endforeach;
		endif;
	endforeach;
}
