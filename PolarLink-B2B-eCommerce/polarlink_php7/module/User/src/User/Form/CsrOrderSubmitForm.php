<?php
namespace User\Form;

 use Zend\Form\Form;

 class CsrOrderSubmitForm extends Form
 {
     public function __construct($name = null)
     {
         // we want to ignore the name passed
         parent::__construct('order-submit-form');

    
         $this->setAttribute('method', 'post');

        
         $this->add ( array (
             'name' => 'csr_email_address',
             'type' => 'Zend\Form\Element\Textarea',
             'attributes' => array (
                 'class' => 'form-control pull-left',
                 'id' => 'csr_email_address',
                 //         				'multiple' => true,
                // 'onblur' => 'changeDirtyFlag(true)'
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
        				'class' => 'btn pull-left',
        				'value' => 'submit'
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