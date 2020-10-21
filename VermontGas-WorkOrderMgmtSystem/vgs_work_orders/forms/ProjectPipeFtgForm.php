<?php
require_once '../forms/VGS_Form.php';
require_once '../model/Project_Pipe_Ftg.php';
require_once '../model/Project_Master.php';
require_once '../common/validators/PipeType_Validator.php';

class ProjectPipeFtgForm extends VGS_Form 
{
	private $db_object;
	
	public function __construct( $conn ) {
		parent::__construct ( $conn );

		$sec = new Security();
		$sec->checkPermissionByCategory('PROJ', $this->mode);
		
		$this->fh->addMetaData($this->conn, "PJPIPEFTG");
		$this->setDefaultElements( );
		$this->screen_title = 'Project Est. Pipe Footage ' . ucfirst($this->mode);
		$this->db_object = new Project_Pipe_Ftg($conn);
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
		$flKeys = "PF_PRJ_NUM, PF_EST_YEAR";
		$this->fh->addFieldGroup( $flKeys, 'keys', 'Key Fields');
		$this->fh->setElementsProperties('PF_PRJ_NUM', 'output_only', true);
		$this->fh->setElementsProperties('PF_EST_YEAR', 'output_only', true);
		
		// Estimated MCF Information
		$flEstimates = "PF_PIPE_TYPE, PF_EST_FOOTAGE";
		$this->fh->addFieldGroup( $flEstimates, 'estimates', 'Footage Estimate for Pipe Type');
		$this->fh->setElementsProperties( 'PF_EST_FOOTAGE', 'required', 'true');
		
		if ( ! $this->isCreateMode() ) {
			// Year is a required key field.
			$this->fh->setElementsProperties('PF_PIPE_TYPE', 'output_only', true);
		} else {
			$this->fh->setElementsProperties("PF_PIPE_TYPE", 'required', true);
			$this->fh->setElementsProperties('PF_PIPE_TYPE', 'lookup', 'javascript:lookupPipeType();');
		}
		
		// This creates Zend_Form_Elements out of the meta data
		$this->fh->addElementsFromMetaData($this->mode);
		
		$this->setName ( 'form1' );
		$this->addElements ( $this->fh->getElements() );

		$this->getElement('PF_PIPE_TYPE')->addValidator(new PipeType_Validator());
		
	}

	/**
	 * reset() function is used to set default values on Project create screen
	 * @see Zend_Form::reset()
	 */
	public function reset() 
	{
		parent::reset();
		if ($_SERVER['REQUEST_METHOD'] == 'GET') { 
			$this->getElement('PF_PRJ_NUM')->setValue($_GET['PF_PRJ_NUM']);
			$this->getElement('PF_EST_YEAR')->setValue($_GET['PF_EST_YEAR']);
			$this->getElement('PF_PIPE_TYPE')->setValue($_GET['PF_PIPE_TYPE']);
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
		$projElem = $this->getElement('PF_PRJ_NUM');
		$projDesc = $projObj->getProjectDescription($projElem->getValue());
		$projElem->setDescription($projDesc);
	
		$pipeObj = new Pipe_Type_Master($this->conn);
		$pipeElem = $this->getElement('PF_PIPE_TYPE');
		$pipeDesc = $pipeObj->getPipeTypeDescription($pipeElem->getValue());
		$pipeElem->setDescription($pipeDesc);
		
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