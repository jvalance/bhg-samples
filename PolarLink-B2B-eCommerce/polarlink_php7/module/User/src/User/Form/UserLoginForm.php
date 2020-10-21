<?php
namespace User\Form;

 use Zend\Form\Form;

 class UserLoginForm extends Form
 {
     public function __construct($name = null)
     {
         // we want to ignore the name passed
         parent::__construct('user');

    
         $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'PLU_USER_ID',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control pull-left',
                'id' => 'sign-in-user-id',
                'maxlength' => '15'
               
            )
        ));

        $this->add(array(
            'name' => 'PLU_PASSWORD',
            'type' => 'Zend\Form\Element\Password',
            'attributes' => array(
                'class' => 'form-control pull-left',
                'id' => 'sign-in-password',
                'maxlength' => '20'
               
            )
        ));

        
        $this->add(array(
            'name' => 'SUBMIT',
            'value' => 'Login',
            'type' => 'Zend\Form\Element\Submit',
            'options' => array(
                'label' => 'Sign In'
            ),
            'attributes' => array(
                'class' => 'btn btn-lg btn-primary btn-block',
                'id' => 'submit'
            )
        ));
    }
    
 }