<?php
require_once '../forms/VGS_Form.php';
require_once '../model/Workorder_Master.php';
require_once '../model/Project_Master.php';
require_once '../model/Pipe_Type_Master.php';
require_once '../model/Premise.php';
require_once '../model/Account.php';
require_once '../model/Crew.php';
require_once '../model/SalesApp.php';
require_once '../model/Code_Values_Master.php';
require_once '../forms/VGS_NavButton.php';
require_once '../common/vgs_utilities.php';
require_once '../common/validators/WO_CostValidator.php';
require_once '../common/validators/PipeType_Validator.php';
require_once '../common/validators/PipeType_Main_Validator.php';
require_once '../common/validators/PipeType_Service_Validator.php';
require_once '../common/validators/Project_Validator.php';
require_once '../common/validators/Crew_Validator.php';
require_once '../common/validators/DepthFeet_Validator.php';
require_once '../common/validators/DepthInches_Validator.php';

/** 
 * @author John
 */
class WorkOrderForm extends VGS_Form 
{
	public $work_order;
	public $wo_type;
	public $woNum;
	public $wo_status;
	/**
	 * Security object for checking user's authority to various options
	 * @var Security
	 */
	private $security; 
	
	public function __construct( $conn ) {
		
		parent::__construct ( $conn );
				
		$this->security = new Security();
		
		$metaTables = array(
				"WOMAST", "WOLEAKINFO", "PIPETYPE", "SLSAPP", "UPRM", "UACT", "UCSR",
		);
		$this->fh->addMetaDataBatch($this->conn, $metaTables);
		
		$this->screen_title = 'Work Order ' . ucfirst($this->mode);

		$this->work_order = new Workorder_Master($conn);
		
		if ($this->isCreateMode()) {
			$this->security->checkPermissionByCategory('WO', $this->mode);
			// For new workorder, type passed must be valid
			$this->wo_type = trim($_REQUEST['WO_TYPE']);
			$this->wo_status = Workorder_Master::WO_STATUS_PENDING;
			$cvm = new Code_Values_Master($conn);
			if (''== $cvm->getCodeValue('WO_TYPE', $this->wo_type)) {
				die("Invalid work order type: {$this->wo_type}. A valid work order type is required to create a new work order.");
			}
		} else {
			// For existing workorder, wo# must be valid
			$woNum = $_REQUEST['WO_NUM'];
			$this->woNum = $woNum;
			
			$this->work_order->record = $this->work_order->getWorkorder($woNum);
			if (!isset($this->work_order->record['WO_NUM'])) {
				die("Invalid work order number: $woNum");
			}
				
			$this->wo_type = trim($this->work_order->record['WO_TYPE']);
			$this->wo_status = $this->work_order->record['WO_STATUS'];

			$authArray = $this->checkUserAuthorityToWO();
			if (!$authArray['authorized']) {
				die($authArray['message']);
			}
		}
		
		$this->setDefaultElements( );
		
	}
	
	private function checkUserAuthorityToWO() {
		// guilty until proven innocent ;-)
		$authResults = array(
			'authorized'=> false, 
			'message' => 'You are not authorized to this operation on work orders. '
		);
		
		// Only check authority if mode is set. If not, then this is instantiation of
		// WorkOrderForm for a different purpose than record maintenance. 
		if (isset($this->mode)) { 
			// Check special permission for acounting close override on completed orders
			$blnWoCompleted = $this->wo_status == Workorder_Master::WO_STATUS_COMPLETED;
			$blnUserAllowedAcctgCloOvr = $this->security->checkAuthoritiesPermission(array('WO_ACCT_CLO_OVR'), false);
			if ($this->isUpdateMode() && $blnWoCompleted && $blnUserAllowedAcctgCloOvr) {
				$authResults['authorized'] = true;
				return $authResults; 
			} 
	
			if (! $this->security->checkPermissionByCategory('WO', $this->mode, false)) {
				$authResults['authorized'] = false;
				return $authResults;
			}
		
			if ($this->isUpdateMode()) {
				// Completed or closed w/o requires authority 'WO_UPD_COMPLETE' in order to update
				$blnWO_CompClo = in_array($this->wo_status, Workorder_Master::$wo_sts_comp_closed);
				if ($blnWO_CompClo 
				&& !$this->security->checkAuthoritiesPermission(array('WO_UPD_COMPLETE')))
				{
					$authResults['authorized'] = false;
					$authResults['message'] .= 'Cannot update completed or closed work orders.';
					return $authResults;
				}
				$blnWO_Cancel = in_array($row['WO_STATUS'], Workorder_Master::$wo_sts_cancel);
				if ($blnWO_Cancel) {
					$authResults['authorized'] = false;
					$authResults['message'] .= "Work Order is cancelled. Update not allowed.";
					return $authResults;
				}
			}
		}


		// Passed validations - user is authorized to this WO for specified mode.
		$authResults['authorized'] = true;
		return $authResults; 
	}
	
	public function createRecord() {
		if (isset($this->inputs['WO_PREMISE_NUM'])
		&& $this->inputs['WO_PREMISE_NUM'] != '' 
		&& $this->inputs['WO_PREMISE_NUM'] != '0') {
			$premiseObj = new Premise($this->conn);
			$rateClass = $premiseObj->getRateClass( $this->inputs['WO_PREMISE_NUM'] );
			$this->inputs['WO_RATE_CLASS'] = trim($rateClass);
		}
		
		if ($this->isLeakWO()) {
			$this->woNum = $this->work_order->createLeakWorkOrder($this->inputs);
			return $this->woNum; 
		} else {
			$this->woNum = $this->work_order->createWorkOrder($this->inputs);
			return $this->woNum; 
		}
	}
	
	public function updateRecord() {
		if ($this->isCompletionRequested()) {
			$isCompletionRequested = true;
			if ($this->isLeakWO()) {
				// Leak go straight from pending to closed
				$this->inputs['WO_STATUS'] = Workorder_Master::WO_STATUS_CLOSED;
			} else {
				// Change status to completed
				$this->inputs['WO_STATUS'] = Workorder_Master::WO_STATUS_COMPLETED;
			}
			/* Use WO_INSTALL_DATE to record date when W/O completion
			 * was performed. This will be used to control inclusion on the
			 * New Services Installed report in Crystal.
			 * (Note: we can't use W/O completion date, since completions
			 *  are usually back-dated.) */
			$this->inputs['WO_INSTALL_DATE'] = date('Y-m-d');
		} else {
			$isCompletionRequested = false;
		}
		
		if ($this->isLeakWO()) {
			return $this->work_order->
						updateLeakWorkOrder($this->inputs, $isCompletionRequested);
		} else {
			$this->work_order->updateWorkOrder($this->inputs, $isCompletionRequested);
			if ($isCompletionRequested) {
				// Need to update all related SLSAPP records on completion, with status and date completed.
				$slsAppObj = new SalesApp($this->conn);
				if ($this->wo_type == 'SI') {
					$slsAppObj->updateSlsAppForWOCompletion(
									$this->inputs['WO_NUM'], 
									$this->inputs['WO_DATE_COMPLETED']); 
				} elseif ($this->wo_type == 'ST') {
					$slsAppNum = $this->inputs['WO_SALES_APP_NUM'];
					$slsAppObj->updateSlsAppFor_ST_Completion($slsAppNum);
				}
			}
		}
	}
	
	public function retrieveRecord() {
		$worec = $this->work_order->getWorkorder($this->inputs['WO_NUM']);
		return $worec;
	}
	
	public function loadScreen() {
		parent::loadScreen();
		
		/** Handle copying data from previous WOs in batch create loop for
		 *  retirements/replacements (SR/ST and MR/MT) */
		if ($this->isCreateMode() 
		&& (isset($_REQUEST['lastSRST_WO']) || isset($_REQUEST['lastMRMT_WO']))
		){
			// retrieve last WO created
			$lastWONum = isset($_REQUEST['lastSRST_WO']) ?
				$_REQUEST['lastSRST_WO'] : $_REQUEST['lastMRMT_WO'];
			$woObj = new Workorder_Master($conn);
			$lastWoRec = $woObj->getWorkorder($lastWONum);
			
			// Copy information from last WO created in batch entry.    
			$newRec = array();
			$newRec['WO_COMPLETE_BY_DATE'] = $lastWoRec['WO_COMPLETE_BY_DATE']; 
			$newRec['WO_PROJECT_NUM'] = $lastWoRec['WO_PROJECT_NUM']; 
			$newRec['WO_TAX_MUNICIPALITY'] = $lastWoRec['WO_TAX_MUNICIPALITY']; 

			if ($_REQUEST['lastMRMT_WO']) {
				$newRec['WO_DESCRIPTION'] = $lastWoRec['WO_DESCRIPTION'];
				$newRec['WO_ESTIMATED_LENGTH'] = $lastWoRec['WO_ESTIMATED_LENGTH'];
			}

			if ($_REQUEST['lastSRST_WO']) {
				if (isset($_REQUEST['WO_PREMISE_NUM']) 
				&& $_REQUEST['WO_PREMISE_NUM'] == $lastWoRec['WO_PREMISE_NUM']) {
					// Only copy length from SR to ST if this is for the same premise
					$newRec['WO_ESTIMATED_LENGTH'] = $lastWoRec['WO_ESTIMATED_LENGTH'];
				}
				// Retrieve last entered cost for this WO type
				switch ($_REQUEST['WO_TYPE']) {
					case 'SR':
						if (isset($_SESSION['SR_Cost']))
							$newRec['WO_EST_COST_PER_FOOT'] = $_SESSION['SR_Cost'];
						break; 
					case 'ST':
						if (isset($_SESSION['ST_Cost']))
							$newRec['WO_EST_COST_PER_FOOT'] = $_SESSION['ST_Cost'];
						break; 
				}
			}
			
			array_map('trim', $newRec);
			$this->setOutputFormatsForScreen($newRec); 
			
			foreach ($newRec as $fn => $fv) {
				if (isset($this->_elements[$fn])) {
					$this->getElement($fn)->setValue($fv);
				}
			}
		}
		
	}
		
