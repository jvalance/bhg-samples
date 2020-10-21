<?php
require_once ('Zend/Validate/Abstract.php');
require_once ('../model/Project_Master.php');
require_once ('../model/VGS_DB_Conn_Singleton.php');

/** 
 * @author John Valance
 * 
 */
class Project_Validator extends Zend_Validate_Abstract {

	protected $_messageTemplates = array(
		'NOT_FOUND' => "The ID entered (%value%) is not a valid Project number in the Project_Master table.",
		'STATUS_CLOSED' => "Invalid project number - status of project %value% is CLOSED.",
		'STATUS_HELD' => "Invalid project number - status of project %value% is HELD.",
		'STATUS_CANCELLED' => "Invalid project number - status of project %value% is CANCELLED.",
	);	
	 
	/**
	 * Check that value passed is a valid Project number in the Project_Master table.
	 * @param   mixed $value
	 * @return  boolean
	 * @throws  Zend_Validate_Exception If validation of $value is impossible
	 * @see Zend_Validate_Interface::isValid()
	 */
	public function isValid($value, $formValues = array()) {
      $this->_setValue(trim($value));

      // Value must be numeric
      if (!ctype_digit($value)) {
			$this->_error('NOT_FOUND');
			return false;
      }
      
      if (trim($value) != '') {
	      $dbconn = VGS_DB_Conn_Singleton::getInstance();
			$projectObj = new Project_Master($dbconn);
	
			$projectRec = $projectObj->retrieveByID($value);
			if (trim($projectRec['PRJ_NUM']) != $value) {
	         $this->_error('NOT_FOUND');
	         return false;
			}
			
			$prjStatus = trim($projectRec['PRJ_STATUS']); 
			if ($prjStatus == 'H') {
	         $this->_error('STATUS_HELD');
	         return false;
			}
			if ($prjStatus == 'C' ) {
	         $this->_error('STATUS_CLOSED');
	         return false;
			}
			if ($prjStatus == 'X' ) {
	         $this->_error('STATUS_CANCELLED');
	         return false;
			}
      }

      return true;
    }	
}

?>