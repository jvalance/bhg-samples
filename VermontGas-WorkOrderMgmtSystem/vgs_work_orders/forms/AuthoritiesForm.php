<?php
require_once '../forms/VGS_Form.php';
require_once '../model/Authorities.php';

class AuthoritiesForm extends VGS_Form 
{
	private $db_object;
	
	public function __construct( $conn ) {
		parent::__construct ( $conn );

		$sec = new Security();
		$sec->checkPermissionByCategory('SEC', $this->mode);
		
		$this->fh->addMetaData($this->conn, "AUTHDEFS");
		$this->setDefaultElements( );
		$this->screen_title = ucfirst($this->mode) . ' Authority Definition';
		$this->db_object = new Authorities($conn);
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
		$flAuth = 'AD_AUTH_ID, AD_AUTH_NAME, AD_DESCRIPTION, AD_FUNCTIONAL_AREA';
		$this->fh->addFieldGroup( $flAuth, 'authority', 'Authority Definition');
		$this->fh->setElementsProperties( 'AD_AUTH_ID', 'required', 'true');
		$this->fh->setElementsProperties( 'AD_AUTH_ID', 'upper-case', 'true');
		$this->fh->setElementsProperties( 'AD_AUTH_NAME', 'required', 'true');
		$this->fh->setElementsProperties( 'AD_DESCRIPTION', 'required', 'true');
		$this->fh->setElementsProperties( 'AD_DESCRIPTION', 'input_type', 'textarea');
		
		if ( ! $this->isCreateMode()) {
			// Key field output-only unless create mode
			$this->fh->setElementsProperties('AD_AUTH_ID', 'output_only', true);
		}

		$maintFieldList = 'AD_CREATE_USER, AD_CREATE_TIME, AD_CHANGE_USER, AD_CHANGE_TIME';
		$this->fh->addFieldGroup( $maintFieldList, 'maintenance', 'Record Maintenance Information');
		$this->fh->setElementsProperties($maintFieldList, 'output_only', true);
		
		// This creates Zend_Form_Elements out of the meta data
		$this->fh->addElementsFromMetaData($this->mode);
		
		$this->setName ( 'form1' );
		$this->addElements ( $this->fh->getElements() );
		
		$this->getElement('AD_DESCRIPTION')->setAttrib('size', 30);
		$this->getElement('AD_CREATE_TIME')->setAttrib('size', 30);
		$this->getElement('AD_CHANGE_TIME')->setAttrib('size', 30);
	}
	
}

?>