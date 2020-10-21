<?php

namespace User\Form;

use Zend\Form\Form;

class CsrAnnouncementSearchForm extends Form {
	public function __construct($name = null) {
		// we want to ignore the name passed
		parent::__construct ( 'user' );
		
		$this->setAttribute ( 'method', 'post' );
		
		$this->add ( array (
				'name' => 'FACILITY',
				'type' => 'Zend\Form\Element\Select',
				'attributes' => array (
						'class' => 'width60per',
						'id' => 'facility'
						//			'readonly' => true
				)
					
		) );
		
		
		$this->add ( array (
				'name' => 'ANNOUNCEMENT_TEXT',
				'type' => 'Zend\Form\Element\Text',
				'attributes' => array (
						'class' => 'form-control pull-left width60per',
						'id' => 'announcement_text',
						'maxlength' => '250' 
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
						'class' => 'width60per',
						'id' => 'cust_type'
						//			'readonly' => true
				)
					
		) );
		
// 		$this->add(array(
// 				//'type' => 'Zend\Form\Element\MultiCheckbox',
// 				//'type' => 'User\Form\Element\formmulticheckbox',
// 				'type' => 'Zend\Form\Element\MultiCheckBox',
// 				'name' => 'VIEW_AS_USER',
// 				'options' => array(
// 						'label' => false
		
// 				),
// 		));
		
// 		$this->add ( array (
// 				'name' => 'VIEW_AS_USER',
// 				'type' => 'Zend\Form\Element\Checkbox',
// 				'options' => array (
// 						'label' => 'View As User:',
// 						'label_options' => array(
// 								'disable_html_escape' => true,
// 						),
// 						'prepend' => true
// 				),
// 				'attributes' => array (
// 						'id' => 'view_as_user'
// 						//			'readonly' => true
// 				)
					
// 		) );
		
// 		$this->add ( array (
// 				'name' => 'COMPANY',
// 				'type' => 'Zend\Form\Element\Select',
// 				'attributes' => array (
// 						'class' => 'width60per',
// 						'id' => 'company'
// 						//			'readonly' => true
// 				)
					
// 		) );
		
		$this->add ( array (
				'name' => 'END_DATE',
				'type' => 'Zend\Form\Element\Text',
				'attributes' => array (
						'class' => 'form-control pull-left',
						'id' => 'end_date',
						'maxlength' => '10'
				)
				 
		) );
		
// 		$this->add ( array (
// 				'name' => 'USER_ID',
// 				'type' => 'Zend\Form\Element\Text',
// 				'attributes' => array (
// 						'class' => 'form-control pull-left',
// 						'id' => 'user_id',
// 						'maxlength' => '250' 
// 				)
				 
// 		) );
		
		$this->add ( array (
				'name' => 'Search',
				'type' => 'Button',
				'options' => array (
						'label' => '<i class="fa fa-search"></i>&nbsp;Search',
						'label_options' => array(
								'disable_html_escape' => true,
						)
				),
				'attributes' => array (
						'class' => 'btn margin-right',
						'id' => 'search',
						'value' => 'search',
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