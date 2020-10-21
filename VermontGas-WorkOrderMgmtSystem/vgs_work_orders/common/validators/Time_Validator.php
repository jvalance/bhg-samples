<?php
require_once ('Zend/Validate/Abstract.php');

/** 
 * @author John Valance
 * 
 */
class Time_Validator extends Zend_Validate_Abstract {

	protected $_messageTemplates = array(
		'BAD_TIME' => "%value% is not a valid time value in the format hh:mm or hh:mm:ss"
	);	
	
	/**
	 * Check that value passed is a valid Time in the format of hh:mm or hh:mm:ss.
	 * @param   string $value
	 * @return  boolean
	 * @throws  Zend_Validate_Exception If validation of $value is impossible
	 * @see Zend_Validate_Interface::isValid()
	 */
	public function isValid($value, $formValues = array()) {
      $this->_setValue(trim($value));

		$value = preg_replace('/\./', ':', $value);
		$hms = split(':', $value);

		if (count($hms) < 2 || count($hms) > 3 
		|| ($hms[0] < '00' || $hms[0] > '24')
		|| ($hms[1] < '00' || $hms[1] > '59')
		|| (isset($hms[2]) && ($hms[2] < '00' || $hms[2] > '59'))) {
			$blnTimeIsValid = false;
		} else {
			$blnTimeIsValid = true;
		}
		
		if (!$blnTimeIsValid) {
			$this->_error('BAD_TIME');
			return false;
		}
      
      return true;
    }	
}

?>