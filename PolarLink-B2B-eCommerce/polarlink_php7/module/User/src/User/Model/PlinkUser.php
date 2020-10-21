<?php

namespace User\Model;

// Add these import statements
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\Factory as InputFactory;

class PlinkUser implements InputFilterAwareInterface {

 //   protected $table = 'PLINK_USER';
    public $PLU_ID;
    public $PLU_USER_ID;
    public $PLU_FIRST_NAME;
    public $PLU_LAST_NAME;
    public $PLU_PASSWORD;
    public $PLU_CUSTNO;
    public $PLU_DFT_SHIPTO;
    public $PLU_DFT_SHIP_METHOD;
    public $PLU_POLAR_CSR;
    public $PLU_PLINK_ADMIN;
    public $PLU_STATUS;
    public $PLU_EMAIL_ADDRESS;
    protected $inputFilter;
    protected $_dbAdapter;

    public function setDbAdapter($dbAdapter) {
        $this->_dbAdapter = $dbAdapter;
    }

    public function getDbAdapter() {
        return $this->_dbAdapter;
    }

    public function exchangeArray($data) {
        //print_r($data);die('Hello');
        $this->PLU_ID = (!empty($data['PLU_ID'])) ? $data['PLU_ID'] : null;
        $this->PLU_USER_ID = (!empty($data['PLU_USER_ID'])) ? $data['PLU_USER_ID'] : null;
        $this->PLU_FIRST_NAME = (!empty($data['PLU_FIRST_NAME'])) ? $data['PLU_FIRST_NAME'] : null;
        $this->PLU_LAST_NAME = (!empty($data['PLU_LAST_NAME'])) ? $data['PLU_LAST_NAME'] : null;
        $this->PLU_PASSWORD = (!empty($data['PLU_PASSWORD'])) ? $data['PLU_PASSWORD'] : null;
        $this->PLU_CUSTNO = (!empty($data['PLU_CUSTNO'])) ? $data['PLU_CUSTNO'] : null;
        $this->PLU_DFT_SHIPTO = (!empty($data['PLU_DFT_SHIPTO'])) ? $data['PLU_DFT_SHIPTO'] : null;
        $this->PLU_DFT_SHIP_METHOD = (!empty($data['PLU_DFT_SHIP_METHOD'])) ? $data['PLU_DFT_SHIP_METHOD'] : null;
        $this->PLU_POLAR_CSR = (!empty($data['PLU_POLAR_CSR'])) ? $data['PLU_POLAR_CSR'] : null;
        $this->PLU_PLINK_ADMIN = (!empty($data['PLU_PLINK_ADMIN'])) ? $data['PLU_PLINK_ADMIN'] : null;
        $this->PLU_STATUS = (!empty($data['PLU_STATUS'])) ? $data['PLU_STATUS'] : null;
        $this->PLU_EMAIL_ADDRESS = (!empty($data['PLU_EMAIL_ADDRESS'])) ? $data['PLU_EMAIL_ADDRESS'] : null;
    }

    // Add the following method:
    public function getArrayCopy() {
        return get_object_vars($this);
    }

    // Add content to these methods:
    public function setInputFilter(InputFilterInterface $inputFilter) {
        throw new \Exception("Not used");
    }

