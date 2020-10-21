<?php 
require_once '../model/VGS_DB_Table.php';
require_once '../common/vgs_utilities.php';
require_once '../forms/VGS_Search_Filter.php';
require_once '../model/Code_Values_Master.php';
require_once '../model/Workorder_Leak.php';
require_once '../common/globals.php';
require_once '../model/Project_Master.php';

class Workorder_Master extends VGS_DB_Table
{
	/**
	 * Array of fieldNames/fieldValues for one workorder record retrieved from database.
	 * @var array
	 */
	public $record; 
	
	public static $wo_types_service = array('SB','SI','SM','SR','ST', 'LS');
	public static $wo_types_main = array('MI','MM', 'MN', 'MR', 'MT', 'LM'); 
	public static $wo_types_maintenance = array('SM','MM', 'MN'); 
	public static $wo_types_leak = array('LM','LS');
	public static $wo_types_nonpipe = array('MN','NW', 'SB', 'CA');

	const WO_STATUS_PENDING = 'PND';
	const WO_STATUS_COMPLETED = 'CMP';
	const WO_STATUS_CLOSED = 'CLO';
	const WO_STATUS_CANCEL_PENDING = 'CNP';
	const WO_STATUS_CANCELLED = 'CNL';
	
	public static $wo_sts_comp_closed = array(
		Workorder_Master::WO_STATUS_COMPLETED, 
		Workorder_Master::WO_STATUS_CLOSED
	);
	public static $wo_sts_cancel = array(
		Workorder_Master::WO_STATUS_CANCEL_PENDING, 
		Workorder_Master::WO_STATUS_CANCELLED
	);
	
//	CA	Contribution Aid-Construction	Neither
//	NW	Non Work Order	Neither
//	LM	Main Leak 	Main
//	MI	Main New Construction	Main
//	MM	Main Maintenance	Main
//	MN	Maintenance-Transmission	Main
//	MR	Main Replacement	Main
//	MT	Main Retirement	Main
//	LS	Service Leak 	Service
//	SB	Service Meter Barricade	Service
//	SI	Service New Construction	Service
//	SM	Service Maintenance	Service
//	SR	Service Replacement	Service
//	ST	Service Retirement	Service
	
	/**
	 * Name of the DB2 stored procedure that corresponds to the RPG program for writing the
	 * Lawson interface records for a work order creation.
	 */
	const LAWSON_INTERFACE_WO_CREATE = 'spWO_Create_LawsonActivity';
	/**
	 * Name of the DB2 stored procedure that corresponds to the RPG program for writing the
	 * Lawson interface records for a work order completion.
	 */
	const LAWSON_INTERFACE_WO_COMPLETION = 'spWO_Complete_LawsonActivity';
	
	public function __construct($conn) {
		parent::__construct($conn);
	 	$this->tableName = 'WORKORDER_MASTER';
    	$this->tablePrefix = 'WO_';
		$this->keyFields = array('WO_NUM');
    	$this->hasAuditFields = true;
	}
	
	public static function isService_WO_TYPE( $wotype ) {
		return in_array($wotype, Workorder_Master::$wo_types_service);
	}
	
	public static function isMain_WO_TYPE( $wotype ) {
		return in_array($wotype, Workorder_Master::$wo_types_main);
	}
	
	public static function isMaintenance_WO_TYPE( $wotype ) {
		return in_array($wotype, Workorder_Master::$wo_types_maintenance);
	}
	
	public static function isLeak_WO_TYPE( $wotype ) {
		return in_array($wotype, Workorder_Master::$wo_types_leak);
	}
	
	public function getWorkorder($WO_NUM) {
		$WO_NUM = ( int ) $WO_NUM;
		$select = new VGS_DB_Select ();
		$this->setDefaultWOSelect ( $select );
		$select->andWhere ( 'WO_NUM = ?', $WO_NUM );
		// pre_dump($select->toString());
		$fetchResult = $this->fetchRow ( $select->toString (), $select->parms );
		if (is_array($fetchResult)) {
// 			$fetchResult = array_map ( 'trim', $fetchResult );
			$this->record = $fetchResult;
		}
		// ary_dump($fetchResult);
		return $fetchResult;
	}
	
	public function validate_WO_NUM( $woNum ) {
		$isValidWO = true;
		if (isset($woNum)) {
			$woRec = $this->getWorkorder($woNum);
//			pre_dump($woRec);
			if (!isset($woRec) || $woRec['WO_NUM'] != $woNum) {
				$isValidWO = false;
			}
		} else {
			$isValidWO = false;
		}
		return $isValidWO;
	}
    
