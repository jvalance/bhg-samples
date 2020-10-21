<?php

require_once ('Zend/Validate/Abstract.php');

/** 
 * @author John Valance
 * 
 */
class DepthInches_Validator extends Zend_Validate_Abstract {
    protected $_messageTemplates = array(
        "Depth: Inches cannot be greater than 11 or less than 0.",
    );	
	
	/**
	 * @param   mixed $value
	 * @return  boolean
	 * @throws  Zend_Validate_Exception If validation of $value is impossible
	 * @see Zend_Validate_Interface::isValid()
	 */
	public function isValid($value, $formValues = array()) {
    	$this->_setValue($value);

    	if ((int)$value < 0 || (int)$value > 11) {
       		$this->_error();
            return false;
        }
 
        return true;
    }	
}

?>