<?php
require_once '../model/VGS_DB_Table.php';
require_once '../model/Code_Values_Master.php';

class Premise extends VGS_DB_Table
{
// 	AC   Active Premises
// 	CD   Condemned
// 	CL   Closed Services at Premises
// 	CX   Application Cancelled
// 	IA   Inactive Premises
// 	OS   Off for the Season
// 	PN   Pending (Construction)
// 	PR   Propane Hold (pending)
// 	VA   Vacant
// 	XX   SERVICE REMOVED
	
    public function __construct($conn) {
    	parent::__construct($conn);
//    	$this->checkPermissionByCategory('WO', 'INQUIRY');
    	$this->tableName = 'UPRM';
    	// Following not needed for UPRM - since no maintenance allowed
		$this->tablePrefix = 'UP';
	    $this->keyFields = array('UPPRM');
    	$this->hasAuditFields = false;
    }

    public function retrieve( $premiseNo ) {
		$select = new VGS_DB_Select();
		$select->from = $this->tableName . ' as prm ';
		$select->joins = "LEFT JOIN UPTP prmtyp on prmtyp.UIPPT = prm.UPTYP       
						  LEFT JOIN UDWL dwltyp on dwltyp.UIDWL = prm.UPDWC";
		$select->andWhere("trim(UPPRM) = ?", trim($premiseNo) );
		$this->execListQuery($select->toString(), $select->parms);
		$row = db2_fetch_assoc( $this->stmt );
		return $row;
    }

    public function retrieve_UCSR( $premiseNo ) {
		$select = new VGS_DB_Select();
		$select->from = 'UCSR as uc1';
		
		// Match on premise no.
		$select->andWhere("trim(uc1.UCPRM) = ?", trim($premiseNo) );
		// Rate schedule not blank
		$select->andWhere("trim(uc1.UCSCH) <> ''");
		// Closed date = zero (i.e. active record)
		$select->andWhere("trim(uc1.UCCLD) = 0");
		// Select service record with most recent connect date
		$select->andWhere("uc1.UCCON = (select max(uc2.UCCON) from UCSR uc2 where trim(uc2.UCPRM) = ?)", trim($premiseNo) );
		
		$this->execListQuery($select->toString(), $select->parms);
		$row = db2_fetch_assoc( $this->stmt );
		return $row;
    }

    /**
     * Calls RPG program GETRATE to retrieve the rate class for a premise#.
     * @param String $premiseNo
     */
    public function getRateClass( $premiseNo ) {

    	$i5o = new VGS_i5_Conn();
    	$tko = $i5o->connect_default();
    	
		$premPad = str_pad($premiseNo, 15, ' ', STR_PAD_LEFT); // pad premise# with blanks, right-adjusted
		$rateClassIn = '     ';
		$param[] = $tko->AddParameterChar('both', 15, 'premise', 'premise', $premPad);
		$param[] = $tko->AddParameterChar('both', 5, 'rate_class', 'rateClassReturned', $rateClassIn);

		$pgmResults = $tko->PgmCall("GETRATE", "", $param, null, null);
		
		if (!$pgmResults) {
			throw new Exception("Error calling program GETRATE. Error = " . 
				$tko->getErrorCode() . ' - ' . $tko->getErrorMsg());
			$rateClassReturned = ' ';
		} else {
			$rateClassReturned = $pgmResults['io_param']['rateClassReturned'];
		}
		
		return $rateClassReturned;
    }
    
