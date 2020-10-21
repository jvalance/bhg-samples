<?php
require_once ('Zend/Validate/Abstract.php');
require_once ('../model/Crew.php');
require_once ('../model/VGS_DB_Conn_Singleton.php');

/** 
 * @author John Valance
 * 
 */
class Crew_Validator extends Zend_Validate_Abstract {

	protected $_messageTemplates = array(
		'NOT_FOUND' => "The ID entered (%value%) is not a valid crew ID.",
	);	
	
	/**
	 * Check that value passed is a valid Crew ID.
	 * @param   mixed $value
	 * @return  boolean
	 * @throws  Zend_Validate_Exception If validation of $value is impossible
	 * @see Zend_Validate_Interface::isValid()
	 */
	public function isValid($value, $formValues = array()) {
      $this->_setValue(trim($value));

      if (trim($value) != '') {
	      $dbconn = VGS_DB_Conn_Singleton::getInstance();
			$crewObj = new Crew($dbconn);
	
			$crewRec = $crewObj->retrieve($value);
			if (trim($crewRec['ID']) != $value) {
	         $this->_error('NOT_FOUND');
	         return false;
			}
      }

      return true;
    }	
}

?>