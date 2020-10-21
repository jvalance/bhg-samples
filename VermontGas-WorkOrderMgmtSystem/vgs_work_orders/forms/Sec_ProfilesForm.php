<?php
require_once '../forms/VGS_Form.php';
require_once '../model/Sec_Profiles.php';
require_once '../common/validators/UserProfile_Validator.php';

class Sec_ProfilesForm extends VGS_Form 
{
	private $db_object;
	
	public function __construct( $conn ) {
		parent::__construct ( $conn );

		$sec = new Security();
		$sec->checkPermissionByCategory('SEC', $this->mode);
		
		$this->fh->addMetaData($this->conn, "SECPROFILE");
		$this->setDefaultElements( );
		$this->screen_title = ucfirst($this->mode) . ' Security Profile';
		$this->db_object = new Sec_Profiles($conn);
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

		$flProfile = 'PRF_PROFILE_ID, PRF_PROFILE_TYPE, PRF_DESCRIPTION, PRF_PROFILE_STATUS';
		$this->fh->addFieldGroup( $flProfile, 'profile', 'Profile Details');
		$this->fh->setElementsProperties( 'PRF_PROFILE_ID', 'required', 'true');
		$this->fh->setElementsProperties('PRF_PROFILE_ID', 'upper-case', 'true');
		$this->fh->setElementsProperties( 'PRF_PROFILE_TYPE', 'required', 'true');
		$this->fh->setElementsProperties( 'PRF_PROFILE_TYPE', 'input_type', 'select');
		$this->fh->setElementsProperties( 'PRF_PROFILE_ID', 'required', 'true');
		$this->fh->setElementsProperties( 'PRF_DESCRIPTION', 'required', 'true');
		$this->fh->setElementsProperties( 'PRF_PROFILE_STATUS', 'required', 'true');
		$this->fh->setElementsProperties( 'PRF_PROFILE_STATUS', 'input_type', 'select');
		
		if ( ! $this->isCreateMode()) {
			// Key field output-only unless create mode
			$this->fh->setElementsProperties('PRF_PROFILE_ID', 'output_only', true);
		}
		 
		$maintFieldList = 'PRF_CREATE_USER, PRF_CREATE_TIME, PRF_CHANGE_USER, PRF_CHANGE_TIME';
		$this->fh->addFieldGroup( $maintFieldList, 'maintenance', 'Record Maintenance Information');
		$this->fh->setElementsProperties($maintFieldList, 'output_only', true);
		
		// This creates Zend_Form_Elements out of the meta data
		$this->fh->addElementsFromMetaData($this->mode);
		
		$this->setName ( 'form1' );
		$this->addElements ( $this->fh->getElements() );

		$profType = $this->inputs['PRF_PROFILE_TYPE'];
		if ($profType == 'USER') {
			$this->getElement('PRF_PROFILE_ID')->addValidator(new UserProfile_Validator());
		}
		
		$this->setUserProfileDescription(); // If description left blank, default to user profile text
		
		$dd = new Code_Values_Master($this->conn);

		$ddList = $dd->getCodeValuesList('AP_PROFILE_TYPE', ' ');
		$this->fh->setMultiOptions('PRF_PROFILE_TYPE', $ddList);

		$ddList = $dd->getCodeValuesList('RECSTATUS', ' ');
		$this->fh->setMultiOptions('PRF_PROFILE_STATUS', $ddList);
		
		$this->getElement('PRF_CREATE_TIME')->setAttrib('size', 30);
		$this->getElement('PRF_CHANGE_TIME')->setAttrib('size', 30);
	}

	public function reset() {
		$this->getElement('PRF_PROFILE_STATUS')->setValue('ACT');
		
	}
	
	private function setUserProfileDescription() {
		if ($this->isCreateMode() || $this->isUpdateMode()) {
			$profileType = $this->inputs['PRF_PROFILE_TYPE'];
			$profileDesc = $this->inputs['PRF_DESCRIPTION'];
	
			if ($profileType == 'USER' && trim($profileDesc) == '') {
				$profileID = $this->inputs['PRF_PROFILE_ID'];
				$secProfile = new Sec_Profiles($this->conn);
				$profileInfo = $secProfile->getProfileInfo($profileID);
				if (trim($profileInfo['profileType']) == 'USER') {
					$this->inputs['PRF_DESCRIPTION'] = $profileInfo['profileText'];
				}
			}
		}
		
	}
}

?>