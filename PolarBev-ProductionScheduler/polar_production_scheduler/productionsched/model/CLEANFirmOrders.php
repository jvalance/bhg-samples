<?php
require_once '../common/DB_Table.php';
require_once '../common/DB_Select.php';
require_once '../common/util.php';
use polarbev\DB_Conn as DB_Conn;

class FirmOrders extends polarbev\DB_Table {
	
	function __construct() {
		parent::__construct();
	}
	
	public function getFirmOrders_KFP( 
		polarbev\DB_Select $select, 
		array $filters 
	){
		$select->columns = <<<SELCOLS
			FPPRTM as SEQNO,
			KFP.FPROD as Item_Number,
			IIM.IDESC as Item_Desc,
			KFP.FRDTE as Due_Date,
			KFP.FQTY as Plan_Qty,
			KFP.FTYPE as Order_Type,
			CIC.ICLOTS as Lot_Size,
			CIC.ICIOQ as Incr_Order_Size,
			KFP.FRSDT as Reschedule,
			0 as ORDNO,
			dec(round(dec((KFP.FQTY/FRT.RMAC),9,2) * dec((LWK.WKDLP/100),7,2),2),7,2) as HOURS 
			
SELCOLS;
		$select->from = "KFP"; // (Planned Order file)
		$select->joins = <<<SELJOINS
		left join IIM 
		on KFP.FPROD = IIM.IPROD
		
		left join CIC 
		on KFP.FPROD = CIC.ICPROD
		and KFP.FPFAC = CIC.ICFAC
		
		left join FRT 
		on KFP.FPROD = FRT.RPROD
		and KFP.FPFAC = FRT.RTWHS
		and FRT.ROPNO = 10 
		and KFP.FPRTEM = FRT.RTRTEM
		
		left join LWK 
		on FRT.RWRKC = LWK.WWRKC
SELJOINS;
		$select->andWhere("KFP.FTYPE = 'F' "); 
		$select->andWhere("KFP.FPFAC = ?", $filters['facility']);
		$select->andWhere("FRT.RWRKC = ?", $filters['work_ctr']);
		//$select->andWhere("KFP.FPRTEM = FRT.RTRTEM");
		$select->andWhere("FRT.RMAC <> 0");
		$select->andWhere("FRT.RMAC is not null");

		if (isset($filters['sched_date'])) {
			$select->andWhere("KFP.FRDTE = ?", $filters['sched_date']);
		}
		if (isset($filters['from_date'])) {
			$select->andWhere("KFP.FRDTE >= ?", $filters['from_date']);
		}
		if (isset($filters['to_date'])) {
			$select->andWhere("KFP.FRDTE <= ?", $filters['to_date']);
		}
		
// 		$select->order = "KFP.FRDTE, KFP.FPPRTM, KFP.FPROD";	
			
// 		pre_dump($select->toString());
// 		pre_dump($select->parms);
		
		return $select;
	}	
	
