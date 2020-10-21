<?php

namespace User\Form;

use Zend\Form\Form;

class CsrAnnouncementForm extends Form {
	public function __construct($name = null) {
		// we want to ignore the name passed
		parent::__construct ( 'user' );
		
		$this->setAttribute ( 'method', 'post' );
		
		$this->add ( array (
				'name' => 'FACILITY',
				'type' => 'Zend\Form\Element\Select',
				'attributes' => array (
				//		'class' => 'width60per',
						'id' => 'facility'
						//			'readonly' => true
				)
					
		) );
		
		$this->add ( array (
				'name' => 'MESSAGE',
				'type' => 'Zend\Form\Element\Textarea',
				'attributes' => array (
						'class' => 'width78per',
						'id' => 'announcement_text',
						'maxlength' => '2500' 
				)
				 
		) );
		
		$this->add ( array (
				'name' => 'START_DATE',
				'type' => 'Zend\Form\Element\Text',
				'attributes' => array (
						'class' => 'form-control pull-left',
						'id' => 'start_date',
						'maxlength' => '10'
			//			'readonly' => true 
				)
				 
		) );

		$this->add ( array (
				'name' => 'CUST_TYPE',
				'type' => 'Zend\Form\Element\Select',
				'attributes' => array (
				//		'class' => 'width60per',
						'id' => 'cust_type'
						//			'readonly' => true
				)
					
		) );
		
		$this->add ( array (
				'name' => 'END_DATE',
				'type' => 'Zend\Form\Element\Text',
				'attributes' => array (
						'class' => 'form-control pull-left',
						'id' => 'end_date',
						'maxlength' => '10'
				)
				 
		) );
		
		$this->add ( array (
				'name' => 'Submit',
				'type' => 'Button',
				'options' => array (
						'label' => 'Save Announcement',
						'label_options' => array(
								'disable_html_escape' => true,
						)
				),
				'attributes' => array (
						'class' => 'btn margin-right',
						'id' => 'submit',
						'value' => 'search',
						'type' => 'submit',
				//		'onclick' => 'changeDirtyFlag(false)'
				) 
		) );
		
		$this->add ( array (
				'name' => 'Cancel',
				'type' => 'Button',
				'options' => array (
						'label' => 'Cancel',
						'label_options' => array(
								'disable_html_escape' => true,
						),
				),
				'attributes' => array (
						'class' => 'btn margin-right',
						'id' => 'cancel',
						'value' => 'Cancel',
			//			'onclick' => 'return checkCancel("header")'
				) 
		) );
		
	}
}