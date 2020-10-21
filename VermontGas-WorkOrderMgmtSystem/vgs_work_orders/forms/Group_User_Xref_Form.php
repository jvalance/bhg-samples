<?php
require_once '../forms/VGS_Form.php';
require_once '../model/Group_User_Xref.php';
require_once '../model/Sec_Profiles.php';
require_once '../common/validators/UserProfile_Validator.php';
require_once '../common/validators/GroupProfile_Validator.php';

class Group_User_Xref_Form extends VGS_Form 
{
	private $db_object;
	
	public function __construct( $conn ) {
		parent::__construct ( $conn );

		$sec = new Security();
		$sec->checkPermissionByCategory('SEC', $this->mode);
		
		$this->fh->addMetaData($this->conn, "GRPUSRXRF");
		$this->setDefaultElements( );
		$this->screen_title = ucfirst($this->mode) . ' User/Group Profile Xref';
		$this->db_object = new Group_User_Xref($conn);
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

		$flUserGroup = 'UG_GROUP_ID, UG_USER_ID';
		$this->fh->addFieldGroup( $flUserGroup, 'user_group', 'User/Group');
		$this->fh->setElementsProperties( 'UG_GROUP_ID', 'required', 'true');
		$this->fh->setElementsProperties( 'UG_GROUP_ID', 'upper-case', 'true');
		$this->fh->setElementsProperties( 'UG_GROUP_ID', 'lookup', 'javascript:lookupGroup();');
		$this->fh->setElementsProperties( 'UG_USER_ID', 'required', 'true');
		$this->fh->setElementsProperties( 'UG_USER_ID', 'upper-case', 'true');
		$this->fh->setElementsProperties( 'UG_USER_ID', 'lookup', 'javascript:lookupUser();');
		
		if ( ! $this->isCreateMode()) {
			// Key field output-only unless create mode
			$this->fh->setElementsProperties('UG_GROUP_ID', 'output_only', true);
			$this->fh->setElementsProperties('UG_USER_ID', 'output_only', true);
		}
		
		$maintFieldList = 'UG_CREATE_USER, UG_CREATE_TIME, UG_CHANGE_USER, UG_CHANGE_TIME';
		$this->fh->addFieldGroup( $maintFieldList, 'maintenance', 'Record Maintenance Information');
		$this->fh->setElementsProperties($maintFieldList, 'output_only', true);
		
		// This creates Zend_Form_Elements out of the meta data
		$this->fh->addElementsFromMetaData($this->mode);
		
		$this->setName ( 'form1' );
		$this->addElements ( $this->fh->getElements() );


		$this->getElement('UG_GROUP_ID')->addValidator(new GroupProfile_Validator());
		$this->getElement('UG_USER_ID')->addValidator(new UserProfile_Validator());
		
//		$cvm = new Code_Values_Master($this->conn);
//		$cvList = $cvm->getCodeValuesList('UG_FUNCTIONAL_AREA', '-- Unknown --');
//		$this->fh->setMultiOptions('UG_FUNCTIONAL_AREA', $cvList);
		
		$this->getElement('UG_CREATE_TIME')->setAttrib('size', 30);
		$this->getElement('UG_CHANGE_TIME')->setAttrib('size', 30);
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
		$groupID = trim($this->getElement('UG_GROUP_ID')->getValue());
		$profileRec = $secProfile->retrieveByID($groupID);
		$this->getElement('UG_GROUP_ID')->setDescription($profileRec['PRF_DESCRIPTION']);

        $userIdElem = $this->getElement('UG_USER_ID');
		$profileInfo = $secProfile->getProfileInfo($userIdElem->getValue());
		if ($profileInfo['profileType'] == 'USER') {
			$userIdElem->setDescription($profileInfo['profileText']);
		} 
		
	}
	
	/**
	 * Custom validations for this form - this overrides the validate() method 
	 *    defined in VGS_Form.php, and calls the Zend_Form isValid() method. 
	 * @see VGS_Form::validate()
	 * @see Zend_Form::isValid()
	 */
	public function validate() {
		$this->valid = parent::isValid($this->inputs);
		 
		return $this->valid ;
	}
}

?>