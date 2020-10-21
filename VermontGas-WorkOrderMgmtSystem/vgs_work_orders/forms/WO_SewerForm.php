<?php
require_once '../forms/VGS_Form.php';
require_once '../model/WO_Sewer.php';
require_once '../model/Workorder_Master.php';

class WO_SewerForm extends VGS_Form 
{
	private $db_object;
	
	/**
	 * Record array for existing w/o sewer record
	 * @var array
	 */
	private $wswRec;
	 	
	/** 
	 * W/O number related to this sewer data.
	 * @var integer
	 */
	private $woNum;
	private $wswSeqNo;
	
	/**
	 * Complete w/o record from workorder_master, for the related w/o  
	 * @var array
	 */
	private $woRec;
	

	/*
	 * CONSTRUCTOR METHOD -------------------------------
	 */	
	public function __construct( $conn, $woNum ) {
		parent::__construct ( $conn );

		$this->fh->addMetaData($this->conn, "WO_SEWER");
		$this->db_object = new WO_Sewer($conn);
		
		$this->woNum = $woNum;
		$woObj = new Workorder_Master($conn);
		$this->woRec = $woObj->getWorkorder($this->woNum);

		if (!isset($this->woRec) || $this->woRec['WO_NUM'] != $this->woNum) {
			throw new Exception("Invalid W/O Number: {$this->woNum}");
		} 
		
		$this->screen_title = ucfirst($this->mode) . ' W/O Sewer Information';

		$this->setDefaultElements( );
	}

	/*
	 * GETTER METHODS ------------------------------------
	 */	
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
    	