	/**
	 * Insert records in WORKORDER_MASTER and WORKORDER_LEAK tables 
	 * for a Leak WorkOrder.
	 * @param array $rec The form inputs
	 */
    public function createLeakWorkOrder( $rec ) {
    	$this->checkPermissionByCategory('WO', 'CREATE');
 
    	$pipeType = $this->getLeakPipeType($rec['LK_TYPE']);
    	if ($pipeType != '') {
    		$rec['WO_PIPE_TYPE'] = $pipeType;
    	} else {
	    	// Default pipe type 44 for LS and 41 for LM
	    	if (trim($rec['WO_TYPE']) == 'LS') {
	    		$rec['WO_PIPE_TYPE'] = '44'; 
	    	}
	    	if (trim($rec['WO_TYPE']) == 'LM') {
	    		$rec['WO_PIPE_TYPE'] = '41'; 
	    	}
    	}
    	
		// Default 01381 for Project#
    	$rec['WO_PROJECT_NUM'] = '01381';
		
    	if ('Y' == $rec['RECHECK_FLAG']) {
			$rec['LK_LEAKWO_TYPE'] = 'ORIG';
    	}
    	
    	$orig_Leak_WO_NUM = $this->getNextWO_Num();
    	$rec['WO_NUM'] = $orig_Leak_WO_NUM;
    	//echo "next_WO_NUM = $orig_Leak_WO_NUM<p>";
    	
    	$wo_OK = $this->autoCreateRecord($rec);
    	//echo "wo_OK = $wo_OK <P>";
    	
		$rec['LK_WO_NUM'] = $rec['WO_NUM'];
		$leak = new Workorder_Leak($this->db_conn_obj);
    	$lk_OK = $leak->autoCreateRecord($rec);
		
    	$this->createLawsonActivity($rec['WO_NUM'], self::LAWSON_INTERFACE_WO_CREATE);
    	
    	if ('Y' == $rec['RECHECK_FLAG']) {
    		$recheckOK = $this->createLeakRecheckWO( $rec );
    	} else {
    		$recheckOK = true;
    	}
    	
    	return $orig_Leak_WO_NUM;
    	//return ($wo_OK && $lk_OK && $recheckOK);
    }

    private function getLeakPipeType($leakType) {
    	$cvm = new Code_Values_Master($this->db_conn_obj);
    	$cvKeys = array('CV_GROUP' => 'LK_TYPE', 
    						 'CV_CODE' => $leakType);
    	$cvRec = $cvm->retrieveRecord($cvKeys);
    	if (is_array($cvRec)) {
    		$cvDesc = $cvRec['CV_DESCRIPTION'];
    		$cvdWords = explode(' ', $cvDesc);
    		$pipeType = end($cvdWords);
    		if (is_numeric($pipeType)) {
    			return $pipeType;
    		}
    	}
    	return '';
    }
    
	/**
	 * Insert records in WORKORDER_MASTER and WORKORDER_LEAK tables 
	 * for a Leak Recheck WorkOrder.
	 * @param array $rec The form inputs
	 */
    private  function createLeakRecheckWO( $rec ) {
    	// Save original W/O num
    	$originalWO = $rec['WO_NUM'];
  		$rec['WO_PRINT_FLAG'] = '';  // clear print flag	
    	
   	// Get recheck w/o num
    	$recheck_WO_NUM = $this->getNextWO_Num();
    	$rec['WO_NUM'] = $recheck_WO_NUM;
    	
    	// Create recheck record in workorder_master
    	$wo_OK = $this->autoCreateRecord($rec);
    	
		$rec['LK_WO_NUM'] = $rec['WO_NUM'];
		$rec['LK_ORIG_WONUM'] = $originalWO;
		$rec['LK_LEAKWO_TYPE'] = 'RECHK';
		$leak = new Workorder_Leak($this->db_conn_obj);
    	// Create recheck record in workorder_leak
		$lk_OK = $leak->autoCreateRecord($rec);
		
		$this->createLawsonActivity($rec['WO_NUM'], self::LAWSON_INTERFACE_WO_CREATE);
		
    	// Update original W/O with recheck w/o num
    	$updateData = array();
		$updateData ['LK_WO_NUM'] = $originalWO;
		$updateData ['LK_RECHECK_WONUM'] = $recheck_WO_NUM;
		$wo_update_OK = $leak->autoUpdateRecord($updateData);
		
    	return ($wo_OK && $lk_OK && $wo_update_OK);
    }
    
