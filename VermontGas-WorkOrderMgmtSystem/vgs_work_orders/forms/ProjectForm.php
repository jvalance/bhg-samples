<?php
require_once '../forms/VGS_Form.php';
require_once '../model/Project_Master.php';

class ProjectForm extends VGS_Form 
{
	private $db_object;
	
	public function __construct( $conn ) {
		parent::__construct ( $conn );

		$sec = new Security();
		$sec->checkPermissionByCategory('PROJ', $this->mode);
		
		$this->fh->addMetaData($this->conn, "PROJECTS");
		$this->setDefaultElements( );
		$this->screen_title = 'Project ' . ucfirst($this->mode);
		$this->db_object = new Project_Master($conn);
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

	public function setDefaultElements( ) {
		// Descriptive fields
		$flDescription = "PRJ_FEASABILITY_NUM, PRJ_FEASABILITY_DATE, PRJ_NUM, PRJ_DESCRIPTION, 
								PRJ_SALES_REP, PRJ_DEVELOPER, PRJ_CONTACT_PERSON, PRJ_DEV_PHONE";
		$this->fh->addFieldGroup( $flDescription, 'desc', 'Project Description');
		// Proj# output-only - it will be generated automatically as highest current number + 1
		$this->fh->setElementsProperties('PRJ_NUM', 'output_only', true);
		$this->fh->setElementsProperties( "PRJ_FEASABILITY_DATE", 'output_only', true);
		$this->fh->setElementsProperties( "PRJ_DESCRIPTION", 'required', 'true');
		$this->fh->setElementsProperties('PRJ_SALES_REP', 'upper-case', true);
		$this->fh->setElementsProperties( "PRJ_SALES_REP", 'required', 'true');
		
		if ($this->isCreateMode()) {
			$this->fh->setElementsProperties( "PRJ_FEASABILITY_NUM", 'required', true);
		} else {
			$this->fh->setElementsProperties('PRJ_FEASABILITY_NUM', 'output_only', true);
		}
		
		// Project General Information
		$flGeneral = "PRJ_STATUS, PRJ_PROJECT_DATE, PRJ_FISCAL_YEAR_APPROVED, PRJ_MUNICIPALITY_CODE, 
							PRJ_CAP_EXP_CODE, PRJ_CUSTOMER_EXCAVATED, PRJ_COMPLETION_DATE";
		$this->fh->addFieldGroup( $flGeneral, 'general', 'Project General Information');
		$this->fh->setElementsProperties( "PRJ_MUNICIPALITY_CODE", 'required', 'true');
		
		if ( ! $this->isCreateMode() ) {
			// Can't change project cost allocation once it is created.
			$this->fh->setElementsProperties('PRJ_CAP_EXP_CODE', 'output_only', true);
		} else {
			$this->fh->setElementsProperties('PRJ_CAP_EXP_CODE', 'required', true);
		}
		
		// Miscellaneous fields
		$flMisc = "PRJ_CONTRIBUTION_IN_AID_AMT, PRJ_CONTRIBUTION_IN_AID_NUM, PRJ_CREDIT_FOR_REINFORCEMENT_AMT";
		$this->fh->addFieldGroup( $flMisc, 'misc', 'Miscellaneous Information');
		
		// Expected Rate of Return
		$flROR = "PRJ_EXP_ROR_YR1, PRJ_EXP_ROR_YR3, PRJ_EXP_ROR_YR10";
		$this->fh->addFieldGroup( $flROR, 'ROR', 'Expected Rate of Return');
		
		// Documents Required
		$flDocsReqd = "PRJ_LTR_OF_CREDIT_REQD, PRJ_LTR_OF_CREDIT_RCVD_DATE, PRJ_LTR_OF_CREDIT_EXP_DATE,
							PRJ_FEASABILITY_CALC_BY, PRJ_FEASABILITY_APPROVAL_REQD, PRJ_ROW_REQUIRED,
							PRJ_ROW_RCVD_DATE, PRJ_COMMITMENT_REQD, PRJ_COMMITMENT_RCVD_DATE";
		$this->fh->addFieldGroup( $flDocsReqd, 'docs', 'Documents Required');
		
		
		$flMaint = "PRJ_CREATE_USER, PRJ_CREATE_TIME, PRJ_CHANGE_USER, PRJ_CHANGE_TIME";
		$this->fh->addFieldGroup( $flMaint, 'maintenance', 'Record Maintenance Information');
		$this->fh->setElementsProperties($flMaint, 'output_only', true);
		
		$yesNoFields = "PRJ_ROW_REQUIRED, PRJ_LTR_OF_CREDIT_REQD, PRJ_COMMITMENT_REQD, 
							PRJ_FEASABILITY_APPROVAL_REQD";
		$this->fh->setElementsProperties( $yesNoFields, 'input_type', 'y/n');
		
		$dropDownFields = "PRJ_STATUS, PRJ_MUNICIPALITY_CODE, PRJ_CUSTOMER_EXCAVATED, PRJ_CAP_EXP_CODE";
		$this->fh->setElementsProperties( $dropDownFields, 'input_type', 'select');
		// Set event handler for change of project status. 
		// When status goes to project, function setProjectDates() will populate project date and completion date.
		$this->fh->setElementsProperties( 'PRJ_STATUS', 'attribs', array('onchange'=>"setProjectDates();return false;"));
		
		// This creates Zend_Form_Elements out of the meta data
		$this->fh->addElementsFromMetaData($this->mode);
		
		$this->setName ( 'form1' );
		$this->addElements ( $this->fh->getElements() );

		$cvm = new Code_Values_Master($this->conn);
		$cvList = $cvm->getCodeValuesList('PRJ_STATUS', '-- Unknown --');
		$this->fh->setMultiOptions('PRJ_STATUS', $cvList);
		
		$cvList = $cvm->getCodeValuesList('TOWN', '-- Unknown --');
		$this->fh->setMultiOptions('PRJ_MUNICIPALITY_CODE', $cvList);
		
		$cvList = $cvm->getCodeValuesList('PT_CAP_EXP', '-- Unknown --');
		$this->fh->setMultiOptions('PRJ_CAP_EXP_CODE', $cvList);
		
		$cvList = $cvm->getCodeValuesList('PRJ_CUSTOMER_EXCAVATED', '-- Unknown --');
		$this->fh->setMultiOptions('PRJ_CUSTOMER_EXCAVATED', $cvList);
		
		$this->getElement('PRJ_DESCRIPTION')->setAttrib('size', 30);
		$this->getElement('PRJ_CREATE_TIME')->setAttrib('size', 30);
		$this->getElement('PRJ_CHANGE_TIME')->setAttrib('size', 30);
	}

	/**
	 * reset() function is used to set default values on Project create screen
	 * @see Zend_Form::reset()
	 */
	public function reset() 
	{
		parent::reset();
		
		$this->getElement('PRJ_STATUS')->setValue('F');
		$this->getElement('PRJ_CUSTOMER_EXCAVATED')->setValue('N');
		$this->getElement('PRJ_FEASABILITY_DATE')->setValue(date('m-d-Y'));
		$nextPrjNum = $this->db_object->getNextProjNum();
		$this->getElement('PRJ_NUM')->setValue($nextPrjNum);
		
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

// Removed this block of code - handling with javascript in view
// 		$projDateElem = $this->getElement('PRJ_PROJECT_DATE');
// 		$projStsElem = $this->getElement('PRJ_STATUS');
// 		if ($projStsElem->getValue() == 'P') {
// 			if (trim($projDateElem->getValue()) == '') {
// 				//	Project Date changes to current date, when status changes to P
// 				$projDateElem->setValue(date('m-d-Y'));
// 			}
// 			if (trim($this->getElement('PRJ_COMPLETION_DATE')->getValue()) == '') {
// 				//	Completion Date changes to 3 years from today, when status changes to P
// 				$completionDate = strtotime('+ 3 years');
// 				$this->getElement('PRJ_COMPLETION_DATE')->setValue(date('m-d-Y', $completionDate));
// 			}
// 		}
		
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