    public function getInputFilter() {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    // These are the input filters for the login form
    public function getLoginInputFilter() {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                        'name' => 'PLU_USER_ID',
                        'filters' => array(
                            array(
                                'name' => 'StripTags'
                            ),
                            array(
                                'name' => 'StringTrim'
                            )
                        ),
                        'validators' => array(
                        		array(
                        				'name' => 'NotEmpty',
                        				'options' => array(
                        						'messages' => array(
                        								\Zend\Validator\NotEmpty::IS_EMPTY => 'User ID is required'
                        						)
                        				),
                        				'break_chain_on_failure' => true
                        		),
                            array(
                                'name' => 'StringLength',
                                'options' => array(
                                    'encoding' => 'UTF-8',
                                    'max' => '15'
                                ),
                                'break_chain_on_failure' => true
                            )

                      /*  		,
                            array(
                                'name' => 'Db\RecordExists',
                                'options' => array(
                                    'table' => $this->table,
                                    'field' => 'PLU_USER_ID',
                                    'adapter' => $this->getDbAdapter(),
                                    'message' => 'This User ID is not registered with us.'
                                ),
                                'break_chain_on_failure' => true
                            ) */
                        )
            )));

            $inputFilter->add($factory->createInput(array(
                        'name' => 'PLU_PASSWORD',
                        'filters' => array(
                            array(
                                'name' => 'StripTags'
                            ),
                            array(
                                'name' => 'StringTrim'
                            )
                        ),
                        'validators' => array(
                            array(
                                'name' => 'NotEmpty',
                                'options' => array(
                                    'messages' => array(
                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Password is required'
                                    )
                                ),
                                'break_chain_on_failure' => true
                            ),
                            array(
                                'name' => 'StringLength',
                                'options' => array(
                                    'encoding' => 'UTF-8',
                                    'max' => '20',
                                    'messages' => array(
                                        \Zend\Validator\StringLength::TOO_LONG => 'Password can not be more than 20 characters long'
                                    )
                                ),
                                'break_chain_on_failure' => true
                            ),
                        )
            )));


            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }


    // These are the input filters for the order header form
    public function getOrderHeaderInputFilter() {
    	if (!$this->inputFilter) {
    		$inputFilter = new InputFilter();
    		$factory = new InputFactory();

    		$inputFilter->add($factory->createInput(array(
    				'name' => 'OH_PO1',
    				'required' => false,
    				'filters' => array(
    						array(
    								'name' => 'StripTags'
    						),
    						array(
    								'name' => 'StringTrim'
    						)
    				),
    				'validators' => array(
    						array(
    								'name' => 'StringLength',
    								'options' => array(
    										'encoding' => 'UTF-8',
    										'max' => '23',
    										'messages' => array(
    												\Zend\Validator\StringLength::TOO_LONG => 'Primary PO# can not be more than 23 characters long'
    										)
    								),
    								'break_chain_on_failure' => true
    						)

    				)
    		)));

    		$inputFilter->add($factory->createInput(array(
    				'name' => 'OH_PO2',
    				'required' => false,
    				'filters' => array(
    						array(
    								'name' => 'StripTags'
    						),
    						array(
    								'name' => 'StringTrim'
    						)
    				),
    				'validators' => array(
    						array(
    								'name' => 'StringLength',
    								'options' => array(
    										'encoding' => 'UTF-8',
    										'max' => '23',
    										'messages' => array(
    												\Zend\Validator\StringLength::TOO_LONG => 'Alternate PO# 1 can not be more than 23 characters long'
    										)
    								),
    								'break_chain_on_failure' => true
    						),
    				)
    		)));


    		$inputFilter->add($factory->createInput(array(
    				'name' => 'OH_PO3',
    				'required' => false,
    				'filters' => array(
    						array(
    								'name' => 'StripTags'
    						),
    						array(
    								'name' => 'StringTrim'
    						)
    				),
    				'validators' => array(
    						array(
    								'name' => 'StringLength',
    								'options' => array(
    										'encoding' => 'UTF-8',
    										'max' => '23',
    										'messages' => array(
    												\Zend\Validator\StringLength::TOO_LONG => 'Alternate PO# 2 can not be more than 23 characters long'
    										)
    								),
    								'break_chain_on_failure' => true
    						),
    				)
    		)));

    		$this->inputFilter = $inputFilter;
    	}

    	return $this->inputFilter;
    }


    // These are the input filters for the announcement Form
    public function getAnnouncementInputFilter() {
    	if (!$this->inputFilter) {
    		$inputFilter = new InputFilter();
    		$factory = new InputFactory();

    		$inputFilter->add($factory->createInput(array(
    				'name' => 'CUST_TYPE',
    				'required' => false
    		)));

    		$inputFilter->add($factory->createInput(array(
    				'name' => 'FACILITY',
    				'required' => false,
    		)));
    		$inputFilter->add($factory->createInput(array(
    				'name' => 'START_DATE',
    				'filters' => array(
    						array(
    								'name' => 'StripTags'
    						),
    						array(
    								'name' => 'StringTrim'
    						)
    				),
    				'validators' => array(
    						array(
    								'name' => 'NotEmpty',
    								'options' => array(
    										'messages' => array(
    												\Zend\Validator\NotEmpty::IS_EMPTY => 'Start Date is required'
    										)
    								),
    								'break_chain_on_failure' => true
    						),
    						array(
							        'name'=>'Date',
							        'break_chain_on_failure'=>true,
							        'options'=>array(
							            'format'=>'m/d/Y',
							            'messages'=>array(
							                'dateFalseFormat'=>'Invalid date format, must be mm/dd/yyyy',
							                'dateInvalidDate'=>'Invalid date, must be mm/dd/yyyy'
							            ),
							        ),
							    ),

    				)
    		)));

    		$inputFilter->add($factory->createInput(array(
    				'name' => 'END_DATE',
    				'filters' => array(
    						array(
    								'name' => 'StripTags'
    						),
    						array(
    								'name' => 'StringTrim'
    						)
    				),
    				'validators' => array(
    						array(
    								'name' => 'NotEmpty',
    								'options' => array(
    										'messages' => array(
    												\Zend\Validator\NotEmpty::IS_EMPTY => 'End Date is required'
    										)
    								),
    								'break_chain_on_failure' => true
    						),
    						array(
						        'name'=>'Date',
						        'break_chain_on_failure'=>true,
						        'options'=>array(
						            'format'=>'m/d/Y',
						            'messages'=>array(
						                'dateFalseFormat'=>'Invalid date format, must be mm/dd/yyyy',
						                'dateInvalidDate'=>'Invalid date, must be mm/dd/yyyy'
						            ),
						        ),
						    ),

    				)
    		)));

    		$inputFilter->add($factory->createInput(array(
    				'name' => 'MESSAGE',
    				'filters' => array(
    						array(
    								'name' => 'StripTags'
    						),
    						array(
    								'name' => 'StringTrim'
    						)
    				),
    				'validators' => array(
    						array(
    								'name' => 'NotEmpty',
    								'options' => array(
    										'messages' => array(
    												\Zend\Validator\NotEmpty::IS_EMPTY => 'Message is required'
    										)
    								),
    								'break_chain_on_failure' => true
    						),
    						array(
    								'name' => 'StringLength',
    								'options' => array(
    										'encoding' => 'UTF-8',
    										'max' => '2500',
    										'messages' => array(
    												\Zend\Validator\StringLength::TOO_LONG => 'Message can not be more than 2500 characters long'
    										)
    								),
    								'break_chain_on_failure' => true
    						),
    				)
    		)));


    		$this->inputFilter = $inputFilter;
    	}

    	return $this->inputFilter;
    }

    // These are the input filters for the order header form
    public function getOrderHistorySearchFilter() {
    	if (!$this->inputFilter) {
    		$inputFilter = new InputFilter();
    		$factory = new InputFactory();
    	}
    	return $this->inputFilter;
    }

    // These are the input filters for the customer add Form
    public function getCustomerAddInputFilter() {
    	if (!$this->inputFilter) {
    		$inputFilter = new InputFilter();
    		$factory = new InputFactory();

    		$inputFilter->add($factory->createInput(array(
    				'name' => 'csr_cust_group',
    				'filters' => array(
    						array(
    								'name' => 'StripTags'
    						),
    						array(
    								'name' => 'StringTrim'
    						)
    				),
    				'validators' => array(
    						array(
    								'name' => 'NotEmpty',
    								'options' => array(
    										'messages' => array(
    												\Zend\Validator\NotEmpty::IS_EMPTY => 'Customer Group is required'
    										)
    								),
    								'break_chain_on_failure' => true
    						),
    						array(
    								'name' => 'StringLength',
    								'options' => array(
    										'encoding' => 'UTF-8',
    										'max' => '10',
    										'messages' => array(
    												\Zend\Validator\StringLength::TOO_LONG => 'Customer Group  can not be more than 10 characters long'
    										)
    								),
    								'break_chain_on_failure' => true
    						),

    				)
    		)));

    		$inputFilter->add($factory->createInput(array(
    				'name' => 'csr_user_name',
    				'filters' => array(
    						array(
    								'name' => 'StripTags'
    						),
    						array(
    								'name' => 'StringTrim'
    						)
    				),
    				'validators' => array(
    						array(
    								'name' => 'NotEmpty',
    								'options' => array(
    										'messages' => array(
    												\Zend\Validator\NotEmpty::IS_EMPTY => 'Name for Polarlink is required'
    										)
    								),
    								'break_chain_on_failure' => true
    						)

    				)
    		)));
    		$inputFilter->add($factory->createInput(array(
    				'name' => 'csr_email_address',
    				'filters' => array(
    						array(
    								'name' => 'StripTags'
    						),
    						array(
    								'name' => 'StringTrim'
    						)
    				),
    				'required' => false,
    				'validators' => array(
    						array(
    								'name' => 'User\Validator\MultipleEmail',
    								'break_chain_on_failure' => true
    						),

    				)
    		)));

    		$inputFilter->add($factory->createInput(array(
    				'name' => 'csr_status',
    				'filters' => array(
    						array(
    								'name' => 'StripTags'
    						),
    						array(
    								'name' => 'StringTrim'
    						)
    				),
    				'validators' => array(
    						array(
    								'name' => 'NotEmpty',
    								'options' => array(
    										'messages' => array(
    												\Zend\Validator\NotEmpty::IS_EMPTY => 'Polarlink Status is required'
    										)
    								),
    								'break_chain_on_failure' => true
    						)

    				)
    		)));


    		$this->inputFilter = $inputFilter;
    	}


    	return $this->inputFilter;
    }


    // These are the input filters for the user add form
    public function getUserAddInputFilter() {
    	if (!$this->inputFilter) {
    		$inputFilter = new InputFilter();
    		$factory = new InputFactory();
    		$inputFilter->add($factory->createInput(array(
    				'name' => 'PLU_DFT_UOM',
    				'required' => false,
    		)));
    		$inputFilter->add($factory->createInput(array(
    				'name' => 'PLU_DFT_SHIP_METHOD',
    				'required' => false,
    		)));

    		$inputFilter->add($factory->createInput(array(
    				'name' => 'PLU_USER_ID',
    				'filters' => array(
    						array(
    								'name' => 'StripTags'
    						),
    						array(
    								'name' => 'StringTrim'
    						)
    				),
    				'validators' => array(
    						array(
    								'name' => 'NotEmpty',
    								'options' => array(
    										'messages' => array(
    												\Zend\Validator\NotEmpty::IS_EMPTY => 'User ID is required'
    										)
    								),
    								'break_chain_on_failure' => true
    						),
    						array(
    								'name' => 'StringLength',
    								'options' => array(
    										'encoding' => 'UTF-8',
    										'max' => '15',
    										'messages' => array(
    												\Zend\Validator\StringLength::TOO_LONG => 'User ID  can not be more than 15 characters long'
    										)
    								),
    								'break_chain_on_failure' => true
    						),
    					
    						array(
    								'name' => 'Regex',
    								'options' => array(
    										'pattern' => '/^[a-zA-Z]{1}/',
    										'messages' => array(
    												\Zend\Validator\Regex::NOT_MATCH => 'First character should be alphabetic'
    										)

    								)
    						),

    						array(
    								'name' => 'Regex',
    								'options' => array(
    										'pattern' => '/^\w+$/',
    										'messages' => array(
    												\Zend\Validator\Regex::NOT_MATCH => 'Spaces and special characters are not allowed (except underscore)'
    										)

    								)
    						)


    				)
    		)));

    		$inputFilter->add($factory->createInput(array(
    				'name' => 'PLU_FIRST_NAME',
    				'filters' => array(
    						array(
    								'name' => 'StripTags'
    						),
    						array(
    								'name' => 'StringTrim'
    						)
    				),
    				'validators' => array(
    						array(
    								'name' => 'NotEmpty',
    								'options' => array(
    										'messages' => array(
    												\Zend\Validator\NotEmpty::IS_EMPTY => 'First Name is required'
    										)
    								),
    								'break_chain_on_failure' => true
    						),
    						array(
    								'name' => 'StringLength',
    								'options' => array(
    										'encoding' => 'UTF-8',
    										'max' => '30',
    										'messages' => array(
    												\Zend\Validator\StringLength::TOO_LONG => 'First Name can not be more than 30 characters long'
    										)
    								),
    								'break_chain_on_failure' => true
    						)

    				)
    		)));

    		$inputFilter->add($factory->createInput(array(
    				'name' => 'PLU_LAST_NAME',
    				'filters' => array(
    						array(
    								'name' => 'StripTags'
    						),
    						array(
    								'name' => 'StringTrim'
    						)
    				),
    				'validators' => array(
    						array(
    								'name' => 'NotEmpty',
    								'options' => array(
    										'messages' => array(
    												\Zend\Validator\NotEmpty::IS_EMPTY => 'Last Name is required'
    										)
    								),
    								'break_chain_on_failure' => true
    						),
    						array(
    								'name' => 'StringLength',
    								'options' => array(
    										'encoding' => 'UTF-8',
    										'max' => '40',
    										'messages' => array(
    												\Zend\Validator\StringLength::TOO_LONG => 'Last Name can not be more than 40 characters long'
    										)
    								),
    								'break_chain_on_failure' => true
    						)

    				)
    		)));

    		$inputFilter->add($factory->createInput(array(
    				'name' => 'PLU_PASSWORD',
    				'filters' => array(
    						array(
    								'name' => 'StripTags'
    						),
    						array(
    								'name' => 'StringTrim'
    						)
    				),
    				'validators' => array(
    						array(
    								'name' => 'NotEmpty',
    								'options' => array(
    										'messages' => array(
    												\Zend\Validator\NotEmpty::IS_EMPTY => 'Password is required'
    										)
    								),
    								'break_chain_on_failure' => true
    						),
    						array(
    								'name' => 'StringLength',
    								'options' => array(
    										'encoding' => 'UTF-8',
    										'max' => '30',
    										'messages' => array(
    												\Zend\Validator\StringLength::TOO_LONG => 'Password can not be more than 30 characters long'
    										)
    								),
    								'break_chain_on_failure' => true
    						)

    				)
    		)));

    		$inputFilter->add($factory->createInput(array(
    				'name' => 'PLU_CONFIRM_PASSWORD',
    				'filters' => array(
    						array(
    								'name' => 'StripTags'
    						),
    						array(
    								'name' => 'StringTrim'
    						)
    				),
    				'validators' => array(
    						array(
    								'name' => 'NotEmpty',
    								'options' => array(
    										'messages' => array(
    												\Zend\Validator\NotEmpty::IS_EMPTY => 'Confirm Password is required'
    										)
    								),
    								'break_chain_on_failure' => true
    						),

    						array(
	    						'name'    => 'Identical',
	    						'options' => array(
	    								'token' => 'PLU_PASSWORD',
	    								'messages' => array(
    												\Zend\Validator\Identical::NOT_SAME => 'Password and Confirm Password do not match'
    										)
	    						)
    						)

    				)
    		)));

    		$inputFilter->add($factory->createInput(array(
    				'name' => 'PLU_EMAIL_ADDRESS',
    				'filters' => array(
    						array(
    								'name' => 'StripTags'
    						),
    						array(
    								'name' => 'StringTrim'
    						)
    				),
//     				'required' => false,
    				'validators' => array(
    						 array(
    						 'name' => 'NotEmpty',
    								'options' => array(
    										'messages' => array(
    												\Zend\Validator\NotEmpty::IS_EMPTY => 'Email Address is required'
    										)
    								),
    								'break_chain_on_failure' => true
    						),
    						array(
    							//	'name' => 'User\Validator\Email',
    								'name' => 'EmailAddress',
    		    								'options' =>array(
    				                        'domain'   => 'true',
    				                        'hostname' => 'true',
    				                        'mx'       => 'true',
    				    					'deep'     => 'true',
    		    							'min'     => '1',
    		    							'max'     => '100',
    				    					'messages' => array(
    												// \Zend\Validator\EmailAddress::domain => 'Email Address is required'
    				    							\Zend\Validator\EmailAddress::INVALID            => 'Invalid type given. String expected',
											        \Zend\Validator\EmailAddress::INVALID_FORMAT     => "Invalid email address",
											        \Zend\Validator\EmailAddress::INVALID_HOSTNAME   => "Invalid email address",
											        \Zend\Validator\EmailAddress::INVALID_MX_RECORD  => "Invalid email address",
											        \Zend\Validator\EmailAddress::INVALID_SEGMENT    => "Invalid email address",
											        \Zend\Validator\EmailAddress::DOT_ATOM           => "Invalid email address",
											        \Zend\Validator\EmailAddress::QUOTED_STRING      => "Invalid email address",
											        \Zend\Validator\EmailAddress::INVALID_LOCAL_PART => "Invalid email address",
											        \Zend\Validator\EmailAddress::LENGTH_EXCEEDED    => "Email address is too long",
    										)
    				                    ),
    								'break_chain_on_failure' => true
    						),

    				)
    		)));

    		$inputFilter->add($factory->createInput(array(
    				'name' => 'PLU_STATUS',
    				'filters' => array(
    						array(
    								'name' => 'StripTags'
    						),
    						array(
    								'name' => 'StringTrim'
    						)
    				),
    				'validators' => array(
    						array(
    								'name' => 'NotEmpty',
    								'options' => array(
    										'messages' => array(
    												\Zend\Validator\NotEmpty::IS_EMPTY => 'Polarlink Status is required'
    										)
    								),
    								'break_chain_on_failure' => true
    						)

    				)
    		)));

    		$inputFilter->add($factory->createInput(array(
    				'name' => 'PLU_USER_TYPE',
    				'validators' => array(
    						array(
    								'name' => 'NotEmpty',
    								'options' => array(
    										'messages' => array(
    												\Zend\Validator\NotEmpty::IS_EMPTY => 'User Type is required'
    										)
    								),
    								'break_chain_on_failure' => true
    						)

    				)
    		)));


    		$this->inputFilter = $inputFilter;
    	}


    	return $this->inputFilter;
    }

    // These are the input filters for the user details edit form
    public function getUserEditInputFilter() {
    	if (!$this->inputFilter) {
    		$inputFilter = new InputFilter();
    		$factory = new InputFactory();

    		$inputFilter->add($factory->createInput(array(
    				'name' => 'PLU_DFT_UOM',
    				'required' => false,
    		)));
    		$inputFilter->add($factory->createInput(array(
    				'name' => 'PLU_DFT_SHIP_METHOD',
    				'required' => false,
    		)));


    		$inputFilter->add($factory->createInput(array(
    				'name' => 'PLU_FIRST_NAME',
    				'filters' => array(
    						array(
    								'name' => 'StripTags'
    						),
    						array(
    								'name' => 'StringTrim'
    						)
    				),
    				'validators' => array(
    						array(
    								'name' => 'NotEmpty',
    								'options' => array(
    										'messages' => array(
    												\Zend\Validator\NotEmpty::IS_EMPTY => 'First Name is required'
    										)
    								),
    								'break_chain_on_failure' => true
    						),
    						array(
    								'name' => 'StringLength',
    								'options' => array(
    										'encoding' => 'UTF-8',
    										'max' => '30',
    										'messages' => array(
    												\Zend\Validator\StringLength::TOO_LONG => 'First Name can not be more than 30 characters long'
    										)
    								),
    								'break_chain_on_failure' => true
    						)

    				)
    		)));

    		$inputFilter->add($factory->createInput(array(
    				'name' => 'PLU_LAST_NAME',
    				'filters' => array(
    						array(
    								'name' => 'StripTags'
    						),
    						array(
    								'name' => 'StringTrim'
    						)
    				),
    				'validators' => array(
    						array(
    								'name' => 'NotEmpty',
    								'options' => array(
    										'messages' => array(
    												\Zend\Validator\NotEmpty::IS_EMPTY => 'Last Name is required'
    										)
    								),
    								'break_chain_on_failure' => true
    						),
    						array(
    								'name' => 'StringLength',
    								'options' => array(
    										'encoding' => 'UTF-8',
    										'max' => '40',
    										'messages' => array(
    												\Zend\Validator\StringLength::TOO_LONG => 'Last Name can not be more than 40 characters long'
    										)
    								),
    								'break_chain_on_failure' => true
    						)

    				)
    		)));

    		$inputFilter->add($factory->createInput(array(
    				'name' => 'PLU_EMAIL_ADDRESS',
    				'filters' => array(
    						array(
    								'name' => 'StripTags'
    						),
    						array(
    								'name' => 'StringTrim'
    						)
    				),
    				//     				'required' => false,
    				'validators' => array(
    						array(
    								'name' => 'NotEmpty',
    								'options' => array(
    										'messages' => array(
    												\Zend\Validator\NotEmpty::IS_EMPTY => 'Email Address is required'
    										)
    								),
    								'break_chain_on_failure' => true
    						),
    						array(
    								//	'name' => 'User\Validator\Email',
    								'name' => 'EmailAddress',
    								'options' =>array(
    										'domain'   => 'true',
    										'hostname' => 'true',
    										'mx'       => 'true',
    										'deep'     => 'true',
    										'min'     => '1',
    										'max'     => '100',
    										'messages' => array(
    												// \Zend\Validator\EmailAddress::domain => 'Email Address is required'
    												\Zend\Validator\EmailAddress::INVALID            => 'Invalid type given. String expected',
    												\Zend\Validator\EmailAddress::INVALID_FORMAT     => "Invalid email address",
    												\Zend\Validator\EmailAddress::INVALID_HOSTNAME   => "Invalid email address",
    												\Zend\Validator\EmailAddress::INVALID_MX_RECORD  => "Invalid email address",
    												\Zend\Validator\EmailAddress::INVALID_SEGMENT    => "Invalid email address",
    												\Zend\Validator\EmailAddress::DOT_ATOM           => "Invalid email address",
    												\Zend\Validator\EmailAddress::QUOTED_STRING      => "Invalid email address",
    												\Zend\Validator\EmailAddress::INVALID_LOCAL_PART => "Invalid email address",
    												\Zend\Validator\EmailAddress::LENGTH_EXCEEDED    => "Email address is too long",
    										)
    								),
    								'break_chain_on_failure' => true
    						),

    				)
    		)));

    		$inputFilter->add($factory->createInput(array(
    				'name' => 'PLU_STATUS',
    				'filters' => array(
    						array(
    								'name' => 'StripTags'
    						),
    						array(
    								'name' => 'StringTrim'
    						)
    				),
    				'validators' => array(
    						array(
    								'name' => 'NotEmpty',
    								'options' => array(
    										'messages' => array(
    												\Zend\Validator\NotEmpty::IS_EMPTY => 'Polarlink Status is required'
    										)
    								),
    								'break_chain_on_failure' => true
    						)

    				)
    		)));

    		$inputFilter->add($factory->createInput(array(
    				'name' => 'PLU_USER_TYPE',
    				'validators' => array(
    						array(
    								'name' => 'NotEmpty',
    								'options' => array(
    										'messages' => array(
    												\Zend\Validator\NotEmpty::IS_EMPTY => 'User Type is required'
    										)
    								),
    								'break_chain_on_failure' => true
    						)

    				)
    		)));

    		$this->inputFilter = $inputFilter;
    	}


    	return $this->inputFilter;
    }


    // These are the input filters for the user details edit form
    public function getUserEditInputPasswordFilter() {
    	if (!$this->inputFilter) {
    		$inputFilter = new InputFilter();
    		$factory = new InputFactory();

    		$inputFilter->add($factory->createInput(array(
    				'name' => 'PLU_DFT_UOM',
    				'required' => false,
    		)));
    		$inputFilter->add($factory->createInput(array(
    				'name' => 'PLU_DFT_SHIP_METHOD',
    				'required' => false,
    		)));


    		$inputFilter->add($factory->createInput(array(
    				'name' => 'PLU_FIRST_NAME',
    				'filters' => array(
    						array(
    								'name' => 'StripTags'
    						),
    						array(
    								'name' => 'StringTrim'
    						)
    				),
    				'validators' => array(
    						array(
    								'name' => 'NotEmpty',
    								'options' => array(
    										'messages' => array(
    												\Zend\Validator\NotEmpty::IS_EMPTY => 'First Name is required'
    										)
    								),
    								'break_chain_on_failure' => true
    						),
    						array(
    								'name' => 'StringLength',
    								'options' => array(
    										'encoding' => 'UTF-8',
    										'max' => '30',
    										'messages' => array(
    												\Zend\Validator\StringLength::TOO_LONG => 'First Name can not be more than 30 characters long'
    										)
    								),
    								'break_chain_on_failure' => true
    						)

    				)
    		)));

    		$inputFilter->add($factory->createInput(array(
    				'name' => 'PLU_LAST_NAME',
    				'filters' => array(
    						array(
    								'name' => 'StripTags'
    						),
    						array(
    								'name' => 'StringTrim'
    						)
    				),
    				'validators' => array(
    						array(
    								'name' => 'NotEmpty',
    								'options' => array(
    										'messages' => array(
    												\Zend\Validator\NotEmpty::IS_EMPTY => 'Last Name is required'
    										)
    								),
    								'break_chain_on_failure' => true
    						),
    						array(
    								'name' => 'StringLength',
    								'options' => array(
    										'encoding' => 'UTF-8',
    										'max' => '40',
    										'messages' => array(
    												\Zend\Validator\StringLength::TOO_LONG => 'Last Name can not be more than 40 characters long'
    										)
    								),
    								'break_chain_on_failure' => true
    						)

    				)
    		)));

    		    		$inputFilter->add($factory->createInput(array(
    		    				'name' => 'PLU_NEW_PASSWORD',
    		    				'filters' => array(
    		    						array(
    		    								'name' => 'StripTags'
    		    						),
    		    						array(
    		    								'name' => 'StringTrim'
    		    						)
    		    				),
    		    				'validators' => array(
    		    						array(
    		    								'name' => 'NotEmpty',
    		    								'options' => array(
    		    										'messages' => array(
    		    												\Zend\Validator\NotEmpty::IS_EMPTY => 'New Password is required'
    		    										)
    		    								),
    		    								'break_chain_on_failure' => true
    		    						),
    		    						array(
    		    								'name' => 'StringLength',
    		    								'options' => array(
    		    										'encoding' => 'UTF-8',
    		    										'max' => '30',
    		    										'messages' => array(
    		    												\Zend\Validator\StringLength::TOO_LONG => 'New Password can not be more than 30 characters long'
    		    										)
    		    								),
    		    								'break_chain_on_failure' => true
    		    						)

    		    				)
    		    		)));

    		    		$inputFilter->add($factory->createInput(array(
    		    				'name' => 'PLU_CONFIRM_PASSWORD',
    		    				'filters' => array(
    		    						array(
    		    								'name' => 'StripTags'
    		    						),
    		    						array(
    		    								'name' => 'StringTrim'
    		    						)
    		    				),
    		    				'validators' => array(
    		    						array(
    		    								'name' => 'NotEmpty',
    		    								'options' => array(
    		    										'messages' => array(
    		    												\Zend\Validator\NotEmpty::IS_EMPTY => 'Confirm New Password is required'
    		    										)
    		    								),
    		    								'break_chain_on_failure' => true
    		    						),
    		    						array(
    		    								'name'    => 'Identical',
    		    								'options' => array(
    		    										'token' => 'PLU_NEW_PASSWORD',
    		    										'messages' => array(
    		    												\Zend\Validator\Identical::NOT_SAME => 'Password and Confirm Password do not match'
    		    										)
    		    								)
    		    						)

    		    				)
    		    		)));

    		$inputFilter->add($factory->createInput(array(
    		'name' => 'PLU_EMAIL_ADDRESS',
    		'filters' => array(
    		array(
    		'name' => 'StripTags'
    				),
    				array(
    				'name' => 'StringTrim'
    						)
    		),
    		//     				'required' => false,
    		'validators' => array(
    		array(
    		'name' => 'NotEmpty',
    		'options' => array(
    		'messages' => array(
    		\Zend\Validator\NotEmpty::IS_EMPTY => 'Email Address is required'
    				)
    		),
    		'break_chain_on_failure' => true
    		),
    		array(
    		//	'name' => 'User\Validator\Email',
    		'name' => 'EmailAddress',
    		'options' =>array(
    		'domain'   => 'true',
    		'hostname' => 'true',
    		'mx'       => 'true',
    		'deep'     => 'true',
    		'min'     => '1',
    		'max'     => '100',
    		'messages' => array(
    		// \Zend\Validator\EmailAddress::domain => 'Email Address is required'
    		\Zend\Validator\EmailAddress::INVALID            => 'Invalid type given. String expected',
    		\Zend\Validator\EmailAddress::INVALID_FORMAT     => "Invalid email address",
    		\Zend\Validator\EmailAddress::INVALID_HOSTNAME   => "Invalid email address",
    		\Zend\Validator\EmailAddress::INVALID_MX_RECORD  => "Invalid email address",
    		\Zend\Validator\EmailAddress::INVALID_SEGMENT    => "Invalid email address",
    		\Zend\Validator\EmailAddress::DOT_ATOM           => "Invalid email address",
    		\Zend\Validator\EmailAddress::QUOTED_STRING      => "Invalid email address",
    		\Zend\Validator\EmailAddress::INVALID_LOCAL_PART => "Invalid email address",
    		\Zend\Validator\EmailAddress::LENGTH_EXCEEDED    => "Email address is too long",
    		)
    		),
    		'break_chain_on_failure' => true
    		),

    		)
    		)));

    		$inputFilter->add($factory->createInput(array(
    				'name' => 'PLU_STATUS',
    				'filters' => array(
    						array(
    								'name' => 'StripTags'
    						),
    						array(
    								'name' => 'StringTrim'
    						)
    				),
    				'validators' => array(
    						array(
    								'name' => 'NotEmpty',
    								'options' => array(
    										'messages' => array(
    												\Zend\Validator\NotEmpty::IS_EMPTY => 'Polarlink Status is required'
    										)
    								),
    								'break_chain_on_failure' => true
    						)

    				)
    		)));

    		$inputFilter->add($factory->createInput(array(
    				'name' => 'PLU_USER_TYPE',
    				'validators' => array(
    						array(
    								'name' => 'NotEmpty',
    								'options' => array(
    										'messages' => array(
    												\Zend\Validator\NotEmpty::IS_EMPTY => 'User Type is required'
    										)
    								),
    								'break_chain_on_failure' => true
    						)

    				)
    		)));
    		$this->inputFilter = $inputFilter;
    	}


    	return $this->inputFilter;
    }

    // These are the input filters for the email address on review order screen
    public function getOrderReviewEmailInputFilter() {
    	if (!$this->inputFilter) {
    		$inputFilter = new InputFilter();
    		$factory = new InputFactory();


    		$inputFilter->add($factory->createInput(array(
    				'name' => 'PLU_EMAIL',
    				'filters' => array(
    						array(
    								'name' => 'StripTags'
    						),
    						array(
    								'name' => 'StringTrim'
    						)
    				),
    				'required' => false,
    				'validators' => array(
    						array(
    								'name' => 'EmailAddress',
    								'options' =>array(
    										'domain'   => 'true',
    										'hostname' => 'true',
    										'mx'       => 'true',
    										'deep'     => 'true',
    										'message'  => 'Invalid email address',
    								),
    								'break_chain_on_failure' => true
    						),

    				)
    		)));


    		$this->inputFilter = $inputFilter;
    	}

    	return $this->inputFilter;
    }

