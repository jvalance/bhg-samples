<?php
namespace User\Form;

 
 use Zend\InputFilter;
 use Zend\Form\Form;

 class OrderAttachmentForm extends Form
 {
     public function __construct($name = null)
     {
     	
         // we want to ignore the name passed
         parent::__construct('order-attachment-form');

    
         $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'PLAT_UPL_FILENAME',
            'type' => 'Zend\Form\Element\File',
            'attributes' => array(
                'class' => 'upload',
                'id' => 'order_file'
                
            )
        ));

        $this->add ( array (
				'name' => 'PLAT_DESCRIPTION',
				'type' => 'Zend\Form\Element\Textarea',
				'attributes' => array (
						'class' => 'form-control',
						'id' => 'plat_description',
						'maxlength' => '450'
				)
				 
		) );
        
       $this->add(array(
        		'type' => 'Submit',
        		'name' => 'save',
        		'attributes' => array(
        				'type'  => 'submit',
        				'class' => 'btn btn-default',
        				'value' => 'Save Attachments'
        		)
        ));
        
      // $this->addInputFilter();
    }
    
    
    public function addInputFilter()
    {
    	$inputFilter = new InputFilter\InputFilter();
    
    	// File Input
    	$fileInput = new InputFilter\FileInput('PLAT_UPL_FILENAME');
    	$fileInput->setRequired(true);
    	$inputFilter->add($fileInput);
    	$this->setInputFilter($inputFilter);
    } 
    
 }