	/**
	 * Update records in WORKORDER_MASTER and WORKORDER_LEAK tables 
	 * for a Leak WorkOrder.
	 * @param array $rec The form inputs
	 */
    public function updateLeakWorkOrder( $rec, $blnUpdateLawson = false) {
    	
    	$this->checkPermissionByCategory('WO', 'UPDATE');
    	
    	$wo_OK = $this->autoUpdateRecord($rec);
		$rec['LK_WO_NUM'] = $rec['WO_NUM'];
		$leak = new Workorder_Leak($this->db_conn_obj);
    	$lk_OK = $leak->autoUpdateRecord($rec);

		if ($blnUpdateLawson) {
			$this->createLawsonActivity($rec['WO_NUM'], self::LAWSON_INTERFACE_WO_COMPLETION);
		}
    	
    	// When updating an original leak, propogate any changes 
    	// to the recheck w/o.
    	$rchk_OK = true;
    	if ($rec['LK_LEAKWO_TYPE'] == 'ORIG') {
	    	$rchk_OK = $this->synchLeakRecheckData($rec);
    	}
    	
    	return ($wo_OK && $lk_OK && $rchk_OK);
    }
    
    /**
     * This will be called after updating an original leak w/o, in order to 
     * synchronize certain data values on the recheck w/o.   
     * @param array $rec  The data values from the original leak w/o.
     */
    private function synchLeakRecheckData( $recheckData ) {
    	// Save the recheck w/o# - we are going to remove it from the data array.
    	$recheck_wonum = $recheckData['LK_RECHECK_WONUM'];
    	
    	/* We want to synchronize most of the information from the original
    	 * leak w/o to the recheck, but we need to remove any fields that can 
    	 * be independently updated on the recheck. Otherwise we would run
    	 * the risk of overwriting any user changes that were made to the recheck.
    	 * We must therefore remove (unset) any of these fields in the data array. */ 
    	$fieldsToRemove = 'WO_CHANGE_TIME, WO_CHANGE_USER, WO_CREATE_TIME, WO_CREATE_USER, 
    		WO_COMPLETE_BY_DATE, WO_DATE_COMPLETED, WO_ENTRY_DATE, WO_NUM, WO_PRINT_COUNT, 
    		WO_STATUS, COMPLETE_CB, LK_CREW_ID, LK_PERSONNEL_HOURS, LK_TOTAL_MAN_HOURS, 
    		LK_METHOD_USED, LK_SURVEYED_BY, LK_LEAKWO_TYPE, LK_RECHECK_WONUM, LK_ORIG_WONUM';
    	
    	// Create a new array without the fields that should not be updated on the recheck w/o
    	$this->removeRecheckFields( $recheckData, $fieldsToRemove ); 
    	
    	// Set the w/o num to be updated, using the recheck#, saved above.
		$recheckData['WO_NUM'] = $recheck_wonum;
		
		// Change the leak w/o type to recheck - otherwise we would have an infinite loop
		// on the recursive call.  
		$recheckData['LK_LEAKWO_TYPE'] = 'RECHK'; 

		// NOTE: This is a recursive call:
		return $this->updateLeakWorkOrder($recheckData);
    }
   
    /**
     * This will remove any fields from the data array that should not be synchronized
     * from an original leak w/o to the recheck w/o. This will prevent overwriting 
     * certain fields on the recheck that should be maintained independently of the original w/o.  
     * @param array $data The data array containing values to update in the database records.
     * @param string $remFields A comma-separated list of fields to be removed from the $data array. 
     */
	private function removeRecheckFields( &$data, $remFields ) {
		$remFldsArr = explode(',', $remFields);
		// Trim the field names
		$remFldsArr = array_map(trim, $remFldsArr);
		
		foreach ($remFldsArr as $field) {
			if (isset($data[$field])) {
				unset($data[$field]);
			}
		}
	}
	
