<?php
require_once ('Zend/Validate/Abstract.php');
require_once ('../model/Premise.php');
require_once ('../model/VGS_DB_Conn_Singleton.php');

/** 
 * @author John Valance
 * 
 */
class Premise_Validator extends Zend_Validate_Abstract {

	protected $_messageTemplates = array(
		'NOT_FOUND' => "The ID entered (%value%) is not a valid Premise number in the UPRM table.",
	);	
	 
	/**
	 * Check that value passed is a valid Premise number in the UPRM table.
	 * @param   mixed $value
	 * @return  boolean
	 * @throws  Zend_Validate_Exception If validation of $value is impossible
	 * @see Zend_Validate_Interface::isValid()
	 */
	public function isValid($value, $formValues = array()) {
		$this->_setValue(trim($value));

		// Don't check blank or zero value
		if (isBlankOrZero($value)) return true;
		
		// Value must be numeric
		if (!ctype_digit($value)) {
			$this->_error('NOT_FOUND');
			return false;
		}
      
		if (trim($value) != '') {
			$dbconn = VGS_DB_Conn_Singleton::getInstance();
			$premiseObj = new Premise($dbconn);
	
			$premiseRec = $premiseObj->retrieve($value);
			if (!is_array($premiseRec) || trim($premiseRec['UPPRM']) != $value) {
				$this->_error('NOT_FOUND');
				return false;
			}
		}

		return true;
	}	
}

?>