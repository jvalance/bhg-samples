<?php

namespace User\Form;

use Zend\Form\Form;

class CsrCustomerSearchForm extends Form {
	public function __construct($name = null) {
		// we want to ignore the name passed
		parent::__construct ( 'user' );
		
		$this->setAttribute ( 'method', 'post' );
		
		$this->add ( array (
				'name' => 'SearchFilters',
				'type' => 'Zend\Form\Element\Text',
				'attributes' => array (
						'class' => 'form-control pull-left text_entry_uppercase',
						'id' => 'search_filters',
						'maxlength' => '100'
						//		'readonly' => true
				)
					
		) );
		
		$this->add ( array (
				'name' => 'Status',
				'type' => 'Zend\Form\Element\Select',
				 'options' => array(
            'label' => 'Status: ',
            'value_options' => array(
            		'Both' => array(
            				'value' => '',
            				'label' => 'Both'
            		),
                'Enabled' => array(
                    'value' => 'E',
                    'label' => 'Enabled',
                    // 'selected' => true,
                ),
                'Disabled' => array(
                    'value' => 'D',
                    'label' => 'Disabled',
                )
            	
            )
        ),
					
		) );
		
		$this->add ( array (
				'name' => 'Submit',
				'type' => 'Button',
				'options' => array (
						'label' => '<i class="fa fa-search"></i>',
						'label_options' => array(
								'disable_html_escape' => true,
						),
				),
				'attributes' => array (
						'class' => 'btn customer-search',
						'id' => 'search',
						'value' => 'search',
						'type' => 'submit'
				)
		) );
		
		
	}
}