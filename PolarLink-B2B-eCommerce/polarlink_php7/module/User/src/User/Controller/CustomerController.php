<?php
namespace User\Controller;

use Zend\Json\Json;
use Zend\Http\Response;
use Zend\Db\Adapter\ParameterContainer;
use User\Helper\Customer;
use User\Helper\Address;
use Zend\View\Model\JsonModel;
use User\Model\PlinkCustomerTable;

/**s
 * CustomerController
 *
 * @author
 *
 * @version
 *
 */
final class CustomerController extends BaseController
{

    const ADAPTER = 'User/Model/PlinkCustomerTable';
   
    const JSON_CONTENT_TYPE = 'Content-Type: application/json';
   
    /**
     * The default action - show the home page
     */
    public function indexAction()  
    {
        
        $response = array();
        
        $this->sm   = $this->getServiceLocator();
        
        $this->auth  = $this->sm->get('AuthService');
     
        
        $this->response->setStatusCode(Response::STATUS_CODE_200);
        
        $this->response->getHeaders()->addHeaderLine(self::JSON_CONTENT_TYPE);

        try{
            
            if (!$this->auth->hasIdentity()){
                
                throw new \Exception('User not logged in.');
            }
            
            $identity = $this->auth->getIdentity();
            
            // obtain a Customer adapter
            $customer = $this->sm->get(self::ADAPTER);
            
            // setup parameter container
            $customer->setParameterContainer(new ParameterContainer());
            
            // get groups 
            $users = $customer->users(
                
                $identity['PLU_CUST_GROUP'], 
                
                $identity['PLU_USER_ID']);
            
            foreach($users as $user){
                
                $response['users'][] = $user;
            }
            
        }catch(\Exception $e){
            
            $this->response->setStatusCode(Response::STATUS_CODE_500);
            
            $response['error'] = $e->getMessage();
            
        }finally{
            
            $this->response->setContent(Json::encode($response));
        }
        
        return $this->response;
    }
    
    /**
     * 
     * @throws Exception
     * @return \Zend\View\Model\JsonModel
     */
    public function shipToEmailsAction(){
        
        try{
            
            $request = $this->getRequest();
            
            $customer = $this->getPlinkCustomerTable();
            
            $data = [
                
                'PLC_CUST_GRP' => 
                
                    $request->getQuery()->customerGroup ?
                
                        $request->getQuery()->customerGroup :
                
                        $request->getPost()->customerGroup,
                
                'PLC_CUST_NO' =>
                
                    $request->getQuery()->customerNumber ?
                
                        $request->getQuery()->customerNumber :
                
                        $request->getPost()->customerNumber,
                
                'PLC_CUST_SHIPTO' => 
                        
                    $request->getQuery()->shipTo ?
                
                        $request->getQuery()->shipTo : 
                
                        $request->getPost()->shipTo
            ];
            
            if($request->getMethod() === 'POST'){
                
                $emails = trim($request->getPost()->emailList);
                
                $customer->saveCustomerShipToEmails(
                
                    $this->getAuthUserId(),
                
                        $data['PLC_CUST_GRP'],
                
                        $data['PLC_CUST_NO'],
                
                        $data['PLC_CUST_SHIPTO'],
                
                        $emails,
                    
                        '' // required output parameter
                 );
            }
           
            $customerGroupList= $customer->getCustomerShiptoEmails($data['PLC_CUST_GRP']);
            
            $filtered = [];
            
            foreach($customerGroupList as $eachList){
                
                if($eachList['PLST_CUSTNO'] === $data['PLC_CUST_NO'] &&
                    
                        $eachList['PLST_SHIPTO'] === $data['PLC_CUST_SHIPTO']): 
                        
                    $filtered = $eachList;
                
                    break;
                
                endif;
            }
            
            // customer ship to formatted address
            
            $address = $this->getHelper('Customer')
            
                ->getUserDefaultShipToAddress(
                    
                    $data['PLC_CUST_GRP'], 
                    
                    $data['PLC_CUST_NO'], 
                    
                    $data['PLC_CUST_SHIPTO']);
            
            $outputEmails = trim($filtered['PLST_EMAILS']);
            
            $totalOutputEmails = count(explode(',', $outputEmails));
            
            return new JsonModel([
                
                'emailList' => $outputEmails,
                
                'totalEmailList' => $totalOutputEmails,
                
                'formattedAddress' => 
                
                    str_replace(PHP_EOL, '<br/>', Address::format($address))
            ]);
            
        }catch(\Exception $e){
            
            $this->getResponse()->setStatusCode(500);
            
            return new JsonModel([
                
                'error' => $e->getMessage()
            ]);
        }
    }
}