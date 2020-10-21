<?php

require_once ('Zend/Validate/Abstract.php');

/** 
 * @author John Valance
 * 
 */
class DepthFeet_Validator extends Zend_Validate_Abstract {
    protected $_messageTemplates = array(
        "Depth: Feet cannot be greater than 15 or less than 0. If unknown, use -1",
    );	
	
	/**
	 * @param   mixed $value
	 * @return  boolean
	 * @throws  Zend_Validate_Exception If validation of $value is impossible
	 * @see Zend_Validate_Interface::isValid()
	 */
	public function isValid($value, $formValues = array()) {
    	$this->_setValue($value);

    	if ((int)$value < -1 || (int)$value > 15) {
       		$this->_error();
            return false;
        }
 
        return true;
    }	
}

?>