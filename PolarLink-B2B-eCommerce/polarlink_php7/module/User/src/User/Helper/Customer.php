<?php
namespace User\Helper;

use User\Model\PlinkUserTable;

/**
 *
 * Customer Helper
 * 
 * @author Jaziel Lopez, <juan.jaziel@gmail.com>
 *        
 */
class Customer
{

    /**
     * 
     * @var PlinkUserTable
     */
    private $pLinkUserTable;
    
    /**
     *
     * @param PlinkUserTable $pLinkUserTable
     */
    public function __construct(PlinkUserTable $pLinkUserTable)
    {
    
        $this->pLinkUserTable = $pLinkUserTable;
    }
    
    /**
     * Get customer data
     * 
     * @param unknown $customer
     * @return string|mixed|NULL
     */
    public function getCustomerData($customer){
        
        return $this->pLinkUserTable
        
            ->callProcedureGetCsrCustomersDetail($customer)['output'];
    }
    /**
     * Get customer default ship-to address
     * 
     * @param unknown $customer
     * @return mixed
     */
    public function getCustomerDefaultShipToAddress($customer){
        
        $data = $this->getCustomerData($customer);
        
        $customerAddressList = $this->getShipToAddressList($customer)[$customer];
        
        if(!is_array($data)){
        
            throw new \Exception(
                sprintf("(%s) Unable to read customer data", $customer));
        }
        
        if(!is_array($customerAddressList)){
            
            throw new \Exception(
                sprintf("(%s) Unable to read customer address list", $customer));
        }
        
        
        $matchAddress = array_filter($customerAddressList, 
            
            function($address) use ($data) {
            
                return $address['ST_CUST'] === $data['PLC_CUSTNO'] &&
            
                        $address['ST_NUM'] === $data['PLC_DFT_SHIPTO'];
        });
        
        return array_reduce($matchAddress, function($previous, $address){
            
            return $address;
            
        });
    }
    
    /**
     * Get customer default ship-to address
     * 
     * @param string $customerGroup
     * @param string $customerNumber
     * @param string $shipTo
     * @throws \Exception
     * @return mixed
     */
    public function getUserDefaultShipToAddress($customerGroup = '',  $customerNumber = '', $shipTo = ''){
    
        $cleanPreferences = array();
        
        
        /**
         * Get all the address list stored in db for this $customerGroup
         */        
        $customerAddressList = $this->getShipToAddressList($customerGroup)[$customerGroup];
        
    
        if(!is_array($customerAddressList)){
    
            throw new \Exception(
                sprintf("(%s) Unable to read user available ship-to preference", 
                    
                    $customerGroup));
        }
    
        
        $matchAddress = array_filter($customerAddressList,
    
            function($address) use ($customerNumber, $shipTo) {
                
                return $address['ST_CUST'] == $customerNumber && 
                
                    $address['ST_NUM'] == $shipTo;
        });
    
        return array_reduce($matchAddress, function($previous, $address){
    
            return $address;
    
        });
    }
    
    
    /**
     * Get customer available addressess for shipping
     * 
     * @param unknown $customer
     * @param array $addressList
     * @return array
     */
    public function getShipToAddressList($customer, $addressList = array()){
        
       $customers = $this->toArray($customer);
       
       $addressess = array_map(function($customerGroup){
           
           return $this->pLinkUserTable
            
            ->callProcedureGetCustomerShipTos($customerGroup)['output'];
        
       }, $customers);
       
       return array_combine($customers, $addressess);
    }
    
    /**
     * Array from scalar
     * 
     * @param unknown $input
     * @return unknown|unknown[]
     */
    private function toArray($input){
        
        if(is_array($input)){
            
            return $input;
        }
        
        
        if(is_scalar($input)){
            
            return [$input];
        }        
    }
}

