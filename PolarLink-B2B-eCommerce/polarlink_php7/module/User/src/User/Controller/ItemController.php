<?php
namespace User\Controller;

use Zend\View\Model\ViewModel;
use User\Controller\BaseController;
use Zend\Form\Form;
use User\Form\ItemSearchForm;
use User\Helper\Session\Inquiry;
use User\Helper\Address;

/**
 * ItemController
 *
 * @author Jaziel Lopez
 *
 * @version 1.0.0
 *
 */
final class ItemController extends BaseController
{
    
    /**
     * The default action - show the home page
     */
    public function indexAction()
    {
        // TODO Auto-generated ItemController::indexAction() default action
        return new ViewModel();
    }
    
    /**
     * Display item inquiry page
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function inquiryAction(){
        
        $identity = $this->getAuthService ()->getIdentity ();
        
        // $identity = $this->isLoggedIn();
        
        $form = new ItemSearchForm();
        
        $form->setData($this->request->getPost());
       
        $sessInquiry = Inquiry::getInstance();
        
        
        /**
         * jlopez
         * Item Inquiry
         * 
         * @link https://app.asana.com/0/322466378561882/433193852074933/f
         * 
         * Update sessInquiry container for non CSR users
         * Prevent app from display an empty product list / no default ship to label.
         */
        if($identity['PLU_POLAR_CSR'] !== 'Y') {
            // jvalance: Added: If default is not already set for item inquiry
            // Otherwise it was not allowing change of ship-to selection. 
            if (empty($sessInquiry::get('customerNumber')) ) {
                $sessInquiry::set('customerNumber', $identity['PLC_CUSTNO']);
                
                $sessInquiry::set('customerGroup', $identity['PLU_CUST_GROUP']);
                
                
                // prefer user preference (if exists)
                // otherwise use customer preference
                
                $defaultShipTo = $identity['PLU_DFT_SHIPTO'] ?
                
                $identity['PLU_DFT_SHIPTO'] : $identity['PLC_DFT_SHIPTO'];
                
                
                $sessInquiry::set('shipNumber', \intval($defaultShipTo));
                
                $defaultShipToAddress =
                
                $this->getHelper('Customer')->getUserDefaultShipToAddress(
                
                    $sessInquiry::get('customerGroup'),
                
                    $sessInquiry::get('customerNumber'),
                
                    $sessInquiry::get('shipNumber'));
                
                $sessInquiry::set('formattedAddress', Address::format($defaultShipToAddress));
                
            }
            
        }
        
        // end of Item Inquiry/jlopez

        return $this->_initView([
            
            'form' => $form,
            
            'sessInquiry' => $sessInquiry,
            
            'formattedAddress' => nl2br($sessInquiry::get('formattedAddress')),
            
            'customerShipTos' => $this->getPlinkUserTable()
            
                ->callProcedureGetCustomerShipTos (
                    
                    $sessInquiry::get('customerGroup') , 
                    
                    ''), 
            
            'itemsByBrand' => 
            
                $this->getPlinkUserTable()->callProcedureGetItemBrandSizes( 
                
                    'BRAND', 
                
                    $sessInquiry::get('customerNumber'), 
                
                    $sessInquiry::get('shipNumber') ),
            
            'itemsBySize' => 
                
                $this->getPlinkUserTable()->callProcedureGetItemBrandSizes( 
                
                    'SIZE', 
                
                    $sessInquiry::get('customerNumber') , 
                
                    $sessInquiry::get('shipNumber') ), 
            
            'itemsByBrandSize' => 
                
                $this->getPlinkUserTable()->callProcedureGetItemBrandSizes( 
                
                    'BRANDSIZE', 
                
                    $sessInquiry::get('customerNumber') , 
                
                    $sessInquiry::get('shipNumber') ) 
            ]);
    }
    
    /**
     * 
     * Update Item Inquiry Customer/Ship-To session values
     * 
     * @return \Zend\Http\Response
     */
    public function updateShipToAction(){
                
        $sessInquiry = Inquiry::getInstance();
        
        $sessInquiry::set('shipNumber', 
            $this->request->getPost()->offsetGet('shipNumber'));
        
        $sessInquiry::set('customerNumber', 
            $this->request->getPost()->offsetGet('customerNumber'));
        
        $defaultShipTo =
        
            $this->getHelper('Customer')->getUserDefaultShipToAddress(
            
                $sessInquiry::get('customerGroup'),
            
                $sessInquiry::get('customerNumber'),
            
                $sessInquiry::get('shipNumber'));
        
        $sessInquiry::set('formattedAddress', Address::format($defaultShipTo));
        
        return $this->redirect()->toRoute('item/inquiry');
    }
    
    /**
     * Item Search
     * 
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function itemInquirySearchAction(){
        
        $parameters = [];
        
        $sessInquiry = Inquiry::getInstance();
        
        foreach($this->request->getPost() as $key=>$value):
        
            $parameters[$key] = $value;
        
        endforeach;
        
        $search = $this->getPlinkUserTable()->callProcedureGetItemInquiry(
            
            $sessInquiry::get('customerNumber'),
            
            $sessInquiry::get('shipNumber'),
            
            $this->request->getPost()->offsetGet('brand'),
            
            $this->request->getPost()->offsetGet('size'),
            
            $this->request->getPost()->offsetGet('filter')
            
        );
        
        // clone results for filtering purposes
        $sessInquiry::set('parameters', $parameters);
        
        $sessInquiry::set('results', $search['output']);
        
        $parameters = array_merge($parameters, $search);

        return $this->_initView($parameters)->setTerminal(true);
    }
    
    /**
     * Filter Search Results
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function itemInquiryFilterResultsAction(){
        
        $sessInquiry = Inquiry::getInstance();
        
        $clean = [
            
            'output' => $sessInquiry::get('results')
            
        ];
        
        $parameters = $sessInquiry::get('parameters');
        
        $filter = $this->request->getPost()->offsetGet('filter');
        
        $parameters['filter'] = $filter;
        
        $sessInquiry::set('parameters', $parameters);
        
        if(preg_match('/\w/', $filter)):
        
            $clean['output'] = array_filter($sessInquiry::get('results'), 
                
                function($result) use ($filter) {
                        
                    return stristr($result['ITM_DESC'], $filter) || 
                    
                        stristr($result['ITM_NUMBER'], $filter);
                
            });
            
        endif;
        
        $parameters = array_merge($sessInquiry::get('parameters'), $clean);
        
        return $this->_initView($parameters)
        
            ->setTemplate('user/item/item-inquiry-search')
        
            ->setTerminal(true);
    }
    
    /**
     * Reset search filter results
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function itemInquiryResetFilterResultsAction(){
    
        $sessInquiry = Inquiry::getInstance();
    
        $parameters = $sessInquiry::get('parameters');
    
        $parameters['filter'] = null;
    
        $sessInquiry::set('parameters', $parameters);
        
        $clean = [
        
            'output' => $sessInquiry::get('results')
        
        ];
        
        $parameters = array_merge($sessInquiry::get('parameters'), $clean);
    
        return $this->_initView($parameters)
    
            ->setTemplate('user/item/item-inquiry-search')
    
            ->setTerminal(true);
    }
}