<?php
require_once ('Zend/Validate/Abstract.php');
require_once ('../model/Pipe_Type_Master.php');
require_once ('../model/VGS_DB_Conn_Singleton.php');

/** 
 * @author John Valance
 * 
 */
class PipeType_Service_Validator extends Zend_Validate_Abstract {

	protected $_messageTemplates = array(
		'NOT_SERVICE' => "The Pipe Type entered (%value%) is not a SERVICE Pipe Type code.",
	);	
	
	/**
	 * Check that value passed is a SERVICE Pipe Type code in the Pipe_Type_Master table.
	 * @param   mixed $value
	 * @return  boolean
	 * @throws  Zend_Validate_Exception If validation of $value is impossible
	 * @see Zend_Validate_Interface::isValid()
	 */
	public function isValid($value, $formValues = array()) {
      $this->_setValue($value);

      // Value must be numeric
	   if (trim($value) != '' && ctype_digit($value)) {
	      $dbconn = VGS_DB_Conn_Singleton::getInstance();
			$pipeTypeObj = new Pipe_Type_Master($dbconn);
	
			$pipeTypeRec = $pipeTypeObj->retrieveByID($value);
			if (trim($pipeTypeRec['PT_PIPE_TYPE']) == $value
			&& trim($pipeTypeRec['PT_CATEGORY']) != 'S') {
	            $this->_error('NOT_SERVICE');
	            return false;
			}
      }
		
		return true;
    }	
}

?>