		return $this->db_object->create($rec);
	}

	//---------------------------------------------------
	public function updateRecord() {
		$data = $this->inputs;
		// Remove output-only fields from the data array for update
		unset($data["WSW_CREATE_USER"]);
		unset($data["WSW_CREATE_TIME"]);
		unset($data["WSW_CHANGE_USER"]);
		unset($data["WSW_CHANGE_TIME"]);		
		
		$this->db_object->update($data);
	}

	//---------------------------------------------------
	public function retrieveRecord() {
		return $this->db_object->retrieve($this->inputs);
	}
	
	//---------------------------------------------------
	public function deleteRecord() {
		return $this->db_object->delete($this->inputs);
	}

	//---------------------------------------------------
	public function setDefaultElements( ) {
		$this->fh->addCustomMetaDatum('WO_TYPE', 'W/O Type', 'CHAR', '2');
		$this->fh->addCustomMetaDatum('WO_STATUS', 'W/O Status', 'CHAR', '3');
  		$this->fh->addCustomMetaDatum('WO_DATE_COMPLETED', 'Date Completed', 'CHAR', 12);
		
  		
		$flWO = 'WSW_WO_NUM, WO_TYPE, WO_STATUS, WO_DATE_COMPLETED';
		$this->fh->addFieldGroup( $flWO, 'wo', 'Work Order Details');
		$this->fh->setElementsProperties( $flWO, 'output_only', true);

		
		$flSewer = 
			'WSW_SEQNO, WSW_ADDRESS, WSW_CITY, WSW_LOCATED_PRIOR, WSW_SEWER_SIZE, WSW_SEWER_MATERIAL, ' 
			. 'WSW_SEWER_TYPE, WSW_SEPARATION_FROM_GAS, WSW_DAMAGED_CONSTR, WSW_INSPECTION_NEEDED, '
			. 'WSW_INSPECT_REASON';
		$this->fh->addFieldGroup( $flSewer, 'sewer', 'Sewer Information');
		$this->fh->setElementsProperties('WSW_SEQNO','output_only',true); 
		$this->fh->setElementsProperties('WSW_ADDRESS, WSW_CITY, WSW_SEWER_TYPE, '
				. 'WSW_INSPECT_REASON', 'required', true);
		$this->fh->setElementsProperties('WSW_LOCATED_PRIOR, WSW_INSPECTION_NEEDED, WSW_DAMAGED_CONSTR',
			'input_type', 'y/n');
		$this->fh->setElementsProperties('WSW_CITY, WSW_SEWER_TYPE, WSW_SEPARATION_FROM_GAS, ', 
				'input_type', 'select');
		
		
		$flPostInsp = 'WSW_DATE_INSP_COMPLETED, WSW_INSP_FINDINGS, WSW_INSPECTED_BY';
		$this->fh->addFieldGroup( $flPostInsp, 'postinsp', 'Post Sewer Inspection');
		$this->fh->setElementsProperties('WSW_LOCATED_PRIOR, WSW_INSPECTION_NEEDED',
			'input_type', 'y/n');
		$this->fh->setElementsProperties('WSW_INSP_FINDINGS, WSW_INSPECTED_BY', 'input_type', 'select');
		
		
		$flMOC = "WSW_MOC_TRENCH, WSW_MOC_HDD, WSW_MOC_HOG, WSW_MOC_PLOWED, WSW_MOC_OTHER";
		$this->fh->addFieldGroup( $flMOC, 'moc', 'Method of Construction');
		$this->fh->setElementsProperties(
			'WSW_MOC_TRENCH, WSW_MOC_HDD, WSW_MOC_HOG, WSW_MOC_PLOWED', 
			'input_type', 'y/n');

		
		$flAudit = 'WSW_CREATE_USER, WSW_CREATE_TIME, WSW_CHANGE_USER, WSW_CHANGE_TIME';
		$this->fh->addFieldGroup( $flAudit, 'maintenance', 'Sewer Record Maintenance Info');
		$this->fh->setElementsProperties($flAudit, 'output_only', true);
		
		
		$this->fh->addFieldGroup( 'WSW_NOTES', 'notes', 'Comments');
		$this->fh->setElementsProperties('WSW_NOTES', 'input_type', 'textarea');
		
		// This creates Zend_Form_Elements out of the meta data
		$this->fh->addElementsFromMetaData($this->mode);
		
		$this->setName ( 'form1' );
		$this->addElements ( $this->fh->getElements() );
		
		$this->populateWOFields();
		
		$dd = new Code_Values_Master($this->conn);
		$ddList = $dd->getCodeValuesList('TOWN', ' ');
		$this->fh->setMultiOptions('WSW_CITY', $ddList);
		
		$ddList = $dd->getCodeValuesList('SEWER_TYPE', ' ');
		$this->fh->setMultiOptions('WSW_SEWER_TYPE', $ddList);
		
		$ddList = $dd->getCodeValuesList('SEWER_SEPARATION', 'Unknown');
		$this->fh->setMultiOptions('WSW_SEPARATION_FROM_GAS', $ddList);
		
		$ddList = $dd->getCodeValuesList('WSW_INSP_FINDINGS', ' ');
		$this->fh->setMultiOptions('WSW_INSP_FINDINGS', $ddList);
		
		$ddList = $dd->getCodeValuesList('CONTRACTORS', ' ');
		$this->fh->setMultiOptions('WSW_INSPECTED_BY', $ddList);

		if($this->isCreateMode() || $this->isUpdateMode()) {
			$copyButton = '<button type="button" onclick="copyAddress(); return false;">' .
							'Copy from W/O</button>';
			$this->getElement('WSW_ADDRESS')->setDescription($copyButton);
		}

		$this->getElement('WSW_CREATE_TIME')->setAttrib('size', 30);
		$this->getElement('WSW_CHANGE_TIME')->setAttrib('size', 30);
	}

	//---------------------------------------------------
	public function reset() {
		parent::reset();
		$this->getElement('WSW_WO_NUM')->setValue($this->woNum);
		$this->getElement('WSW_SEQNO')->setValue($this->wswSeqNo);
		
		// Default city from W/O record on create
		if ($this->isCreateMode() && 
		'' == trim($this->getElement('WSW_CITY')->getValue())) {
			$city = $this->woRec['WO_TAX_MUNICIPALITY'];
			$this->getElement('WSW_CITY')->setValue($city);
		}

		$this->populateWOFields();
	}

	//---------------------------------------------------
	private function populateWOFields() {
		$dd = new Code_Values_Master($conn);
		
		$this->getElement('WSW_WO_NUM')->setDescription($this->woRec['WO_DESCRIPTION']);
		
		$this->getElement('WO_TYPE')->setValue($this->woRec['WO_TYPE']);
		$woTypeDesc = $dd->getCodeValue('WO_TYPE', $this->woRec['WO_TYPE']);
		$this->getElement('WO_TYPE')->setDescription($woTypeDesc);
		
		$this->getElement('WO_STATUS')->setValue($this->woRec['WO_STATUS']);
		$woStsDesc = $dd->getCodeValue('WO_STATUS', $this->woRec['WO_STATUS']);
		$this->getElement('WO_STATUS')->setDescription($woStsDesc);
		
		$dateCompleted = $this->fixDateOutput($this->woRec['WO_DATE_COMPLETED'], true);
		$this->getElement('WO_DATE_COMPLETED')->setValue($dateCompleted);

// 		$this->getElement('WSW_CITY')->setValue($this->woRec['WO_TAX_MUNICIPALITY']);
		$woTownDesc = $dd->getCodeValue('TOWN', $this->woRec['WSW_CITY']);
		$this->getElement('WSW_CITY')->setDescription($woTownDesc);
		
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
		$this->populateWOFields();

//		Changed logic: reason is now always required (7/30/2012) 
// 		if ($this->inputs['WSW_INSPECTION_NEEDED'] == 'N' 
// 		&& trim($this->inputs['WSW_INSPECT_REASON']) == '') {
// 			$errMsg = 'Reason is required if no inspection.'; 
//   			$this->getElement('WSW_INSPECT_REASON')->addError($errMsg);
// 			$this->valid = false;
// 		}

		if ($this->inputs['WSW_MOC_TRENCH'] != 'Y' 
		&& $this->inputs['WSW_MOC_HDD'] != 'Y'
		&& $this->inputs['WSW_MOC_HOG'] != 'Y'
		&& $this->inputs['WSW_MOC_PLOWED'] != 'Y'
		&& trim($this->inputs['WSW_MOC_OTHER']) == ''
		) {
			$errMsg = 'Please select at least one method of construction.'; 
  			$this->getElement('WSW_MOC_OTHER')->addError($errMsg);
			$this->valid = false;
		}
		
		return $this->valid ;
	}
	
	//---------------------------------------------------
	public function returnToCaller() {
 		// Redirect to the work order sewers search screen after form processing
		$woNum = $this->inputs['WSW_WO_NUM'];
		header("Location: wswListCtrl.php?filter_WSW_WO_NUM=$woNum&popup=1&filtSts=restore" );
		exit;
	}
	
}

