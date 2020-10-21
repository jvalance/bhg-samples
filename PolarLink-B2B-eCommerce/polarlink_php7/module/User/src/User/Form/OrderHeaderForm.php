<?php

namespace User\Form;

use Zend\Form\Form;

class OrderHeaderForm extends Form {
	public function __construct($name = null) {
		// we want to ignore the name passed
		parent::__construct ( 'user' );
		
		$this->setAttribute ( 'method', 'post' );
		
		$this->add ( array (
				'name' => 'OH_PO1',
				'type' => 'Zend\Form\Element\Text',
				'attributes' => array (
						'class' => 'form-control pull-left text_entry_uppercase',
						'id' => 'OhPo1',
						'maxlength' => '23'
				)
				 
		) );
		
		$this->add ( array (
				'name' => 'OH_PO2',
				'type' => 'Zend\Form\Element\Text',
				'attributes' => array (
						'class' => 'form-control pull-left text_entry_uppercase',
						'id' => 'OhPo2',
						'maxlength' => '23' 
				)
				 
		) );
		
		$this->add ( array (
				'name' => 'OH_PO3',
				'type' => 'Zend\Form\Element\Text',
				'attributes' => array (
						'class' => 'form-control pull-left text_entry_uppercase',
						'id' => 'OhPo3',
						'maxlength' => '23' 
				)
				 
		) );
		
		$this->add ( array (
				'name' => 'OH_REQ_DELIV_DATE',
				'type' => 'Zend\Form\Element\Text',
				'attributes' => array (
						'class' => 'form-control pull-left',
						'id' => 'OhReqDelivDate',
						'maxlength' => '10',
						'readonly' => true,
						'required' =>true,
						
				)
				 
		) );
		
		$this->add ( array (
				'name' => 'OH_REQ_DELIV_TIME',
				'type' => 'Zend\Form\Element\Text',
				'attributes' => array (
						'class' => 'time',
						'id' => 'OhReqDelivTime',
						'readonly' => true 
				)
				 
		) );
		
		$this->add ( array (
				'name' => 'OH_NOTES',
				'type' => 'Zend\Form\Element\Textarea',
				'attributes' => array (
						'class' => 'form-control pull-left text_entry_uppercase',
						'id' => 'OhNotes',
						'maxlength' => '5000' 
				)
				 
		) );
		
		$this->add ( array (
				'name' => 'Continue',
				'type' => 'Button',
				'options' => array (
						'label' => 'Continue',
						'label_options' => array(
								'disable_html_escape' => true,
						),
				),
				'attributes' => array (
						'class' => 'btn',
						'id' => 'continue',
						'value' => 'continue',
						'type' => 'submit',
				//		'onclick' => 'changeDirtyFlag(false)'
				) 
		) );
		
		$this->add ( array (
				'name' => 'Cancel',
				
				'type' => 'Button',
				'options' => array (
						'label' => 'Cancel Order',
						'label_options' => array(
								'disable_html_escape' => true,
						),
				),
				'attributes' => array (
						'class' => 'btn',
						'id' => 'submit',
						'value' => 'Cancel',
						'onclick' => 'return checkCancel("header")'
				) 
		) );
		
		// $this->add(array(
		// 'name' => 'SUBMIT',
		// 'value' => 'Login',
		// 'type' => 'Zend\Form\Element\Submit',
		// 'options' => array(
		// 'label' => 'Sign In'
		// ),
		// 'attributes' => array(
		// 'class' => 'btn btn-lg btn-primary btn-block',
		// 'id' => 'submit'
		// )
		// ));
	}
}