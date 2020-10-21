<?php
require_once '../forms/VGS_Form.php';
require_once '../model/WO_Cancellations.php';
require_once '../model/Workorder_Master.php';
require_once '../model/Pipe_Type_Master.php';
require_once '../model/Project_Master.php';

class WOCancellationsForm extends VGS_Form 
{
	private $db_object;
	
	/**
	 * Record array for existing w/o cancellation request
	 * @var array
	 */
	private $wcnRec;
	 	
	/** 
	 * W/O number to be cancelled.
	 * @var integer
	 */
	private $woNum;
	
	/**
	 * Complete w/o record from workorder_master, for the w/o to be cancelled 
	 * @var array
	 */
	private $woRec;

	/**
	 * Complete pipe type record from pipe_type_master, for the pipe type of w/o to be cancelled 
	 * @var array
	 */
	private $ptRec;
	
	/**
	 * Complete project record from project_master, for the project of the w/o to be cancelled 
	 * @var array
	 */
	private $prjRec;

	/**
	 * $costDtls is an array that stores the cost records from Lawson 
	 * associated with this order, if any. 
	 * @var array
	 */
	private $costDtls = array();
	
	/**
	 * $transferSources is an array of the Lawson source codes which, if any cost records
	 * exist with these source codes, the dollars from this w/o must be transferred to 
	 * another w/o in order to cancel the order.
	 * @var array
	 */
	private $transferSources = array('AP', 'IC', 'PR', 'CW');
	
	/**
	 * Boolean variable, if true then operations cannot cancel the w/o, 
	 * it must be cancelled by accounting 
	 * @var boolean
	 */
	public $isDollarsApplied = false;
	
	/** 
	 * Boolean variable, if true then the dollars from this w/o must be transferred to 
	 * another w/o in order to cancel the order. 
	 * @var boolean
	 */
	public $isTransferDollarsRequired = false;
		

	/*
	 * CONSTRUCTOR METHOD -------------------------------
	 */	
	public function __construct( $conn, $woNum, $retrieveOtherRecs = false ) {
		parent::__construct ( $conn );

		$this->fh->addMetaData($this->conn, "WOCANCEL");
		$this->db_object = new WO_Cancellations($conn);
		
		$this->woNum = $woNum;
		
		if ($retrieveOtherRecs) {
			$woObj = new Workorder_Master($conn);
			$this->woRec = $woObj->getWorkorder($this->woNum);
			
			$ptObj = new Pipe_Type_Master($conn);
			$this->ptRec = $ptObj->retrieveById($this->woRec['WO_PIPE_TYPE']);
			
			$prjObj = new Project_Master($conn);
			$this->prjRec = $prjObj->retrieveById($this->woRec['WO_PROJECT_NUM']);

			if (!isset($this->woRec) || $this->woRec['WO_NUM'] != $this->woNum) {
				throw new Exception("Invalid W/O Number: {$this->woNum}");
			}
		}
		
		$this->wcnRec = $this->db_object->retrieveByID($this->woNum);
		// Cancel request already exists?
		if (is_array($this->wcnRec)) {
			if ($this->wcnRec['WCN_CANCEL_STATUS'] == WO_Cancellations::CANCEL_REQUEST_STATUS_COMPLETE) {
				$this->mode = 'inquiry';
			}
			if ($this->wcnRec['WCN_CANCEL_STATUS'] == WO_Cancellations::CANCEL_REQUEST_STATUS_PENDING) {
				$this->mode = 'update';
			}
		// Not found - this is a new cancellation request
		} else {
			$this->mode = 'create';
		}

		$this->screen_title = ucfirst($this->mode) . ' W/O Cancellation Request';
		
		// Retrieve dollars posted to this w/o from Lawson
		if ($retrieveOtherRecs) {
			$this->retrieveLawsonDollars($this->woNum);
		}
		
		$this->setDefaultElements( );
	}

	/*
	 * GETTER METHODS ------------------------------------
	 */	
	public function getCostDtls() {
		return $this->costDtls;
	}

	public function getIsDollarsApplied() {
		return $this->isDollarsApplied;
	}

	public function getIsTransferDollarsRequired() {
		return $this->isTransferDollarsRequired;
	}

	public function getWoNum() {
		return $this->woNum;
	}
	
	public function getWoRec() {
		return $this->woRec;
	}
	
	
	
