<?php
require_once '../forms/VGS_Form.php';
require_once '../model/Crew.php';
require_once '../common/validators/Crew_Validator.php';
require_once '../model/WO_Electrofusion.php';
require_once '../model/Workorder_Master.php';

class WO_ElectrofusionForm extends VGS_Form 
{
	private $db_object;
	
	/**
	 * Record array for existing w/o electrofusion record
	 * @var array
	 */
	private $wefRec;
	 	
	/** 
	 * W/O number related to this electrofusion data.
	 * @var integer
	 */
	private $woNum;
	private $wefSeqNo;
	
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

		$this->fh->addMetaData($this->conn, "WOELECFUSN");
		$this->db_object = new WO_Electrofusion($conn);
		
		$this->woNum = $woNum;
		$woObj = new Workorder_Master($conn);
		$this->woRec = $woObj->getWorkorder($this->woNum);

		if (!isset($this->woRec) || $this->woRec['WO_NUM'] != $this->woNum) {
			throw new Exception("Invalid W/O Number: {$this->woNum}");
		} 
		
		$this->screen_title = ucfirst($this->mode) . ' W/O Electrofusion Information';

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
		unset($data["WEF_CREATE_USER"]);
		unset($data["WEF_CREATE_TIME"]);
		unset($data["WEF_CHANGE_USER"]);
		unset($data["WEF_CHANGE_TIME"]);		
		
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
		$this->fh->addCustomMetaDatum('WO_TAX_MUNICIPALITY', 'W/O Town', 'CHAR', '4');
  		$this->fh->addCustomMetaDatum('WO_DATE_COMPLETED', 'Date Completed', 'CHAR', 12);
		
		$flWO = 'WEF_WO_NUM, WEF_SEQNO, WO_TYPE, WO_STATUS, WO_TAX_MUNICIPALITY, WO_DATE_COMPLETED';
		$this->fh->addFieldGroup( $flWO, 'wo', 'Work Order Details');
		$this->fh->setElementsProperties( $flWO, 'output_only', true);

		
		$flElectrofusion = 
			'WEF_FUSER_NUM, WEF_FUSER_NUM2, WEF_PROCESSOR_SERIAL_NUM, WEF_FUSION_NUM, WEF_FUSION_DATE, WEF_FUSION_TYPE, ' 
			. 'WEF_JUNCTION_TYPE, WEF_LOT_NO, WEF_PRODUCTION_DATE';
		$this->fh->addFieldGroup( $flElectrofusion, 'electrofusion', 'Electrofusion Information');
		$this->fh->setElementsProperties($flElectrofusion, 'required', true);
		$this->fh->setElementsProperties('WEF_FUSER_NUM2', 'required', false);
		$this->fh->setElementsProperties('WEF_FUSION_TYPE, WEF_JUNCTION_TYPE', 'input_type', 'select');
		
		
		$flDescription = 'WEF_DESCRIPTION, WEF_COMPLETED_BY, WEF_NOTES';
		$this->fh->addFieldGroup( $flDescription, 'description', 'Description');
		$this->fh->setElementsProperties('WEF_DESCRIPTION, WEF_COMPLETED_BY', 'required', true);
		$this->fh->setElementsProperties('WEF_COMPLETED_BY', 'lookup', 'javascript:lookupCrew();');
		$this->fh->setElementsProperties('WEF_NOTES', 'input_type', 'textarea');
		

		$flAudit = 'WEF_CREATE_USER, WEF_CREATE_TIME, WEF_CHANGE_USER, WEF_CHANGE_TIME';
		$this->fh->addFieldGroup( $flAudit, 'maintenance', 'Electrofusion Record Maintenance Info');
		$this->fh->setElementsProperties($flAudit, 'output_only', true);
		
		
		// This creates Zend_Form_Elements out of the meta data
		$this->fh->addElementsFromMetaData($this->mode);
		
		$this->setName ( 'form1' );
		$this->addElements ( $this->fh->getElements() );

		if (!$this->isInquiryMode()) {
			if ($this->getElement('WEF_COMPLETED_BY') != NULL) {
				$this->getElement('WEF_COMPLETED_BY')->addValidator(new Crew_Validator());
			}
		}
		
