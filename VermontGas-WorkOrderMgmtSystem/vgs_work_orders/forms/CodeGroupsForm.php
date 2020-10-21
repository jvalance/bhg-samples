<?php
require_once '../forms/VGS_Form.php';
require_once '../model/Code_Groups.php';
/** 
 * @author John
 * 
 * 
 */
class CodeGroupsForm extends VGS_Form 
{
	private $db_object;
	
	public function __construct( $conn ) {
		parent::__construct ( $conn );

		$sec = new Security();
		$sec->checkPermissionByCategory('DD', $this->mode);
		
		$this->fh->addMetaData($this->conn, "CODEGROUPS");
		$this->setDefaultElements( );
		$this->screen_title = 'Drop Down Lists ' . ucfirst($this->mode);
		$this->db_object = new Code_Groups($conn);
	}
	
	public function createRecord() {
		return $this->db_object->create($this->inputs);
	}
	public function updateRecord() {
		return $this->db_object->update($this->inputs);
	}
	public function retrieveRecord() {
		return $this->db_object->retrieve($this->inputs['CG_GROUP']);
	}
	public function deleteRecord() {
		return $this->db_object->delete($this->inputs);
	}
	
	public function setDefaultElements( ) {
		$generalFieldList = 'CG_GROUP, CG_DESCRIPTION, CG_STATUS, CG_SEQUENCE';
		$this->fh->addFieldGroup( $generalFieldList, 'general', 'General Information');
		$this->fh->setElementsProperties( 'CG_GROUP, CG_DESCRIPTION, CG_STATUS', 'required', 'true');
		
		if ( ! $this->isCreateMode()) {
			// Key field output-only unless create mode
			$this->fh->setElementsProperties('CG_GROUP', 'output_only', true);
		} else {
			$this->fh->setElementsProperties('CG_GROUP', 'upper-case', true);
		}
					
		$maintFieldList = 'CG_CREATE_USER, CG_CREATE_TIME, CG_CHANGE_USER, CG_CHANGE_TIME';
		$this->fh->addFieldGroup( $maintFieldList, 'maintenance', 'Record Maintenance Information');
		$this->fh->setElementsProperties($maintFieldList, 'output_only', true);

		$this->fh->setElementsProperties( 'CG_STATUS', 'input_type', 'select');
		
		// This creates Zend_Form_Elements out of the meta data
		$this->fh->addElementsFromMetaData($this->mode);
		
		$this->fh->setMultiOptions('CG_STATUS', Code_Groups::$statusCodes);

		$this->setName ( 'form1' );
		$this->addElements ( $this->fh->getElements() );
		
		$this->getElement('CG_CREATE_TIME')->setAttrib('SIZE', 30);
		$this->getElement('CG_CHANGE_TIME')->setAttrib('SIZE', 30);
	}
	
}

?>