<?php
namespace User\Form;

 use Zend\Form\Form;

 class ItemSearchForm extends Form
 {
     public function __construct($name = null)
     {
         // we want to ignore the name passed
         parent::__construct('item-search-form');

    
         $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'filter',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'itemFilter',
                'maxlength' => '100',
            		'placeholder' => ''
               
            )
        ));

        $this->add(array(
        		'type' => 'Zend\Form\Element\Hidden',
        		'name' => 'brand',
        		'attributes' => array(
        				'id' => 'itemBrand'
        		)
        ));
        $this->add(array(
        		'type' => 'Zend\Form\Element\Hidden',
        		'name' => 'brandName',
        		'attributes' => array(
        				'id' => 'itemBrandNameToDisplay'
        		)
        ));
        
        $this->add(array(
        		'type' => 'Zend\Form\Element\Hidden',
        		'name' => 'size',
        		'attributes' => array(
        				'id' => 'itemSize'
        		)
        ));
        $this->add(array(
        		'type' => 'Zend\Form\Element\Hidden',
        		'name' => 'sizeName',
        		'attributes' => array(
        				'id' => 'itemSizeToDisplay'
        		)
        ));
        
        
        
        $this->add(array(
        		'type' => 'Button',
        		'name' => 'save',
        		'options' => array(
        				'label' => 'Search',
        				'label_options' => array(
        						'disable_html_escape' => true,
        				),
        				
        		),
        		'attributes' => array(
        				'type'  => 'submit',
        				'class' => 'btn margin-right',
        				'value' => 'submit'
        		)
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