<?php
require_once '../forms/VGS_Form.php';
require_once '../model/Pipe_Type_Master.php';
require_once '../common/validators/Login_Validator.php';


class LoginForm extends VGS_Form 
{
	
	public function __construct( $conn ) {
		parent::__construct ( $conn );
		$this->setDefaultElements( );
		$this->screen_title = 'Login to the W/O System';
	}
	public function createRecord() {	}
	public function updateRecord() {	}
	public function retrieveRecord() {	}
		
	public function setDefaultElements( ) {
		$loginFields = 'USER, PSWD';
		$this->fh->addCustomMetaDatum('USER', 'User ID', 'VARCHAR', 12);
		$this->fh->addCustomMetaDatum('PSWD', 'Password', 'VARCHAR', 12);
		$this->fh->addFieldGroup( $loginFields, 'login', 'Enter Credentials');
		$this->fh->setElementsProperties( $loginFields, 'required', 'true');
		$this->fh->setElementsProperties( 'PSWD', 'input_type', 'password');
		
		// This creates Zend_Form_Elements out of the meta data
		$this->fh->addElementsFromMetaData();
		$this->addElements ( $this->fh->getElements() );

		$this->getElement('USER')->addValidator(new Login_Validator());
		$this->getElement('PSWD')->addValidator(new Login_Validator());
//		var_dump($this);
	}


	public function activate() {
		if ($_SERVER['REQUEST_METHOD'] == 'GET') { 
			$this->loadScreen();
		} else {
			$this->processScreen();
		}
	}

	public function loadScreen() {
		$this->reset();
	}
	 
	public function processScreen() {
		if ($this->validate()) {
			$this->goToMainMenu();
		} else {
			
			$this->populate($this->inputs);
		} 
	}

	public function goToMainMenu() {
		// Redirect to the originally requested page
		header("Location: menuMainCtrl.php");
		exit;
	}
}
