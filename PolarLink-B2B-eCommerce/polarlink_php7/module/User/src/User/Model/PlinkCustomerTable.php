<?php
namespace User\Model;

 use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\ParameterContainer;
    
 final class PlinkCustomerTable
 {
     private $stmt;
     
     private $tableGateway;
     
     private $adapter;
     
     private $parameterContainer;
     
     public function setParameterContainer(ParameterContainer $parameterContainer){
         
         $this->parameterContainer = $parameterContainer;
     }

     public function __construct(TableGateway $tableGateway)
     {
         $this->tableGateway = $tableGateway;
     }
     
     public function users($inCustGroup = '', $inCurrUserID = '', $outMessage = ''){
         
         try{
             $this->adapter = $this->tableGateway->getAdapter();
              
             $this->stmt = $this->adapter->createStatement();
             
             // Get adapter
             
             $this->stmt->prepare('CALL sp_Get_UsersByCustGrp(?, ?, ?)' );
             
             $this->parameterContainer->offsetSet('in_CustGroup', 
                 
                 $inCustGroup, 
                 
                 ParameterContainer::TYPE_STRING);
             
             $this->parameterContainer->offsetSet('in_CurrUserID',
                  
                 $inCurrUserID,
                  
                 ParameterContainer::TYPE_STRING);
             
             $this->parameterContainer->offsetSet('out_Message',
                  
                 $outMessage,
                  
                 ParameterContainer::TYPE_STRING);
             
             $this->stmt->setParameterContainer($this->parameterContainer);
             
             $params = array (
                 "in_CustGroup"  => DB2_PARAM_IN,
                 "in_CurrUserID" => DB2_PARAM_IN,
                 "out_Message"   => DB2_PARAM_OUT
             );

             
             $result = $this->stmt->execute ($this->parameterContainer, $params);
             
             if(!$result) {
                 
                 throw new \Exception('Unexpected database result: false.');
             }
             
             return $result;
             
         }catch(\Exception $e){
             
             throw $e;
         }
     }
     
     public function getCustomerNumberShipToEmails($inCustGroup = '', $inCustNo = '', $inShipTo = '', $outMessage = ''){
         
         try{
             
             $this->adapter = $this->tableGateway->getAdapter();
             
             $this->stmt = $this->adapter->createStatement();
             
             $this->stmt->prepare('CALL sp_Get_PL_ShipTo_Rec(?, ?, ?, ?)' );
             
             $this->parameterContainer->offsetSet('in_CustGroup',
                 
                 $inCustGroup,
                 
                 ParameterContainer::TYPE_STRING);
             
             $this->parameterContainer->offsetSet('in_CustNo',
                 
                 $inCustNo,
                 
                 ParameterContainer::TYPE_STRING);
             
             $this->parameterContainer->offsetSet('in_ShipTo',
                 
                 $inShipTo,
                 
                 ParameterContainer::TYPE_STRING);
             
             $this->parameterContainer->offsetSet('out_Message',
                 
                 $outMessage,
                 
                 ParameterContainer::TYPE_STRING);
             
             $this->stmt->setParameterContainer($this->parameterContainer);
             
             $params = array (
                 "in_CustGroup"  => DB2_PARAM_IN,
                 "in_CustNo"  => DB2_PARAM_IN,
                 "in_ShipTo"  => DB2_PARAM_IN,
                 "out_Message"   => DB2_PARAM_OUT
             );
             
             
             $result = $this->stmt->execute ($this->parameterContainer, $params);
             
             if(!$result) {
                 
                 throw new \Exception('Unexpected database result: false.');
             }
             
             return $result;
             
         }catch(\Exception $e){
             
             throw $e;
         }
         
         
     }
     
     public function getCustomerShiptoEmails($inCustGroup= '', $inSearchFilters='', $outMessage = '') {
         
         try{
             
             $this->adapter = $this->tableGateway->getAdapter();
             
             $this->stmt = $this->adapter->createStatement();
             
             $this->stmt->prepare('CALL sp_Get_PL_ShipTo_Search(?, ?, ?)' );
             
             $this->parameterContainer->offsetSet('in_CustGroup',
                 
                 $inCustGroup,
                 
                 ParameterContainer::TYPE_STRING);
             
             $this->parameterContainer->offsetSet('in_SearchFilters',
                 
                 $inSearchFilters,
                 
                 ParameterContainer::TYPE_STRING);
             
             $this->parameterContainer->offsetSet('out_Message',
                 
                 $outMessage,
                 
                 ParameterContainer::TYPE_STRING);

             $this->stmt->setParameterContainer($this->parameterContainer);
             
             $params = array (
                 "in_CustGroup"  => DB2_PARAM_IN,
                 "in_SearchFilters" => DB2_PARAM_IN,
                 "out_Message"   => DB2_PARAM_OUT
             );
             
             
             $result = $this->stmt->execute ($this->parameterContainer, $params);
             
             if(!$result) {
                 
                 throw new \Exception('Unexpected database result: false.');
             }
             
             return $result;
             
         }catch(\Exception $e){
             
             throw $e;
         }
     }
     
     public function saveCustomerShipToEmails($inCurrUserID= '', $inCustGroup = '',
         
         $inCustNo = '', $inShipTo = '' , $inEmails = '' , $outMessage = '' ){
         
             try{
                 
                 $this->adapter = $this->tableGateway->getAdapter();
                 
                 $this->stmt = $this->adapter->createStatement();
                 
                 /**
                  * Setup common parameters both for
                  * reset entirely ship to email addresses and 
                  * insert/update ship to email address.
                  */
                 
                 $this->parameterContainer->offsetSet('in_CustGroup',
                     
                     $inCustGroup,
                     
                     ParameterContainer::TYPE_STRING);
                 
                 $this->parameterContainer->offsetSet('in_CustNo',
                     
                     $inCustNo,
                     
                     ParameterContainer::TYPE_STRING);
                 
                 $this->parameterContainer->offsetSet('in_ShipTo',
                     
                     $inShipTo,
                     
                     ParameterContainer::TYPE_STRING);
                 
                 $this->parameterContainer->offsetSet('out_Message',
                     
                     $outMessage,
                     
                     ParameterContainer::TYPE_STRING);
                 
                 /**
                  * Setup secific reset ship to email address parameters
                  * Prepare reset method
                  */
                 if(empty($inEmails)){
                     
                     $params = array(
                         "in_CustGroup" => DB2_PARAM_IN,
                         "in_CustNo" => DB2_PARAM_IN,
                         "in_ShipTo" => DB2_PARAM_IN,
                         "out_Message"   => DB2_PARAM_OUT
                     );
                     
                     print_r('<pre>%s</pre>', 'sp_Delete_PL_ShipTo_Rec');
                     
                     // call to sp_Delete_PL_ShipTo_Rec
                     $this->stmt->prepare('CALL sp_Delete_PL_ShipTo_Rec(?, ?, ?, ?)' );
                     
                 } else {
                 

                     /**
                      * Setup specific insert/update ship to email address parameters
                      * Prepare reset method
                     **/  
                     $params = array (
                         "in_CurrUserID"  => DB2_PARAM_IN,
                         "in_CustGroup" => DB2_PARAM_IN,
                         "in_CustNo" => DB2_PARAM_IN,
                         "in_ShipTo" => DB2_PARAM_IN,
                         "in_Emails" => DB2_PARAM_IN,
                         "out_Message"   => DB2_PARAM_OUT
                     );
                     
                     
                     $this->parameterContainer->offsetSet('in_CurrUserID',
                         
                         $inCustGroup,
                         
                         ParameterContainer::TYPE_STRING);
                     
                     $this->parameterContainer->offsetSet('in_Emails',
                         
                         $inEmails,
                         
                         ParameterContainer::TYPE_STRING);
                     
                     $this->stmt->prepare('CALL sp_Save_PL_ShipTo_Rec(?, ?, ?, ?, ?, ?)' );
                 }

                 /**
                  * Continue setting up database action and execute
                  */
                 $this->stmt->setParameterContainer($this->parameterContainer);

                 $result = $this->stmt->execute($this->parameterContainer, $params);
                 
                                  
                 if(!$result) {
                     
                     throw new \Exception('Unexpected database result: false.');
                 }

                 return $result;
                 
             }catch(\Exception $e){
                 
                 throw $e;
             }
     }
     
     // 

 }