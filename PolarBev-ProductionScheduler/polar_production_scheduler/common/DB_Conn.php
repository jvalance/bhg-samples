<?php

namespace polarbev;
require_once 'Environment.php';
use polarbev\Environment as Environment;

/**
 * Note: private constructor! This is a singleton class;
 * To instantiate, you must use DB_Conn::getInstance();
 * This will ensure that only one DB2 connection is created per session.
 */
class DB_Conn {
	private static $connectionResource;

	private static $conn_options = array(
		'i5_naming'=> DB2_I5_NAMING_ON
	);

	/**
	 * Note: private constructor! This is a singleton class; To instantiate,
	 * you must use DB_Conn::getInstance();
	 *
	 */
	private function __construct() {
	}

	public static function getInstance()
	{
		// This is where singleton nature is defined: If connection already exists, return
		// that one. Each request will only create a single DB2 connection.
	    if (!self::$connectionResource) {
 	    	//echo "Connecting with user=" . Environment::getUID() . ", pswd=" . Environment::getPWD() . ', options:<br>';
 	    	//pre_dump(self::$conn_options);

	        self::$connectionResource =
					db2_connect('*LOCAL',
									Environment::getUID(),
									Environment::getPWD(),
									self::$conn_options)
					or die("Connection failed! ". db2_conn_errormsg());
	        //Security::swap_ibm_UsrPrf(); // set job USRPRF to current user
	    }
	    return self::$connectionResource;
	}

}