	/**
	 * Overriden returnToCaller() - will redirect to workorder additional options menu, unless 
	 * 	this is inquiry mode. 
	 * @see VGS_Form::returnToCaller()
	 */
	public function returnToCaller() {
		if (!$this->isInquiryMode() 
		&& !$this->isCopyWOLoop() ) {
			$woNum = $this->woNum;
			$return_point = $this->return_point;
			header("Location: woOptionsMenuCtrl.php?WO_NUM=$woNum&return_point=$return_point");
			exit;
		} else {
			// Handle batch entry of SR/ST
			if ($this->isCreateMode() && $_REQUEST['AUTOCRT'] == 'true') {
				// Save estimated cost in session variable for next WO of same type in the loop
				switch ($this->wo_type) {
					case 'SR':
						$_SESSION['SR_Cost'] = $this->inputs['WO_EST_COST_PER_FOOT'] ;
						break;
					case 'ST':
						$_SESSION['ST_Cost'] = $this->inputs['WO_EST_COST_PER_FOOT'] ;
						break;
				}
				
				if (!strpos($this->return_point,'?')) { 
					$connector = '?'; 
				} else {
					$connector = '&';
				}
				// Add WO# just created to list for printing when loop ends
				$_SESSION['SRSTs_To_Print'][] = $this->woNum;
				
				// Pass back the WO# just created
				$this->return_point .= "{$connector}lastSRST_WO={$this->woNum}";
			}

			// Handle copy of MR/MT
			if ($this->isMR_MT_CopyAllowed()) 
			{
				if (!isset($_REQUEST['lastMRMT_WO'])) 
				{
					if(isset($this->_elements['COPY_MR_MT'])
					&& $this->getElement('COPY_MR_MT')->getValue() == 'Y') 
					{
						// If user requested copy MR to MT or vice versa, 
						// set return point to create next WO
						if ($this->wo_type == 'MR') $newWoType = 'MT';
						else $newWoType = 'MR';
						$woEditURL = "woEditCtrl.php?mode=create" .
								"&WO_TYPE=$newWoType" .
								"&lastMRMT_WO=" . $this->woNum;
					
						// Add WO# just created to list for printing when loop ends
						$_SESSION['MRMTs_To_Print'][] = $this->woNum;
					
						// Pass back the WO# just created
						$this->return_point = $woEditURL;
					}
				} elseif (isset($_REQUEST['lastMRMT_WO'])) {
					// MR/MT has been copied; print them both
					$lastWO2print = $_REQUEST['lastMRMT_WO'];
					$thisWO2print = $this->woNum;
					// Return to WO list, but spawn a new window to print the WOs just created
					$this->return_point = 
						"woListCtrl.php?pageToView=1&spawn=".
						urlencode("woPrintCtrl.php?WO_NUM[$lastWO2print]=$lastWO2print" .
									   			 "&WO_NUM[$thisWO2print]=$thisWO2print");
				}
			}
			
			parent::returnToCaller();
			exit;
		}
	}

	// Used in returnToCaller, to determine if we are in a create WO loop.
	private function isCopyWOLoop () {
		if ($this->isCreateMode()) {
			if ($_REQUEST['AUTOCRT'] == 'true') {
				return true;
			}
			if (isset($this->_elements['COPY_MR_MT'])
					&& $this->getElement('COPY_MR_MT')->getValue() == 'Y') {
				return true;
			}
			if (isset($_REQUEST['lastMRMT_WO'])) {
				return true;
			}
		}
		return false;
	}
	
	public function isLeakWO() {
		return (substr($this->wo_type,0,1) == 'L');
	}
	
	public function isRecheckLeakWO() {
		return ($this->work_order->record['LK_LEAKWO_TYPE'] == 'RECHK');
	}
	
	public function isCancelledWO( $woStsCode ) {
		$woStsCode = trim($woStsCode);
		switch ($woStsCode) {
			case Workorder_Master::WO_STATUS_CANCELLED:
			case Workorder_Master::WO_STATUS_CANCEL_PENDING:
				return true;
				break;
			default:
				return false;
				break;
		}
	}
	
	public function isServiceWO( $woType = null ) {
		if ($woType == null) {
			$woType = $this->wo_type;
		}
		return (in_array($woType, Workorder_Master::$wo_types_service));
	}
	
	public function isMainWO( $woType = null ) {
		if ($woType == null) {
			$woType = $this->wo_type;
		}
		return (in_array($woType, Workorder_Master::$wo_types_main));
	}
	
	public function isMaintenanceWO( $woType = null ) {
		if ($woType == null) {
			$woType = $this->wo_type;
		}
		return (in_array($woType, Workorder_Master::$wo_types_maintenance));
	}
	
	public function isNonPipeWO( $woType = null ) {
		if ($woType == null) {
			$woType = $this->wo_type;
		}
		return (in_array($woType, Workorder_Master::$wo_types_nonpipe));
	}
	
	
	public function isRetireReplaceWO() {
		$types = array('MR','MT','SR','ST');
		return (in_array($this->wo_type, $types));
	}
	
	public function isRetireWO() {
		$types = array('MT','ST');
		return (in_array($this->wo_type, $types));
	}
	
	public function isSewerWO() {
		$types = array('MI','MR','MT','SI','SR','ST','TI');
		return (in_array($this->wo_type, $types));
	}
	
