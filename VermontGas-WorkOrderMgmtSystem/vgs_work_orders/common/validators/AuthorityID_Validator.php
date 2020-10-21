<?php
require_once ('Zend/Validate/Abstract.php');
require_once '../model/Authorities.php';
require_once ('../model/VGS_DB_Conn_Singleton.php');

/** 
 * @author John Valance
 * 
 */
class AuthorityID_Validator extends Zend_Validate_Abstract {

	protected $_messageTemplates = array(
    	"The authority ID entered (%value%) is not a valid ID in the AUTHORITIES table."
	);	
	
	/**
	 * Check that value passed is a valid User Profile ID on the IBM iSeries.
	 * @param   mixed $value
	 * @return  boolean
	 * @throws  Zend_Validate_Exception If validation of $value is impossible
	 * @see Zend_Validate_Interface::isValid()
	 */
	public function isValid($value, $formValues = array()) {
        $this->_setValue($value);
        
        $dbconn = VGS_DB_Conn_Singleton::getInstance();
		$authObj = new Authorities($dbconn);

		$authID = trim($value);
		$authRec = $authObj->retrieveByID($authID);
		
		if (trim($authRec['AD_AUTH_ID']) != $authID) {
            $this->_error();
            return false;
		} else {
            return true;
		}
					
    }	
}

?>