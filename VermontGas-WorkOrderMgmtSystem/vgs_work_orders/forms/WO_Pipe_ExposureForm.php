<?php
require_once '../forms/VGS_Form.php';
require_once '../model/WO_Pipe_Exposure.php';
require_once '../model/Pipe_Type_Master.php';
require_once '../model/Workorder_Master.php';
// require_once '../common/validators/Crew_Validator.php';
require_once '../common/validators/DepthFeet_Validator.php';
require_once '../common/validators/DepthInches_Validator.php';

/** 
 * @author John
 */
class WO_Pipe_ExposureForm extends VGS_Form 
{
	private $db_object;
	public $wpe_record = array();
	public $wo_record = array();
	public $woObj;
	
	public function __construct( $conn ) {
		parent::__construct ( $conn );

		$sec = new Security();
		$sec->checkPermissionByCategory('WO', $this->mode);

		$this->woObj = new Workorder_Master($conn);
		$this->wo_record = $this->woObj->getWorkorder($this->inputs['WPE_WO_NUM']);
		
		$this->fh->addMetaData($this->conn, "WOPIPEEXP");
		$this->fh->addMetaData($this->conn, "WOMAST");
		$this->setDefaultElements( );
		$this->screen_title = 'W/O Pipe Exposure ' . ucfirst($this->mode);
		
		$this->db_object = new WO_Pipe_Exposure($conn);
// 		$this->wpe_record = $this->db_object->retrieve($this->inputs);
	}
	
	public function createRecord() {
		return $this->db_object->create($this->inputs);
	}
	public function updateRecord() {
		return $this->db_object->update($this->inputs);
	}
	public function retrieveRecord() {
		return $this->db_object->retrieve($this->inputs);
	}
	
