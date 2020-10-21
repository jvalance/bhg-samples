<?php
require_once '../forms/VGS_Form.php';
require_once '../model/Project_MCF_Rates.php';
require_once '../model/Project_Master.php';

class ProjectMCFRatesForm extends VGS_Form 
{
	private $db_object;
	
	public function __construct( $conn ) {
		parent::__construct ( $conn );

		$sec = new Security();
		$sec->checkPermissionByCategory('PROJ', $this->mode);
		
		$this->fh->addMetaData($this->conn, "PROJMCF");
		$this->setDefaultElements( );
		$this->screen_title = 'Project MCF Rates ' . ucfirst($this->mode);
		$this->db_object = new Project_MCF_Rates($conn);
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
	public function deleteRecord() {
		return $this->db_object->delete($this->inputs);
	}
	
	public function setDefaultElements( ) {
		// Key fields
		$flKeys = "PM_PRJ_NUM, PM_EST_YEAR";
		$this->fh->addFieldGroup( $flKeys, 'keys', 'Key Fields');
		$this->fh->setElementsProperties('PM_PRJ_NUM', 'output_only', true);
		$this->fh->setElementsProperties('PM_EST_YEAR', 'output_only', true);
		
		// Estimated MCF Information
		$flEstimates = "PM_RATE_CLASS, PM_MCF";
		$this->fh->addFieldGroup( $flEstimates, 'estimates', 'MCF Estimates by Rate Class');
		$this->fh->setElementsProperties( $flEstimates, 'required', 'true');
			$this->fh->setElementsProperties( "PM_RATE_CLASS", 'input_type', 'select');
		
		if ( ! $this->isCreateMode() ) {
			// Year is a required key field.
			$this->fh->setElementsProperties('PM_RATE_CLASS', 'output_only', true);
		} else {
			$this->fh->setElementsProperties( "PM_RATE_CLASS", 'required', true);
		}
		
		// This creates Zend_Form_Elements out of the meta data
		$this->fh->addElementsFromMetaData($this->mode);
		
		$this->setName ( 'form1' );
		$this->addElements ( $this->fh->getElements() );


		$cvm = new Code_Values_Master($this->conn);
		$cvList = $cvm->getCodeValuesList('RATECLASS', ' ');
		$this->fh->setMultiOptions('PM_RATE_CLASS', $cvList);
		
	}

	/**
	 * reset() function is used to set default values on Project create screen
	 * @see Zend_Form::reset()
	 */
	public function reset() 
	{
		parent::reset();
		if ($_SERVER['REQUEST_METHOD'] == 'GET') { 
			$this->getElement('PM_PRJ_NUM')->setValue($_GET['PM_PRJ_NUM']);
			$this->getElement('PM_EST_YEAR')->setValue($_GET['PM_EST_YEAR']);
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
	
		$projObj = new Project_Master($this->conn);
		$projElem = $this->getElement('PM_PRJ_NUM');
		$projDesc = $projObj->getProjectDescription($projElem->getValue());
		$projElem->setDescription($projDesc);
		
	}
	
	/**
	 * Custom validations for this form - this overrides the validate() method 
	 *    defined in VGS_Form.php, and calls the Zend_Form isValid() method. 
	 * @see VGS_Form::validate()
	 * @see Zend_Form::isValid()
	 */	
//	public function validate() 
//	{
//		$this->valid = parent::isValid($this->inputs);
//		
//		return $this->valid ;
//	}	
		
}
        
?>