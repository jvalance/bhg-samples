<?php
namespace User\Form;

 use Zend\Form\Form;

 class CsrCustomerForm extends Form
 {
     public function __construct($name = null)
     {
         // we want to ignore the name passed
         parent::__construct('user');

    
         $this->setAttribute('method', 'post');

        $this->add(array(
        		'type' => 'Zend\Form\Element\Hidden',
        		'name' => 'csr_cust_group',
        		'attributes' => array(
        				'id' => 'csr_cust_group'
        		)
        ));
        $this->add(array(
        		'type' => 'Zend\Form\Element\Hidden',
        		'name' => 'csr_user_name',
        		'attributes' => array(
        				'id' => 'csr_user_name'
        		)
        ));
        $this->add(array(
        		'type' => 'Button',
        		'name' => 'save',
        		'options' => array(
        				'label' => 'Select',
        				'label_options' => array(
        						'disable_html_escape' => true,
        				),
        				
        		),
        		'attributes' => array(
        				'type'  => 'submit',
        				'class' => 'btn margin-right',
        				'value' => 'submit',
        				'onclick' => 'changeDirtyFlag(false)'
        		)
        ));
        
        $this->add(array(
        		'name' => 'Cancel',
        		'type' => 'Button',
        		'value' => 'cancel',
        		'options' => array(
        				'label' => 'Cancel',
        				'label_options' => array(
        						'disable_html_escape' => true,
        				)
        		),
        		'attributes' => array(
        				'class' => 'btn',
        				'id' => 'cancel',
        			//	'onclick' => 'location.href = "'. $this->url('user/csrIndex').'";'
        		)
        ));
    }
    
 }