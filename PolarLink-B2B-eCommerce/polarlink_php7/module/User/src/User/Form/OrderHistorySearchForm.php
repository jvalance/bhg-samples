<?php

namespace User\Form;

use Zend\Form\Form;

class OrderHistorySearchForm extends Form {
	public function __construct($name = null) {
		// we want to ignore the name passed
		parent::__construct ( 'user' );
		
		$this->setAttribute ( 'method', 'post' );
		
		$this->add ( array (
				'name' => 'FROM_DATE',
				'type' => 'Zend\Form\Element\Text',
				'attributes' => array (
						'class' => 'form-control pull-left',
						'id' => 'fromDate',
						'maxlength' => '10'
				//		'readonly' => true 
				)
				 
		) );
		
		$this->add ( array (
				'name' => 'TO_DATE',
				'type' => 'Zend\Form\Element\Text',
				'attributes' => array (
						'class' => 'form-control pull-left',
						'id' => 'toDate',
						'maxlength' => '10'
				//		'readonly' => true 
				)
				 
		) );
		
		
		$this->add ( array (
				'name' => 'Submit',
				'type' => 'Button',
				'options' => array (
						'label' => 'Search <i class="fa fa-search"></i>',
						'label_options' => array(
								'disable_html_escape' => true,
						),
				),
				'attributes' => array (
						'class' => 'btn margin-bottom0',
						'id' => 'search',
						'value' => 'search',
						'type' => 'submit'
				) 
		) );
		
		
	}
}