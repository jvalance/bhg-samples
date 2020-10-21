<?php
namespace User\Helper;

use User\Model\PlinkUserTable;
use Zend\Http\Header\SetCookie;
/**
 *
 * @author Jaziel Lopez <juan.jaziel@gmail.com>
 *        
 */
class PendingOrders
{
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
     * 
     * @param unknown $customerId
     * @param unknown $userId
     * @return resource|\User\Model\NULL
     */
    public function getOrders($customerId, $userId) {
        
        $orders = $this->pLinkUserTable->callProcedureGetPendingOrders(
            
            $customerId, $userId);
        
        foreach($orders as &$order) {
        
            $orderTotalValue = 0;
            
            $orderTotalItems = 0;
        
            $orderItems =
        
                $this->pLinkUserTable->callProcedureGetOrderLineItems ( $order['OH_PLINK_ORDERNO'] );
        
            // var_dump($orderItems);
             
            if(is_array($orderItems['output']) && !empty($orderItems['output'])) {
                 
                for($i =0; $i < count($orderItems['output']); $i++) {
                     
                    // $orderTotalItems += (int)$orderItems['output'][$i]['OL_QTY_ORD'];
                    $orderTotalItems ++;
                    
                    $orderTotalValue +=
                    
                        (int)$orderItems['output'][$i]['OL_QTY_ORD'] *
                        
                        (float)$orderItems['output'][$i]['OL_NET_PRICE'];
                }
            }
             
            $order['totalItems'] = $orderTotalItems;
            $order['totalValue'] = number_format(round($orderTotalValue, 2),2);
            
            /**
             * @jlopez
             * 
             * Each order should indicate whether the user who created it
             * Belongs to CSR user group or not.
             * 
             * @link https://app.asana.com/0/322466378561882/333825692419040
             */
            $userDetails = $this->pLinkUserTable
                ->callProcedureGetCsrUsersDetail($order['OH_ENTRY_USER']);

            $order['OH_ENTRY_USER_IS_CSR'] = $userDetails['output']['PLU_POLAR_CSR'];
        }
        
        
        return $orders;
    }
    
    /**
     * Clear current order cookie
     * 
     * @return \Zend\Http\Header\SetCookie
     */
    public function clearCurrentOrder(){
        
        $pLinkOrderNoCookie = new SetCookie('OH_PLINK_ORDERNO', null);
        
        $pLinkOrderNoCookie->setPath('/');
        
        $pLinkOrderNoCookie->setExpires(time() + 365 * 60 * 60 + 24);
        
        return $pLinkOrderNoCookie;
    }
}