	/* ---------------------------------------------------
	 * ABSTRACT DATABASE ACCESS METHODS 
	 * --------------------------------------------------- */	
	public function createRecord() {
    	$rec = $this->inputs;
    	
    	$rec['isDollarsApplied'] = $this->isDollarsApplied;
    	$rec['isTransferDollarsRequired'] = $this->isTransferDollarsRequired;
    	$rec['woNum'] = $this->woNum;
    	$rec['prevWOStatus'] = $this->woRec['WO_STATUS'];
    	
		return $this->db_object->create($rec);
	}

	//---------------------------------------------------
	public function updateRecord() {
		$data = $this->inputs;
		// Remove output-only fields from the data array for update
		unset($data['WCN_PREV_WO_STATUS']);
		unset($data['WCN_CANCEL_STATUS']);
		unset($data["WCN_CANCELLED_BY"]);
		unset($data["WCN_CANCEL_TIME"]);
		unset($data["WCN_CREATE_USER"]);
		unset($data["WCN_CREATE_TIME"]);
		unset($data["WCN_CHANGE_USER"]);
		unset($data["WCN_CHANGE_TIME"]);		
		
		$this->db_object->update($data);
	}

	//---------------------------------------------------
	public function retrieveRecord() {
		return $this->db_object->retrieve($this->inputs);
	}
	
	//---------------------------------------------------
	public function deleteRecord() {

	}

	//---------------------------------------------------
	public function setDefaultElements( ) {
		$this->fh->addCustomMetaDatum('WO_TYPE', 'W/O Type', 'CHAR', '4');
		$this->fh->addCustomMetaDatum('WO_PIPE_TYPE', 'Pipe Type', 'CHAR', '3');
		$this->fh->addCustomMetaDatum('WO_PROJECT_NUM', 'Project', 'CHAR', '5');
		$this->fh->addCustomMetaDatum('GL_ACCT_COST', 'G/L Account Cost', 'CHAR', '15');
		$this->fh->addCustomMetaDatum('GL_ACCT_CLOSE', 'G/L Account Close', 'CHAR', '15');
		
		$flWO = 'WCN_WO_NUM, WO_TYPE, WO_PIPE_TYPE, WO_PROJECT_NUM';
		
		$this->fh->addFieldGroup( $flWO, 'wo', 'Work Order Details');
		$this->fh->setElementsProperties( $flWO, 'output_only', true);
		
		$flDollars = 'GL_ACCT_COST, GL_ACCT_CLOSE, WCN_DOLLARS_APPLIED';
		if ($this->isTransferDollarsRequired) { 
			$flDollars .= ', WCN_TRFR_WO_NUM';
		} else { 
			$flDollars .= ', WCN_REVERSE_DOLLARS';
		}
		
		$this->fh->addFieldGroup( $flDollars, 'dollars', 'Accounting');
		$this->fh->setElementsProperties('GL_ACCT_COST, GL_ACCT_CLOSE, WCN_DOLLARS_APPLIED', 'output_only', true);

		if ($this->isTransferDollarsRequired) {
			$this->fh->setElementsProperties('WCN_TRFR_WO_NUM', 'required', true);
		} else {
			$this->fh->setElementsProperties('WCN_REVERSE_DOLLARS', 'output_only', true);
		}
		
		if ($this->isUpdateMode()) {
			$this->fh->addCustomMetaDatum('COMPLETE_CANCEL', 'Complete W/O Cancellation?', 'CHAR', 1);
			$this->fh->setElementsProperties('COMPLETE_CANCEL', 'input_type', 'y/n');
			$cancelCheckBox = 'COMPLETE_CANCEL,';
		} else {
			$cancelCheckBox = '';
		}
		
		$flReason = "$cancelCheckBox WCN_REASON_CODE, WCN_REASON_DESCRIPTION";
		$this->fh->addFieldGroup( $flReason, 'reason', 'Reason for Cancellation');
		$this->fh->setElementsProperties('WCN_REASON_CODE', 'input_type', 'select');
		$this->fh->setElementsProperties('WCN_REASON_CODE', 'required', true);

		$flAudit = 'WCN_CANCEL_STATUS, WCN_CANCELLED_BY, WCN_CANCEL_TIME, WCN_PREV_WO_STATUS, '
					. 'WCN_CREATE_USER, WCN_CREATE_TIME, WCN_CHANGE_USER, WCN_CHANGE_TIME';
		$this->fh->addFieldGroup( $flAudit, 'maintenance', 'W/O Cancel Request Maintenance Info');
		$this->fh->setElementsProperties($flAudit, 'output_only', true);
		
		// This creates Zend_Form_Elements out of the meta data
		$this->fh->addElementsFromMetaData($this->mode);
		
		$this->setName ( 'form1' );
		$this->addElements ( $this->fh->getElements() );
				
		if ($this->getElement('COMPLETE_CANCEL') != null) {
			$this->getElement('COMPLETE_CANCEL')->setValue($_REQUEST['COMPLETE_CANCEL']);
		}
		$this->populateWOFields();
		
		$dd = new Code_Values_Master($this->conn);

		$ddList = $dd->getCodeValuesList('WCN_REASON_CODE', '-- Select a reason --');
		$this->fh->setMultiOptions('WCN_REASON_CODE', $ddList);
		
		$this->getElement('WCN_CANCEL_TIME')->setAttrib('size', 30);
		$this->getElement('WCN_CREATE_TIME')->setAttrib('size', 30);
		$this->getElement('WCN_CHANGE_TIME')->setAttrib('size', 30);
	}

