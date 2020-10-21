<?php

require_once ('Zend/Validate/Abstract.php');
require_once ('../model/Security.php');

/** 
 * @author John Valance
 * 
 */
class Login_Validator extends Zend_Validate_Abstract {
    protected $_messageTemplates = array();	
	
	/**
	 * @param   mixed $value
	 * @return  boolean
	 * @throws  Zend_Validate_Exception If validation of $value is impossible
	 * @see Zend_Validate_Interface::isValid()
	 */
	public function isValid($value, $formValues = array()) {
        $this->_setValue($value);

        $security = new Security();
        
		$user = trim($formValues['USER']);
		$pswd = trim($formValues['PSWD']);
        
        $authResult = $security->authenticateWinAD($user, $pswd);
		
        if (is_string( $authResult )) {
        	$_messageTemplates[0] = $authResult;
            $this->_error(0);
            return false;
        }
 
        return true;
    }	
}

?>