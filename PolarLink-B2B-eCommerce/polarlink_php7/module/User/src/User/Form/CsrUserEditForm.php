<?php
namespace User\Form;

 use Zend\Form\Form;

 class CsrUserEditForm extends Form
 {
     public function __construct($name = null)
     {
         // we want to ignore the name passed
         parent::__construct('user');

    
         $this->setAttribute('method', 'post');

        $this->add(array(
        		'type' => 'Zend\Form\Element\Hidden',
        		'name' => 'PLU_USER_ID',
        		
        ));
        
        $this->add(array(
        		'type' => 'Zend\Form\Element\Hidden',
        		'name' => 'PLU_CUST_GROUP',
        ));
        
        $this->add(array(
        		'type' => 'Zend\Form\Element\Text',
        		'name' => 'PLU_FIRST_NAME',
        		'attributes' => array(
        				'id' => 'plu_first_name',
        				'maxlength' => '30',
        				'onblur' => 'changeDirtyFlag(true)'
        		)
        ));
        
        $this->add(array(
        		'type' => 'Zend\Form\Element\Text',
        		'name' => 'PLU_LAST_NAME',
        		'attributes' => array(
        				'id' => 'plu_last_name',
        				'maxlength' => '40',
        				'onblur' => 'changeDirtyFlag(true)'
        		)
        ));
        
        $this->add(array(
        		'type' => 'Zend\Form\Element\Password',
        		'name' => 'PLU_OLD_PASSWORD',
        		'attributes' => array(
        				'id' => 'plu_old_password',
        				'maxlength' => '30',
        				'onblur' => 'changeDirtyFlag(true)'
        		)
        ));
        
        $this->add(array(
        		'type' => 'Zend\Form\Element\Password',
        		'name' => 'PLU_NEW_PASSWORD',
        		'attributes' => array(
        				'id' => 'plu_new_password',
        				'maxlength' => '30',
        				'onblur' => 'changeDirtyFlag(true)'
        		)
        ));
        $this->add(array(
        		'type' => 'Zend\Form\Element\Password',
        		'name' => 'PLU_CONFIRM_PASSWORD',
        		'attributes' => array(
        				'id' => 'plu_confirm_password',
        				'maxlength' => '30',
        				'onblur' => 'changeDirtyFlag(true)'
        		)
        ));
        
        $this->add(array(
        		'type' => 'Zend\Form\Element\Hidden',
        		'name' => 'PLU_PASSWORD',
        		'attributes' => array(
        			
        		)
        ));
        
        $this->add(array(
        		'type' => 'Zend\Form\Element\Hidden',
        		'name' => 'PLU_CUSTNO',
        		'attributes' => array(
        				'id' => 'plu_cust_number',
        			//	'maxlength' => '8'
        		)
        ));
        
//         $this->add(array(
//         		'type' => 'Zend\Form\Element\Checkbox',
//         		'name' => 'PLU_PLINK_ADMIN',
//         		'attributes' => array(
//         				'id' => 'plu_plink_admin',
//         				'onchange' => 'changeDirtyFlag(true)'
//         		)
//         ));
        
        
        
//         $this->add(array(
//         		'type' => 'Zend\Form\Element\Checkbox',
//         		'name' => 'PLU_POLAR_CSR',
//         		'attributes' => array(
//         				'id' => 'plu_polar_csr',
//         				'onchange' => 'changeDirtyFlag(true)'
//         		)
//         ));
        $this->add(array(
        		'type' => 'Zend\Form\Element\Radio',
        		'name' => 'PLU_USER_TYPE',
        		'options' => array(
        				'disable_inarray_validator' => true,
        		),
        		'attributes' => array(
        				'id' => 'plu_user_type'
        		)
        ));
        
        
        $this->add ( array (
        		'name' => 'PLU_DFT_SHIP_METHOD',
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
       

       $this->add ( array (
       		'name' => 'PLU_DFT_UOM',
       		'type' => 'Zend\Form\Element\Select',
       		'options' => array(
       				'label' => false,
       				'value_options' => array(
       						'No Defaults' => array(
       								'value' => '',
       								'label' => '- No Default -'
       						),
//        						'Pallets' => array(
//        								'value' => 'PL',
//        								'label' => 'Pallets',
//        								// 'selected' => true,
//        						),
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
       		'name' => 'csr_user_field',
       		'type' => 'Zend\Form\Element\Hidden',
       		'attributes' => array (
       				'value' => '1'
       				//		'readonly' => true
       		)
       		 
       ) );
       
       $this->add ( array (
       		'name' => 'csr_change_pwd',
       		'type' => 'Zend\Form\Element\Hidden',
       		'attributes' => array (
       				'value' => '0',
       				'id' => 'csr_change_pwd'
       				//		'readonly' => true
       		)
       ) );
       
       $this->add ( array (
       		'name' => 'csr_user_defaults',
       		'type' => 'Zend\Form\Element\Hidden',
       		'attributes' => array (
       				'value' => '1'
       				//		'readonly' => true
       		)
       
       ) );
       
        
        $this->add ( array (
				'name' => 'PLU_STATUS',
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
        		'name' => 'PLU_EMAIL_ADDRESS',
        		'type' => 'Zend\Form\Element\Text',
        		'attributes' => array (
        				'class' => '',
        				'id' => 'plu_email_address',
        				'maxlength' => '100',
//         				'multiple' => true,
        				'onblur' => 'changeDirtyFlag(true)'
        				//		'readonly' => true
        		)
        			
        ) );
        
        $this->add ( array (
        		'name' => 'PLU_CRT_USER',
        		'type' => 'Zend\Form\Element\Hidden',
        		'attributes' => array (
        				'class' => '',
        				'id' => 'plu_crt_user',
        				'maxlength' => '100',
//         				'multiple' => true,
        				'onblur' => 'changeDirtyFlag(true)'
        				//		'readonly' => true
        		)
        			
        ) );
        
        $this->add ( array (
        		'name' => 'PLU_DFT_SHIPTO',
        		'type' => 'Zend\Form\Element\Hidden',
        		'attributes' => array (
        				'class' => '',
        				'id' => 'plu_default_shipto',
        				//         				'multiple' => true,
        				'onblur' => 'changeDirtyFlag(true)'
        				//		'readonly' => true
        		)
        		 
        ) );
        $this->add(array(
        		'type' => 'Button',
        		'name' => 'save',
        		'options' => array(
        				'label' => 'Save PolarLink User',
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
						'onclick' => 'return checkCancelUserForm()'
        		//		'onclick' => 'location.href = "'. $this->url('user/csrIndex').'";'
        		)
        ));
    }
   
 }