<?php
namespace User\Form;

 use Zend\Form\Form;

 class CsrCustomerAddForm extends Form
 {
     public function __construct($name = null)
     {
         // we want to ignore the name passed
         parent::__construct('user');

    
         $this->setAttribute('method', 'post');

        $this->add(array(
        		'type' => 'Zend\Form\Element\Text',
        		'name' => 'csr_cust_group',
        		'attributes' => array(
        				'id' => 'csr_cust_group',
        				'class' => 'text_entry_uppercase',
        				'maxlength' => '10',
        				//'onblur' => 'changeDirtyFlag(true); return stringToUpper(this);'
        		)
        ));
        $this->add(array(
        		'type' => 'Zend\Form\Element\Text',
        		'name' => 'csr_user_name',
        		'attributes' => array(
        				'id' => 'csr_user_name',
        				'class' => 'text_entry_uppercase',
        				'onblur' => 'changeDirtyFlag(true)'
        				
        				
        		)
        ));
        
        $this->add ( array (
				'name' => 'csr_status',
				'type' => 'Zend\Form\Element\Select',
				 'options' => array(
            'label' => false,
            'value_options' => array(
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
        		'attributes' => array(
            			'onchange' => 'changeDirtyFlag(true)'
            ) 
					
		) );
        
        $this->add ( array (
        		'name' => 'csr_email_address',
        		'type' => 'Zend\Form\Element\Textarea',
        		'attributes' => array (
        				'class' => 'textarea-with-side-text form-control pull-left',
        				'id' => 'csr_email_address',
        				'maxlength' => '2500',
//         				'multiple' => true,
        				'onblur' => 'changeDirtyFlag(true)'
        				//		'readonly' => true
        		)
        			
        ) );
        
        
        
        
        
        $this->add ( array (
        		'name' => 'PLC_DFT_UOM',
        		'type' => 'Zend\Form\Element\Select',
        		'options' => array(
        				'label' => false,
        				'value_options' => array(
        						/*'No Defaults' => array(
        								'value' => '',
        								'label' => '- No Default -'
        						),*/
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
        			//	'class' => 'width32per',
        				'id' => 'PLC_DFT_UOM',
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
        				//'class' => 'width32per',
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
    
        
        $this->add(array(
        		'type' => 'Button',
        		'name' => 'save',
        		'options' => array(
        				'label' => 'Save PolarLink Customer',
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
						'onclick' => 'return checkCancelCustomerForm()'
        		//		'onclick' => 'location.href = "'. $this->url('user/csrIndex').'";'
        		)
        ));
    }
   
 }