	//---------------------------------------------------
	public function reset() {
		parent::reset();

		$this->getElement('WCN_WO_NUM')->setValue($this->woNum);
		$this->getElement('WCN_DOLLARS_APPLIED')->setValue($this->isDollarsApplied ? 'Y':'N');
		
		$this->populateWOFields();
		
		$this->setFieldMessages();		
	}

	//---------------------------------------------------
	private function populateWOFields() {
		$dd = new Code_Values_Master($conn);
		
		$this->getElement('WO_TYPE')->setValue($this->woRec['WO_TYPE']);
		$this->getElement('WCN_WO_NUM')->setDescription($this->woRec['WO_DESCRIPTION']);
		$woTypeDesc = $dd->getCodeValue('WO_TYPE', $this->woRec['WO_TYPE']);
		$this->getElement('WO_TYPE')->setDescription($woTypeDesc);
		
		$this->getElement('WO_PIPE_TYPE')->setValue($this->woRec['WO_PIPE_TYPE']);
		
		$ptCapExp = $dd->getCodeValue('PT_CAP_EXP', $this->ptRec['PT_CAP_EXP']);
		$this->getElement('WO_PIPE_TYPE')->setDescription($this->ptRec['PT_DESCRIPTION'] . "($ptCapExp)");
		
		$this->getElement('WO_PROJECT_NUM')->setValue($this->woRec['WO_PROJECT_NUM']);
		$prjCapExp = $dd->getCodeValue('PT_CAP_EXP', $this->prjRec['PRJ_CAP_EXP_CODE']);
		$this->getElement('WO_PROJECT_NUM')->setDescription($this->prjRec['PRJ_DESCRIPTION'] . "($prjCapExp)");
		
		$glCost = trim($this->ptRec['PT_ACCTG_UNIT_COST']) .'-'.
					 trim($this->ptRec['PT_GL_ACCT_COST']) .'-'.
					 trim($this->ptRec['PT_SUB_ACCT_COST']);

		$glClose = trim($this->ptRec['PT_ACCTG_UNIT_CLOSE']) .'-'.
					 trim($this->ptRec['PT_GL_ACCT_CLOSE']) .'-'.
					 trim($this->ptRec['PT_SUB_ACCT_CLOSE']);
							 
		$this->getElement('GL_ACCT_COST')->setValue($glCost);
		$this->getElement('GL_ACCT_CLOSE')->setValue($glClose);
		
	}
	
	//---------------------------------------------------
	private function setFieldMessages() {
		if ($this->isDollarsApplied) {
			$desc = '<br>Dollars were applied to this work order. Accounting must cancel this W/O.';
			$this->getElement('WCN_DOLLARS_APPLIED')->setDescription($desc);
			$transxCodes = implode(', ', $this->transferSources);
			if ($this->isTransferDollarsRequired) {
				$desc = "Lawson transactions of $transxCodes were found. You must specify the W/O to which dollars should be transferred.";
				$oldDesc = $this->getElement('WCN_TRFR_WO_NUM')->getDescription();
				$this->getElement('WCN_TRFR_WO_NUM')->setDescription($oldDesc . '<br>' . $desc);
			} else {
				$this->getElement('WCN_REVERSE_DOLLARS')->setValue('Y');
				$desc = "NO Lawson transactions of $transxCodes were found. Dollars will be reversed.";
				$oldDesc = $this->getElement('WCN_REVERSE_DOLLARS')->getDescription();
				$this->getElement('WCN_REVERSE_DOLLARS')->setDescription($oldDesc . '<br>' . $desc);
			}
		} else {
			$desc = 'No dollars were applied to this work order. You can cancel this W/O by clicking Save.';
			$oldDesc = $this->getElement('WCN_DOLLARS_APPLIED')->getDescription();
			$this->getElement('WCN_DOLLARS_APPLIED')->setDescription($oldDesc . '<br>' . $desc);
		}
	}
	
