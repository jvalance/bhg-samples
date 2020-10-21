<?php

namespace User\Model;

// Add these import statements
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\Factory as InputFactory;

class OrderAttachment implements InputFilterAwareInterface {

    public $PLAT_ORDER_NO;
    public $PLAT_UPL_FILENAME;
    public $PLAT_IFS_FILENAME;
    public $PLAT_FILE_EXT;
    public $PLAT_FILE_SIZE;
    public $PLAT_DESCRIPTION;
    public $USER;

   public function exchangeArray($data) {
        $this->PLAT_ORDER_NO = (!empty($data['PLAT_ORDER_NO'])) ? $data['PLAT_ORDER_NO'] : null;
        $this->PLAT_UPL_FILENAME = (!empty($data['PLAT_UPL_FILENAME'])) ? $data['PLAT_UPL_FILENAME'] : null;
        $this->PLAT_IFS_FILENAME = (!empty($data['PLAT_IFS_FILENAME'])) ? $data['PLAT_IFS_FILENAME'] : null;
        $this->PLAT_FILE_EXT = (!empty($data['PLAT_FILE_EXT'])) ? $data['PLAT_FILE_EXT'] : null;
        $this->PLAT_FILE_SIZE = (!empty($data['PLAT_FILE_SIZE'])) ? $data['PLAT_FILE_SIZE'] : null;
        $this->PLAT_DESCRIPTION = (!empty($data['PLAT_DESCRIPTION'])) ? $data['PLAT_DESCRIPTION'] : null;
        $this->USER = (!empty($data['USER'])) ? $data['USER'] : null;
    }

    // Add the following method:
    public function getArrayCopy() {
        return get_object_vars($this);
    }
    
    public function setDbAdapter($dbAdapter) {
    	$this->_dbAdapter = $dbAdapter;
    }
    
    public function getDbAdapter() {
    	return $this->_dbAdapter;
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
    
    
    
    // These are the input filters for the Attachment form
    public function getAttachmentInputFilter() {
    	if (isset($this->inputFilter) === false || !$this->inputFilter) {
    		$inputFilter = new InputFilter();
    		$factory = new InputFactory();
    
    		$inputFilter->add($factory->createInput(array(
    				'name' => 'PLAT_UPL_FILENAME',
    				'required' => false,
    				'filters' => array(
    						array(
    								'name' => 'StripTags'
    						),
    						
    				),
    				'validators' => array(
    						array(
    								'name' => 'NotEmpty',
    								'options' => array(
    										'messages' => array(
    												\Zend\Validator\NotEmpty::IS_EMPTY => 'File is required'
    										)
    								),
    								'break_chain_on_failure' => true
    						),
    				)
    		)));
    
    		$inputFilter->add($factory->createInput(array(
    				'name' => 'PLAT_DESCRIPTION',
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
    										'max' => '455',
    										'messages' => array(
    												\Zend\Validator\StringLength::TOO_LONG => 'Description can not be more than 455 characters long'
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
    
    
  
}