	public function setDefaultElements( ) 
	{
		// Show "Primary Exposure" field, only for Maintenance and Leak WOs
		$woType = $this->wo_record['WO_TYPE'];
		if ($this->woObj->isMaintenance_WO_TYPE($woType)
		|| $this->woObj->isLeak_WO_TYPE($woType)) {
			$primaryField = 'WPE_PRIMARY_WOEXP, ';
		} else {
			$primaryField = '';
		}
		$generalFieldList = "WPE_WO_NUM, $primaryField WPE_EXPOSURE_DATE, WO_TYPE, WO_DESCRIPTION, " . 
							"WO_TAX_MUNICIPALITY, WO_PIPE_TYPE, WO_PRESSURE";
		$this->fh->addFieldGroup( $generalFieldList, 'general', 'General Information');
		$this->fh->setElementsProperties($generalFieldList, 'output_only', true);
		$this->fh->setElementsProperties( 'WPE_EXPOSURE_DATE', 'output_only', false);
		$this->fh->setElementsProperties( 'WPE_EXPOSURE_DATE', 'required', true);
		$this->fh->setElementsProperties( 'WPE_PRIMARY_WOEXP', 'output_only', false);
		$this->fh->setElementsProperties( 'WPE_PRIMARY_WOEXP', 'input_type', 'select');
		$this->fh->setElementsProperties( 'WPE_PRIMARY_WOEXP', 'required', true);
		
		if ( ! $this->isCreateMode()) {
			// Key field output-only unless create mode
			$this->fh->setElementsProperties('WPE_EXPOSURE_DATE', 'output_only', true);
		}

		$this->fh->addFieldGroup( 'WPE_COMMENTS', 'comments', 'Comments');
		$this->fh->setElementsProperties( 'WPE_COMMENTS', 'input_type', 'textarea');
		
		$maintFieldList = 'WPE_CREATE_USER, WPE_CREATE_TIME, WPE_CHANGE_USER, WPE_CHANGE_TIME';
		$this->fh->addFieldGroup( $maintFieldList, 'maintenance', 'Record Maintenance Information');
		$this->fh->setElementsProperties($maintFieldList, 'output_only', true);
		
		$exposureFieldList = 'WPE_REASON, WPE_DESIGNATION, WPE_DEPTH_FEET, WPE_DEPTH_INCHES, ' .
			'WPE_PIPE_COMPOSITION, WPE_PIPE_SIZE, WPE_PIPE_COATING, ' .
			'WPE_COATING_CONDITION, WPE_PIPE_CONDITION, WPE_INTERNAL_CONDITION,' .
			'WPE_CP20_READING';
		$this->fh->addFieldGroup( $exposureFieldList, 'exposure', 'Exposure Data');
		$this->fh->setElementsProperties( $exposureFieldList, 'required', true);
		$this->fh->setElementsProperties( 'WPE_DEPTH_FEET, WPE_DEPTH_INCHES, WPE_CP20_READING', 'required', false);
		
		$this->fh->setElementsProperties( 'WPE_REASON, WPE_DESIGNATION, WPE_PRESSURE, WPE_PIPE_COATING', 'input_type', 'select');
		$this->fh->setElementsProperties( 'WPE_COATING_CONDITION, WPE_PIPE_CONDITION, WPE_INTERNAL_CONDITION,' .
			'WPE_PIPE_COMPOSITION, WPE_PIPE_SIZE', 'input_type', 'select');
		
		// This creates Zend_Form_Elements out of the meta data
		$this->fh->addElementsFromMetaData($this->mode);
		
		$cvm = new Code_Values_Master($this->conn);
		$cvList = $cvm->getCodeValuesList('YES_NO', ' ');
		$this->fh->setMultiOptions('WPE_PRIMARY_WOEXP', $cvList);
		
		$cvList = $cvm->getCodeValuesList('WPE_REASON', ' ');
		$this->fh->setMultiOptions('WPE_REASON', $cvList);
		
		$cvList = $cvm->getCodeValuesList('WPE_DESIGNATION', ' ');
		$this->fh->setMultiOptions('WPE_DESIGNATION', $cvList);
		
		$cvList = $cvm->getCodeValuesList('PIPE_COATING', ' ');
		$this->fh->setMultiOptions('WPE_PIPE_COATING', $cvList);
		
		$cvList = $cvm->getCodeValuesList('WPE_COATCOND', ' ');
		$this->fh->setMultiOptions('WPE_COATING_CONDITION', $cvList);
				
		$cvList = $cvm->getCodeValuesList('PIPE_CONDITION', ' ');
		$this->fh->setMultiOptions('WPE_PIPE_CONDITION', $cvList);
				
		$cvList = $cvm->getCodeValuesList('WPE_INTCOND', ' ');
		$this->fh->setMultiOptions('WPE_INTERNAL_CONDITION', $cvList);
				
		$cvList = $cvm->getCodeValuesList('PIPE_MTRL', ' ');
		$this->fh->setMultiOptions('WPE_PIPE_COMPOSITION', $cvList);
				
		$cvList = $cvm->getCodeValuesList('PIPE_DIAM', ' ');
		$this->fh->setMultiOptions('WPE_PIPE_SIZE', $cvList);
		
		$this->setName ( 'form1' );
		$this->addElements ( $this->fh->getElements() );
		
		// Add validators
		if (!$this->isInquiryMode()) {
// 			$this->getElement('WPE_INSPECTOR_CLOCK')->addValidator(new Crew_Validator());
// 			$this->fh->setElementsProperties('WPE_INSPECTOR_CLOCK', 'lookup', 'javascript:lookupCrew();');
		}

		if ($this->getElement('WPE_DEPTH_FEET') != NULL) {
			$this->getElement('WPE_DEPTH_FEET')->addValidator(new DepthFeet_Validator());
		}
		if ($this->getElement('WPE_DEPTH_INCHES') != NULL) {
			$this->getElement('WPE_DEPTH_INCHES')->addValidator(new DepthInches_Validator());
		}
		$this->getElement('WPE_DEPTH_FEET')->setLabel('Pipe Depth')->setDescription('Feet (0-15)');
		$this->getElement('WPE_DEPTH_INCHES')->setLabel('')->setDescription('Inches (0-11)');
		$this->getElement('WPE_CP20_READING')->setDescription('Volts');
		
		$this->getElement('WO_DESCRIPTION')->setAttrib('size', 50);
		$this->getElement('WPE_CREATE_TIME')->setAttrib('size', 30);
		$this->getElement('WPE_CHANGE_TIME')->setAttrib('size', 30);
	}
	