	//---------------------------------------------------
	private function retrieveLawsonDollars( $woNum ) {
		$select = Workorder_Master::getDollarsPostedSQLSelect($woNum);
		$dbTable = new VGS_DB_Table($conn);
		$dbTable->execListQuery($select->toString(), $select->parms);
		
		while ($costDtl = db2_fetch_assoc( $dbTable->stmt )) {
			$this->costDtls[] = $costDtl;
			$this->isDollarsApplied = true;
			if (in_array($costDtl["Src Code"], $this->transferSources)) {
				$this->isTransferDollarsRequired = true;		
			}
		}
	}


	//---------------------------------------------------
	/**
	 * Custom validations for this form - this overrides the validate() method 
	 *    defined in VGS_Form.php, and calls the Zend_Form isValid() method. 
	 * @see VGS_Form::validate()
	 * @see Zend_Form::isValid()
	 */	
	public function validate() 
	{
		$this->valid = parent::isValid($this->inputs);
		$this->setFieldMessages();		
		$this->populateWOFields();
		

		$sec = new Security();
		if ($_REQUEST['COMPLETE_CANCEL'] == 'Y') {
			$sec->setRedirectOnDeny(false);
			$authorized = $sec->checkPermissionByCategory('WO', 'CANCEL_PND');
			if (!$authorized) {
				$errMsg = 'You are not authorized to cancel work orders in cancel pending status.'; 
	  			$this->getElement('COMPLETE_CANCEL')->addError($errMsg);
				$this->valid = false;
			}
		}
		
		$reasonCode = $this->inputs['WCN_REASON_CODE'];
		$reasonDesc = $this->inputs['WCN_REASON_DESCRIPTION'];

		if (isset($this->inputs['WCN_TRFR_WO_NUM'])) {
			$tfrWoNum = trim($this->inputs['WCN_TRFR_WO_NUM']);
			if ($tfrWoNum == $this->woNum) {
				$errMsg = 'Transfer to W/O Number cannot be the same as the W/O being cancelled.'; 
	  			$this->getElement('WCN_TRFR_WO_NUM')->addError($errMsg);
				$this->valid = false;
			} elseif (! ctype_digit($tfrWoNum) || (int) $tfrWoNum > 10000000) {
				$errMsg = 'Transfer to W/O Number is not valid.'; 
	  			$this->getElement('WCN_TRFR_WO_NUM')->addError($errMsg);
				$this->valid = false;
			} else {
				$tfrWoObj = new Workorder_Master($this->conn);
				$tfrWoRec = $tfrWoObj->getWorkorder($tfrWoNum);
				if (!is_array($tfrWoRec) || $tfrWoRec['WO_NUM'] != $tfrWoNum) {
					$errMsg = 'Transfer to W/O Number is not found on work order master file.'; 
		  			$this->getElement('WCN_TRFR_WO_NUM')->addError($errMsg);
					$this->valid = false;
				}
			}
		}
		
		if ($reasonCode == 'OTHER' && trim($reasonDesc) == '') {
			$errMsg = 'Please specify reason for "OTHER"'; 
  			$this->getElement('WCN_REASON_DESCRIPTION')->addError($errMsg);
			$this->valid = false;
		}
		
		if ($_REQUEST['COMPLETE_CANCEL'] == 'Y') {
			$netCharges = '0';
			//pre_dump($this->costDtls);
			foreach ($this->costDtls as $costTrx) {
				$netCharges = bcadd($netCharges, $costTrx['TransX Amount'], 2);
				//pre_dump("This transx amt = " . $costTrx['TransX Amount'] . "; running total = $netCharges");
			}
			//pre_dump("Net charges = $netCharges");
			
			if ($netCharges != 0) {
				$netChgFmtd = '$' . number_format((float) $netCharges, 2);
				$errMsg = "Net charges for this W/O must be zero in order to cancel. Net charges of $netChgFmtd are still applied to this W/O."; 
				$this->getElement('WCN_DOLLARS_APPLIED')->addError($errMsg);
				$this->valid = false;
			}
		}
		return $this->valid ;
	}
	
	//---------------------------------------------------
	public function returnToCaller() {
		// Redirect to the work order search screen after form processing
		header("Location: woListCtrl.php?filtSts=restore" );
		exit;
	}
	
}