	public function getShopOrders_FSO( 
		polarbev\DB_Select $select, 
		array $filters 
	){
		//FSO.SUTIM1 as SEQNO,
			
		$select->columns = <<<SELCOLS
			SUTIM1 as SEQNO,
			FSO.SPROD as Item_Number,
			IIM.IDESC as Item_Desc,
			FSO.SRDTE as Due_Date,
			FSO.SQREQ as Plan_Qty,
			'S' as order_type,
			CIC.ICLOTS as Lot_Size,
			CIC.ICIOQ as Incr_Order_Size,
			FSO.SRSDT as Reschedule,
			FSO.SORD AS ORDNO,
			FSO.SHRSM AS HOURS
SELCOLS;
		//fso.SOORDT as Order_Type,
			
		$select->from = "FSO"; // (Shop Order file)
		$select->joins = 
			"left join IIM on FSO.SPROD = IIM.IPROD " .
			"left join CIC on FSO.SPROD = CIC.ICPROD and FSO.SOFAC = CIC.ICFAC";
		
		//$select->andWhere("FSO.FTYPE = 'F' "); 
		$select->andWhere("FSO.SOFAC = ?", $filters['facility']);
		$select->andWhere("FSO.SWRKC = ?", $filters['work_ctr']);
		
		if (isset($filters['sched_date'])) {
			$select->andWhere("FSO.SRDTE = ?", $filters['sched_date']);
		}
		if (isset($filters['from_date'])) {
			$select->andWhere("FSO.SRDTE >= ?", $filters['from_date']);
		}
		if (isset($filters['to_date'])) {
			$select->andWhere("FSO.SRDTE <= ?", $filters['to_date']);
		}
		
// 		$select->order = "FSO.SRDTE, FSO.SUTIM1, FSO.SPROD";	
// 		pre_dump($select->toString());
		
		return $select;
	}

	
	public function updateOrder($conn, $logger, $table, $id, $oldDate, $newValues) {
		// Extract new values from passed array into scalar variables.
		$values = array_values($newValues);
		list($newDate, $newSeqNo, $newQty, $newType, $newRouting) = $values;
		
		if ($table == 'KFP') {
			$sql = "UPDATE KFP 
						SET FRDTE = $newDate, 
						    FDATE = $newDate, 
							FPPRTM = $newSeqNo, 
							FQTY = $newQty,
							FTYPE = '$newType',
							FPRTEM = '$newRouting'
						WHERE FPROD = '$id' 
						AND FRDTE = $oldDate";
		} elseif ($table == 'FSO') {
			$sql = "UPDATE FSO 
						SET SRDTE = $newDate, 
						    SDDTE = $newDate, 
							SUTIM1 = $newSeqNo,
							SQREQ = $newQty 
						WHERE SORD = $id 
						AND SRDTE = $oldDate";
		}
		//$parms = array($newDate, $id, $oldDate);
		//pre_dump($sql);
				
		$stmt = db2_prepare($conn, $sql);
		$res = db2_execute($stmt) //, $parms) 
			or die("Error in class FirmOrder, method updateOrder(), 
					updating order date for table=$table, id=$id, 
					old date = $oldDate, new date = $newDate <br>$sql<br>" . db2_stmt_errormsg());

		$logMsg = "Updated $table for order $orderNo, id = $id, oldDate = $oldDate - with the following:";
		foreach ($newValues as $field => $value) {
			$logMsg .= "\n$field = $value";
		} 
		$logger->info($logMsg);		
		
		return $res;
	}
	
	public function changePlannedOrderToFirm($conn, $id, $oldDate, $firmDate, $seqNo) {
		$sql = "UPDATE KFP 
				SET FRDTE = ?, FPPRTM = ?, FTYPE = 'F' 
				WHERE FPROD = ? AND FRDTE = ?";
		$parms = array($firmDate, $seqNo, $id, $oldDate);
		
		$stmt = db2_prepare($conn, $sql);
		$res = db2_execute($stmt, $parms) 
			or die("Error in class FirmOrder, method changePlannedOrderToFirm(), 
					updating order date for table=KFP, id=$id, 
					old date = $oldDate, new date = $firmDate <br>$sql<br>" . db2_stmt_errormsg());

		return $res;
	}
	
	public static function getProductAlternateRoutings( $logger, $prod, $whs, $wrkctr ) {
		$prod = trim($prod);
		$sql = <<<ALTROUT
			select RWRKC from FRT 
			where trim(rprod) = ? 
			and RTWHS = ? 
			and ROPNO = 10
			and RWRKC <> ?
ALTROUT;
		$parms = array($prod, $whs, $wrkctr);
		$conn = DB_Conn::getInstance();
		
		$stmt = db2_prepare($conn, $sql);
		if (!$stmt) {
			$logger->debug(
				'Error in db2_prepare, ' . 'method ' 
				. __FUNCTION__ . '(): ' 
				. db2_stmt_errormsg());
			return false;
		}
			
		$res = db2_execute($stmt, $parms);
		if (!$res) {
			$logger->debug(
				'Error in db2_execute, ' . 'method ' 
				. __FUNCTION__ . '(): ' 
				. db2_stmt_errormsg());
			return false;
		}
			
		$alts = array(); 
		while ($row = db2_fetch_assoc($stmt)) {
			$alts[] = $row['RWRKC']; 
		}
		return $alts;
	}

}

?>