	public function setDefaultElements() {

		if ($this->isCompletionAllowed()) {
			// If update mode and w/o is pending, we will add a check box to complete w/o
			$this->fh->addCustomMetaDatum('COMPLETE_CB', 'Complete W/O?', 'CHAR', 1);
			$completeCheckBox = 'COMPLETE_CB,';
		} else {
			$completeCheckBox = '';
		}

		if ($this->isTieInAllowed()) {
			// If create mode and this is a MR or MI w/o, we will add a check box to automatically
			// generate a tie-in (TI) w/o. This will default to checked (i.e., create tie-in).
			$this->fh->addCustomMetaDatum('TIE_IN_CB', 'Create Tie-In W/O?', 'CHAR', 1);
			$tieInCheckBox = 'TIE_IN_CB,';
		} else {
			$tieInCheckBox = '';
		}
		
		if ($this->isMR_MT_CopyAllowed() && !isset($_REQUEST['lastMRMT_WO'])) {
			// If create mode and this is a MR or MT w/o, we will add a check box to automatically
			// copy the MR to an MT, or vice versa. This will default to un-checked.
			switch ($this->wo_type) {
				case 'MR': 
					$copyMRMT_Label = 'Copy to MT?'; break; 
				case 'MT': 
					$copyMRMT_Label = 'Copy to MR?'; break; 
			}
			$this->fh->addCustomMetaDatum('COPY_MR_MT', $copyMRMT_Label, 'CHAR', 1);
			$copyMRMTCheckBox = 'COPY_MR_MT,';
		} else {
			$copyMRMTCheckBox = '';
		}
		
		
		if ( $this->isLeakWO() ) {
			$this->setLeakFields ( $completeCheckBox );
		} else {
			$this->setNonLeakFields ( $completeCheckBox, $tieInCheckBox, $copyMRMTCheckBox  );
		}
		
		if ($this->isServiceWO()) {
			// Add premise and sales app info if service-related w/o 
			$premiseFieldList = 'WO_PREMISE_NUM, UPSAD, UPARA, UMACT,UMNAM,UCSCH,
								UMOPH,UPTYP,UPDWC,UMSTS,UMTYP,UMPCN,UMISD,UCCTP,UCMTR,UCSIZ';
			$this->fh->addFieldGroup( $premiseFieldList, 'premise', 'Premise Information');
			$this->fh->setElementsProperties($premiseFieldList, 'output_only', true);
			$this->fh->setElementsProperties('UPARA', 'COLUMN_TEXT', 'City');
			$this->fh->fieldGroups['premise']['hidden'] = true;
			
			$slsappFields = 'WO_SALES_APP_NUM,SLSFS#,SLSSMN,SLSDAT,SLSDES,SLSIDT,SLSDSG,STATUS,SLSWOS,
							SLSCON,SLSODT,SLSMNC,SLSBTU,SLSMCF,SLSRWR,SLSRWD';
			$this->fh->addFieldGroup( $slsappFields, 'slsapp', 'Sales Application Information');
			$this->fh->setElementsProperties($slsappFields, 'output_only', true);
			$this->fh->fieldGroups['slsapp']['hidden'] = true;
		}
		
		$spclinstFieldList = 'WO_SPECIAL_INSTRUCTION, WO_MISC_NOTES';
		$this->fh->addFieldGroup( $spclinstFieldList, 'spclinst', 'Instructions / Notes');
		
		if ($this->isRetireWO()) {
			$retireFL = 'WO_RETIRE_FROM, WO_RETIRE_TO';
			if ($this->wo_type == 'ST') {
				$retireFL .= ', WO_RETIRED_MAIN'; 
			}
			$this->fh->addFieldGroup( $retireFL, 'retire', 'Retirement/Replacement');
			if ($this->wo_type == 'ST') {
				$this->fh->setElementsProperties('WO_RETIRED_MAIN', 'input_type', 'select');
			}
		}
		
		if (!$this->isRecheckLeakWO()) {
			if ($this->wo_status == Workorder_Master::WO_STATUS_PENDING || $this->isCreateMode()) 
			{
				$pendingFL = 'WO_PENDING_DIGSAFE, WO_PENDING_PERMITS, WO_PENDING_PREMARK';
				$this->fh->addFieldGroup( $pendingFL, 'pending', 'Holds Pending');
				$this->fh->setElementsProperties($pendingFL, 'input_type', 'y/n');
			}
			$digSafeFL = 'WO_DIGSAFE_AUTHNUM, WO_DIGSAFE_DATE_CALLED, WO_DIGSAFE_TIME_CALLED, WO_DIGSAFE_CALLED_BY, 
						  WO_DIGSAFE_BEGIN_DATE, WO_EXCAVATION_PERMIT_NO, WO_EXCAVATION_DATE_OBTAINED, 
						  WO_EXCAVATION_OBTAINED_BY, WO_EXCAVATION_OBTAINED_FROM';  
			$this->fh->addFieldGroup( $digSafeFL, 'digsafe', 'Dig Safe / Excavation');
			$this->fh->fieldGroups['digsafe']['hidden'] = true;
		}
		
		if ($this->isCompletionAllowed()) {
			$this->fh->setElementsProperties('COMPLETE_CB', 'input_type', 'y/n');
			if ($this->isCompletionRequested()) {
				$this->fh->setElementsProperties('WO_DATE_COMPLETED', 'required', true);
			}
		} else {
			$this->fh->setElementsProperties('WO_DATE_COMPLETED', 'output_only', true);
		}

		if ($this->isCompletionRequested()) {
			$this->fh->setElementsProperties('WO_CREW_ID', 'required', true);
			$this->fh->setElementsProperties('LK_CREW_ID', 'required', true);
		}
		
		if ( $this->isInquiryMode() 
		|| $this->isRecheckLeakWO()) 
		{
			// Set output only for all fields in inquiry mode or for leak rechecks
			foreach ($this->fh->fieldGroups as $fg) {
				$this->fh->setElementsProperties($fg['fieldlist'], 'output_only', true);
			} 
		} 
		
		if ( $this->isRecheckLeakWO() && !$this->isInquiryMode() ) {
			// Set input fields for leak rechecks
			if ($this->isCompletionAllowed()) {
				$this->fh->setElementsProperties('COMPLETE_CB', 'output_only', false);				
			}
			if ($this->isCompletionRequested()) {
				$this->fh->setElementsProperties('WO_DATE_COMPLETED', 'output_only', false);
			}
			$this->fh->setElementsProperties('LK_CREW_ID', 'output_only', false);
			$this->fh->setElementsProperties('LK_REPAIRED_METHOD', 'output_only', false);
			$this->fh->setElementsProperties('WO_DATE_COMPLETED', 'output_only', false);
		} 

		$maintFieldList = 'WO_CREATE_USER, WO_CREATE_TIME, WO_CHANGE_USER, WO_CHANGE_TIME';
		$this->fh->addFieldGroup( $maintFieldList, 'maintenance', 'Record Maintenance Information');
		$this->fh->setElementsProperties($maintFieldList, 'output_only', true);
		$this->fh->fieldGroups['maintenance']['hidden'] = true;
		
		$this->fh->setElementsProperties( 'WO_SPECIAL_INSTRUCTION', 'input_type', 'textarea');
		$this->fh->setElementsProperties( 'WO_MISC_NOTES', 'input_type', 'textarea');
			
		if ($this->isUpdateMode() && $this->isCompletedOrClosed()) {
			// If w/o is completed or closed, user needs special authority to update.
			$allowUpdComp = $this->security->checkAuthoritiesPermission(array('WO_UPD_COMPLETE'),false);
			if (!$allowUpdComp) {
				foreach ( $this->fh->fieldGroups as $fgName ) {
					$fgFieldList = $fgName ['fieldlist'];
					$this->fh->setElementsProperties ( $fgFieldList, 'output_only', true );
				}
			}
			if ($this->isOk_AcctgCloseOverride ()) {
				// Make these fields input capable
				$this->fh->setElementsProperties ( 'WO_ACCT_CLOSED_FLAG', 'input_type', 'y/n' );
				$this->fh->setElementsProperties ( 'WO_ACCT_CLOSED_FLAG', 'output_only', false );
				$this->fh->setElementsProperties ( 'WO_DATE_ACCT_CLOSED', 'output_only', false );
			}
		}
		
		
		//=========================================================================
		// This is where the Zend Form elements are generated from the metadata.
		//=========================================================================
		$this->fh->addElementsFromMetaData($this->mode);
		$this->setName('form1');
		$this->addElements ( $this->fh->getElements() );

		$this->addDropDownValues();
		$this->setFormElementAttributes();
		
		// Add validators
		if (!$this->isInquiryMode()) {
//			if ($this->isCostInformationRequired()) {
//				$this->getElement('WO_ESTIMATED_COST')->addValidator(new WO_CostValidator());
//				$this->getElement('WO_EST_COST_PER_FOOT')->addValidator(new WO_CostValidator());
//				$this->getElement('WO_ESTIMATED_LENGTH')->addValidator(new WO_CostValidator());
//			}
			if ($this->getElement('WO_CREW_ID') != NULL) {
				$this->getElement('WO_CREW_ID')->addValidator(new Crew_Validator());
			}
			if ($this->getElement('WO_PROJECT_NUM') != NULL) {
				$this->getElement('WO_PROJECT_NUM')->addValidator(new Project_Validator());
			}
			
			if ($this->getElement('WO_PIPE_TYPE') != NULL) {
				$this->getElement('WO_PIPE_TYPE')->addValidator(new PipeType_Validator());
				if ($this->isServiceWO()) {
					$this->getElement('WO_PIPE_TYPE')->addValidator(new PipeType_Service_Validator());
				} 
				if ($this->isMainWO()) {
					$this->getElement('WO_PIPE_TYPE')->addValidator(new PipeType_Main_Validator());
				} 
			}
			if ($this->getElement('WO_MAIN_PIPE_TYPE') != NULL) {
				$this->getElement('WO_MAIN_PIPE_TYPE')->addValidator(new PipeType_Validator());
				$this->getElement('WO_MAIN_PIPE_TYPE')->addValidator(new PipeType_Main_Validator());
			}
			
			if ($this->isDepthRequired()) {
				if ($this->getElement('WO_DEPTH_FEET') != NULL) {
					$this->getElement('WO_DEPTH_FEET')->addValidator(new DepthFeet_Validator());
				}
				if ($this->getElement('WO_DEPTH_INCHES') != NULL) {
					$this->getElement('WO_DEPTH_INCHES')->addValidator(new DepthInches_Validator());
				}
			}
				
		}
			
	}

	private function setFormElementAttributes() {
		if ($this->isServiceWO()) {
			$this->getElement('UPSAD')->setAttrib('size', '40');
			$this->getElement('UPARA')->setAttrib('size', '30');
		}
		
		if (!$this->isCreateMode()) {
			// Add button to view DB Update Log for this work order
			$jsonKeys = array("WO_NUM" => $this->getElement('WO_NUM')->getValue());
			$logLink = 'dblListCtrl.php?key_field=WO_NUM&key_value=' . $this->woNum;
			$logLink = "javascript:openPopUp('$logLink', 'DBUpdateLog');";
			$logButton = new VGS_NavButton('View Update Log', $logLink, 'js');
			$logButton->setIcon(VGS_NavButton::POPUP_ICON);
			$this->getElement('WO_CHANGE_TIME')->setDescription('<br />' . $logButton->render('return'));
		}
		
		if ( $this->isLeakWO() ) {
			$this->getElement('LK_BILLTO_ADDR')->setAttrib('rows', '3');
			$this->getElement('LK_BILLTO_ADDR')->setAttrib('cols', '30');
			$this->getElement('LK_BILLING_COMMENTS')->setAttrib('rows', '3');
			$this->getElement('LK_BILLING_COMMENTS')->setAttrib('cols', '30');
		}
		
		$this->getElement('WO_SPECIAL_INSTRUCTION')->setAttrib('cols', '35');
		$this->getElement('WO_MISC_NOTES')->setAttrib('cols', '35');
		
		if ($this->isDepthRequired()) {
			$this->getElement('WO_DEPTH_FEET')->setLabel('Pipe Depth')->setDescription('Feet (0-15)');
			$this->getElement('WO_DEPTH_INCHES')->setLabel('')->setDescription('Inches (0-11)');
		}
		
	}
	