public function getOrderReviewEmailRequiredInputFilter() {
    	if (!$this->inputFilter) {
    		$inputFilter = new InputFilter();
    		$factory = new InputFactory();


    		$inputFilter->add($factory->createInput(array(
    				'name' => 'PLU_EMAIL',
    				'filters' => array(
    						array(
    								'name' => 'StripTags'
    						),
    						array(
    								'name' => 'StringTrim'
    						)
    				),
    				'required' => true,
    				'validators' => array(

    						array(
    								'name' => 'NotEmpty',
    								'options' => array(
    										'messages' => array(
    												\Zend\Validator\NotEmpty::IS_EMPTY => 'Email Address is required'
    										)
    								),
    								'break_chain_on_failure' => true
    						),
    						array(
    								'name' => 'EmailAddress',
    								'options' =>array(
    										'domain'   => 'true',
    										'hostname' => 'true',
    										'mx'       => 'true',
    										'deep'     => 'true',
    										'message'  => 'Invalid email address',
    								),
    								'break_chain_on_failure' => true
    						),

    				)
    		)));


    		$this->inputFilter = $inputFilter;
    	}

    	return $this->inputFilter;
    }

    
    public function getOrderReviewCSREmailInputFilter() {
        
        if (!$this->inputFilter) {
           
            $inputFilter = new InputFilter();
            $factory = new InputFactory();
    
    
            $inputFilter->add($factory->createInput(array(
    				'name' => 'csr_email_address',
    				'filters' => array(
    						array(
    								'name' => 'StripTags'
    						),
    						array(
    								'name' => 'StringTrim'
    						)
    				),
    				'required' => false,
    				'validators' => array(
    						array(
    								'name' => 'User\Validator\MultipleEmail',
    								'break_chain_on_failure' => true
    						),

    				)
    		)));
    
    
            $this->inputFilter = $inputFilter;
        }
        
    
        return $this->inputFilter;
    }
    
}