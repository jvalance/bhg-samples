<?php
require_once '../forms/VGS_Form.php';
require_once '../model/Plastic_Pipe_Fail.php';
require_once '../model/WorkOrder_Master.php';

class PlasticPipeFailForm extends VGS_Form 
{
	private $db_object;
	
	public function __construct( $conn ) {
		parent::__construct ( $conn );
		$this->fh->addMetaData($this->conn, "PLPIPEFAIL");
		$this->fh->addMetaData($this->conn, "WOMAST");
		$this->setDefaultElements( );
		$this->validate_WO_NUM($_REQUEST['PP_WONUM']);
		$this->screen_title = ucfirst($this->mode) . ' Plastic Pipe Failure';
		$this->db_object = new Plastic_Pipe_Fail($conn);
	}
	
	public function validate_WO_NUM($woNum) {
		$wo = new Workorder_Master($this->conn);
		if (!$wo->validate_WO_NUM($woNum)) {
			die("Invalid W/O number passed: $woNum.");
		}
	}
	
	public function createRecord() {
		return $this->db_object->create($this->inputs);
		exit;
	}
	public function updateRecord() {
		return $this->db_object->update($this->inputs);
	}
	public function retrieveRecord() {
		return $this->db_object->retrieve($this->inputs);
	}

	public function setDefaultElements( ) {
		$woFieldList = 'PP_WONUM, WO_DESCRIPTION, WO_TYPE';
		$this->fh->addFieldGroup( $woFieldList, 'wo', 'Work Order Information');
		$this->fh->setElementsProperties( $woFieldList, 'output_only', 'true');
		
		$maintFieldList = 'PP_CREATE_USER, PP_CREATE_TIME, PP_CHANGE_USER, PP_CHANGE_TIME';
		$this->fh->addFieldGroup( $maintFieldList, 'maintenance','Record Maintenance Information');
		$this->fh->setElementsProperties($maintFieldList, 'output_only', true);
	
		$materialFieldList = 'PP_MATERIAL,PP_MANUFACTURER,PP_MFR_PRINT_LINE ,PP_NOMINAL_SIZE,PP_MFG_DATE';
		$this->fh->addFieldGroup( $materialFieldList, 'material','Material Section');
		$this->fh->setElementsProperties( 'PP_MATERIAL', 'output_only', 'true');

		$installFieldList = 'PP_INSTALL_METHOD,PP_INSTALL_OTHER, PP_SOIL_TYPE, PP_SOIL_TYPE_OTHER,PP_PSIG_FAIL,PP_PSIG_NORMAL,PP_INSTALL_DATE ';
		$this->fh->addFieldGroup( $installFieldList, 'install','Installation and Operations Section');
		//$this->fh->setElementsProperties( $installFieldList, 'required', 'true');
		
		$failureFieldList = 'PP_LOCATION,PP_FITTING,PP_FITTING_OTHER ,PP_JOINT,PP_JOINT_OTHER,PP_CAUSE,PP_CAUSE_OTHER,PP_FAIL_DATE';
		$this->fh->addFieldGroup( $failureFieldList, 'failure', 'Failure Section');
		//$this->fh->setElementsProperties( $failureFieldList, 'required', 'true');
		
		$contactFieldList = 'PP_CONTACT_NAME, PP_CONTACT_EMAIL, PP_CONTACT_PHONE';
		$this->fh->addFieldGroup( $contactFieldList, 'contact', 'Contact Section');
		$this->fh->setElementsProperties( $contactFieldList, 'required', 'true');

		$this->fh->setElementsProperties( 'PP_MANUFACTURER,PP_MFR_PRINT_LINE,PP_NOMINAL_SIZE,
				PP_MFG_DATE,PP_INSTALL_METHOD, PP_LOCATION, PP_CAUSE', 'required', 'true');
		$this->fh->setElementsProperties( 'PP_SOIL_TYPE,PP_PSIG_FAIL,
				PP_PSIG_NORM,PP_INSTL_DATE,PP_FAIL_DATE', 'required', 'true');
		$this->fh->setElementsProperties( 'PP_INSTALL_METHOD, PP_SOIL_TYPE, PP_LOCATION, 
				PP_MANUFACTURER, PP_FITTING, PP_JOINT, PP_CAUSE', 'input_type', 'select');	
		
		if (trim($this->inputs['PP_INSTALL_METHOD']) == 'OTHER') {
			$this->fh->setElementsProperties( 'PP_INSTALL_OTHER', 'required', 'true');
		}
			
		if (trim($this->inputs['PP_SOIL_TYPE']) == 'OTH') {
			$this->fh->setElementsProperties( 'PP_SOIL_TYPE_OTHER', 'required', 'true');
		}
			
		if (trim($this->inputs['PP_LOCATION']) == 'FITTING') {
			$this->fh->setElementsProperties( 'PP_FITTING', 'required', 'true');
		}
		if (trim($this->inputs['PP_FITTING']) == 'OTHER') {
			$this->fh->setElementsProperties( 'PP_FITTING_OTHER', 'required', 'true');
		}
			
		if (trim($this->inputs['PP_LOCATION']) == 'JOINT') {
			$this->fh->setElementsProperties( 'PP_JOINT', 'required', 'true');
		}
		if (trim($this->inputs['PP_JOINT']) == 'OTHER') {
			$this->fh->setElementsProperties( 'PP_JOINT_OTHER', 'required', 'true');
		}

		if (trim($this->inputs['PP_CAUSE']) == 'OTHER') {
			$this->fh->setElementsProperties( 'PP_CAUSE_OTHER', 'required', 'true');
		}
		
		// This creates Zend_Form_Elements out of the meta data
		$this->fh->addElementsFromMetaData($this->mode);
		
		$this->setName ( 'form1' );
		$this->addElements ( $this->fh->getElements() );

		$cvm = new Code_Values_Master($this->conn);
		
		$cvList = $cvm->getCodeValuesList('PP_INSTALL', ' ');
		$this->fh->setMultiOptions('PP_INSTALL_METHOD', $cvList);
		
		$cvList = $cvm->getCodeValuesList('SOIL_CONDITION', ' ');
		$this->fh->setMultiOptions('PP_SOIL_TYPE', $cvList);
		
		$cvList = $cvm->getCodeValuesList('PP_LOC', ' ');
		$this->fh->setMultiOptions('PP_LOCATION', $cvList);
		
		$cvList = $cvm->getCodeValuesList('PP_MANUFACTURER', ' ');
		$this->fh->setMultiOptions('PP_MANUFACTURER', $cvList);
		
		$cvList = $cvm->getCodeValuesList('PP_CAUSE', ' ');
		$this->fh->setMultiOptions('PP_CAUSE', $cvList);
		
		$cvList = $cvm->getCodeValuesList('PP_FIT', ' ');
		$this->fh->setMultiOptions('PP_FITTING', $cvList);
		
		$cvList = $cvm->getCodeValuesList('PP_JOINT', ' ');
		$this->fh->setMultiOptions('PP_JOINT', $cvList);

		$this->getElement('PP_MFR_PRINT_LINE')->setAttrib('size', 40);
		$this->getElement('PP_NOMINAL_SIZE')->setAttrib('size', 40);
		$this->getElement('PP_INSTALL_OTHER')->setAttrib('size', 40);
		$this->getElement('PP_SOIL_TYPE_OTHER')->setAttrib('size', 40);
		$this->getElement('PP_FITTING_OTHER')->setAttrib('size', 40);
		$this->getElement('PP_JOINT_OTHER')->setAttrib('size', 40);
		$this->getElement('PP_CAUSE_OTHER')->setAttrib('size', 40);
		$this->getElement('PP_CONTACT_NAME')->setAttrib('size', 40);
		$this->getElement('PP_CONTACT_EMAIL')->setAttrib('size', 40);
		$this->getElement('PP_CREATE_TIME')->setAttrib('size', 30);
		$this->getElement('PP_CHANGE_TIME')->setAttrib('size', 30);
	}
	
	public function reset() {
		parent::reset();

		$cvm = new Code_Values_Master($this->conn);
		
		if ($this->isCreateMode()) {
			$woNum = $_REQUEST['PP_WONUM'];
			// restore W/O Num because form activation will blank out key fields
			$this->getElement('PP_WONUM')->setValue($woNum);
			$wo = new Workorder_Master($this->conn);
			$woRec = $wo->getWorkorder($woNum);
//			pre_dump($woRec);
			$this->getElement('WO_DESCRIPTION')->setValue($woRec['WO_DESCRIPTION']);
			$this->getElement('WO_TYPE')->setValue($woRec['WO_TYPE']);
			$this->getElement('PP_MATERIAL')->setValue('HDPE-3408');
			
			$contactName = $cvm->getCodeValue('PP_CONTACT', 'NAME');
			$this->getElement('PP_CONTACT_NAME')->setValue($contactName);
			$contactEmail = $cvm->getCodeValue('PP_CONTACT', 'EMAIL');
			$this->getElement('PP_CONTACT_EMAIL')->setValue($contactEmail);
			$contactPhone = $cvm->getCodeValue('PP_CONTACT', 'PHONE');
			$this->getElement('PP_CONTACT_PHONE')->setValue($contactPhone);
							
		}
		return $this;
	}
}


?>