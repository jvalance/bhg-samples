<?php
require_once '../forms/VGS_Form.php';
require_once '../model/Mechanical_Fitting_Failure.php';
/** 
 * @author Carol
 */
	                    	                    
class MechanicalFittingFailureForm extends VGS_Form 
{
	private $db_object;
	
	public function __construct( $conn ) {
		parent::__construct ( $conn );
		$this->fh->addMetaData($this->conn, "MECFITFAIL");
		$this->fh->addMetaData($this->conn, "WOMAST");
		$this->setDefaultElements( );
		$this->validate_WO_NUM($_REQUEST['MF_WONUM']);
		$this->screen_title = ucfirst($this->mode) . ' Mechanical Fitting Failure';
		$this->db_object = new Mechanical_Fitting_Failure($conn);
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
		$woFL = 'MF_WONUM, WO_DESCRIPTION, WO_TYPE';
		$this->fh->addFieldGroup( $woFL, 'wo', 'Work Order Information');
		$this->fh->setElementsProperties( $woFL, 'output_only', 'true');

		$generlFL = "MF_YEAR, MF_INITIAL_REPORT, MF_SUPPLEMENTAL_REPORT, MF_OPERATOR_ID, 
			MF_SUBMIT_DATE, MF_STATE_FAIL, MF_DATE_FAILED"; 
		$this->fh->addFieldGroup( $generlFL, 'general','General Information');
		//$this->fh->setElementsProperties($generlFL, 'output_only', true);
		
		$fittingFL = "MF_MECHANICAL_FITTING, MF_MECHANICAL_FITTING_OTHER, MF_MECHANICAL_TYPE, MF_MECHANICAL_TYPE_OTHER, 
			MF_LEAK_LOC_GROUND, MF_LEAK_LOC_SIDE, MF_LEAK_LOC_CONN";
		$this->fh->addFieldGroup( $fittingFL, 'fitting','Fitting Information');
		
		$manuFL = "MF_YEAR_INSTALL, MF_YEAR_MANUFACTURED, MF_DECADE_INSTALLED, MF_MANUFACTURER, MF_PART_NUMBER, 
	 		MF_LOT_NUMBER, MF_OTHER_ATTRIBUTES"; 
		$this->fh->addFieldGroup( $manuFL, 'mfr','Manufacture/Installation');

		$materialFL = 
			"MF_FITTING_MATERIAL, MF_FITTING_MATERIAL_OTHER, MF_1ST_PIPE_SIZE, MF_1ST_PIPE_UNIT, 
			MF_1ST_PIPE_MATERIAL, MF_1ST_PIPE_MATERIAL_OTHER, MF_1ST_PIPE_MATERIAL_PLASTIC,
			MF_1ST_PIPE_MATERIAL_PLASTIC_OTHER, MF_2ND_PIPE_SIZE FOR COLUMN MF2PSIZE, 
			MF_2ND_PIPE_UNIT, MF_2ND_PIPE_MATERIAL, MF_2ND_PIPE_MATERIAL_OTHER, MF_2ND_PIPE_MATERIAL_PLASTIC, 
			MF_2ND_PIPE_MATERIAL_PLASTIC_OTHER";
		$this->fh->addFieldGroup( $materialFL, 'material','Materials Section');
		
		$causeFL = "MF_CAUSE_OF_LEAK, MF_CAUSE_THERMAL_EXPANSION, MF_CAUSE_DAMAGE_TIME, 
			MF_CAUSE_LEAK_DUE_TO, MF_CAUSE_OTHER, MF_HOW_OCCUR"; 
		$this->fh->addFieldGroup( $causeFL, 'cause','Casue of Leak Section');

		$maintFL = 'MF_CREATE_USER, MF_CREATE_TIME, MF_CHANGE_USER, MF_CHANGE_TIME';
		$this->fh->addFieldGroup( $maintFL, 'maintenance','Record Maintenance Information');
		$this->fh->setElementsProperties($maintFL, 'output_only', true);
		
// 		$this->fh->setElementsProperties('MF_PART_NUMBER,MF_LOT_NUMBER,MF_OTHER_ATTRIBUTES,MF_1ST_PIPE_SIZE,MF_2ND_PIPE_SIZE, MF_1ST_PIPE_MATERIAL, MF_2ND_PIPE_MATERIAL' , 'required', 'true');
// 		$this->fh->setElementsProperties('MF_CAUSE_OF_LEAK,MF_THERMAL_EXPANSION,MF_AT_TIME_PREVIOUS_TIME, MF_LEAK_DUE_TO,MF_HOW_OCCUR' , 'required', 'true');
// 		$this->fh->setElementsProperties('MF_STATE,MF_DATE_FAILED,MF_MECHANICAL_FITTING,MF_MECHANICAL_TYPE, MF_LEAK_LOCATION,MF_YEAR_INSTALL,MF_YEAR_MANUFACTURED,MF_DECADE_INSTALLED,MF_MANUFACTURER' , 'required', 'true');
// 		$this->fh->setElementsProperties('MF_MECHANICAL_FITTING,MF_1ST_PIPE_SIZE,MF_2ND_PIPE_SIZE,MF_1ST_PIPE_MATERIAL,MF_2ND_PIPE_MATERIAL' , 'input_type', 'select');	
// 		$this->fh->setElementsProperties('MF_CAUSE_OF_LEAK, MF_THERMAL_EXPANSION, MF_AT_TIME_PREVIOUS_TIME, MF_LEAK_DUE_TO, MF_HOW_OCCUR, MF_MECHANICAL_TYPE, MF_LEAK_LOCATION, MF_MANUFACTURER', 'input_type', 'select');			
		
		$this->fh->addElementsFromMetaData($this->mode);
		
		$this->setName ( 'form1' );
		$this->addElements ( $this->fh->getElements() );
		
		$cvm = new Code_Values_Master($this->conn);
// 		$cvList = $cvm->getCodeValuesList('MF_MECHANICAL_FITTING', '-- Unknown --');
// 		$this->fh->setMultiOptions('MF_MECHANICAL_FITTING', $cvList);
// 		$cvList = $cvm->getCodeValuesList('MF_1ST_PIPE_SIZE', '-- Unknown --');
// 		$this->fh->setMultiOptions('MF_1ST_PIPE_SIZE', $cvList);
// 		$cvList = $cvm->getCodeValuesList('MF_2ND_PIPE_SIZE', '-- Unknown --');
// 		$this->fh->setMultiOptions('MF_2ND_PIPE_SIZE', $cvList);
// 		$cvList = $cvm->getCodeValuesList('MF_1ST_PIPE_MATERIAL', '-- Unknown --');
// 		$this->fh->setMultiOptions('MF_1ST_PIPE_MATERIAL', $cvList);
// 		$cvList = $cvm->getCodeValuesList('MF_2ND_PIPE_MATERIAL', '-- Unknown --');
// 		$this->fh->setMultiOptions('MF_2ND_PIPE_MATERIAL', $cvList);
// 		$cvList = $cvm->getCodeValuesList('MF_CAUSE_OF_LEAK', '-- Unknown --');
// 		$this->fh->setMultiOptions('MF_CAUSE_OF_LEAK', $cvList);
// 		$cvList = $cvm->getCodeValuesList('MF_THERMAL_EXPANSION', '-- Unknown --');
// 		$this->fh->setMultiOptions('MF_THERMAL_EXPANSION', $cvList);
// 		$cvList = $cvm->getCodeValuesList('MF_AT_TIME_PREVIOUS_TIME', '-- Unknown --');
// 		$this->fh->setMultiOptions('MF_AT_TIME_PREVIOUS_TIME', $cvList);
// 		$cvList = $cvm->getCodeValuesList('MF_LEAK_DUE_TO', '-- Unknown --');
// 		$this->fh->setMultiOptions('MF_LEAK_DUE_TO', $cvList);
// 		$cvList = $cvm->getCodeValuesList('MF_HOW_OCCUR', '-- Unknown --');
// 		$this->fh->setMultiOptions('MF_HOW_OCCUR', $cvList);
// 		$cvList = $cvm->getCodeValuesList('MF_MECHANICAL_TYPE', '-- Unknown --');
// 		$this->fh->setMultiOptions('MF_MECHANICAL_TYPE', $cvList);
// 		$cvList = $cvm->getCodeValuesList('MF_LEAK_LOCATION', '-- Unknown --');
// 		$this->fh->setMultiOptions('MF_LEAK_LOCATION', $cvList);
// 		$cvList = $cvm->getCodeValuesList('MF_MANUFACTURER', '-- Unknown --');
// 		$this->fh->setMultiOptions('MF_MANUFACTURER', $cvList);
// 		$this->getElement('MF_MECHANICAL_FITTING_DESCRIPTION')->setAttrib('size', 40);
// 		$this->getElement('MF_1ST_PIPE_SIZE_DESCRIPTION')->setAttrib('size', 40);
// 		$this->getElement('MF_2ND_PIPE_SIZE_DESCRIPTION')->setAttrib('size', 40);
// 		$this->getElement('MF_1ST_PIPE_MATERIAL_DESCRIPTION')->setAttrib('size', 40);
// 		$this->getElement('MF_2ND_PIPE_MATERIAL_DESCRIPTION')->setAttrib('size', 40);
// 		$this->getElement('MF_CAUSE_OF_LEAK_DESCRIPTTION')->setAttrib('size', 40);
// 		$this->getElement('MF_LEAK_DUE_TO_DESCRIPTION')->setAttrib('size', 40);
// 		$this->getElement('MF_MECHANICAL_FITTING_DESCRIPTION')->setAttrib('size', 40);
// 		$this->getElement('MF_MECHANICAL_TYPE_DESCRIPTION')->setAttrib('size', 40);
		
// 		$this->getElement('MF_CREATE_TIME')->setAttrib('size', 30);
// 		$this->getElement('MF_CHANGE_TIME')->setAttrib('size', 30);
	}
	
	public function reset() {
		parent::reset();

		if ($this->isCreateMode()) {
			$woNum = $_REQUEST['MF_WONUM'];
			// restore W/O Num because form activation will blank out key fields
			$this->getElement('MF_WONUM')->setValue($woNum);
			$wo = new Workorder_Master($this->conn);
			$woRec = $wo->getWorkorder($woNum);
//			pre_dump($woRec);
			$this->getElement('WO_DESCRIPTION')->setValue($woRec['WO_DESCRIPTION']);
			$this->getElement('WO_TYPE')->setValue($woRec['WO_TYPE']);
		}
		return $this;
	}
}


?>