<?php
require_once '../forms/VGS_Form.php';
require_once '../model/Code_Values_Master.php';
/** 
 * @author Carol
 */
class CodeValuesForm extends VGS_Form 
{
	private $db_object;
	
	public function __construct( $conn ) {
		parent::__construct ( $conn );

		$sec = new Security();
		$sec->checkPermissionByCategory('DD', $this->mode);
		
		$this->fh->addMetaData($this->conn, "CODEMAST");
		$this->setDefaultElements( );
		$this->screen_title = 'Drop Down Values ' . ucfirst($this->mode);
		$this->db_object = new Code_Values_Master($conn);
	}
	
	public function createRecord() {
		return $this->db_object->create($this->inputs);
	}
	public function updateRecord() {
		return $this->db_object->update($this->inputs);
	}
	public function retrieveRecord() {
		return $this->db_object->retrieveRecord($this->inputs);
	}
	public function deleteRecord() {
		return $this->db_object->delete($this->inputs);
	}
	
	public function setDefaultElements( ) {
		$generalFieldList = 'CV_GROUP, CV_CODE, CV_VALUE, CV_DESCRIPTION, CV_STATUS, CV_SEQUENCE';
		$this->fh->addFieldGroup( $generalFieldList, 'general', 'General Information');
		$this->fh->setElementsProperties( 'CV_GROUP, CV_CODE, CV_VALUE, CV_STATUS', 'required', 'true');
		
		$this->fh->setElementsProperties('CV_GROUP', 'output_only', true);
		if ( ! $this->isCreateMode()) {
			// Key field output-only unless create mode
			$this->fh->setElementsProperties('CV_CODE', 'output_only', true);
		} else {
			$this->fh->setElementsProperties('CV_CODE', 'upper-case', true);
		}
		
		$maintFieldList = 'CV_CREATE_USER, CV_CREATE_TIME, CV_CHANGE_USER, CV_CHANGE_TIME';
		$this->fh->addFieldGroup( $maintFieldList, 'maintenance', 'Record Maintenance Information');
		$this->fh->setElementsProperties($maintFieldList, 'output_only', true);

		$this->fh->setElementsProperties( 'CV_STATUS', 'input_type', 'select');
		
		// This creates Zend_Form_Elements out of the meta data
		$this->fh->addElementsFromMetaData($this->mode);
		
 		$this->fh->setMultiOptions('CV_STATUS', Code_Values_Master::$statusCodes);

		$this->setName ( 'form1' );
		$this->addElements ( $this->fh->getElements() );

		if ($this->isCreateMode()) {
			$this->getElement('CV_STATUS')->setValue('ACT');
			$this->getElement('CV_SEQUENCE')->setValue(0.00);
		}
		
		$this->getElement('CV_VALUE')->setAttrib('size', 30);
		$this->getElement('CV_DESCRIPTION')->setAttrib('size', 40);
		$this->getElement('CV_CREATE_TIME')->setAttrib('size', 30);
		$this->getElement('CV_CHANGE_TIME')->setAttrib('size', 30);
	}
	
}

?>