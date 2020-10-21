<?php

namespace User\Validator;

use Zend\Validator\AbstractValidator;

class MultipleEmail extends AbstractValidator {
	const ERROR = 'length';
	protected $messageTemplates = array(
			self::ERROR => "'%value%' Not a valid email address"
	);
	public function isValid($value)
	{
		$isValid = true;
		if (!empty($value)) {
			 
			$emailAddressesArray = explode(',', $value);
			
			foreach($emailAddressesArray as $emailAdd){
				if (filter_var(trim($emailAdd), FILTER_VALIDATE_EMAIL)) {
					$isValid = true;
				} else {
					$this->error(self::ERROR);
					$isValid = false;
					break;
				}
			}
		}
		return $isValid;
		 
	}
}