    /**
	 * Insert a record in the WORKORDER_MASTER table
	 * @param array $rec
	 */
    public function createWorkOrder( $rec ) {
    	$this->checkPermissionByCategory('WO', 'CREATE');
    	
    	$rec['WO_NUM'] = $this->getNextWO_Num();
    	$crt_res = $this->autoCreateRecord($rec);
    	$this->createLawsonActivity($rec['WO_NUM'], self::LAWSON_INTERFACE_WO_CREATE);
    	
    	// Handle sales app for SI w/o type
    	if ($rec['WO_TYPE'] == 'SI' 
    	&& trim($rec['WO_SALES_APP_NUM']) != '0' 
    	&& trim($rec['WO_SALES_APP_NUM']) != '') {
    		// Creates sales app x-ref record for primary sales app
    		$sa = new SalesApp($this->db_conn_obj);
    		$sa->linkSlsAppToWO($rec['WO_SALES_APP_NUM'], $rec['WO_NUM']);
    	}
    	
    	// Handle creation of tie in for the following w/o types
		$tieInTypes = array('MR', 'MT', 'MI');
		if (in_array($rec['WO_TYPE'], $tieInTypes)) {
			// Create a tie-in work order if checkbox was checked
			if ($rec['TIE_IN_CB'] == 'Y') {
				$tiRec = $rec;  // default all values from original w/o
		   	 	$tiRec['WO_NUM'] = $this->getNextWO_Num();
		   	 	$tiRec['WO_TYPE'] = 'TI';
		   	 	// Unset fields whose values should not be carried over to the Tie In w/o. 
		   	 	unset($tiRec['WO_ESTIMATED_COST']);
		   	 	unset($tiRec['WO_EST_COST_PER_FOOT']);
		   	 	unset($tiRec['WO_ESTIMATED_LENGTH']);
		   	 	unset($tiRec['WO_ACTUAL_LENGTH']);
		   	 	unset($tiRec['WO_SALES_APP_NUM']);
			    	$crt_res = $crt_res && $this->autoCreateRecord($tiRec);
	    			$this->createLawsonActivity($tiRec['WO_NUM'], self::LAWSON_INTERFACE_WO_CREATE);
				}
		}
    	
    	return $rec['WO_NUM'];
    	//return $crt_res;
    }	

    private function createLawsonActivity( $woNum, $storedProc ) {
    	// Left pad workorder number with zeros to 9 digits. 
    	$woNum = str_pad($woNum, 9, '0', STR_PAD_LEFT);
    	
    	// Call the Lawson interface procedure.
    	$lawsonEnv = VGS_DB_Conn_Singleton::getLawsonEnvironment();
    	$spCall = "call $storedProc('$woNum', '$lawsonEnv');";
    	return $this->execUpdate($spCall); 
    }
    
    public function getNextWO_Num() {
    	$select = new VGS_DB_Select();
    	$select->from = $this->tableName;
    	$select->columns = 'max(WO_NUM) as LAST_WO_NUM';
        $maxAry = $this->fetchRow($select->toString());
        return (int)$maxAry['LAST_WO_NUM'] + 1;
    }
    /**
     * Update a record in the WORKORDER_MASTER table
     * @param array $rec
     */
    public function updateWorkOrder( $rec, $blnUpdateLawson = false) {
    	$this->checkPermissionByCategory('WO', 'UPDATE');
		$this->checkUpdatesForNotification( $rec ); 
    	$result = $this->autoUpdateRecord($rec);
		if ($blnUpdateLawson) {
			$this->createLawsonActivity($rec['WO_NUM'], self::LAWSON_INTERFACE_WO_COMPLETION);
		}
		return $result;
    }	
    
