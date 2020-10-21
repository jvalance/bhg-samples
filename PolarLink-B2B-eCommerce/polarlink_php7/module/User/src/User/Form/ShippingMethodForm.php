<?php
namespace User\Form;

 use Zend\Form\Form;

 class ShippingMethodForm extends Form
 {
     public function __construct($name = null)
     {
         // we want to ignore the name passed
         parent::__construct('user');

    
         $this->setAttribute('method', 'post');

        $this->add(array(
        'name' => 'shippingMethod',
        'options' => array(
            'label' => 'Select one of the following:',
            'value_options' => array(
                'Backhaul' => array(
                    'value' => 'B',
                    'label' => 'Delivery with Backhaul',
                    'selected' => true,
                ),
                'NoBackhaul' => array(
                    'value' => 'D',
                    'label' => 'Delivery, no Backhaul',
                ),
            	'Pickup' => array(
            		'value' => 'P',
            			'label' => 'Pickup'
            	)
            ),
            'label_attributes' => array(
                'class' => 'radio-inline',
            ),
        ),
        'type'  => 'Radio',
    ));
        $this->add(array(
        		'type' => 'Zend\Form\Element\Hidden',
        		'name' => 'shipto',
        		'attributes' => array(
        				'id' => 'shipToAddToSend'
        		)
        ));
        $this->add(array(
        		'type' => 'Zend\Form\Element\Hidden',
        		'name' => 'customerNumber',
        		'attributes' => array(
        				'id' => 'shipToCustomerNumber'
        		)
        ));
        $this->add(array(
        		'type' => 'Button',
        		'name' => 'save',
        		'options' => array(
        				'label' => 'Save and Continue',
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
        				'label' => 'Cancel Order',
        				'label_options' => array(
        						'disable_html_escape' => true,
        				)
        		),
        		'attributes' => array(
        				'class' => 'btn',
        				'id' => 'cancel',
        				'onclick' => 'return checkCancel("shipping")'
        		)
        ));
    }
    
 }