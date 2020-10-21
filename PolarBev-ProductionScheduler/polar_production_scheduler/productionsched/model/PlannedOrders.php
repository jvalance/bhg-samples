<?php
require_once '../common/DB_Conn.php';
require_once '../common/DB_Table.php';
require_once '../common/DB_Select.php';

class PlannedOrders extends polarbev\DB_Table {

	function __construct() {
		parent::__construct();
	}

	public function getPlannedOrdersSelect(
			polarbev\DB_Select $select,
			array $filters )
	{
		$select->columns = <<<SELCOLS
			KFP.FPPRTM as SEQNO,
			IIM.IITYP as Item_Type,
			IIM.IPFDV as Group_Tech,
		    FRT.RTRTEM as Routing_Method,
			IIM.IREF04 as Package,
			KFP.FPROD as Item_Number,
			IIM.IDESC as Item_Desc,
			KFP.FRDTE as Due_Date,
			KFP.FQTY as Plan_Qty,
    		( -- Get total on-hand for all nettable warehouses for this item/facility
		      select sum(IWI.WOPB + IWI.WRCT + IWI.WADJ - IWI.WISS)
    			from IWI
    			join IWM
    				on LWHS = WWHS
    				and WMFAC = FPFAC
    				and LNETW = 'Y'
    			where FPROD = WPROD
    		) as ON_HAND,
    		( -- Get total available for all nettable warehouses for this item/facility
		      select sum(IWI.WOPB + IWI.WRCT + IWI.WADJ - IWI.WISS - IWI.WCUSA)
    			from IWI
		        join IWM
    				on LWHS = WWHS
    				and WMFAC = FPFAC
    				and LNETW = 'Y'
    			where FPROD = WPROD
		    ) as AVAIL,
			KFP.FRSDT as Reschedule,
			KFP.FTYPE as Order_Type,
			KFP.FPCSEQ as Seq_No,
			dec(round(dec((KFP.FQTY/FRT.RMAC),9,2) * dec((LWK.WKDLP/100),7,2),2),7,2) as HOURS

SELCOLS;
		$select->from = "KFP"; // (Planned Order file)
		$select->joins = <<<SELJOINS
		left join IIM
		on KFP.FPROD = IIM.IPROD

		left join FRT
		on KFP.FPROD = FRT.RPROD
		and KFP.FPFAC = FRT.RTWHS
		and FRT.ROPNO = 10

		left join LWK
		on FRT.RWRKC = LWK.WWRKC
SELJOINS;
		$select->andWhere("KFP.FTYPE = 'P'");
		$select->andWhere("KFP.FPFAC = ?", $filters['facility']);
		$select->andWhere("FRT.RWRKC = ?", $filters['work_ctr']);

		if (isset($filters['sched_date'])) {
			$select->andWhere("KFP.FRDTE = ?", $filters['sched_date']);
		}
		if (isset($filters['from_date'])) {
			$select->andWhere("KFP.FRDTE >= ?", $filters['from_date']);
		}
		if (isset($filters['to_date'])) {
			$select->andWhere("KFP.FRDTE <= ?", $filters['to_date']);
		}

		$select->order = 'KFP.FRDTE, KFP.FPCSEQ';
// 		pre_dump($select->toString());
		return $select;
	}
}

?>