    /**
     * This will send notifications to accounting if certain fields are changed 
     * on completed or closed WOs.
     * @param array $new_values Array containing the new WO record values
     */
    private function checkUpdatesForNotification( $new_values ) {
    	
    	$wo_num = $new_values['WO_NUM'];
    	$sql = "select * from workorder_master where wo_num = $wo_num";
    	
    	$flags = array();
    	
    	static $objTable;
    	if ($objTable == NULL) $objTable = new VGS_DB_Table();
    	
    	if ($old_values = $objTable->fetchRow($sql)) {
    		if (isset($new_values['WO_PROJECT_NUM']) &&
    			trim($new_values['WO_PROJECT_NUM']) != trim($old_values['WO_PROJECT_NUM'])) {
				// Send notification if project changed for any WO status
				$flags['ProjectChgd'] = true;
			}

    		if (isset($new_values['WO_ACTUAL_LENGTH'])) { 
				// Change blank values to 0
    			if (trim($old_values['WO_ACTUAL_LENGTH']) == '' 
	    		|| trim($old_values['WO_ACTUAL_LENGTH']) == '.00') {
					$old_values['WO_ACTUAL_LENGTH'] = '0';
				}
	    		if (trim($new_values['WO_ACTUAL_LENGTH']) == '' 
	    		|| trim($new_values['WO_ACTUAL_LENGTH']) == '.00') {
					$new_values['WO_ACTUAL_LENGTH'] = '0';
				}
    			if (trim($new_values['WO_ACTUAL_LENGTH']) != trim($old_values['WO_ACTUAL_LENGTH'])
				&&  in_array($old_values['WO_STATUS'], Workorder_Master::$wo_sts_comp_closed)) {
					// Send notification if actual length changed for completed or closed WO status
					$flags['ActLenChgd'] = true;
				}
    		}
			
    		if (isset($new_values['WO_DESCRIPTION']) 
    		&&  trim($new_values['WO_DESCRIPTION']) != trim($old_values['WO_DESCRIPTION'])) {
				// Send notification if description changed for any WO status
				$flags['DescriptionChgd'] = true;
			}
			if (isset($new_values['WO_TAX_MUNICIPALITY']) 
    		&&  trim($new_values['WO_TAX_MUNICIPALITY']) != trim($old_values['WO_TAX_MUNICIPALITY'])
			&&  $new_values['WO_STATUS'] == Workorder_Master::WO_STATUS_CLOSED) {
				// Send notification if town changed for closed WO status
				$flags['TownChgd'] = true;
			}
    	}
    	
    	if (count($flags) > 0) {
			$cvm = new Code_Values_Master ();
			$wo_sts_desc = $cvm->getCodeValue ( 'WO_STATUS', $new_values ['WO_STATUS'] );
    		$emailBody = $this->getUpdNotifyBodyText($old_values, $new_values, $flags, $wo_sts_desc);
    		$this->sendUpdateNotification($wo_num, $wo_sts_desc, $emailBody);
    	}
    	
    }
	
	private function getUpdNotifyBodyText($old_values, $new_values, $flags, $wo_sts_desc) {
		$wo_num = $new_values['WO_NUM'];
		$wo_desc = trim($new_values['WO_DESCRIPTION']);
		
		$emailBody = "User {$_SESSION['current_user']} changed the following values " .
			"on work order $wo_num ($wo_desc) with status = '$wo_sts_desc':<br />";
		
		if ($flags['ProjectChgd']) {
			$projObj = new Project_Master ();
			$oldProj = $projObj->retrieveById ( $old_values ['WO_PROJECT_NUM'] );
			$newProj = $projObj->retrieveById ( $new_values ['WO_PROJECT_NUM'] );
			$projMsg = "Project:<br />
		    	Old = <b>{$old_values['WO_PROJECT_NUM']}: {$oldProj['PRJ_DESCRIPTION']}</b><br />
    			New = <b>{$new_values['WO_PROJECT_NUM']}: {$newProj['PRJ_DESCRIPTION']}</b><br />";
			$emailBody .= "----------<br />$projMsg<br />";
		}
		
		if ($flags['ActLenChgd']) {
			$lengthMsg = "Actual Length:<br />
    			Old = <b>{$old_values['WO_ACTUAL_LENGTH']}</b><br />
    			New = <b>{$new_values['WO_ACTUAL_LENGTH']}</b><br />";
			$emailBody .= "----------<br />$lengthMsg<br />";
		}
		
		if ($flags['TownChgd']) {
			$cvm = new Code_Values_Master ();
			$oldTownDesc = $cvm->getCodeValue('TOWN', $old_values ['WO_TAX_MUNICIPALITY'] );
			$newTownDesc = $cvm->getCodeValue('TOWN', $new_values ['WO_TAX_MUNICIPALITY'] );
			$townMsg = "Tax Municipality:<br />
			    Old = <b>{$old_values['WO_TAX_MUNICIPALITY']}: $oldTownDesc</b><br />
				New = <b>{$new_values['WO_TAX_MUNICIPALITY']}: $newTownDesc</b><br />";
			$emailBody .= "----------<br />$townMsg<br />";
		}
		
		if ($flags['DescriptionChgd']) {
			$descMsg = "WO Description:<br />
			    Old = <b>{$old_values['WO_DESCRIPTION']}</b><br />
				New = <b>{$new_values['WO_DESCRIPTION']}</b><br />";
			$emailBody .= "----------<br />$descMsg<br />";
		}
	
		return $emailBody;
	}
    
