<?php
require_once '../forms/VGS_Form.php';
require_once '../model/Pipe_Type_Master.php';
/** 
 * @author Carol
 */
class PipeTypesForm extends VGS_Form 
{
	private $db_object;
	
	public function __construct( $conn ) {
		parent::__construct ( $conn );

		$sec = new Security();
		$sec->checkPermissionByCategory('PIPE', $this->mode);
		
		$this->fh->addMetaData($this->conn, "PIPETYPE");
		$this->setDefaultElements( );
		$this->screen_title = 'Pipe Type ' . ucfirst($this->mode);
		$this->db_object = new Pipe_Type_Master($conn);
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
		$generalFieldList = 'PT_PIPE_TYPE, PT_DESCRIPTION';
		$this->fh->addFieldGroup( $generalFieldList, 'general', 'General Information');
		$this->fh->setElementsProperties( $generalFieldList, 'required', 'true');
		
		if ( ! $this->isCreateMode()) {
			// Key field output-only unless create mode
			$this->fh->setElementsProperties('PT_PIPE_TYPE', 'output_only', true);
		}
		
		$maintFieldList = 'PT_CREATE_USER, PT_CREATE_TIME, PT_CHANGE_USER, PT_CHANGE_TIME';
		$this->fh->addFieldGroup( $maintFieldList, 'maintenance', 'Record Maintenance Information');
		$this->fh->setElementsProperties($maintFieldList, 'output_only', true);
		
		$materialFieldList = 'PT_MATERIAL, PT_DIAMETER, PT_CATEGORY, PT_COATING';
		$this->fh->addFieldGroup( $materialFieldList, 'material', 'Material Information');
		$this->fh->setElementsProperties( 'PT_MATERIAL, PT_CATEGORY', 'required', 'true');
		
		$acctgFieldList = 'PT_ACCTG_UNIT_COST, PT_GL_ACCT_COST, PT_SUB_ACCT_COST, PT_SUMMARY_TYPE_COST,
						   PT_ACCTG_UNIT_CLOSE, PT_GL_ACCT_CLOSE, PT_SUB_ACCT_CLOSE, PT_SUMMARY_TYPE_CLOSE, PT_CAP_EXP';
		$this->fh->addFieldGroup( $acctgFieldList, 'acctg', 'Accounting Information');
		 
		$acctgRequired = 'PT_ACCTG_UNIT_COST, PT_GL_ACCT_COST, PT_ACCTG_UNIT_CLOSE,' 
						. 'PT_GL_ACCT_CLOSE, PT_CAP_EXP';
		$this->fh->setElementsProperties( $acctgRequired, 'required', 'true');
		$this->fh->setElementsProperties('PT_ACCTG_UNIT_COST', 'upper-case', true);
		$this->fh->setElementsProperties('PT_ACCTG_UNIT_CLOSE', 'upper-case', true);
		
		$this->fh->setElementsProperties( 'PT_MATERIAL, PT_CATEGORY, PT_COATING, PT_CAP_EXP', 'input_type', 'select');
		
		// This creates Zend_Form_Elements out of the meta data
		$this->fh->addElementsFromMetaData($this->mode);
		
		$this->setName ( 'form1' );
		$this->addElements ( $this->fh->getElements() );

		$cvm = new Code_Values_Master($this->conn);
		$cvList = $cvm->getCodeValuesList('PT_MATERIAL', '-- Unknown --');
		$this->fh->setMultiOptions('PT_MATERIAL', $cvList);
		$cvList = $cvm->getCodeValuesList('PT_CATEGORY', '-- Unknown --');
		$this->fh->setMultiOptions('PT_CATEGORY', $cvList);
		$cvList = $cvm->getCodeValuesList('PT_COATING', '-- Unknown --');
		$this->fh->setMultiOptions('PT_COATING', $cvList);
		$cvList = $cvm->getCodeValuesList('PT_CAP_EXP', '-- Unknown --');
		$this->fh->setMultiOptions('PT_CAP_EXP', $cvList);
		
		$this->getElement('PT_DESCRIPTION')->setAttrib('size', 30);
		$this->getElement('PT_CREATE_TIME')->setAttrib('size', 30);
		$this->getElement('PT_CHANGE_TIME')->setAttrib('size', 30);
	}
	
}

?>