	private function addDropDownValues() {
		$cvm = new Code_Values_Master($this->conn);
		if ( $this->isLeakWO() ) {
			$ddvals = $cvm->getCodeValuesList('LK_TYPE', ' ');
			$this->fh->setMultiOptions('LK_TYPE', $ddvals);
			$ddvals = $cvm->getCodeValuesList('LK_LEAK_CLASS', ' ');
			$this->fh->setMultiOptions('LK_LEAK_CLASS', $ddvals);
			$ddvals = $cvm->getCodeValuesList('LK_MATERIAL_TYPE', ' ');
			$this->fh->setMultiOptions('LK_MATERIAL_TYPE', $ddvals);

			$ddvals = $cvm->getCodeValuesList('LK_LEAK_ORIGIN', ' ');
			$this->fh->setMultiOptions('LK_LEAK_ORIGIN', $ddvals);
			
			if (trim($this->inputs['LK_LEAK_ORIGIN']) == '' && is_array($this->work_order->record)) {
				$lkOrigin = $this->work_order->record['LK_LEAK_ORIGIN'];
			} else {
				$lkOrigin = $this->inputs['LK_LEAK_ORIGIN']; 
			}
			$equipTypeGroup = "LK_EQUIPTYPE_$lkOrigin";
			$ddvals = $cvm->getCodeValuesList($equipTypeGroup, ' ');
			$this->fh->setMultiOptions('LK_EQUIPMENT_TYPE', $ddvals);
						
			$ddvals = $cvm->getCodeValuesList('LK_THREAT', ' ');
			$this->fh->setMultiOptions('LK_THREAT', $ddvals);

			if (trim($this->inputs['LK_THREAT']) == '' && is_array($this->work_order->record)) {
				$lkThreat = $this->work_order->record['LK_THREAT'];
			} else {
				$lkThreat = $this->inputs['LK_THREAT']; 
			}
			$subThreatGroup = "LK_SUB_THREAT_$lkThreat";
			$ddvals = $cvm->getCodeValuesList($subThreatGroup, ' ');
			$this->fh->setMultiOptions('LK_SUB_THREAT', $ddvals);

			$ddvals = $cvm->getCodeValuesList('LK_REPAIRED_METHOD', ' ');
			$this->fh->setMultiOptions('LK_REPAIRED_METHOD', $ddvals);
			$ddvals = $cvm->getCodeValuesList('LK_REPAIRED_EQUIPMENT', ' ');
			$this->fh->setMultiOptions('LK_REPAIRED_EQUIPMENT', $ddvals);
				
			$ddvals = $cvm->getCodeValuesList('LK_SURVEY_TYPE', ' ');
			$this->fh->setMultiOptions('LK_SURVEY_TYPE', $ddvals);
// 			if ($this->isCompletionRequested() || $this->inputs['WO_DATE_COMPLETED'] != '0001-01-01') {
// 				$ddvals = $cvm->getCodeValuesList('LK_EVENTS', '-- Unknown --');
// 				$this->fh->setMultiOptions('LK_EVENTS', $ddvals);
// 			}
		} else {
			if ($this->isServiceWO()) {
				$ddvals = $cvm->getCodeValuesList('WO_METER_LOCATION', ' ');
				$this->fh->setMultiOptions('WO_METER_LOCATION', $ddvals);
			}

			$ddvals = $cvm->getCodeValuesList('SOIL_CONDITION', ' ');
			$this->fh->setMultiOptions('WO_SOIL_CONDITION', $ddvals);
			
			$ddvals = $cvm->getCodeValuesList('SOIL_PACKING', ' ');
			$this->fh->setMultiOptions('WO_SOIL_PACKING', $ddvals);
			
			$ddvals = $cvm->getCodeValuesList('SOIL_MOISTURE', ' ');
			$this->fh->setMultiOptions('WO_SOIL_MOISTURE', $ddvals);
		}
		
		if (!$this->isNonPipeWO()) {
			$ddvals = $cvm->getCodeValuesList('PIPE_MTRL', ' ');
			$this->fh->setMultiOptions('WO_PIPE_MATERIAL', $ddvals);
			
			$ddvals = $cvm->getCodeValuesList('PIPE_DIAM', ' ');
			$this->fh->setMultiOptions('WO_PIPE_SIZE', $ddvals);
			
			$ddvals = $cvm->getCodeValuesList('PIPE_COATING', ' ');
			$this->fh->setMultiOptions('WO_COATING_TYPE', $ddvals);
		}
		
		if ($this->isInstallMethodShown()) {
			$ddvals = $cvm->getCodeValuesList('METHOD_OF_CONSTRUCTION', ' ');
			$this->fh->setMultiOptions('WO_INSTALL_METHOD', $ddvals);
		}
		
		if ($this->isMaintenanceWO()) {
			$ddvals = $cvm->getCodeValuesList('CONDITION_FOUND');
			$this->fh->setMultiOptions('WO_CONDITION_FOUND', $ddvals);
			$ddvals = $cvm->getCodeValuesList('REPAIR_METHOD_EQUIP');
			$this->fh->setMultiOptions('WO_REPAIR_METHOD_EQUIP', $ddvals);
		}
		
		if ($this->wo_type == 'SI') {
			$ddvals = $cvm->getCodeValuesList('CONS_TYPES_SI', ' ');
			$this->fh->setMultiOptions('WO_CONSTRUCTION_TYPE', $ddvals);
		}
		
		if ($this->wo_type == 'MI') {
			$ddvals = $cvm->getCodeValuesList('CONS_TYPES_MI', ' ');
			$this->fh->setMultiOptions('WO_CONSTRUCTION_TYPE', $ddvals);
		}
		
		
		if ($this->wo_type == 'SR' || $this->wo_type == 'SI') {
			$ddvals = $cvm->getCodeValuesList('WO_FLOW_LIMITER_SIZE', ' ');
			$this->fh->setMultiOptions('WO_FLOW_LIMITER_SIZE', $ddvals);
		}
		
		if ($this->isRetireWO()) {
			if ($this->wo_type == 'ST') {
				$ddvals = $cvm->getCodeValuesList('WO_RETIRED_MAIN', ' ');
				$this->fh->setMultiOptions('WO_RETIRED_MAIN', $ddvals);
			}
		}
		
		$ddvals = $cvm->getCodeValuesList('PRESSURE', ' ');
		$this->fh->setMultiOptions('WO_PRESSURE', $ddvals);
		
		$taxmunis = $cvm->getCodeValuesList('TOWN', ' ');
		$this->fh->setMultiOptions('WO_TAX_MUNICIPALITY', $taxmunis);
	}
	
	/**
	 * Determines if the "Complete W/O?" checkbox should be available on the update screen.
	 * @return true if this workorder is pending and mode is update.
	 */
	public function isCompletionAllowed() {
		// If update mode and w/o is pending, we will add a check box to complete w/o
		return $this->isUpdateMode() && $this->work_order->record['WO_STATUS'] == Workorder_Master::WO_STATUS_PENDING;
	}
	
	/**
	 * Determines if the "Create Tie-In W/O?" checkbox should be available on the create screen.
	 * @return true if workorder type is MR or MI and mode is create.
	 */
	public function isTieInAllowed() {
		// For w/o types that may involve a tie-in, we will add a check box (default to Y)
		// which, if checked, will automatically generate the tie-in w/o. 
		$tieInTypes = array('MR', 'MI');
		return $this->isCreateMode() && in_array($this->wo_type, $tieInTypes);
	}
	
	/**
	 * Determines if the "Copy to MR/MT?" checkbox should be available on the create screen.
	 * @return true if workorder type is MR or MT and mode is create.
	 */
	public function isMR_MT_CopyAllowed() {
		// For main retire/replacement WOs, add a check box (default to N)
		// which, if checked, will automatically generate an MR or MT as well. 
		$copyTypes = array('MR', 'MT');
		$copyAllowed = 
			$this->isCreateMode() 
			&& in_array($this->wo_type, $copyTypes);
		return $copyAllowed;
	}

	/**
	 * Returns true if the user has requested to complete an incomplete work order.
	 * @return true if w/o is incomplete and the "Complete W/O" checkbox is checked.
	 */
	public function isCompletionRequested() {
		return (
			$this->isCompletionAllowed() && 
			isset($this->inputs['COMPLETE_CB']) && 
			$this->inputs['COMPLETE_CB'] == 'Y'
		);
	}

	/**
	 * Returns true if this work order is either completed or closed.
	 * @return true if this work order is either completed or closed.
	 */
	public function isCompletedOrClosed() {
		return 
			$this->wo_status == Workorder_Master::WO_STATUS_COMPLETED
			 ||
			$this->wo_status == Workorder_Master::WO_STATUS_CLOSED
		;
	}

	/**
	 * Returns true if fields that should only show on completed/closed work orders should be visible.
	 * @return true if fields that should only show on completed/closed work orders should be visible.
	 */
	public function showCompletionFields() {
		return $this->isCompletedOrClosed() || $this->isCompletionRequested();
	}
	
	private function isOk_AcctgCloseOverride( ) {
		// In order to update accounting close fields, user must be authorized to this function,
		// mode must be update, and wo status must be "completed".
		$allowAcctgClsOvr = $this->security->checkAuthoritiesPermission(array('WO_ACCT_CLO_OVR'),false);
		if (!$allowAcctgClsOvr 
		||  !$this->isUpdateMode()
 		||  !($this->wo_status == Workorder_Master::WO_STATUS_COMPLETED)) {
 			return false;	
 		} else {
	 		return true;
 		}
	}
	
