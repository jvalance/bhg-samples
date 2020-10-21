<?php
require_once 'model/FirmOrders.php';
require_once 'model/WorkCenter.php';
require_once 'model/Product.php';
require_once 'model/VP117X.php';

use polarbev\Product;
// use polarbev\DB2_Adapter as DB2_Adapter;
use polarbev\DB_Conn as DB_Conn;


function convertFirmToShop() {
	$logger = new Zend\Log\Logger;
	$writer = new Zend\Log\Writer\Stream('logs/firmOrdersToShop.log');
	$logger->addWriter($writer);
	$logger->info("================= Entering convertFirmToShop() =======================");

	$conn = DB_Conn::getInstance();
// 	$conn = DB2_Adapter::getInstance();
	$objProd = new Product();

	$objFirmOrders = new FirmOrders();
	$objWrkCtr = new WorkCenter();

	// Parse json for weekly schedule into associative array
	$dailySchedule = json_decode($_POST['jsonDaily'], true);
// 	$logger->info($_POST['jsonDaily']);

	$vp117x = new VP117X;

	$rdate = str_replace("-","",$_REQUEST['weekly_current_date']);

	$next_seq_no = $vp117x->get_next_seqno();

	foreach ($dailySchedule as $orderNo => $orderDetails) {
		// loop through orders and assemble table to send to MRP
		if (substr($orderNo, 0, 1) == 'F') {
			// filter out orders that have been changed to not firm

			$shopOrders = array (
				"itemno" => $orderDetails['itemno'],
				"date" => $rdate,
				"qty" => $orderDetails['qty'],
				"facility" => $_REQUEST['facility'],
				"work_ctr" => $_REQUEST['work_ctr'],
				"seqno" => $next_seq_no,   // set to this for now; set to increment later
			);
			$logger->info(var_export($shopOrders,true));
			$vp117x->addVP117X($shopOrders, $logger);
		}
	}
//  pre_dump($shopOrders);
//  exit;
	// function here that submits $

}
