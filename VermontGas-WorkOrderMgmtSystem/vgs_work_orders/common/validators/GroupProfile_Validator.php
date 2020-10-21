<?php
require_once ('Zend/Validate/Abstract.php');
require_once ('../model/Sec_Profiles.php');
require_once ('../model/VGS_DB_Conn_Singleton.php');

/** 
 * @author John Valance
 * 
 */
class GroupProfile_Validator extends Zend_Validate_Abstract {

	protected $_messageTemplates = array(
		'NOT_FOUND' => "The ID entered (%value%) is not a valid Profile ID in the SEC_PROFILES table.",
		'NOT_GROUP' => "The ID entered (%value%) is not a GROUP profile in the SEC_PROFILES table."
	);	
	
	/**
	 * Check that value passed is a valid Group Profile ID on the Sec_Profiles table.
	 * @param   mixed $value
	 * @return  boolean
	 * @throws  Zend_Validate_Exception If validation of $value is impossible
	 * @see Zend_Validate_Interface::isValid()
	 */
	public function isValid($value, $formValues = array()) {
      $this->_setValue($value);
        
      $dbconn = VGS_DB_Conn_Singleton::getInstance();
		$secProfObj = new Sec_Profiles($dbconn);
        
		$secProfRec = $secProfObj->retrieveByID($value);
		if (trim($secProfRec['PRF_PROFILE_ID']) != $value) {
            $this->_error('NOT_FOUND');
            return false;
		} elseif (trim($secProfRec['PRF_PROFILE_TYPE']) != 'GROUP') {
            $this->_error('NOT_GROUP');
			return false;
		}
		return true;
    }	
}

?>