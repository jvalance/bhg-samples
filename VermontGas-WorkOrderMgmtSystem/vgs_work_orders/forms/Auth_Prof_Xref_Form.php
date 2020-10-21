<?php
require_once '../forms/VGS_Form.php';
require_once '../model/Auth_Profile_Xref.php';
require_once '../model/Authorities.php';
require_once '../common/validators/Sec_Profile_Validator.php';
require_once '../common/validators/AuthorityID_Validator.php';

class Auth_Profile_Xref_Form extends VGS_Form 
{
	private $db_object;
	
	public function __construct( $conn ) {
		parent::__construct ( $conn );

		$sec = new Security();
		$sec->checkPermissionByCategory('SEC', $this->mode);
		 
		$this->fh->addMetaData($this->conn, "AUTHPROFX");
		$this->setDefaultElements( );
		$this->screen_title = ucfirst($this->mode) . ' Authority Profile Xref';
		$this->db_object = new Auth_Profile_Xref($conn);
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

		$flAuthProf = 'AP_PROFILE_ID, AP_AUTH_ID, AP_PERMISSION';
		$this->fh->addFieldGroup( $flAuthProf, 'auth_prof', 'Authority/Profile');
		
		$this->fh->setElementsProperties('AP_PROFILE_ID', 'required', 'true');
		$this->fh->setElementsProperties('AP_PROFILE_ID', 'upper-case', 'true');
		$this->fh->setElementsProperties('AP_PROFILE_ID', 'lookup', 'javascript:lookupProfile();');
		
		$this->fh->setElementsProperties('AP_AUTH_ID', 'required', 'true');
		$this->fh->setElementsProperties('AP_AUTH_ID', 'upper-case', 'true');
		$this->fh->setElementsProperties('AP_AUTH_ID', 'lookup', 'javascript:lookupAuthority();');
		
		$this->fh->setElementsProperties('AP_PERMISSION', 'required', 'true');
		$this->fh->setElementsProperties('AP_PERMISSION', 'input_type', 'select');
		
		
		if ( ! $this->isCreateMode()) {
			// Key field output-only unless create mode
			$this->fh->setElementsProperties('AP_AUTH_ID', 'output_only', true);
			$this->fh->setElementsProperties('AP_PROFILE_ID', 'output_only', true);
		}
		
		$maintFieldList = 'AP_CREATE_USER, AP_CREATE_TIME, AP_CHANGE_USER, AP_CHANGE_TIME';
		$this->fh->addFieldGroup( $maintFieldList, 'maintenance', 'Record Maintenance Information');
		$this->fh->setElementsProperties($maintFieldList, 'output_only', true);
		
		// This creates Zend_Form_Elements out of the meta data
		$this->fh->addElementsFromMetaData($this->mode);
		
		$this->setName ( 'form1' );
		$this->addElements ( $this->fh->getElements() );

		$this->getElement('AP_PROFILE_ID')->addValidator(new Sec_Profile_Validator());
		$this->getElement('AP_AUTH_ID')->addValidator(new AuthorityID_Validator());
		
		$cvm = new Code_Values_Master($this->conn);
		$cvList = $cvm->getCodeValuesList('AP_PERMISSION', ' ');
		$this->fh->setMultiOptions('AP_PERMISSION', $cvList);
		
		$this->getElement('AP_CREATE_TIME')->setAttrib('size', 30);
		$this->getElement('AP_CHANGE_TIME')->setAttrib('size', 30);
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
		
		$secProfile = new Sec_Profiles($this->conn);
		$profileID = trim($this->getElement('AP_PROFILE_ID')->getValue());
		$profileRec = $secProfile->retrieveByID($profileID);
		$this->getElement('AP_PROFILE_ID')->setDescription($profileRec['PRF_DESCRIPTION']);
		
		$auth = new Authorities($this->conn);
		$authID = $this->getElement('AP_AUTH_ID')->getValue();
		$rtvData['AD_AUTH_ID'] = $data['AP_AUTH_ID'];  
		$authRec = $auth->retrieve($rtvData);
		$this->getElement('AP_AUTH_ID')->setDescription($authRec['AD_AUTH_NAME']);
		
	}
	
	/**
	 * Custom validations for this form - this overrides the validate() method 
	 *    defined in VGS_Form.php, and calls the Zend_Form isValid() method. 
	 * @see VGS_Form::validate()
	 * @see Zend_Form::isValid()
	 */
	public function validate() {
		$this->valid = parent::validate();

		// Custom validations moved to custom validator classes in /common/validators.
		// See addValidator() calls in setDefaultElements().
		return $this->valid ;
	}
}

?>