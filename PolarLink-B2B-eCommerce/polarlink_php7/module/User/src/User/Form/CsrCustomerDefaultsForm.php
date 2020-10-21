<?php

namespace User\Form;

use Zend\Form\Form;

class CsrCustomerDefaultsForm extends Form {
	public function __construct($name = null) {
		// we want to ignore the name passed
		parent::__construct ( 'user' );
		
		$this->setAttribute ( 'method', 'post' );
		
		$this->add ( array (
				'name' => 'PLC_DFT_UOM',
				'type' => 'Zend\Form\Element\Select',
				 'options' => array(
            'label' => false,
            'value_options' => array(
            		'No Defaults' => array(
            				'value' => '',
            				'label' => '- No Default -'
            		),
//                 'Pallets' => array(
//                     'value' => 'PL',
//                     'label' => 'Pallets',
//                     // 'selected' => true,
//                 ),
                'Cases' => array(
                    'value' => 'CS',
                    'label' => 'Cases',
                ),
                'Each' => array(
                    'value' => 'EA',
                    'label' => 'Each',
                )
            	
            )
             
        ),
				'attributes' => array(
                	'class' => 'width20per',
						'onchange' => 'changeDirtyFlag(true)'
            )
					
		) );
		
		$this->add ( array (
				'name' => 'PLC_DFT_SHIP_METHOD',
				'type' => 'Zend\Form\Element\Select',
				'options' => array(
						'label' => false,
						'value_options' => array(
								'No Defaults' => array(
										'value' => '',
										'label' => '- No Default -'
								),
								'Pallets' => array(
										'value' => 'B',
										'label' => 'Delivery with Backhaul',
										// 'selected' => true,
								),
								'Cases' => array(
										'value' => 'D',
										'label' => 'Delivery - no Backhaul',
								),
								'Each' => array(
										'value' => 'P',
										'label' => 'Pick-Up',
								)
								 
						)
				),
				'attributes' => array(
                	'class' => 'width32per',
						'onchange' => 'changeDirtyFlag(true)'
            )
					
		) );
		
		$this->add(array(
				'type' => 'Zend\Form\Element\Hidden',
				'name' => 'PLC_DFT_SHIPTO',
				'attributes' => array(
						'id' => 'shipToPlcDefault'
				)
		));
		
		$this->add(array(
				'type' => 'Zend\Form\Element\Hidden',
				'name' => 'PLC_CUSTNO',
				'attributes' => array(
						'id' => 'shipToPlcCustNo'
				)
		));
		
		 $this->add ( array (
        		'name' => 'csr_customer_defaults',
        		'type' => 'Zend\Form\Element\Hidden',
        		'attributes' => array (
        				'value' => '1'
        				//		'readonly' => true
        		)
        		 
        ) );
		
		$this->add ( array (
				'name' => 'save',
				'type' => 'Button',
				'options' => array (
						'label' => 'Save Customer Defaults',
						'label_options' => array(
								'disable_html_escape' => true,
						),
				),
        		'attributes' => array(
        				'type'  => 'submit',
        				'class' => 'btn',
        				'value' => 'submit',
        				'onclick' => 'changeDirtyFlag(false)'
        		)
// 				,
				
// 				'attributes' => array (
// 						'class' => 'btn margin-bottom0',
// 						'id' => 'submit-customer-default',
// 						'value' => 'CustomerDefaults',
// 						'type' => 'submit',
// 			//			'onclick' => 'ChangeDirtyFlag(false)'
// 				)
		) );
		
		
		$this->add ( array (
				'name' => 'Cancel',
				'type' => 'Button',
				'options' => array (
						'label' => 'Cancel Changes',
						'label_options' => array(
								'disable_html_escape' => true,
						),
				),
				'attributes' => array (
						'class' => 'btn',
						'id' => 'submit-customer-default',
						'value' => 'CustomerDefaults',
						'onclick' => 'return checkCancelCustomerForm()'
				)
		) );
		
	}
}