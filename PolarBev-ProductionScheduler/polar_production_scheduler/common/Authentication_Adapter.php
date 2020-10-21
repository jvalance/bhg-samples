<?php
namespace polarbev;
require_once 'IBMi_Connection.php';
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use polarbev\IBMi_Connection;

class Authentication_Adapter implements AdapterInterface
{
	protected $user = '';
	protected $pswd = '';
	protected $ibmi_conn;
		
	/**
	 * Sets username and password for authentication
	 *
	 * @return void
	 */
	public function __construct($username, $password)
	{
		$this->user = $username;
		$this->pswd = $password;
		$this->ibmi_conn = new IBMi_Connection();
	}

	/**
	 * Performs an authentication attempt
	 *
	 * @return \Zend\Authentication\Result
	 * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface
	 *               If authentication cannot be performed
	 */
	public function authenticate()
	{
		$connResult = 
			$this->ibmi_conn->connect($this->user, $this->pswd);
		
		if ( !$connResult ) {
			return new Result(
					Result::FAILURE_UNCATEGORIZED, 
					null, 
					array( $this->ibmi_conn->getErrorMessage())
			);
		} else {
			return new Result(
					Result::SUCCESS, 
					$this->user, 
					array());
		}
	}
}
?>