<?php 
require_once '../model/VGS_DB_Conn_Singleton.php';
require_once 'ToolkitService.php';

class VGS_i5_Conn {
	private  static $tkConn;
	private $errmsg; 

	/**
	 * Use this to connect with default user/password. 
	 */ 
	public function connect_default() {
		$user = VGS_DB_Conn_Singleton::getUID();
		$pswd = VGS_DB_Conn_Singleton::getPWD();
		// If user is already connected, use that connection
		return $this->connect($user, $pswd);
	}	
	
	/**
	 * Use this to connect with a specific user/pswd
	 * @param string $user
	 * @param string $pswd
	 */
	public function connect($user, $pswd = '') {
		if (!isset($this->tkConn) || !is_resource($this->tkConn)) 
		{
			$this->tkConn = ToolkitService::getInstance('*LOCAL', $user, $pswd);
			$this->tkConn->setOptions(array('stateless' => true));
// 			$this->tkConn->setToolkitServiceParams(
// 					array('v5r4' => false)
// 			);
			// Note: new OO toolkit will use system naming by default (per A. Seiden, 5/25/2013)
			if (! $this->tkConn ) {
				$this->errmsg = $this->tkConn->getErrorCode() 
					  . ' - ' . $this->tkConn->getErrorMsg();
				return false;
			}
		} 

		return $this->tkConn;
	}
	
	public function get_i5connResource() {
		return $this->tkConn;
	}
	
	public function get_i5error() {
		return $this->tkConn->getErrorCode() 
			. ' - ' . $this->tkConn->getErrorMsg();;
	}
	
	/**
	 * Retrieve error message if connection failed
	 */
	public function getErrorMessage() {
		return $this->errmsg;
	}
	
}
