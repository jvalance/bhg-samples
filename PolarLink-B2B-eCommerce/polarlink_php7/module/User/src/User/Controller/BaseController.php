<?php
namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use User\Helper\Customer;
use User\Model\PlinkUserTable;
use Zend\Db\Adapter\ParameterContainer;
/**
 * Base PolarLink Controller
 *
 * @author Jaziel Lopez
 *
 * @version 1.0.0
 *
 */
abstract class BaseController extends AbstractActionController
{
    /**
     * 
     * @var $_viewModel
     */
    private $_viewModel;
    
    /**
     * 
     * @var $_plinkUserTable
     */
    private $_plinkUserTable;
    
    /**
     *
     * @var $_plinkCustomerTable
     */
    private $_plinkCustomerTable;
        

    /*
     * getAuthService - 
     * this function is used for utilize the zend framework 2's Auth Service. 
     * @param void @return object 
     * @author rohit
     */
    public function getAuthService() {
        
    
        return $this->getServiceLocator ()->get ( 'AuthService' );
    }
    
    /**
     * Get auth user ID
     * @return unknown
     */
    public function getAuthUserId(){
        
        return $this->getAuthService()->getIdentity()['PLU_USER_ID'];
    }
    
    public function getHelper($name = ''){
        
        if($name === 'Customer') {
            
            return new Customer($this->getPlinkUserTable());
        }
    }
    
    /*
     * getPlinkUserTable - 
     * 
     * this function is used to get the plinkuser table. 
     * 
     * @param void 
     * @return object 
     * @author rohit
     */
    public function getPlinkUserTable() {
        
        if (! $this->_plinkUserTable) {
            
            $this->_plinkUserTable = 
            
                $this->getServiceLocator()->get ( 'User\Model\PlinkUserTable' );
        }
        
        return $this->_plinkUserTable;
    }
    
    /*
     * getPlinkCustomerTable -
     *
     * this function is used to get the plinkcustomer table.
     *
     * @param void
     * @return object
     * @author rohit
     */
    public function getPlinkCustomerTable() {
        
        if (! $this->_plinkCustomerTable) {
            
            $this->_plinkCustomerTable=
            
            $this->getServiceLocator()->get ( 'User\Model\PlinkCustomerTable' );
            
            $this->_plinkCustomerTable->setParameterContainer(new ParameterContainer());
        }
        
        return $this->_plinkCustomerTable;
    }
    
    
    /**
     * Bind environment, session variables to layout
     * 
     * @param array $dataBinder
     * 
     * @return ViewModel
     */
    public function _initView($dataBinder = array()) {
        
        if ($this->getAuthService ()->hasIdentity ()) {
            // Identity exists; get it
    
            $identity = $this->getAuthService ()->getIdentity ();
            $this->layout ()->identity = $identity;
    
        }
    
        $configArray = $this->getServiceLocator()->get('Config');
    
        $env = '';
    
        $displayEnv = array_key_exists('settings',$configArray);
    
        if($displayEnv){
    
            if(array_key_exists('environment',$configArray['settings'])){
    
                $env = $configArray['settings']['environment']['name'];
            }
        }
    
        $this->layout()->env = $env;
        $this->layout ()->configArray = $configArray;
        
        return new ViewModel($dataBinder);
    }
}