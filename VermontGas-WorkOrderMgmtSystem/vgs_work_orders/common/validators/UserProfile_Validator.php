<?php
require_once ('Zend/Validate/Abstract.php');
require_once ('../model/Security.php');
require_once ('../model/Sec_Profiles.php');
require_once ('../model/VGS_DB_Conn_Singleton.php');

/** 
 * @author John Valance
 * 
 */
class UserProfile_Validator extends Zend_Validate_Abstract {

	protected $_messageTemplates = array(
        "The profile ID entered (%value%) is not a valid USER profile on the IBM iSeries.",
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
        
		$secProfile = new Sec_Profiles($dbconn);
		$profileInfo = $secProfile->getProfileInfo($value);
		if ($profileInfo['profileType'] != 'USER') {
            $this->_error();
            return false;
		} 
 
        return true;
    }	
}

?>