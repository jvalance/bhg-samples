<?php

require_once ('Zend/Validate/Abstract.php');

/** 
 * @author John Valance
 * 
 */
class WO_CostValidator extends Zend_Validate_Abstract {
    protected $_messageTemplates = array(
        "Either (Estimated Cost) or (Est Cost Per Foot and Estimated Length) must be entered.",
    );	
	
	private function isBlankOrZero( $value ) {
		$value = trim($value);
		return ($value == '' || (int) $value == 0);	
	}
	
	/**
	 * @param   mixed $value
	 * @return  boolean
	 * @throws  Zend_Validate_Exception If validation of $value is impossible
	 * @see Zend_Validate_Interface::isValid()
	 */
	public function isValid($value, $formValues = array()) {
    	$this->_setValue($value);

		$estCost = trim($formValues['WO_ESTIMATED_COST']);
		$estCostPerFoot = trim($formValues['WO_EST_COST_PER_FOOT']);
		$estLength = trim($formValues['WO_ESTIMATED_LENGTH']);
		
        if ($this->isBlankOrZero($estCost)
        && ($this->isBlankOrZero($estCostPerFoot) 
	       	|| $this->isBlankOrZero($estLength))) {
        		$this->_error();
            return false;
        }
 
        return true;
    }	
}

?>