	/**
	 * Determine screen fields and groupings for non-leak workorders
	 * @param completeCheckBox
	 */
	private function setNonLeakFields($completeCheckBox, $tieInCheckBox, $copyMRMTCheckBox) {
		$generalFieldList = "WO_NUM, WO_TYPE, WO_STATUS, $completeCheckBox WO_ENTRY_DATE, 
							WO_COMPLETE_BY_DATE, WO_DATE_COMPLETED,WO_DESCRIPTION, $tieInCheckBox $copyMRMTCheckBox WO_PRINT_COUNT";
		$this->fh->addFieldGroup( $generalFieldList, 'general', 'General Information');
		$this->fh->setElementsProperties('WO_NUM, WO_TYPE, WO_STATUS, WO_ENTRY_DATE', 'output_only', true);
		$this->fh->setElementsProperties('WO_DESCRIPTION', 'required', true);
		$this->fh->setElementsProperties('WO_PRINT_COUNT', 'output_only', true);
		if ($tieInCheckBox != '') {
			$this->fh->setElementsProperties('TIE_IN_CB', 'input_type', 'y/n');
		}
		if ($copyMRMTCheckBox != '') {
			$this->fh->setElementsProperties('COPY_MR_MT', 'input_type', 'y/n');
		}
		
		$projectFieldList = 'WO_PROJECT_NUM, WO_TAX_MUNICIPALITY, WO_ROW_NUM';
		$this->fh->addFieldGroup( $projectFieldList, 'project', 'Project Information');
		$this->fh->setElementsProperties('WO_TAX_MUNICIPALITY', 'input_type', 'select');
		$this->fh->setElementsProperties('WO_TAX_MUNICIPALITY', 'required', true);
		$this->fh->setElementsProperties('WO_PROJECT_NUM', 'lookup', 'javascript:lookupProject();');
		$this->fh->setElementsProperties('WO_PROJECT_NUM', 'required', true);
		
		if ($this->isServiceWO()) {
			$meterFieldList = 'WO_METER_LOCATION, WO_METER_BARRIER, WO_METER_ROOF';
			$this->fh->setElementsProperties('WO_METER_BARRIER', 'input_type', 'y/n');
			$this->fh->setElementsProperties('WO_METER_ROOF', 'input_type', 'y/n');
			$this->fh->setElementsProperties('WO_METER_LOCATION', 'required', true);
			$this->fh->setElementsProperties( 'WO_METER_LOCATION', 'input_type', 'select');
			if ($this->wo_type == 'SR' || $this->wo_type == 'SI') {
				$meterFieldList .= ', WO_CURB_STOP, WO_FLOW_LIMITER, WO_FLOW_LIMITER_SIZE';
				$this->fh->setElementsProperties('WO_CURB_STOP', 'input_type', 'y/n');
				$this->fh->setElementsProperties('WO_FLOW_LIMITER', 'input_type', 'y/n');
				$this->fh->setElementsProperties( 'WO_FLOW_LIMITER_SIZE', 'input_type', 'select');
				if ($this->inputs['WO_FLOW_LIMITER'] == 'Y') {
					$this->fh->setElementsProperties('WO_FLOW_LIMITER_SIZE', 'required', true);
				}
			}
			$this->fh->addFieldGroup( $meterFieldList, 'meter', 'Meter Information');
		}

		$methodOfConstruction = $this->isInstallMethodShown() ? 'WO_INSTALL_METHOD, ' : '';
		$installMethod = $this->isInstallWO() ? 'WO_CONSTRUCTION_TYPE, ' : '';
		$condition_repaired = $this->isMaintenanceWO() ? 'WO_CONDITION_FOUND, WO_REPAIR_METHOD_EQUIP, ' : '';
		
		if ($this->isServiceWO()) {
			$pipeFieldList = "WO_MAIN_PIPE_TYPE, WO_PIPE_TYPE, $methodOfConstruction $installMethod $condition_repaired WO_PRESSURE,WO_ROAD_CROSSING";
			$this->fh->addFieldGroup( $pipeFieldList, 'pipe', 'Pipe Information');
			$this->fh->setElementsProperties('WO_PIPE_TYPE', 'required', true);
			$this->fh->setElementsProperties('WO_PIPE_TYPE', 'lookup', 'javascript:lookupPipeType();');
			$this->fh->setElementsProperties( 'WO_METER_LOCATION', 'input_type', 'select');
			$this->fh->setElementsProperties( 'WO_ROAD_CROSSING', 'input_type', 'y/n');
		} else {
			$pipeFieldList = "WO_PIPE_TYPE, $methodOfConstruction $installMethod $condition_repaired WO_PRESSURE";
			$this->fh->setElementsProperties('WO_PIPE_TYPE', 'required', true);
			$this->fh->setElementsProperties('WO_PIPE_TYPE', 'lookup', 'javascript:lookupPipeType();');
			$this->fh->addFieldGroup( $pipeFieldList, 'pipe', 'Pipe Information');
		}
		
		if ($this->isMaintenanceWO()) {
			$this->fh->setElementsProperties(
				'WO_CONDITION_FOUND, WO_REPAIR_METHOD_EQUIP', 'input_type', 'multi-checkbox');
			if ($this->isCompletionRequested()) {
				$this->fh->setElementsProperties(
					'WO_CONDITION_FOUND, WO_REPAIR_METHOD_EQUIP', 'required', 'true');
			}
		}
		if ($this->isInstallMethodShown()) {
			$this->fh->setElementsProperties('WO_INSTALL_METHOD', 'input_type', 'select');
			if ($this->isCompletionRequested()) {
				$this->fh->setElementsProperties('WO_INSTALL_METHOD', 'required', 'true');
			}
		}
		if ($this->isInstallWO()) {
			$this->fh->setElementsProperties('WO_CONSTRUCTION_TYPE', 'input_type', 'select');
			if ($this->isCompletionRequested()) {
				$this->fh->setElementsProperties('WO_CONSTRUCTION_TYPE', 'required', 'true');
			}
		}
		
		$this->addGLInfo();
		
		$this->fh->setElementsProperties('WO_PRESSURE', 'required', true);
		$this->fh->setElementsProperties( 'WO_PRESSURE', 'input_type', 'select');
		$this->fh->setElementsProperties('WO_MAIN_PIPE_TYPE', 'lookup', 'javascript:lookupMainPipeType();');
		
		if ($this->isCostInformationRequired()) {
			$costFieldList = 'WO_CREW_ID, WO_ESTIMATED_COST, WO_EST_COST_PER_FOOT,  
							  WO_ESTIMATED_LENGTH, WO_ACTUAL_LENGTH';
			if ($this->isCompletionRequested()) {
				$this->fh->setElementsProperties('WO_ACTUAL_LENGTH', 'required', 'true');
			}
		} elseif ($this->wo_type == 'NW') {
			$costFieldList = 'WO_CREW_ID, WO_ESTIMATED_COST';
			$this->fh->setElementsProperties('WO_ESTIMATED_COST', 'required', 'true');
		} else {
			$costFieldList = 'WO_CREW_ID';
		}
		
		if (!$this->isNonPipeWO()) {
			$optionalPipeFields = '';
			if ($this->isMaintenanceWO()) {
				$this->fh->addCustomMetaDatum('OPTIONAL_PIPE_FIELDS', "Pipe fields N/A?", 'CHAR', 1);
				$optionalPipeFields = ', OPTIONAL_PIPE_FIELDS';
				$this->fh->setElementsProperties('OPTIONAL_PIPE_FIELDS', 'input_type', 'y/n');
			}
			$costFieldList .= "$optionalPipeFields, WO_PIPE_MATERIAL, WO_PIPE_SIZE, WO_COATING_TYPE";
		}
		if ($this->isDepthRequired()) {
			$costFieldList .= ', WO_DEPTH_FEET, WO_DEPTH_INCHES';
		}
		
		$costFieldList .= ', WO_SOIL_CONDITION, WO_SOIL_PACKING, WO_SOIL_MOISTURE';
		
		$this->fh->addFieldGroup( $costFieldList, 'cost', 'Costs/Completion Information');
		
		$this->fh->setElementsProperties('WO_CREW_ID', 'lookup', 'javascript:lookupCrew();');
		$this->fh->setElementsProperties('WO_SOIL_CONDITION, WO_SOIL_PACKING, WO_SOIL_MOISTURE', 
				'input_type', 'select');
		if (!$this->isNonPipeWO()) {
			$this->fh->setElementsProperties('WO_PIPE_MATERIAL, WO_PIPE_SIZE, WO_COATING_TYPE', 
				'input_type', 'select');
		}

		if ($this->isCompletionRequested()) {
			if (!$this->isNonPipeWO() ){
				$this->fh->setElementsProperties('WO_PIPE_MATERIAL, WO_COATING_TYPE', 'required', 'true');
// 				pre_dump("OPTIONAL_PIPE_FIELDS = " . $this->inputs['OPTIONAL_PIPE_FIELDS']);
				if ($this->inputs['OPTIONAL_PIPE_FIELDS'] == 'Y') {
// 					pre_dump("Should be optional!!");
				} else {
					$this->fh->setElementsProperties('WO_PIPE_SIZE', 'required', 'true');
// 					pre_dump("Should be REQUIRED!!");
				}
			}
		}
	}
 
	private function addGLInfo() {
		$glFieldList = "PT_ACCTG_UNIT_COST, PT_GL_ACCT_COST, PT_SUB_ACCT_COST,                     	                    
						PT_ACCTG_UNIT_CLOSE, PT_GL_ACCT_CLOSE, PT_SUB_ACCT_CLOSE, 
						WO_ACCT_CLOSED_FLAG, WO_DATE_ACCT_CLOSED";
 		
		$this->fh->setElementsProperties($glFieldList, 'output_only', true);
		$this->fh->addFieldGroup($glFieldList, 'gl', 'Accounting Information');
		if (!$this->isOk_AcctgCloseOverride()) {
			// Hide acctg info on page load, unless user is able to change acctg close values.
			$this->fh->fieldGroups['gl']['hidden'] = true;
		} else {
			if ($this->wo_status == Workorder_Master::WO_STATUS_COMPLETED) {
				// Make these fields input capable
				$this->fh->setElementsProperties ( 'WO_ACCT_CLOSED_FLAG', 'input_type', 'y/n' );
				$this->fh->setElementsProperties ( 'WO_ACCT_CLOSED_FLAG', 'output_only', false );
				$this->fh->setElementsProperties ( 'WO_DATE_ACCT_CLOSED', 'output_only', false );
			}
		}
	}
	
	public function isCostInformationRequired() {
		$costWOTypes = array('SR', 'ST', 'SI', 'MR', 'MT', 'MI');
		return in_array($this->wo_type, $costWOTypes);
	}
	
	public function isInstallMethodShown() {
		$mocWOTypes = array('SR', 'SI', 'MR', 'MI');
		return in_array($this->wo_type, $mocWOTypes);
	}
	
	public function isInstallWO() {
		$installWOTypes = array('SI', 'MI');
		return in_array($this->wo_type, $installWOTypes);
	}

	public function isDepthRequired() {
		$depthWOTypes = array('SI', 'MI', 'SR', 'ST', 'MR', 'MT');
		return in_array($this->wo_type, $depthWOTypes);
	}

