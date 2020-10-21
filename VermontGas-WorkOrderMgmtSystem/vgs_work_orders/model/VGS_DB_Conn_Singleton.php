<?php 
require_once '../model/VGS_DB_Conn.php';
require_once '../model/Security.php';

/**
 * Note: private constructor! This is a singleton class; 
 * To instantiate, you must use VGS_DB_Conn_Singleton::getInstance(); 
 * This will ensure that only one DB2 connection is created per session.
 */
class VGS_DB_Conn_Singleton {
	const DB_PROD = 'PROD';
	const DB_TEST = 'TEST';
	const DB_DEV = 'DEV';
	
	const PROD_UID = 'WOPROD';
	const PROD_PWD = 'AA847$$WOP';

	const TEST_UID = 'WOTEST';
	const TEST_PWD = 'AA847$$WOT';

	const DEV_UID = 'WODEV';
	const DEV_PWD = 'AA847$$WOD';
	
	const LAWSON_ENV_TEST = "LAW9T";
	const LAWSON_ENV_PROD = "LAW9";
	
	const ENV_FOLDER_PROD = 'wo';
	const ENV_FOLDER_TEST = 'wotest';
	const ENV_FOLDER_DEV = 'wodev';
	
	private static $connectionResource; 
	
	private static $conn_options = array(
		'i5_naming'=> DB2_I5_NAMING_ON
	);

	/**
	 * Note: private constructor! This is a singleton class; To instantiate, 
	 * you must use VGS_DB_Conn_Singleton::getInstance(); 
	 * 
	 */
	private function __construct() {
	}

	public static function isDevEnvironment() {
		return self::getEnvironment() == VGS_DB_Conn_Singleton::DB_DEV; 
	}

	public static function isTestEnvironment() {
		return self::getEnvironment() == VGS_DB_Conn_Singleton::DB_TEST; 
	}

	public static function isProductionEnvironment() {
		return self::getEnvironment() == VGS_DB_Conn_Singleton::DB_PROD; 
	}
	
	public static function getEnvironment() {
		if (preg_match('/wotest/', $_SERVER["SCRIPT_FILENAME"])) {
			return VGS_DB_Conn_Singleton::DB_TEST; 

		} elseif (preg_match('/wodev.*/', $_SERVER["SCRIPT_FILENAME"])) { 
			return VGS_DB_Conn_Singleton::DB_DEV; 
		
		} elseif (preg_match('/wo/', $_SERVER["SCRIPT_FILENAME"])) { 
			return VGS_DB_Conn_Singleton::DB_PROD; 
		} 
		
		return ''; 
	}

	public static function getEnvBaseFolder() {
		$baseFolder = '';
		$path = $_SERVER["SCRIPT_FILENAME"];
		$envStartPos = strpos($path, '/wo') + 1;
		$envEndPos = strpos($path, '/', $envStartPos);
		$length = $envEndPos - $envStartPos;
		$baseFolder = substr($path, $envStartPos, $length);
		
		return $baseFolder;
	}
	
	public static function getUID() {
		if (self::isProductionEnvironment()) {
			return self::PROD_UID;
		}
		if (self::isTestEnvironment()) {
			return self::TEST_UID;
		}
		if (self::isDevEnvironment()) {
			return self::DEV_UID;
		}
		return '';
	}
	
	public static function getPWD() {
		if (self::isProductionEnvironment()) {
			return self::PROD_PWD;
		}
		if (self::isTestEnvironment()) { 
			return self::TEST_PWD;
		}
		if (self::isDevEnvironment()) {
			return self::DEV_PWD;
		}
		return '';
	}
	
	public static function getInstance()
	{
		// This is where singleton nature is defined: If connection already exists, return
		// that one. Each request will only create a single DB2 connection. 
	    if (!self::$connectionResource) {
	    	  //pre_dump("Connecting with user " . self::getUID());
	        self::$connectionResource = 
					db2_connect('*LOCAL', 
									self::getUID(), 
									self::getPWD(), 
									self::$conn_options)
					or die("Connection failed! ". db2_conn_errormsg());
	        Security::swap_ibm_UsrPrf(); // set job USRPRF to current user
	    }
	    return self::$connectionResource;
	}  

	public static function dumpDb2Job($msg = ' ') {
		echo "$msg<br>";    	
		
		if (isset(self::$connectionResource)) {
			$stmt = db2_prepare( self::$connectionResource, "call spShowJob('$msg')");
			if (!$stmt) echo db2_stmt_errormsg() . '<br>';
			$result = db2_execute($stmt);
			if (!$result) echo db2_stmt_errormsg() . '<br>';
		} else {
			echo "Connection does not exist.<br>";
		}
		
	}
	
	public static function getLawsonEnvironment() {
    	
		$lawsonEnv = VGS_DB_Conn_Singleton::LAWSON_ENV_TEST;
		
    	switch (self::getEnvironment()) {
    		case VGS_DB_Conn_Singleton::DB_TEST:
    		case VGS_DB_Conn_Singleton::DB_DEV:
    			$lawsonEnv = VGS_DB_Conn_Singleton::LAWSON_ENV_TEST;
    			break;
    		case VGS_DB_Conn_Singleton::DB_PROD:
    			$lawsonEnv = VGS_DB_Conn_Singleton::LAWSON_ENV_PROD;
    			break;
    		default:
    			$lawsonEnv = '';
    	}
		
    	return $lawsonEnv;
	}
}
