<?php
namespace polarbev;

require_once 'front.php';

class Environment {

	const ENV_TEST = 'TEST';
	const ENV_PROD = 'PROD';
	const ENV_PLT = 'PLT';  
	  
	public static function isTestEnvironment() {
		return self::getEnvironment() == self::ENV_TEST;
	}

	public static function isProductionEnvironment() {
		return self::getEnvironment() == self::ENV_PROD;
	}
	
	public static function isPilotEnvironment() {
		return self::getEnvironment() == self::ENV_PLT;
	}
	
	public static function getEnvironment() {
		return $GLOBALS['polar_ini']['db2']['environment'];
	}
	
	/*
	 * DB_Conn, DB2_Adapter, IBMi_Connection
	 */
	public static function getUID() {
		return $GLOBALS['polar_ini']['db2']['db2_user'];
	}
	
	/*
	 * DB_Conn, DB2_Adapter, IBMi_Connection
	 */
	public static function getPWD() {
		return $GLOBALS['polar_ini']['db2']['db2_pswd'];
	}
		
}
?>