	/**
	 * Determine screen fields and field groupings for leak workorders
	 * @param completeCheckBox
	 */
	private function setLeakFields($completeCheckBox) {
		// Leak fields
		$this->screen_title = 'Leak ' . $this->screen_title;

		// Add original/recheck fields to leak screens
		if ($this->isCreateMode() )
		{
			$this->fh->addCustomMetaDatum('RECHECK_FLAG', 'Create ReCheck?', 'CHAR', 1);
			$this->fh->setElementsProperties('RECHECK_FLAG', 'input_type', 'y/n');
			$reCheckFlag = 'RECHECK_FLAG,';
			$reCheckWO = '';
			$leakWOType = '';
		} else {
			$reCheckFlag = '';
			$reCheckWO = '';
			$leakWOType = 'LK_LEAKWO_TYPE,';
			if( trim($this->work_order->record['LK_RECHECK_WONUM']) != '0')
			{
				$reCheckWO = 'LK_RECHECK_WONUM,';
			} elseif( trim($this->work_order->record['LK_ORIG_WONUM']) != '0') {
				$reCheckWO = 'LK_ORIG_WONUM, ';
			}
		}
		
		$generalFieldList = "WO_NUM, WO_TYPE, WO_STATUS, $completeCheckBox WO_ENTRY_DATE," 
						  . "WO_DATE_COMPLETED, $reCheckFlag $leakWOType $reCheckWO"
						  . "WO_DESCRIPTION, WO_COMPLETE_BY_DATE, WO_TAX_MUNICIPALITY, WO_PRINT_COUNT";
						  
		$this->fh->addFieldGroup( $generalFieldList, 'general', 'General Information');
		$this->fh->setElementsProperties("WO_NUM, WO_TYPE, WO_STATUS, $leakWOType $reCheckWO WO_ENTRY_DATE", 'output_only', true);
		$this->fh->setElementsProperties('WO_DESCRIPTION', 'required', true);
		$this->fh->setElementsProperties('WO_TAX_MUNICIPALITY', 'input_type', 'select');
		$this->fh->setElementsProperties('WO_TAX_MUNICIPALITY', 'required', true);
		$this->fh->setElementsProperties('WO_PRINT_COUNT', 'output_only', true);
		
		$leakFieldList = 'LK_LEAK_CLASS, LK_TYPE, LK_REPORTED_BY, LK_REPORTED_TO, LK_SURVEYED_BY, ' .
						 'LK_SURVEY_TYPE, LK_DATE_FOUND, LK_TIME_FOUND, LK_PAGE';
		$this->fh->setElementsProperties('LK_LEAK_CLASS', 'required', true);
		if ($this->isCreateMode()) {
			$this->fh->setElementsProperties('LK_TYPE', 'required', true);
		} else {
			$this->fh->setElementsProperties('LK_TYPE', 'output_only', true);
		}
		$this->fh->setElementsProperties('LK_SURVEY_TYPE', 'input_type', 'select');
		$this->fh->addFieldGroup( $leakFieldList, 'leak', 'Leak Information');

		$this->addGLInfo();
		
		$billingFieldList = 'LK_BILLABLE, LK_BILLTO_NAME, LK_BILLTO_ADDR, LK_RELIGHT_WONUM, LK_BILLING_COMMENTS';
		$this->fh->addFieldGroup( $billingFieldList, 'billing', 'Billing Information');
		$this->fh->setElementsProperties('LK_BILLABLE', 'input_type', 'y/n');
		$this->fh->setElementsProperties('LK_BILLING_COMMENTS', 'input_type', 'textarea');
		$this->fh->setElementsProperties('LK_BILLTO_ADDR', 'input_type', 'textarea');
		
		$gaslostFieldList = 'LK_HOURS_BLOWING, LK_SIZE_OF_OPENING, LK_GAS_VOLUME, LK_PSI';
		$this->fh->addFieldGroup( $gaslostFieldList, 'gaslost', 'Gas Lost');
		
		$lkPersFieldList = 'LK_CREW_ID, LK_PERSONNEL_HOURS, LK_CREW_SIZE, LK_TOTAL_MAN_HOURS';
		$this->fh->addFieldGroup( $lkPersFieldList, 'lkPers', 'Personnel Information');
		$this->fh->setElementsProperties('LK_TOTAL_MAN_HOURS', 'output_only', true);
		$this->fh->setElementsProperties('LK_CREW_ID', 'lookup', 'javascript:lookupLeakCrew();');
				
		$repairFieldList = 'WO_PRESSURE, LK_LEAK_ORIGIN, LK_EQUIPMENT_TYPE, LK_THREAT, LK_SUB_THREAT, 
				LK_REPAIRED_METHOD, LK_REPMETH_OTHER, LK_REPAIRED_EQUIPMENT, LK_REPEQUIP_OTHER, 
				WO_PIPE_MATERIAL, WO_PIPE_SIZE, WO_COATING_TYPE, LK_MATERIAL_COST';
		
// 		if ($this->isCompletionRequested() && $this->inputs['WO_DATE_COMPLETED'] != '0001-01-01') {
// 			$repairFieldList .= ',LK_EVENTS,LK_ADV_NOTICE, LK_MONITOR_FURTHER, LK_SAMPLES_AVAILABLE';
// 		}

		$this->fh->addFieldGroup( $repairFieldList, 'lkRepairs', 'Cause and Repair');
		$this->fh->setElementsProperties('WO_PRESSURE', 'required', true);
		$this->fh->setElementsProperties( 'WO_PRESSURE', 'input_type', 'select');
		$this->fh->setElementsProperties( 'WO_PIPE_MATERIAL, WO_PIPE_SIZE, WO_COATING_TYPE', 'input_type', 'select');
		$this->fh->setElementsProperties( 'LK_LEAK_ORIGIN', 'input_type', 'select');
		$this->fh->setElementsProperties( 'LK_EQUIPMENT_TYPE', 'input_type', 'select');
		$this->fh->setElementsProperties( 'LK_THREAT', 'input_type', 'select');
		$this->fh->setElementsProperties( 'LK_SUB_THREAT', 'input_type', 'select');
		$this->fh->setElementsProperties( 'LK_REPAIRED_METHOD', 'input_type', 'select');
		$this->fh->setElementsProperties( 'LK_REPAIRED_EQUIPMENT', 'input_type', 'select');
		$this->fh->setElementsProperties( 'LK_LEAK_CLASS', 'input_type', 'select');
		$this->fh->setElementsProperties( 'LK_TYPE', 'input_type', 'select');
		
		if ($this->isCompletionRequested() && !$this->isRecheckLeakWO()) {
			$this->fh->setElementsProperties('LK_PERSONNEL_HOURS', 'required', true);
			if ($this->inputs['LK_BILLABLE'] == 'Y') {
				$this->fh->setElementsProperties('LK_BILLTO_NAME, LK_CREW_ID, LK_CREW_SIZE', 'required', true);
			}
			if ($this->inputs['LK_LEAK_CLASS'] == '1' || $this->inputs['LK_LEAK_CLASS'] == '9') {
				$this->fh->setElementsProperties('LK_TIME_FOUND', 'required', true);
			}
			$this->fh->setElementsProperties('LK_LEAK_ORIGIN, LK_EQUIPMENT_TYPE, LK_THREAT, 
					LK_SUB_THREAT, LK_REPAIRED_METHOD, LK_REPAIRED_EQUIPMENT', 'required', true);
		}
		
	}


	/**
	 * reset() function is used to set default values on w/o create screen
	 * @see Zend_Form::reset()
	 */
	public function reset() 
	{
		parent::reset();
		$cvm = new Code_Values_Master($this->conn);
		
		$this->getElement('WO_TYPE')->setValue($this->wo_type);
		$wotype = $this->getElement('WO_TYPE')->getValue();
		$type_desc = $cvm->getCodeValue('WO_TYPE', $wotype);
		$this->getElement('WO_TYPE')->setDescription($type_desc);
		
		$this->getElement('WO_STATUS')->setValue(Workorder_Master::WO_STATUS_PENDING);
		$wosts = $this->getElement('WO_STATUS')->getValue();
		$sts_desc = $cvm->getCodeValue('WO_STATUS', $wosts);
		$this->getElement('WO_STATUS')->setDescription($sts_desc);
		
		$this->getElement('WO_ENTRY_DATE')->setValue(date('m-d-Y'));
		$this->getElement('WO_PRESSURE')->setValue('M');

		$this->setDefaultsByWOType($wotype);
		
		if ($this->isServiceWO()) {
			$this->populatePremiseFields($this->inputs['WO_PREMISE_NUM']);
			$this->populateSlsAppFields($this->inputs['WO_PREMISE_NUM']);
		}
		
		if ($this->isLeakWO()) {
			$this->getElement('RECHECK_FLAG')->setValue('Y');
			$crewSize = $this->getElement('LK_CREW_SIZE')->getValue();
			if ($crewSize == 0) $this->getElement('LK_CREW_SIZE')->setValue('1');
		} else {
			if ($this->isServiceWO()) {
				$this->getElement('WO_METER_LOCATION')->setValue('OS');
			}
			if ($this->isTieInAllowed()) {
				$this->getElement('TIE_IN_CB')->setValue('Y');
			}
			if (isset($this->_elements['COPY_MR_MT'])) {
				$this->getElement('COPY_MR_MT')->setValue('N');
			}
		}
	}
	
	private function setDefaultsByWOType( $wotype ) {
		switch ($wotype) { 
			case 'SB':
				// Service meter barricade
				$this->getElement('WO_PROJECT_NUM')->setValue(1131);
				$this->getElement('WO_PIPE_TYPE')->setValue(69);
				break;
			
			case 'SM':
			case 'MM':
				// service maintenance or Main maintenance
				$this->getElement('WO_PROJECT_NUM')->setValue(1383);
				break;
			
			case 'NW':
				// Non-workorder
				$this->getElement('WO_TAX_MUNICIPALITY')->setValue('SOB');
				break;
			
			default:
				;
				break;
		}
	}
	/**
	 * Retrieve premise record and set w/o screen fields for premise info.
	 * @param integer-string $premiseNo
	 */
	private function populatePremiseFields( $premiseNo ) {
		$premise = new Premise($this->conn);
		$premRec = $premise->retrieve($premiseNo);
		if ($this->isCreateMode() && trim($this->getElement('WO_DESCRIPTION')->getValue()) == '') {
			$this->getElement('WO_PREMISE_NUM')->setValue(trim($premRec['UPPRM']));
			$this->getElement('WO_DESCRIPTION')->setValue($premRec['UPSAD']);
		}
		
		$this->getElement('UPSAD')->setValue($premRec['UPSAD']);
		$this->getElement('UPSAD')->setAttrib('size', '40');
		$this->getElement('UPARA')->setValue($premRec['UPCTC']);
		$this->getElement('UPTYP')->setValue($premRec['UIPPT']);
		$this->getElement('UPTYP')->setDescription($premRec['UIDES']);
		$this->getElement('UPDWC')->setValue($premRec['UIDWL']);
		$this->getElement('UPDWC')->setDescription($premRec['UIDES']);
		
		// Add google map button to town
		$mapRec = array_map(trim, $premRec);
		$mapAddress = ltrim($mapRec['UPSH#'], '0') . 
					" {$mapRec['UPSSP']} {$mapRec['UPSST']} {$mapRec['UPSSF']}" .
					", {$mapRec['UPCTC']}, {$mapRec['UPSTC']} {$mapRec['UPZIP']}";
		
		$mapAddr = 'http://maps.google.com/maps?hl=en&z=15&output=embed&iwd=1&q=' . 
					urlencode(trim($mapAddress));
		$js = "myRef = window.open('$mapAddr','mapwin', 'left=20,top=20,width=1000,height=650,toolbar=yes,resizable=yes,location=yes'); myRef.focus(); return false;";
		$mapButton = "<button onclick=\"$js\">Map Premise</button>";
		$this->getElement('WO_TAX_MUNICIPALITY')->setDescription($mapButton);
		
		$account = new Account($this->conn);
		$acctRec = $account->retrieveByPremiseNo($premiseNo);
		$this->getElement('UMACT')->setValue($acctRec['UMACT']);
		$this->getElement('UMNAM')->setValue($acctRec['UMNAM']);
		
		$phoneNo = trim($acctRec['UMOPH']);
		if ($phoneNo == '0' || $phoneNo == '') {
			$phoneNo = trim($acctRec['UMBPH']);
		}
		$phoneNo = Account::formatPhoneNo($phoneNo);
		$this->getElement('UMOPH')->setValue($phoneNo);

		$this->getElement('UMSTS')->setValue($acctRec['UMSTS']);
		$this->getElement('UMTYP')->setValue($acctRec['UMTYP']);

		$connDt = $this->convertDateFormat($acctRec['UMPCN'], 'Ymd', 'M d, Y');
		$this->getElement('UMPCN')->setValue($connDt);
		$this->getElement('UMPCN')->setAttrib('size', '25');
		$initDt = $this->convertDateFormat($acctRec['UMISD'], 'Ymd', 'M d, Y');
		$this->getElement('UMISD')->setValue($initDt);
		$this->getElement('UMISD')->setAttrib('size', '25');
		
		$ucsrRec = $premise->retrieve_UCSR($premiseNo);
		$this->getElement('UCCTP')->setValue($ucsrRec['UCCTP']);
		$this->getElement('UCMTR')->setValue($ucsrRec['UCMTR']);
		$this->getElement('UCSCH')->setValue($ucsrRec['UCSCH']);
		$this->getElement('UCSIZ')->setValue($ucsrRec['UCSIZ']);
		
	}

	/**
	 * Retrieve SlsApp record and set w/o screen fields for SLM info.
	 * @param integer-string $slsAppNum
	 */
	private function populateSlsAppFields( $premiseNo ) {
		$slsapp = new SalesApp($this->conn);
		$slsappRec = $slsapp->retrieveByPremiseNo($premiseNo);
		if (!is_array($slsappRec)) {
			// If we didn't get sales app by premise. try using sales app num
			$slsappRec = $slsapp->retrieve($this->inputs['WO_SALES_APP_NUM']);
		}
		if (is_array($slsappRec)) {
			$this->getElement('WO_SALES_APP_NUM')->setValue($slsappRec['SLSAP#']);
//			if ($this->isCreateMode() && is_object($this->getElement('WO_PROJECT_NUM'))) {
//				$proj = new Project_Master($this->conn);
//				$projRec = $proj->retrieveByFeasibilityNum($slsappRec['SLSFS#']);
//				$this->getElement('WO_PROJECT_NUM')->setValue($projRec['PRJ_NUM']);
//			}
			
			if ($this->wo_type == 'SI' 
			&&  $this->isCreateMode()
			&& (int)$slsappRec['SLSIDT'] > 0) 
			{
				// For SIs, default complete by date from SLSAPP 
				$finishByDate = DateTime::createFromFormat('Ymd',$slsappRec['SLSIDT']);
				$finishByDateFmtd = $finishByDate->format('m-d-Y');
				$this->getElement('WO_COMPLETE_BY_DATE')->setValue($finishByDateFmtd);
			}
			
			$this->inputs = array_merge($this->inputs, $slsappRec);
			$slsdat = $this->convertDateFormat($slsappRec['SLSDAT'], 'mdY', 'M d, Y');
			$this->getElement('SLSDAT')->setValue($slsdat);
			
			$slsdes = $this->convertDateFormat($slsappRec['SLSDES'], 'mdY', 'M d, Y');
			$this->getElement('SLSDES')->setValue($slsdes);
			
			$slsidt = $this->convertDateFormat($slsappRec['SLSIDT'], 'Ymd', 'M d, Y');
			$this->getElement('SLSIDT')->setValue($slsidt);
			
			$slsodt = $this->convertDateFormat($slsappRec['SLSODT'], 'Ymd', 'M d, Y');
			$this->getElement('SLSODT')->setValue($slsodt);

			$this->getElement('SLSFS#')->setValue($slsappRec['SLSFS#']);
			$this->getElement('SLSSMN')->setValue($slsappRec['SLSSMN']);
			$this->getElement('SLSDSG')->setValue($slsappRec['SLSDSG']);
			$this->getElement('STATUS')->setValue($slsappRec['STATUS']);
			$this->getElement('SLSCON')->setValue($slsappRec['SLSCON']);
			$this->getElement('SLSMNC')->setValue($slsappRec['SLSMNC']);
			$this->getElement('SLSBTU')->setValue(number_format($slsappRec['SLSBTU'],0));
			$this->getElement('SLSMCF')->setValue(number_format($slsappRec['SLSMCF'],0));
			$this->getElement('SLSRWR')->setValue($slsappRec['SLSRWR']);
			$this->getElement('SLSRWD')->setValue($slsappRec['SLSRWD']);
		}
	}
	
	/**
	 * The populate() function is used to load the screen initially from an existing record, and to load
	 * values when the screen is redisplayed on an error condition - This function should retrieve 
	 * any ancillary values needed to display the screen completely (i.e., descriptions for coded values)
	 * 
	 * @see Zend_Form::populate()
	 */	
	public function populate(array $data) 
	{
		parent::populate($data);
		
		$crew = new Crew($this->conn);

		if ( ! $this->isLeakWO() ) {
			$prj = new Project_Master($this->conn);
			$projectElem = $this->getElement('WO_PROJECT_NUM'); 
			if (isset($projectElem)) {
				$projectNum = $projectElem->getValue();
				if (trim($projectNum) != '' && ctype_digit( $projectNum ) ) {
					$projDesc = $prj->getProjectDescription($projectNum);
					$this->getElement('WO_PROJECT_NUM')->setDescription($projDesc);
				}
			} 			
			if ($this->isCostInformationRequired()) {
				$crewId = $this->getElement('WO_CREW_ID')->getValue();
				$this->getElement('WO_CREW_ID')->setDescription($crew->getCrewName($crewId));


				// Compute total est cost if cost per foot and length are entered
				$estCost = trim($this->getElement('WO_ESTIMATED_COST')->getValue());
				$estCostPerFoot = trim($this->getElement('WO_EST_COST_PER_FOOT')->getValue());
				$estLength = trim($this->getElement('WO_ESTIMATED_LENGTH')->getValue());
				if ((float) $estCost == 0 && (float) $estCostPerFoot != 0 && (float) $estLength != 0) {
					$calcCost = (float) $estCostPerFoot * (float) $estLength;
					$this->getElement('WO_ESTIMATED_COST')->setValue($calcCost);
				}
			}
		} else {
			$crewHrs = $this->getElement('LK_PERSONNEL_HOURS')->getValue();
			$crewSize = $this->getElement('LK_CREW_SIZE')->getValue();
			if ($crewSize == 0) {
				$this->getElement('LK_CREW_SIZE')->setValue('1');
				$crewSize = 1;
			}
			
			$crewId = $this->getElement('LK_CREW_ID')->getValue();
			$this->getElement('LK_CREW_ID')->setDescription($crew->getCrewName($crewId));
			
			$totalManHrs = (int) $crewHrs * (int) $crewSize;
			$this->getElement('LK_TOTAL_MAN_HOURS')->setValue($totalManHrs);
		}

		
		if ($this->isServiceWO()) {
			$this->populatePremiseFields($data['WO_PREMISE_NUM']);
			$this->populateSlsAppFields($data['WO_PREMISE_NUM']);
		}
		
		$ptObj = new Pipe_Type_Master($this->conn);
		
		if (trim($this->work_order->record['WO_PIPE_TYPE']) != '') {
			$ptCode = $this->work_order->record['WO_PIPE_TYPE'];
			if (trim($ptCode) != '' && ctype_digit( $ptCode ) ) {
				$ptRec = $ptObj->retrieveById($ptCode);
				$this->getElement('PT_ACCTG_UNIT_COST')->setValue($ptRec['PT_ACCTG_UNIT_COST']);
				$this->getElement('PT_GL_ACCT_COST')->setValue($ptRec['PT_GL_ACCT_COST']);
				$this->getElement('PT_SUB_ACCT_COST')->setValue($ptRec['PT_SUB_ACCT_COST']);

				$this->getElement('PT_ACCTG_UNIT_CLOSE')->setValue($ptRec['PT_ACCTG_UNIT_CLOSE']);
				$this->getElement('PT_GL_ACCT_CLOSE')->setValue($ptRec['PT_GL_ACCT_CLOSE']);
				$this->getElement('PT_SUB_ACCT_CLOSE')->setValue($ptRec['PT_SUB_ACCT_CLOSE']);
			}
		}
		if ($this->getElement('WO_PIPE_TYPE') != NULL) { 
			$pipeTypeElem = $this->getElement('WO_PIPE_TYPE'); 
			if (isset($pipeTypeElem)) {
				$ptCode = $pipeTypeElem->getValue();
				if (trim($ptCode) != '' && ctype_digit( $ptCode ) ) {
					$ptRec = $ptObj->retrieveById($ptCode);
					$pipeTypeElem->setDescription($ptRec['PT_DESCRIPTION']);
				}
			} 			
		}

		if ($this->getElement('WO_MAIN_PIPE_TYPE') != NULL) {
			$mainPipeTypeElem = $this->getElement('WO_MAIN_PIPE_TYPE'); 
			if (isset($mainPipeTypeElem)) {
				$ptCode = $mainPipeTypeElem->getValue();
				if (trim($ptCode) != '' && ctype_digit( $ptCode ) ) {
					$ptDesc = $ptObj->getPipeTypeDescription($ptCode);
					$mainPipeTypeElem->setDescription($ptDesc);
				}
			} 			
		}
		
		$cvm = new Code_Values_Master($this->conn);
		
		$wotype = $this->getElement('WO_TYPE')->getValue();
		$type_desc = $cvm->getCodeValue('WO_TYPE', $wotype);
		$this->getElement('WO_TYPE')->setDescription($type_desc);

		// Format the work order status with description
		$woNum = $this->getElement('WO_NUM')->getValue();
		$wosts = $this->getElement('WO_STATUS')->getValue();
		$sts_desc = $cvm->getCodeValue('WO_STATUS', $wosts);
		$woStsCode = trim($this->getElement('WO_STATUS')->getValue());
		// If w/o is cancelled, retrieve the reason description and add
		// it to the status description 
		if ($this->isCancelledWO($woStsCode)) {
			$woCnlObj = new WO_Cancellations($this->conn);
			$cnlRec = $woCnlObj->retrieveByID($woNum);
			if (is_array($cnlRec)) {
				$cnlReason = ': ' . $cnlRec['REASON_TEXT'];
				if (trim($cnlRec['WCN_REASON_DESCRIPTION']) != '') {
					$cnlReason .= ' (' . $cnlRec['WCN_REASON_DESCRIPTION'] . ')'; 
				}				 
				$sts_desc .= $cnlReason; 
			}
		}
		$this->getElement('WO_STATUS')->setDescription($sts_desc);

		
		$this->getElement('WO_TYPE')->setAttrib('onchange',"this.value = '$wotype';");
		
		$this->getElement('WO_ENTRY_DATE')->setAttrib('size', '25');
		
		$ts = $this->getTimeStampOutputFormat($this->getElement('WO_CREATE_TIME')->getValue());
		$this->getElement('WO_CREATE_TIME')->setValue($ts);
		$this->getElement('WO_CREATE_TIME')->setAttrib('size', '40');
		
		$ts = $this->getTimeStampOutputFormat($this->getElement('WO_CHANGE_TIME')->getValue());
		$this->getElement('WO_CHANGE_TIME')->setValue($ts);
		$this->getElement('WO_CHANGE_TIME')->setAttrib('size', '40');
	}

	/**
	 * Custom validations for this form - this overrides the validate() method 
	 *    defined in VGS_Form.php, and calls the Zend_Form isValid() method. 
	 * @see VGS_Form::validate()
	 * @see Zend_Form::isValid()
	 */	
	public function validate() 
	{
		$this->valid = parent::isValid($this->inputs);
		$woType = $this->inputs['WO_TYPE'];

		$woStatus = $this->inputs['WO_STATUS'];
// 		if ($woStatus == Workorder_Master::WO_STATUS_COMPLETED) {
// 			// Don't perform these validations on completed workorders.
// 			return $this->valid;
// 		}
		
		if ($this->isCostInformationRequired()) {
			$estCost = $this->inputs['WO_ESTIMATED_COST'];
			$estCostPerFoot = $this->inputs['WO_EST_COST_PER_FOOT'];
			$estLength = $this->inputs['WO_ESTIMATED_LENGTH'];

			$estCostErrMsg = "Either (Estimated Cost) or (Est Cost Per Foot and Estimated Length) must be entered.";
			
			if (isBlankOrZero($estCost)
			&& (isBlankOrZero($estCostPerFoot) 
			 || isBlankOrZero($estLength))) {
  				$this->getElement('WO_ESTIMATED_COST')->addError($estCostErrMsg);
  				$this->getElement('WO_EST_COST_PER_FOOT')->addError($estCostErrMsg);
  				$this->getElement('WO_ESTIMATED_LENGTH')->addError($estCostErrMsg);
  				$this->valid = false;
	      }
		}

		if ($this->isDepthRequired() && $this->isCompletionRequested()) {
	        if ($this->isBlankOrZero($this->inputs['WO_DEPTH_FEET'])
	        &&  $this->isBlankOrZero($this->inputs['WO_DEPTH_INCHES'])) {
				$depthMsg = "Depth (feet and/or inches) must be entered.";
		        $this->getElement('WO_DEPTH_FEET')->addError($depthMsg);
		        $this->getElement('WO_DEPTH_INCHES')->addError($depthMsg);
	  			$this->valid = false;
	        }
		}
		
		$pipeTypeCode = $this->inputs['WO_PIPE_TYPE'];
		if (isset($pipeTypeCode) &&  ctype_digit($pipeTypeCode) ) {
			$ptObj = new Pipe_Type_Master($this->conn);
			$ptRec = $ptObj->retrieveById($pipeTypeCode) ;
		}
		
		// Check for permission to create capital work orders
		if (is_array($ptRec)) {
			if ($this->isCreateMode() && $ptRec['PT_CAP_EXP'] == 'C') {
				// Check for authority to create capital work orders
    			$allowCapitalWO = $this->security->checkAuthoritiesPermission(array('WO_CRT_CAP'), false);
    			if (!$allowCapitalWO) {
    				$this->getElement('WO_PIPE_TYPE')->
    					addError('You are not authorized to create capital work orders.');
    				$this->valid = false;
    			}
			}
			
			if ($woType != 'NW') { // non-w/o should allow any pipe type
				if ($ptRec['PT_CATEGORY'] == 'S' 
				&& ! Workorder_Master::isService_WO_TYPE($woType)) {
	    				$this->getElement('WO_PIPE_TYPE')->
	    					addError("Pipe type category is (S)ervice but this is not a service related work order.");
	    				$this->valid = false;
				}
				if ($ptRec['PT_CATEGORY'] == 'M' 
				&& ! Workorder_Master::isMain_WO_TYPE($woType)) {
	    				$this->getElement('WO_PIPE_TYPE')->
	    					addError("Pipe type category is (M)ain but this is not a main related work order.");
	    				$this->valid = false;
				}
				if (trim($ptRec['PT_CATEGORY']) == 'N' 
				&& (Workorder_Master::isMain_WO_TYPE($woType) ||
					 Workorder_Master::isService_WO_TYPE($woType))
				){
	    				$this->getElement('WO_PIPE_TYPE')->
	    					addError("Pipe type category is (N)either; this is not allowed on a main or service related work order.");
	    				$this->valid = false;
				}
			}
		}
 
		$projectNum = $this->inputs['WO_PROJECT_NUM'];
		if (isset($projectNum) && ctype_digit($projectNum)) {
			$prjObj = new Project_Master($this->conn);
			$prjRec = $prjObj->retrieveById($projectNum) ;
		}
			
		if (is_array($prjRec) && is_array($ptRec)) {
			if ($prjRec['PRJ_CAP_EXP_CODE'] != $ptRec['PT_CAP_EXP']) {
				$capExpMsg = 'Project and Pipe Type do not have the same cost allocation (capital/expense)';
   			$this->getElement('WO_PIPE_TYPE')->addError($capExpMsg);
   			$this->getElement('WO_PROJECT_NUM')->addError($capExpMsg);
   			$this->valid = false;
			}
			if ($this->isUpdateMode()) {
				$woAlloc = $this->getCostAllocDesc($this->work_order->record['WO_COST_ALLOC']);
				$projAlloc = $this->getCostAllocDesc($prjRec['PRJ_CAP_EXP_CODE']);
				$pipeAlloc = $this->getCostAllocDesc($ptRec['PT_CAP_EXP']);
				
				if ($woAlloc != $projAlloc) {
					// Don't allow change of WO cost allocation from capital <-> expense after WO is created
					$costAllocChgErr = "Work Order is $woAlloc. Project is $projAlloc. You cannot change cost allocation of an existing work order. Choose a(n) $woAlloc project.";
					$this->getElement('WO_PROJECT_NUM')->addError($costAllocChgErr);
					$this->valid = false;
				}
				if ($woAlloc != $pipeAlloc) {
					// Don't allow change of WO cost allocation from capital <-> expense after WO is created
					$costAllocChgErr = "Work Order is $woAlloc. Pipe Type is $pipeAlloc. You cannot change cost allocation of an existing work order. Choose a(n) $woAlloc pipe type.";
					$this->getElement('WO_PIPE_TYPE')->addError($costAllocChgErr);
					$this->valid = false;
				}
			}
		}					

		if ($woType == 'SR' || $woType == 'SI') {
			$curbStop = $this->inputs['WO_CURB_STOP'];
			$flowLimiterFlag = $this->inputs['WO_FLOW_LIMITER'];
			if ($curbStop != 'Y' && $flowLimiterFlag != 'Y') {
				$flowErr = 'Either curb stop or flow limiter is required for SR/SI.';
   			$this->getElement('WO_CURB_STOP')->addError($flowErr);
   			$this->getElement('WO_FLOW_LIMITER')->addError($flowErr);
   			$this->valid = false;
			}
		}
		
		if ($this->isLeakWO()) {
			if ($this->inputs['LK_REPAIRED_METHOD'] == 'OTHER' 
			&& trim($this->inputs['LK_REPMETH_OTHER']) == '') {
				$repMethErr = 'Please enter a description for Other Repaired Method';
				$this->getElement('LK_REPMETH_OTHER')->addError($repMethErr);
				$this->valid = false;
			}
			if ($this->inputs['LK_REPAIRED_EQUIPMENT'] == 'OTHER' 
			&& trim($this->inputs['LK_REPEQUIP_OTHER']) == '') {
				$repMethErr = 'Please enter a description for Other Repaired Equipment';
				$this->getElement('LK_REPEQUIP_OTHER')->addError($repMethErr);
				$this->valid = false;
			}
		}
		
		return $this->valid ;
	}	
	
	private function getCostAllocDesc($code) {
		switch ($code) {
			case 'C':
				return 'Capital';
				break;
			case 'E':
				return 'Expense';
				break;
			default:
				return '';
				break;
		}
	}
}