    public function buildFilteredSelect(
       	array &$screenData, 
    	VGS_DB_Select $select,
    	VGS_Search_Filter_Group $filter	) 
	{
		$filter->addFilter('UPPRM', 'Premise#');
		$filter->setInputSize('UPPRM', 3);
		// Special where because need to trim leading blanks off premise# for comparison
		$filter->setSpecialWhere('UPPRM');
		
		$filter->addFilter('UPSAD', 'Svc Address', 'LIKE');
		$filter->setInputSize('UPSAD', 25);
		
		$filter->addFilter('UPCTC', 'City', 'LIKE');
		$filter->setInputSize('UPCTC', 20);
		
		$filter->addFilter('UPTYP', 'Prem Type');
		$filter->setInputSize('UPTYP', 3);
		
		$filter->saveRestoreFilters($screenData);
		
		$select->from = "{$this->tableName} as prm";
		$select->order = 'UPPRM desc';
//		$select->andWhere("UPSTS <> 'CL'"); // exclude closed premise records
		
		$filter->renderWhere($screenData, $select);

		// Special WHEREs
		$premNo = trim($screenData['filter_UPPRM']);
		if ($premNo != '') {
			$select->andWhere("trim(UPPRM) = ?", trim($premNo) );
		}
		
    }	
    
