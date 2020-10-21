<?php 
namespace polarbev;
require_once 'DB_Conn.php';
require_once 'ToolkitService.php';
use \ToolkitService as ToolkitService;
use \Exception  as Exception;

class IBMi_Connection {
	private  static $tkConn;
	private $errmsg; 

	/**
	 * Use this to connect with default user/password as defined in DB_Conn class. 
	 */ 
	public function connect_default() {
		$user = Environment::getUID();
		$pswd = Environment::getPWD();
		return $this->connect($user, $pswd);
	}	
	
	/**
	 * Use this to connect with a specific user/pswd
	 * @param string $user
	 * @param string $pswd
	 */
	public function connect($user, $pswd = '') {
		// If user is not already connected, create a new connection
		if (!isset($this->tkConn) || !is_resource($this->tkConn)) 
		{
			try {
				$this->tkConn = ToolkitService::getInstance('*LOCAL', $user, $pswd);
			} catch (Exception $e) {
				$this->errmsg = $e->getCode()
				. ' - ' . $e->getMessage();
				return false;
			}
			
			$this->tkConn->setOptions(array('stateless' => true));
			$this->tkConn->setToolkitServiceParams(array('v5r4' => false));
			// Note: new OO toolkit will use system naming by default (per A. Seiden, 5/25/2013)
			if (! $this->tkConn ) {
				$this->errmsg = $this->tkConn->getErrorCode() 
					  . ' - ' . $this->tkConn->getErrorMsg();
				return false;
			}
		} 

		return $this->tkConn;
	}
	
	public function getConnection() {
		return $this->tkConn;
	}
		
	/**
	 * Retrieve error message if connection failed
	 */
	public function getErrorMessage() {
		return $this->errmsg;
	}
	
}
