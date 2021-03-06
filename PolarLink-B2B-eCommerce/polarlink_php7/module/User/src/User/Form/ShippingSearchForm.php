<?php
namespace User\Form;

 use Zend\Form\Form;

 class ShippingSearchForm extends Form
 {
     public function __construct($name = null)
     {
         // we want to ignore the name passed
         parent::__construct('user');

    
         $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'SEARCHPARAMETER',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control pull-left',
                'id' => 'searchShippingText',
                'maxlength' => '100',
            		'placeholder' => 'search...'
               
            )
        ));

        $this->add(array(
        		'type' => 'Zend\Form\Element\Hidden',
        		'name' => 'searchshipping',
        		'attributes' => array(
        				'value' => 'search'
        		)
        ));
        
        $this->add(array(
        		'type' => 'Zend\Form\Element\Hidden',
        		'name' => 'CUST_GROUP',
        		'attributes' => array(
        				'id' => 'customerIdSearchShipping'
        		)
        		
        ));
        $this->add(array(
        		'name' => 'SUBMIT',
        		'type'  => 'Zend\Form\Element\Image',
        		
        		'attributes' => array(
        				'type'  => 'image',
        				'src'   => '/img/search_btn.jpg',
        				'alt'   => 'SEARCH',
        				'value' => 'Login'
        		),
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