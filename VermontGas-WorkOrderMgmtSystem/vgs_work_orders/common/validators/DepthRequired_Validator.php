<?php

require_once ('Zend/Validate/Abstract.php');

/** 
 * @author John Valance
 * 
 */
class DepthRequired_Validator extends Zend_Validate_Abstract {
    protected $_messageTemplates = array(
        "Depth (feet and/or inches) must be entered.",
    );	
	
	private function isBlankOrZero( $value ) {
		$value = trim($value);
		$err = ($value == '' || (int) $value == 0);
// 		pre_dump("Value = '$value'; err = '$err'");
		return $err;	
	}
	
	/**
	 * @param   mixed $value
	 * @return  boolean
	 * @throws  Zend_Validate_Exception If validation of $value is impossible
	 * @see Zend_Validate_Interface::isValid()
	 */
	public function isValid($value, $formValues = array()) {
    	$this->_setValue($value);
// 		pre_dump("value = $value");
		$feet = trim($formValues['WO_DEPTH_FEET']);
		$inches = trim($formValues['WO_DEPTH_INCHES']);
		
        if ($this->isBlankOrZero($feet)
        &&  $this->isBlankOrZero($inches))
        {
        	$this->_error();
            return false;
        }
 
        return true;
    }	
}

?>