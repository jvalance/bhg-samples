<?php 
require_once '../model/VGS_DB_Conn_Singleton.php';
/**
 * @deprecated
 * This has been superceded by the VGS_DB_Conn_Singleton class.
 * @author John
 */
class VGS_DB_Conn {
	public $connectionResource;
	private $user = 'WOTEST';
	private $pswd = 'AA847$$WOT';
	private $conn_options = array(
		'i5_naming'=> DB2_I5_NAMING_ON 
	);

	public function __construct() {
		$this->connectionResource =  
			db2_pconnect('*LOCAL', 
					$this->user, 
					$this->pswd, 
					$this->conn_options)
			 	or die("Connection failed! ". db2_conn_errormsg());

//		$this->swapAuthority();
	}
	
	private function swapAuthority() {
		if (!isset($_SESSION['current_user'])) 
			return true;
		
    	$sql = 'call spSwapUserProfile(?, ?)';
    	$stmt = db2_prepare($this->connectionResource, $sql);

    	$toProfile = $_SESSION['current_user'];
    	$toPswd = '*NOPWDCHK';
    	
    	db2_bind_param($stmt, 1, "toProfile", DB2_PARAM_INOUT);
		db2_bind_param($stmt, 2, "toPswd", DB2_PARAM_INOUT);
		
		db2_execute($stmt);
	}
	
}