	public function reset() {
		parent::reset();

		if (isset($_GET['WPE_WO_NUM'])) {
			// In create mode, populate form with WO_NUM passed on request
			$this->getElement('WPE_WO_NUM')->setValue($_GET['WPE_WO_NUM']);
		} else {
			die("Workorder Number required to create new Pipe Exposure record.");
		}

		$woNum = $this->getElement('WPE_WO_NUM')->getValue();
		$woObj = new Workorder_Master($this->conn);
		$woRec = $woObj->getWorkorder($woNum);
		
		if (is_array($woRec)) {
			$this->getElement('WO_TAX_MUNICIPALITY')->setValue($woRec['WO_TAX_MUNICIPALITY']);
			$this->getElement('WO_PRESSURE')->setValue($woRec['WO_PRESSURE']);
			$this->getElement('WO_PIPE_TYPE')->setValue($woRec['WO_PIPE_TYPE']); 
			$this->getElement('WO_TYPE')->setValue($woRec['WO_TYPE']); 
			$this->getElement('WO_DESCRIPTION')->setValue($woRec['WO_DESCRIPTION']);
		}
		
		$this->getCodeDescriptions();
		
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
		// NOTE: (Jan 18 2012 - JGV)
		// For some reason this date conversion needs to be done only on this field, otherwise 
		// the value for exposure date gets lost when the screen is redisplayed for errors. 
		// I think it may be related to the fact that exposure date is one of the key 
		// fields for the exposure record, but not sure why.
		$data['WPE_EXPOSURE_DATE'] = 
			VGS_Form::convertDateFormat($data['WPE_EXPOSURE_DATE'], 'm-d-Y', 'Y-m-d');  

		parent::populate($data);
		
		$this->getCodeDescriptions();
	}
	
		
	private function getCodeDescriptions() {
		
		$cvm = new Code_Values_Master($this->conn);
		
		$town = $this->getElement('WO_TAX_MUNICIPALITY')->getValue();
		$town_desc = $cvm->getCodeValue('TOWN', $town);
		$this->getElement('WO_TAX_MUNICIPALITY')->setDescription($town_desc);
		
		$type = $this->getElement('WO_TYPE')->getValue();
		$type_desc = $cvm->getCodeValue('WO_TYPE', $type);
		$this->getElement('WO_TYPE')->setDescription($type_desc);
		
		$pressure = $this->getElement('WO_PRESSURE')->getValue();
		$pressure_desc = $cvm->getCodeValue('PRESSURE', $pressure);
		$this->getElement('WO_PRESSURE')->setDescription($pressure_desc);
		
		$ptObj = new Pipe_Type_Master($this->conn);
		$pipeTypeElem = $this->getElement('WO_PIPE_TYPE'); 
		if (isset($pipeTypeElem)) {
			$ptCode = $pipeTypeElem->getValue();
			if (trim($ptCode) != '' && ctype_digit( $ptCode ) ) {
				$ptDesc = $ptObj->getPipeTypeDescription($ptCode);
				$pipeTypeElem->setDescription($ptDesc);
			}
		} 			
		
// 		$crew = new Crew($this->conn);
// 		$crewId = $this->getElement('WPE_INSPECTOR_CLOCK')->getValue();
// 		$this->getElement('WPE_INSPECTOR_CLOCK')->setDescription($crew->getCrewName($crewId));
		
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
				
		return $this->valid ;
	}	
		
}

?>