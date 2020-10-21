<?php
namespace User\Form;

 use Zend\Form\Form;

 class OrderSubmitForm extends Form
 {
     public function __construct($name = null)
     {
         // we want to ignore the name passed
         parent::__construct('order-submit-form');

    
         $this->setAttribute('method', 'post');

        
         $this->add(array(
         		'type' => 'Zend\Form\Element\Text',
         		'name' => 'PLU_EMAIL',
         		'options' => array(
         				'label' => false,
         
         		)
         ));
         
         $this->add ( array (
             'name' => 'csr_email_address',
             'type' => 'Zend\Form\Element\Textarea',
             'attributes' => array (
                 'class' => 'textarea-with-side-text form-control pull-left',
                 'id' => 'csr_email_address',
                 //         				'multiple' => true,
                 'onblur' => 'changeDirtyFlag(true)'
                 //		'readonly' => true
             )
              
         ) );
         
        $this->add(array(
        		'type' => 'Button',
        		'name' => 'save',
        		'options' => array(
        				'label' => 'Submit Your Order',
        				'label_options' => array(
        						'disable_html_escape' => true,
        				),
        				
        		),
        		'attributes' => array(
        				'type'  => 'submit',
        				'class' => 'btn',
        				'value' => 'submit'
        		)
        ));
        
        $this->add(array(
        		'type' => 'Zend\Form\Element\Checkbox',
        		'name' => 'PLU_EMAIL_SAVE',
        		'options' => array(
        				'label' => false,
        				 
        		)
        ));
       
        
//         $this->add(array(
//             'name' => 'SUBMIT',
//             'value' => 'Login',
//             'type' => 'Zend\Form\Element\Submit',
//             'options' => array(
//                 'label' => 'Sign In'
//             ),
//             'attributes' => array(
//                 'class' => 'btn btn-lg btn-primary btn-block',
//                 'id' => 'submit'
//             )
//         ));
    }
    
 }