    private function sendUpdateNotification( $woNum, $woSts, $bodyText ) {
    	$cvm = new Code_Values_Master ();
    	// Email address for "FROM" on update comp/clo WO notifcations
    	$from = $cvm->getCodeValue('WO_UPD_COMP_EMAIL', 'EMAIL_FROM');
    	// Name to use for "FROM" on update comp/clo WO notifcations   
    	$fromName = $cvm->getCodeValue('WO_UPD_COMP_EMAIL', 'EMAIL_FROM_NAME');
    	// List of email addresses, separated by commas, to receive update comp/clo WO notifcations 
    	$toList = $cvm->getCodeValue('WO_UPD_COMP_EMAIL', 'EMAILS_TO');

    	$subject = "Notice of Changes to $woSts W/O# $woNum";

		$mail = new VGS_Mail();
		$mail->sendHtmlMail($from, $fromName, $toList, $subject, $bodyText);
    }
    
    
    /**
     * This will set up the default columns and joins in a VGS_DB_Select object for 
     * Work Order master record selection (including joins to code files etc)
     *   
     * @param VGS_DB_Select $select
     */
    
    public function setDefaultWOSelect( VGS_DB_Select $select ) {
    	$select->from = 'WORKORDER_MASTER as wo';
		$select->columns = <<<WO_COLS_1
			wo.*, 
			lk.*,
			char(fnFmtNullDate(WO_ENTRY_DATE)) as WO_ENTRY_DATE, 
			char(fnFmtNullDate(WO_DATE_COMPLETED)) as WO_DATE_COMPLETED, 
			cv1.CV_VALUE as WO_TYPE_DESC, 
			cv2.CV_VALUE as WO_STATUS_DESC, 
			cv3.CV_VALUE as WO_TOWN_NAME, 
			cv4.CV_VALUE as WO_ZONE_NAME, 
			PRJ_DESCRIPTION as PROJECT_DESC, 
			PRJ_CAP_EXP_CODE as WO_COST_ALLOC,
			PT_DESCRIPTION as PIPE_TYPE_DESC,
			pt.*,
			sa.*, 
			UPSAD, UPARA, 
			UPTYP, UPDWC,
			prmtyp.UIDES as PREMISE_TYPE,
			dwltyp.UIDES as DWELLING_TYPE
WO_COLS_1;
		
		$select->joins = <<<WO_JOINS_1
			LEFT JOIN CODE_VALUES_MASTER as cv1 ON cv1.CV_GROUP = 'WO_TYPE' and cv1.CV_CODE = WO_TYPE 
			LEFT JOIN CODE_VALUES_MASTER as cv2 ON cv2.CV_GROUP = 'WO_STATUS' and cv2.CV_CODE = WO_STATUS  
			LEFT JOIN CODE_VALUES_MASTER as cv3 ON cv3.CV_GROUP = 'TOWN' and cv3.CV_CODE = WO_TAX_MUNICIPALITY  
			LEFT JOIN CODE_VALUES_MASTER as cv4 ON cv4.CV_GROUP = 'ZONE' and cv4.CV_CODE = WO_ZONE_CODE  
			LEFT JOIN PIPE_TYPE_MASTER as pt ON PT_PIPE_TYPE = WO_PIPE_TYPE 
			LEFT JOIN SLSAPP as sa ON SLSBKF = WO_PREMISE_NUM  
			LEFT JOIN PROJECT_MASTER as prj ON PRJ_NUM = WO_PROJECT_NUM 
			LEFT JOIN UPRM prm ON (WO_PREMISE_NUM <> 0 and UPPRM = WO_PREMISE_NUM) 
			LEFT JOIN UPTP prmtyp on prmtyp.UIPPT = prm.UPTYP       
			LEFT JOIN UDWL dwltyp on dwltyp.UIDWL = prm.UPDWC       
			LEFT JOIN WORKORDER_LEAK lk on wo.WO_NUM = lk.LK_WO_NUM       
WO_JOINS_1;
    }

    
    public function buildFilteredSelect(
    	array &$screenData, 
    	VGS_DB_Select $select,
    	VGS_Search_Filter_Group $filter
	   	) 
    {
		$cvm = new Code_Values_Master($this->db_conn_obj);
    	
		$filter->addFilter('WO_NUM', 'W/O#');
		$filter->setInputSize('WO_NUM', 6);
		
		$filter->addFilter('WO_TYPE', 'Type');
		$filter->setSpecialWhere('WO_TYPE');
		$wo_types = $cvm->getCodeValuesList('WO_TYPE', '-- All --');
		$wo_types['LO'] = 'Leaks-Originals'; // = (LM||LS) && LK_LEAKWO_TYPE='ORIG'
		$wo_types['LR'] = 'Leaks-Rechecks'; // = (LM||LS) && LK_LEAKWO_TYPE='RECHK'
		$wo_types['L*'] = '*All Leaks'; // = LM,LS
		$wo_types['M*'] = '*All Main'; // MI, MM, MN, MR, MT 
		$wo_types['S*'] = '*All Service'; // SB,SI,SM,SR,ST
		$filter->setDropDownList('WO_TYPE', $wo_types); 
		
		$filter->addFilter('WO_STATUS', 'Status');
		$filter->setSpecialWhere('WO_STATUS');
		$filter->setDefaultValue('WO_STATUS', 'P'); // Pending
		$status_codes = $cvm->getCodeValuesList('WO_STATUS', '-- All --');
		$status_codes['*not_open'] = '*All not pending';  
		$status_codes['*not_cnl'] = '*All not cancelled';  
		$filter->setDropDownList('WO_STATUS', $status_codes); 
		
		$filter->addFilter('WO_TAX_MUNICIPALITY', 'Town');
		$town_codes = $cvm->getCodeValuesList('TOWN', '-- All --');
		$filter->setDropDownList('WO_TAX_MUNICIPALITY', $town_codes); 
		
		$filter->addFilter('WO_PREMISE_NUM', 'Premise');
		$filter->setInputSize('WO_PREMISE_NUM', 4);
		$filter->addFilter('WO_PROJECT_NUM', 'Project');
		$filter->setInputSize('WO_PROJECT_NUM', 4);
		$filter->addFilter('WO_DESCRIPTION', '<br />Description', 'LIKE');
		
		$filter->addFilter('WO_ENTRY_DATE_from', 'Entry From/To:', '>=');
		$filter->setSpecialWhere('WO_ENTRY_DATE_from');
		$filter->setDateField('WO_ENTRY_DATE_from');

		$filter->addFilter('WO_ENTRY_DATE_to', '', '<=');
		$filter->setSpecialWhere('WO_ENTRY_DATE_to');
		$filter->setDateField('WO_ENTRY_DATE_to');
		
		$filter->addFilter('WO_DATE_COMPLETED_from', 'Comp From/To', '>=');
		$filter->setSpecialWhere('WO_DATE_COMPLETED_from');
		$filter->setDateField('WO_DATE_COMPLETED_from');

		$filter->addFilter('WO_DATE_COMPLETED_to', '', '<=');
		$filter->setSpecialWhere('WO_DATE_COMPLETED_to');
		$filter->setDateField('WO_DATE_COMPLETED_to');

		$filter->addFilter('WO_SPECIAL_INSTRUCTION', 'Spec\'l Instr', 'LIKE');
		
		$filter->saveRestoreFilters($screenData);
    	
		$select->columns = "wo.*, lk.*, " .
				"(select count(*) 
					from  DBGLGLT as gla
					Where trim(gla.DGLTACTVTY) = CHAR(wo.WO_NUM) 
				 ) as dollars_applied, " .
				"(select count(*) 
				 	from DBGLGLT as glb
					Where trim(glb.DGLTACTVTY) = CHAR(wo.WO_NUM)
					and trim(glb.R_SYSTEM) in ('AP', 'IC', 'PR', 'CW')
				 ) as transfer_dollars, " . 
				"cv1.CV_VALUE as TOWN_NAME, " .
				"cv2.CV_VALUE as WO_STATUS_DESC, " .
				"cv3.CV_VALUE as WO_TYPE_DESC ";
		$select->from = 'WORKORDER_MASTER as wo';
		$select->order = 'WO_NUM DESC';
		$select->joins = "LEFT JOIN WORKORDER_LEAK as lk on WO_NUM = LK_WO_NUM
			LEFT JOIN CODE_VALUES_MASTER as cv1 ON cv1.CV_GROUP = 'TOWN' and cv1.CV_CODE = WO_TAX_MUNICIPALITY
			LEFT JOIN CODE_VALUES_MASTER as cv2 ON cv2.CV_GROUP = 'WO_STATUS' and cv2.CV_CODE = WO_STATUS
			LEFT JOIN CODE_VALUES_MASTER as cv3 ON cv3.CV_GROUP = 'WO_TYPE' and cv3.CV_CODE = WO_TYPE
			";
		
		$filter->renderWhere($screenData, $select);

		// Special processing for work order type filter
		$spcl_types = array();
		$spcl_types['L*'] = "'LM','LS'";
		$spcl_types['M*'] = "'MI','MM', 'MN', 'MR', 'MT', 'LM'"; 
		$spcl_types['S*'] = "'SB','SI','SM','SR','ST', 'LS'";
		if (isset($screenData['filter_WO_TYPE']) 
		&& trim($screenData['filter_WO_TYPE']) != '') 
		{
			$wotype = trim($screenData['filter_WO_TYPE']);
			if (strpos($wotype,'*') > 0) { 
				$select->andWhere("WO_TYPE in ({$spcl_types[$wotype]})");  
			} elseif ($wotype == 'LO') { // Leaks-Originals 
		    	$select->andWhere("WO_TYPE in ('LM', 'LS')");
		    	$select->andWhere("LK_LEAKWO_TYPE='ORIG' or trim(LK_LEAKWO_TYPE)='' ");
			} elseif ($wotype == 'LR') { // Leaks-Rechecks
		    	$select->andWhere("WO_TYPE in ('LM', 'LS')");
		    	$select->andWhere("LK_LEAKWO_TYPE='RECHK'");
			} else { // specific w/o type selected
		    	$select->andWhere('WO_TYPE = ?', $wotype);
			}
		}

		// Special processing for work order status filter
		if (isset($screenData['filter_WO_STATUS']) 
		&& trim($screenData['filter_WO_STATUS']) != '') 
		{
			$wostatus = trim($screenData['filter_WO_STATUS']);
			if ($wostatus == '*not_open') { 
				$select->andWhere("WO_STATUS in ('CMP', 'CLO')");  
			} elseif ($wostatus == '*not_cnl') { 
				$select->andWhere("WO_STATUS not in ('CNL', 'CNP')");  
			} else { // specific w/o status selected
		    	$select->andWhere('WO_STATUS = ?', $wostatus);
			}
		}

		// WO_ENTRY_DATE filters
		if (isset($screenData['filter_WO_ENTRY_DATE_from']) 
			&& trim($screenData['filter_WO_ENTRY_DATE_from']) != '') 
		{
			$entry_date_from = trim($screenData['filter_WO_ENTRY_DATE_from']);
	    	$select->andWhere("WO_ENTRY_DATE >= ?", $entry_date_from);
		}
		if (isset($screenData['filter_WO_ENTRY_DATE_to']) 
			&& trim($screenData['filter_WO_ENTRY_DATE_to']) != '') 
		{
			$entry_date_to = trim($screenData['filter_WO_ENTRY_DATE_to']);
	    	$select->andWhere("WO_ENTRY_DATE <= ?", $entry_date_to);
		}

		// WO_DATE_COMPLETED filters
		if (isset($screenData['filter_WO_DATE_COMPLETED_from']) 
			&& trim($screenData['filter_WO_DATE_COMPLETED_from']) != '') 
		{
			$complete_date_from = trim($screenData['filter_WO_DATE_COMPLETED_from']);
	    	$select->andWhere("WO_DATE_COMPLETED >= ?", $complete_date_from);
		}
		if (isset($screenData['filter_WO_DATE_COMPLETED_to']) 
			&& trim($screenData['filter_WO_DATE_COMPLETED_to']) != '') 
		{
			$complete_date_to = trim($screenData['filter_WO_DATE_COMPLETED_to']);
	    	$select->andWhere("WO_DATE_COMPLETED <= ?", $complete_date_to);
		}

	}	

	public static function getDollarsPostedSQLSelect( $woNum ) {
		$select = new VGS_DB_Select(); 
		
		$select->from = "DBGLGLT"; 
		$select->columns = <<<WOGL_COLS
			trim(ACTIVITY) as "W/O Num", 
			trim(R_SYSTEM) as "Src Code", 
			POSTING_DATE as "Posting Date", 
			FISCAL_YEAR as "Fiscal Year",
			ACCT_PERIOD as "Acct Period",
			trim(ACCT_UNIT) as "Acct Unit", 
			ACCOUNT as "Account",
			SUB_ACCOUNT as "Sub Acct", 
			ACCT_CATEGORY as "Acct Category", 
			trim(REFERENCE) as "Reference", 
			trim(DGLTDSCRPT) as "Description", 
			DGLTBSAMNT as "TransX Amount"
WOGL_COLS;
		$select->andWhere("DGLTACTVTY = ?", $woNum);
//	pre_dump($select);
		return $select;		
	}
}