		$this->populateWOFields();
		
		$dd = new Code_Values_Master($this->conn);
		
		$ddList = $dd->getCodeValuesList('FUSION_TYPE', ' ');
		$this->fh->setMultiOptions('WEF_FUSION_TYPE', $ddList);
		
		$ddList = $dd->getCodeValuesList('WEF_JUNCTION_TYPE', ' ');
		$this->fh->setMultiOptions('WEF_JUNCTION_TYPE', $ddList);

		$this->getElement('WEF_CREATE_TIME')->setAttrib('size', 30);
		$this->getElement('WEF_CHANGE_TIME')->setAttrib('size', 30);
	}

	//---------------------------------------------------
	public function reset() {
		parent::reset();
		$this->getElement('WEF_WO_NUM')->setValue($this->woNum);
		$this->getElement('WEF_SEQNO')->setValue($this->wefSeqNo);
		
		// Default city from W/O record on create
		if ($this->isCreateMode()) { 
			// Allow copy address from W/O in create mode. 
			$copyButton = '<button type="button" onclick="copyAddress(); return false;">' . 
				'Copy from W/O</button>';
			$this->getElement('WEF_DESCRIPTION')->setDescription($copyButton);
		}

		$this->populateWOFields();
	}

	//---------------------------------------------------
	/**
	 * The populate() function is used to load the screen initially from an existing record, and to load
	 * values when the screen is redisplayed on an error condition - This function should retrieve
	 * any ancillary values needed to display the screen completely (i.e., descriptions for coded values)<br>
	 * @see Zend_Form::populate()
	 */
	public function populate(array $data)
	{
		parent::populate($data);
	
		$crew = new Crew($this->conn);
		$crewId = $this->getElement('WEF_COMPLETED_BY')->getValue();
		$this->getElement('WEF_COMPLETED_BY')->setDescription($crew->getCrewName($crewId));


		if ($this->isInquiryMode()) {
			// Change formatting for Production Date from CCYYMM to MM-CCYY
			$prodDate = $this->getElement('WEF_PRODUCTION_DATE')->getValue();
			$prodDateYY = substr($prodDate, 0, 4);
			$prodDateMM = substr($prodDate, 4, 2);
			$this->getElement('WEF_PRODUCTION_DATE')->setValue("$prodDateMM-$prodDateYY");
		}
		
	}
	
	//---------------------------------------------------
	private function populateWOFields() {
		$dd = new Code_Values_Master($conn);
		
		$this->getElement('WEF_WO_NUM')->setDescription($this->woRec['WO_DESCRIPTION']);
		
		$this->getElement('WO_TYPE')->setValue($this->woRec['WO_TYPE']);
		$woTypeDesc = $dd->getCodeValue('WO_TYPE', $this->woRec['WO_TYPE']);
		$this->getElement('WO_TYPE')->setDescription($woTypeDesc);
		
		$this->getElement('WO_TAX_MUNICIPALITY')->setValue($this->woRec['WO_TAX_MUNICIPALITY']);
		$woTownDesc = $dd->getCodeValue('TOWN', $this->woRec['WO_TAX_MUNICIPALITY']);
		$this->getElement('WO_TAX_MUNICIPALITY')->setDescription($woTownDesc);
		
		$this->getElement('WO_STATUS')->setValue($this->woRec['WO_STATUS']);
		$woStsDesc = $dd->getCodeValue('WO_STATUS', $this->woRec['WO_STATUS']);
		$this->getElement('WO_STATUS')->setDescription($woStsDesc);
		
		$dateCompleted = $this->fixDateOutput($this->woRec['WO_DATE_COMPLETED'], true);
		$this->getElement('WO_DATE_COMPLETED')->setValue($dateCompleted);
		
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
		
		return $this->valid ;
	}
	
	//---------------------------------------------------
	public function returnToCaller() {
 		// Redirect to the work order electrofusions search screen after form processing
		$woNum = $this->inputs['WEF_WO_NUM'];
		header("Location: wefListCtrl.php?filter_WEF_WO_NUM=$woNum&popup=1&filtSts=restore" );
		exit;
	}
	
}

