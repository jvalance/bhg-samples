<?php 
namespace polarbev;
require_once 'autoloader.php';
use Zend\Db\Adapter\Adapter as DbAdapter;
require_once 'Environment.php';
use polarbev\Environment as Environment;
 
/**
 * Note: private constructor! This is a singleton class; 
 * To instantiate, you must use DB2_Adapter::getInstance(); 
 * This will ensure that only one DB2 connection is created per session.
 */
class DB2_Adapter {
	
	private static $adapter = NULL; 

	/**
	 * Note: private constructor! This is a singleton class; To instantiate, 
	 * you must use DB2_Adapter::getInstance(); 
	 * 
	 */
	private function __construct() {
	}

	public static function getInstance()
	{
		// This is where singleton nature is defined: If connection already exists, return
		// that one. Each request will only create a single DB2 connection. 
		if (self::$adapter === NULL) {

			// DB2 Connection
			self::$adapter = new DbAdapter(array(
					'driver' => 'IbmDb2',
					'database' => '*LOCAL',
					'username' => Environment::getUID(),
					'password' => Environment::getPWD(),
					'driver_options' => array(
					    'i5_naming' => DB2_I5_NAMING_ON
					),
					'platform_options' => array('quote_identifiers' => false)
			));
				
			/** TODO: Do we need to do a profile swap, to ensure system security is honored? */
			//Security::swap_ibm_UsrPrf(); // set job USRPRF to current user
	    }
	    return self::$adapter;
	}  

}
