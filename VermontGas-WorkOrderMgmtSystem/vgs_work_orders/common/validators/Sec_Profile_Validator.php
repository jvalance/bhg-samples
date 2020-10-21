<?php
require_once ('Zend/Validate/Abstract.php');
require_once ('../model/Security.php');
require_once ('../model/Sec_Profiles.php');
require_once ('../model/VGS_DB_Conn_Singleton.php');

/** 
 * @author John Valance
 * 
 */
class Sec_Profile_Validator extends Zend_Validate_Abstract {

	protected $_messageTemplates = array(
    	"The ID entered (%value%) is not a valid Group Profile ID in the SEC_PROFILES table.",
	);	
	
	/**
	 * Check that value passed is a valid profile ID on the Sec_Profiles table.
	 * @param   mixed $value
	 * @return  boolean
	 * @throws  Zend_Validate_Exception If validation of $value is impossible
	 * @see Zend_Validate_Interface::isValid()
	 */
	public function isValid($value, $formValues = array()) {
        $this->_setValue($value);
        
        $dbconn = VGS_DB_Conn_Singleton::getInstance();
		$secprof = new Sec_Profiles($dbconn);
        
		$profileRec = $secprof->retrieveByID($value);
		if (trim($profileRec['PRF_PROFILE_ID']) != $value) {
            $this->_error();
            return false;
		} else {
            return true;
		}
    }	
}

?>