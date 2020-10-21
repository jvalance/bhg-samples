<?php
require_once '../forms/VGS_Form.php';
require_once '../model/Project_Estimates.php';
require_once '../model/Project_Master.php';

class ProjectEstimatesForm extends VGS_Form 
{
	private $db_object;
	
	public function __construct( $conn ) {
		parent::__construct ( $conn );

		$sec = new Security();
		$sec->checkPermissionByCategory('PROJ', $this->mode);
		
		$this->fh->addMetaData($this->conn, "PROJEST");
		$this->setDefaultElements( );
		$this->screen_title = 'Project Yearly Estimates ' . ucfirst($this->mode);
		$this->db_object = new Project_Estimates($conn);
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
		$flKeys = "PE_PRJ_NUM, PE_EST_YEAR";
		$this->fh->addFieldGroup( $flKeys, 'keys', 'Key Fields');
		$this->fh->setElementsProperties('PE_PRJ_NUM', 'output_only', true);
		
		if ( ! $this->isCreateMode() ) {
			// Year is a required key field.
			$this->fh->setElementsProperties('PE_EST_YEAR', 'output_only', true);
		} else {
			$this->fh->setElementsProperties( "PE_EST_YEAR", 'required', 'true');
		}
		
		// Project Estimate Information
		$flEstimates = "PE_EST_MAIN_COST, PE_EST_SVC_COST, PE_EST_METER_COST, PE_EST_NUM_CUSTS,
							PE_EST_NUM_SVCS, PE_YEAR_BLD";
		$this->fh->addFieldGroup( $flEstimates, 'estimates', 'Project Year Estimates');
		
		// This creates Zend_Form_Elements out of the meta data
		$this->fh->addElementsFromMetaData($this->mode);
		
		$this->setName ( 'form1' );
		$this->addElements ( $this->fh->getElements() );
	}

	/**
	 * reset() function is used to set default values on Project create screen
	 * @see Zend_Form::reset()
	 */
	public function reset() 
	{
		parent::reset();
		if ($_SERVER['REQUEST_METHOD'] == 'GET') { 
			$this->getElement('PE_PRJ_NUM')->setValue($_GET['PE_PRJ_NUM']);
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
		$projElem = $this->getElement('PE_PRJ_NUM');
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
//		$this->valid = parent::validate();
//		
//		return $this->valid ;
//	}	
		
}
        
?>