    public function buildFilteredSelect_SvcXref(
    		array &$screenData,
    		VGS_DB_Select $select,
    		VGS_Search_Filter_Group $filter	)
    {
    	$svID = $screenData['filter_SV_SERVICE_ID'];
    	if (!isset($svID) ) die('Service ID is required.');
    	
    	$cvm = new Code_Values_Master($this->db_conn_obj);
    	
    	$select->joins = "LEFT JOIN SV_PREMISE_XREF as xref on UPPRM = SPX_PREMISE_NUM";
		
		// Drop-down - to choose premises based on whether linked to current service
		$filter->addFilter('SVID_ONLY', 'Linked Only?');
		$filter->setSpecialWhere('SVID_ONLY');
		$linkedOnlyChoices = array( ''  => 'All', 
									'L' => "Linked to Service ID $svID", 
									'A' => "Available to Service ID $svID", 
									'O' => "Linked to another service");
		$filter->setDropDownList('SVID_ONLY', $linkedOnlyChoices);
		
    	$filter->addFilter('UPPRM', 'Premise#');
    	$filter->setInputSize('UPPRM', 3);
    	// Special where because need to trim leading blanks off premise# for comparison
    	$filter->setSpecialWhere('UPPRM');
    
    	$filter->addFilter('UPSAD', '<br />Svc Address', 'LIKE');
    	$filter->setInputSize('UPSAD', 25);

    	$filter->addFilter('UPCTC', 'City', 'LIKE');
    	$city_names = $cvm->getCodeValuesList('SV_PREM_ADDR_XREF', '-- All --', true);
    	$filter->setDropDownList('UPCTC', $city_names);
    	     
    	$filter->addFilter('UPTYP', 'Prem Type');
    	$filter->setInputSize('UPTYP', 3);
    	
    	$filter->addFilter('UPSTS', 'Status');
    	$filter->setSpecialWhere('UPSTS');
    	//$filter->setDefaultValue('UPSTS', 'AC'); //  Should this be Active Premises or *All operational?
    	$status_codes = $cvm->getCodeValuesList('PR_STATUS', '-- All --');
    	$status_codes['*operl'] = '*All operational';
    	$filter->setDropDownList('UPSTS', $status_codes);
    
    	$select->from = "{$this->tableName} as prm";
    	$select->columns = "prm.*, xref.*";
    	$select->order = 'UPPRM desc';
    	//		$select->andWhere("UPSTS <> 'CL'"); // exclude closed premise records
    
		switch (trim($screenData['filter_SVID_ONLY'])) {
			case 'L':
				$select->andWhere("SPX_SERVICE_ID = ?", trim($svID));
				break;
			case 'A':
				$select->orWhere("SPX_SERVICE_ID = ?", trim($svID));
				$select->orWhere("SPX_SERVICE_ID IS NULL");
				break;
			case 'O':
				$select->andWhere("SPX_SERVICE_ID <> ?", trim($svID));
				$select->andWhere("SPX_SERVICE_ID IS NOT NULL");
				break;			
		}
    	
    	 
    	$filter->saveRestoreFilters($screenData);
		//-------------------------------------------------
		$filter->renderWhere($screenData, $select);
    	    
    	// Special WHEREs
    	$premNo = trim($screenData['filter_UPPRM']);
    	if ($premNo != '') {
    		$select->andWhere("trim(UPPRM) = ?", trim($premNo) );
    	}

    	// Special processing for premise status filter
    	if (isset($screenData['filter_UPSTS'])
    	&& trim($screenData['filter_UPSTS']) != '')
    	{
    		$prstatus = trim($screenData['filter_UPSTS']);
    		if ($prstatus == '*operl') {
    			$select->andWhere("UPSTS in ('AC', 'OS', 'PN', 'PR', 'VA')");
    		} else { // specific premise status selected
    			$select->andWhere('UPSTS = ?', $prstatus);
    		}
    	}
    	 
    	
    }
    
    
    public function buildFilteredSelect_SR_ST(
       	array &$screenData, 
    	VGS_DB_Select $select,
    	VGS_Search_Filter_Group $filter	) 
	{
		$cvm = new Code_Values_Master($this->db_conn_obj);
		
		$filter->addFilter('UPPRM', 'Premise#');
		$filter->setInputSize('UPPRM', 3);
		// Special where because need to trim leading blanks off premise# for comparison
		$filter->setSpecialWhere('UPPRM');
		
		$filter->addFilter('from_UPSH#', 'From House#');
		$filter->setInputSize('from_UPSH#', 7);
		$filter->setSpecialWhere('from_UPSH#');
		
		$filter->addFilter('to_UPSH#', 'To House#');
		$filter->setInputSize('to_UPSH#', 7);
		$filter->setSpecialWhere('to_UPSH#');
		
		$filter->addFilter('UPSAD', 'Svc Address', 'LIKE');
		$filter->setInputSize('UPSAD', 15);
		
		$filter->addFilter('UPCTC', 'City', 'LIKE');
		$filter->setInputSize('UPCTC', 15);
		
		$filter->addFilter('UPSTS', 'Status');
		$filter->setInputSize('UPSTS', 2);
		$prm_sts = $cvm->getCodeValuesList('PREMISE_STS', ' ');
		$filter->setDropDownList('UPSTS', $prm_sts);
		
		$filter->addFilter('wo_pipe_type', 'Pipe Type');
		$filter->setInputSize('wo_pipe_type', 4);
		
		$filter->saveRestoreFilters($screenData);
		
		$select->columns = "prm.*, wo1.wo_pipe_type, wo1.wo_num, pt_description, cv1.cv_value as PREM_STS";
		$select->from = "{$this->tableName} as prm";
		$select->joins = "left join workorder_master wo1
						 on UPPRM = wo1.WO_PREMISE_NUM and wo1.WO_TYPE = 'SI' 
						 and wo1.WO_ENTRY_DATE = 
						 (select max(wo_entry_date) 
						 	from workorder_master wo2 
						 	where wo2.WO_PREMISE_NUM = UPPRM 
						 	and wo2.WO_TYPE in ('SB','SI','SM','SR','ST')
							and wo2.wo_pipe_type > 0) ";
		$select->joins .= " left join pipe_type_master on wo1.wo_pipe_type = pt_pipe_type ";
		$select->joins .= " left join code_values_master cv1 on cv_group = 'PREMISE_STS' and CV_CODE = UPSTS ";
		$select->order = 'UPSAD';
//		$select->andWhere("UPSTS <> 'CL'"); // exclude closed premise records
		
		$filter->renderWhere($screenData, $select);

		// Special WHEREs
		$premNo = trim($screenData['filter_UPPRM']);
		if ($premNo != '') {
			$select->andWhere("trim(UPPRM) = ?", trim($premNo) );
		}
		
		$fromHouse = trim($screenData['filter_from_UPSH#']);
		if ($fromHouse != '') {
			$fromHouse = str_pad($fromHouse, 7, '0', STR_PAD_LEFT);
			$select->andWhere("UPSH# >= ?", trim($fromHouse));
		}
		
		$toHouse = trim($screenData['filter_to_UPSH#']);
		if ($toHouse != '') {
			$toHouse = str_pad($toHouse, 7, '0', STR_PAD_LEFT);
			$select->andWhere("UPSH# <= ?", trim($toHouse));
		}
// 		pre_dump($select);	
    }	
    
}
