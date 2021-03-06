<?php
namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
// for using the sessions
use Zend\Session\Container;
use User\Model\PlinkUser;
use User\Model\PlinkAnnouncements;
use User\Model\OrderAttachment;

// forms
use User\Form\UserLoginForm;
use User\Form\ShippingSearchForm;
use User\Form\ShippingMethodForm;
use User\Form\OrderHeaderForm;
use User\Form\ItemSearchForm;
use User\Form\ItemSubstituteForm;
use User\Form\OrderHistorySearchForm;
use User\Form\CsrCustomerForm;
use User\Form\CsrAnnouncementSearchForm;
use User\Form\CsrAnnouncementForm;
use User\Form\CsrCustomerSearchForm;
use User\Form\CsrCustomerAddForm;
use User\Form\CsrCustomerEditForm;
use User\Form\OrderSubmitForm;
use User\Form\CsrOrderSubmitForm;
use User\Form\CsrCustomerDefaultsForm;
use User\Form\CsrUserSearchForm;
use User\Form\CsrUserAddForm;
use User\Form\CsrUserEditForm;
use User\Form\OrderAttachmentForm;

// for emails
use Zend\Mime;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Validator\File\Size;
use Zend\Http\Response;
use Zend\Form\Element\Select;
use Zend\Db\Adapter\ParameterContainer;
use User\Helper\PendingOrders;
use Zend\Http\Header\SetCookie;
use User\Helper\Customer;
use User\Helper\Session\Inquiry;
use User\Helper\Address;
use Zend\Filter\StripTags;
use User\Helper\HTMLTag;
use Zend\Debug\Debug;

class UserController extends AbstractActionController
{

    protected $plinkUserTable;

    protected $plinkCustomerTable;

    protected $plinkAnnouncementsTable;

    protected $orderAttachmentTable;

    protected $authservice;

    /*
     * protected function attachDefaultListeners() { parent::attachDefaultListeners(); $events = $this->getEventManager(); $this->events->attach('dispatch', array($this, 'preDispatch'), 100); $this->events->attach('dispatch', array($this, 'postDispatch'), -100); } public function preDispatch () { // this is a db convenience class I setup in global.php // under the service_manager factories (will show below) // this is just standard config loaded from ServiceManager // set your property in your class for $config (protected $config;) // then have access in entire controller $this->config = $this->getServiceLocator()->get('Config'); // this comes from the things.config.local.php file echo $this->config['substitutes']['min_value']; die; } public function postDispatch (MvcEvent $e) { // Called after actions }
     */
    
    /*
     * getAuthService - this function is used for utilize the zend framework 2's Auth Service. @param void @return object @author rohit
     */
    public function getAuthService()
    {
        if (! $this->authservice) {
            $this->authservice = $this->getServiceLocator()->get('AuthService');
        }
        
        return $this->authservice;
    }

    /*
     * getPlinkUserTable - this function is used to get the plinkuser table. @param void @return object @author rohit
     */
    public function getPlinkUserTable()
    {
        if (! $this->plinkUserTable) {
            $sm = $this->getServiceLocator();
            $this->plinkUserTable = $sm->get('User\Model\PlinkUserTable');
        }
        return $this->plinkUserTable;
    }

    /*
     * getPlinkCustomerTable - this function is used to get the plinkcustomer table. @param void @return object @author rohit
     */
    public function getPlinkCustomerTable()
    {
        if (! $this->plinkCustomerTable) {
            $sm = $this->getServiceLocator();
            $this->plinkCustomerTable = $sm->get('User\Model\PlinkCustomerTable');
            
            $this->plinkCustomerTable->setParameterContainer(new ParameterContainer());
        }
        return $this->plinkCustomerTable;
    }

    /*
     * getPlinkAnnouncementsTable - this function is used to get the plinkannouncement table. @param void @return object @author rohit
     */
    public function getPlinkAnnouncementsTable()
    {
        if (! $this->plinkAnnouncementsTable) {
            $sm = $this->getServiceLocator();
            $this->plinkAnnouncementsTable = $sm->get('User\Model\PlinkAnnouncementsTable');
        }
        return $this->plinkAnnouncementsTable;
    }

    /*
     * getOrderAttachmentTable - this function is used to get the OrderAttachment table. @param void @return object @author rohit
     */
    public function getOrderAttachmentTable()
    {
        if (! $this->orderAttachmentTable) {
            $sm = $this->getServiceLocator();
            $this->orderAttachmentTable = $sm->get('User\Model\OrderAttachmentTable');
        }
        return $this->orderAttachmentTable;
    }

    /*
     * addFlashMessage - Add flash message after saving the order for later use
     */
    protected function addFlashMessage($message = '')
    {
        if (empty($message)) {
            $message = 'Order Details saved successfully.';
        }
        $this->flashMessenger()->addMessage($message);
    }

    /*
     * _initView - this function is used to set the users session in the layout. @param void @return object @author rohit
     */
    protected function _initView()
    {
        $this->_viewModel = new ViewModel();
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            
            $identity = $this->getAuthService()->getIdentity();
            $this->layout()->identity = $identity;
        }
        
        $configArray = $this->getServiceLocator()->get('Config');
        
        $env = '';
        
        $displayEnv = array_key_exists('settings', $configArray);
        
        if ($displayEnv) {
            
            if (array_key_exists('environment', $configArray['settings'])) {
                
                $env = $configArray['settings']['environment']['name'];
            }
        }
        
        $this->layout()->env = $env;
        $this->layout()->configArray = $configArray;
    }

    /*
     * loginAction - this function is used to login and set the session for the users.
     * @param void
     * @return object
     * @author rohit
     */
    public function loginAction()
    {
        $configArray = $this->getServiceLocator()->get('Config');
        
        $env = '';
        
        $displayEnv = array_key_exists('settings', $configArray);
        
        if ($displayEnv) {
            
            if (array_key_exists('environment', $configArray['settings'])) {
                
                $env = $configArray['settings']['environment']['name'];
            }
        }
        
        $this->layout()->env = $env;
        $this->layout()->configArray = $configArray;
        
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            
            $this->redirect()->toRoute('user/index');
        }
        $loginResult = array();
        $viewModel = new ViewModel();
        $request = $this->getRequest();
        $successMessage = '';
        
        $form = new UserLoginForm();
        
        if ($request->isPost()) {
            
            $user = $this->getServiceLocator()->get('User/Model/PlinkUser');
            
            // get and set input filter validation of form user
            // model
            $form->setInputFilter($user->getLoginInputFilter());
            
            $formData = $request->getPost();
            
            if (! empty($formData->PLU_USER_ID))
                $formData->PLU_USER_ID = strtoupper($formData->PLU_USER_ID);
            $form->setData($formData);
            if ($form->isValid()) {
                // code to call the stored procedure
                $plinkUserTable = $this->getPlinkUserTable();
                
                $loginResult = $plinkUserTable->callProcedureLogin($formData->PLU_USER_ID, md5($formData->PLU_PASSWORD));
                
                if ($loginResult['result'] == '1') {
                    if (! empty($loginResult['output'])) {
                        if ($loginResult['output']['PLU_POLAR_CSR'] == 'Y') {
                            $loginResult['output']['PLU_CUST_GROUP'] = '';
                            $loginResult['output']['CUST_NAME'] = '';
                        }
                        $this->getAuthService()
                            ->getStorage()
                            ->write($loginResult['output']);
                        if ($loginResult['output']['PLU_POLAR_CSR'] == 'Y')
                            $this->redirect()->toRoute('user/csrIndex');
                        else
                            $this->redirect()->toRoute('user/index');
                    } else {
                        $loginResult['result'] = '0';
                        $loginResult['message'] = 'There is some error. Please try again later.';
                    }
                }
            }
        }
        
        $viewModel->setVariables(array(
            'form' => $form,
            'flashMessages' => $this->flashMessenger()
                ->getMessages(),
            'loginResult' => $loginResult
        ));
        
        $this->layout('layout/login');
        
        return $viewModel;
    }

    /*
     * Logout Action - This is the action for logging out the user and deleting the sessions @param void @author rohit
     */
    public function logoutAction()
    {
        $this->getAuthService()->clearIdentity();
        
        // destroy Order Session
        $order_session = new Container('order');
        $order_session->getManager()->destroy();
        // $order_session->offsetUnset ( 'order_num' );
        
        // Destroy Item Inquiry Session
        $sessInquiry = new Container('sess_inquiry');
        $sessInquiry->getManager()->destroy();
        
        $this->flashmessenger()->addMessage("You've been logged out");
        $this->redirect()->toRoute('user');
    }

    /*
     * Index Action - This is the action for the users home page
     * @param void
     * @author rohit
     */
    public function indexAction()
    {
        //
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        $plinkUserTable = $this->getPlinkUserTable();
        
        //
        // Pending Orders Storage
        //
        $helperPendingOrders = new PendingOrders($plinkUserTable);
        
        $orders = $helperPendingOrders->getOrders(
        $identity['PLU_CUST_GROUP'], $identity['PLU_USER_ID'])
        ;
        
        $pendingOrders = new Container('pendingOrders');
        
        if ($pendingOrders->offsetExists('orders')) {
            
            $pendingOrders->offsetUnset('orders');
        }
        
        $pendingOrders->offsetSet('orders', $orders);
        
        //
        // End of Pending Orders storage
        // /
        
        /**
         * @jlopez
         *
         * end of processing pending orders
         */
        
        // to redirect the csr user to the csr home page
        if ($identity['PLU_POLAR_CSR'] == 'Y') {
            $hasMessages = $this->flashMessenger()->hasMessages();
            if ($hasMessages) {
                $messages = $this->flashMessenger()->getMessages();
                foreach ($messages as $message)
                    $this->flashMessenger()->addMessage($message);
            }
            $this->redirect()->toRoute('user/csrIndex');
        }
        $this->_initView();
        
        /**
         * In order to prepare data for item inquiry
         * a customer must be selected
         * Skip otherwise
         */
        $sessInquiry = Inquiry::getInstance();
        
        if (! empty(preg_replace('/\s+/', '', $identity['PLU_CUST_GROUP']))) {
            
            $customerHelper = new Customer($plinkUserTable);
            
            $preferences = [
                
                $identity['PLU_DFT_SHIPTO'],
                
                $identity['PLC_DFT_SHIPTO']
            ];
            
            $defaultShipTo = 
            $customerHelper->getUserDefaultShipToAddress(
            $identity['PLU_CUST_GROUP'], 
            $identity['PLU_CUSTNO'], 
            $preferences);
            
            $sessInquiry = Inquiry::getInstance();
            $sessInquiry::set('shipNumber', $defaultShipTo['ST_NUM']);
            $sessInquiry::set('customerNumber', $defaultShipTo['ST_CUST']);
            $sessInquiry::set('customerGroup', $identity['PLU_CUST_GROUP']);
            $sessInquiry::set('formattedAddress', Address::format($defaultShipTo));
        }
        
        /**
         *
         * @todo : Ask John Vallance
         *       Hardcoded values user Id/ship to for announcements
         */
        $userIdToFetchAnnouncements = 0;
        $shipTo = 0;
        // code to call the stored procedure
        $plinkAnnouncementsTable = $this->getPlinkAnnouncementsTable();
        
        $currentAnnouncement = $plinkAnnouncementsTable->callProcedureGetCurrentAnnouncements($userIdToFetchAnnouncements, $shipTo);
        
        $partialEditCurrentOrder = $this->getRequest()
            ->
        getHeaders()
            ->
        get('Cookie')->
        OH_PLINK_ORDERNO;
        
        $viewModel = new ViewModel();
        
        $viewModel->setVariables(array(
            'identity' => $identity,
            'flashMessages' => $this->flashMessenger()
                ->getMessages(),
            'currentAnnouncement' => $currentAnnouncement,
            'currentUnsavedOrder' => $currentUnsavedOrder,
            'partialEditCurrentOrder' => $partialEditCurrentOrder,
            'pendingOrders' => $pendingOrders->offsetGet('orders')
        ));
        
        return $viewModel;
    }

    /**
     * Set current order
     * Validate current order progress
     * Redirect to the corresponding workflow step.
     *
     * @author Jaziel Lopez <jaziel@artedigital-mx.com>
     * @throws \Exception
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function currentOrderAction()
    {
        try {
            
            $cookies = $this->getRequest()
                ->getHeaders()
                ->get('Cookie')->OH_PLINK_ORDERNO;
            
            if (is_null($this->params('id'))) {
                
                return $this->redirect()->toRoute('user');
            }
            
            if ($this->getAuthService()->hasIdentity()) {
                
                // Identity exists; get it
                $identity = $this->getAuthService()->getIdentity();
            } else {
                
                return $this->redirect()->toRoute('user');
            }
            
            $validationError = '';
            
            /**
             * Refresh order session container
             */
            $storage = new Container('order');
            
            if ($storage->offsetExists('order_num')) {
                
                $storage->offsetUnset('order_num');
            }
            
            $storage->offsetSet('order_num', $this->params('id'));
            
            /**
             * Get instance of UserTable Gateway
             */
            $plinkUserTable = $this->getPlinkUserTable();
            
            /**
             * Get current order step
             */
            $order = $plinkUserTable->callProcedureGetOrderHeader($storage->offsetGet('order_num'));
            
            /**
             * Parse and validate step
             * Redirect the user to the current view upon step analysis.
             */
            
            if (is_array($order) && array_key_exists('output', $order)) {
                
                if (is_array($order['output']) && 
                array_key_exists('PLINK_ENTRY_STEP', $order['output'])) {
                    
                    switch ($order['output']['PLINK_ENTRY_STEP']) {
                        
                        case '1':
                            $ahead = 'user/orderShipping';
                            break;
                        case '2':
                            $ahead = 'user/orderHeader';
                            break;
                        case '3':
                            $ahead = 'user/itemSearch';
                            break;
                        case '4':
                            $ahead = 'user/substitutes';
                            break;
                        case '5':
                            $ahead = 'user/reviewOrder';
                            break;
                        case '6':
                            $ahead = 'user/orderConfirm';
                        default:
                            
                            $validationError = 
                            sprintf('Invalid Order Step: %s.
    	                               
    	                               Expected steps: 1-6.', 
                            $order['output']['PLINK_ENTRY_STEP']);
                            break;
                    }
                    
                    if (empty($validationError)) {
                        
                        return $this->redirect()->toRoute($ahead);
                    }
                } else {
                    
                    $validationError = 'Order keys error: 
    	                    
    	                    `PLINK_ENTRY_STEP` key not found.';
                }
            } else {
                
                $validationError = 'Order keys error: 
    	            
    	            `output` key not found.';
            }
            
            /**
             * Process errors
             */
            throw new \Exception($validationError);
        } catch (\Exception $e) {
            
            /**
             * Return error view
             */
            $viewModel = new ViewModel();
            
            $viewModel->setVariables(array(
                'error' => $validationError
            ));
            
            return $viewModel;
        }
    }

    public function placeOrderAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            return $this->redirect()->toRoute('user');
        }
        
        /**
         * Refresh order session container
         *
         * Preset order number to zero to create a new order after shipping selection.
         */
        $storage = new Container('order');
        
        if ($storage->offsetExists('order_num')) {
            
            $storage->offsetUnset('order_num');
        }
        
        $storage->offsetSet('order_num', '0');
        
        /**
         * Redirect the user to order shipping
         */
        return $this->redirect()->toRoute('user/orderShipping');
    }

    /*
     * Order shipping Action - This is the action for the users order shipping selection page
     * @param void
     * @author rohit
     * @contrib jlopez
     */
    public function orderShippingAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        $currentOrdNum = 0;
        
        // this is to check the value of the order id in the session
        $order_session = new Container('order');
        if ($order_session->offsetExists('order_num')) {
            $currentOrdNum = $order_session->offsetGet('order_num');
        }
        
        $plinkUserTable = $this->getPlinkUserTable();
        
        $this->_initView();
        $request = $this->getRequest();
        $searchFilter = '';
        $form = new ShippingSearchForm();
        $formData = $request->getPost();
        $formShippingMethod = new ShippingMethodForm();
        $customerOrderShipping = '';
        
        // echo $formData->redirect;
        
        // this is to set the data in the search form
        $form->setData($formData);
        // calling the model
        $plinkUserTable = $this->getPlinkUserTable();
        $getOrderHeader = array();
        $showTotalForm = '1';
        // to check whether we are calling this page using ajax
        if ($request->isXmlHttpRequest()) {
            $customerId = trim($this->getRequest()->getPost('customerNumber'));
            $shipTo = $this->getRequest()->getPost('shipto');
            $shipMethod = $this->getRequest()->getPost('shippingMethod');
            $userId = trim($identity['PLU_USER_ID']);
            
            // code to call the stored procedure for saving the order shipping
            $customerOrderShipping = $plinkUserTable->callProcedureSaveOrderShipping($customerId, $shipTo, $shipMethod, $userId, $currentOrdNum);
            
            if ($customerOrderShipping['orderNum'] > 0) {
                $this->addFlashMessage();
                return new JsonModel(array(
                    'success' => true
                ));
            } else {
                return new JsonModel(array(
                    'success' => false
                ));
            }
        }
        
        // code to call the stored procedure for the orderHeader
        if (! empty($currentOrdNum)) {
            $getOrderHeader = $plinkUserTable->callProcedureGetOrderHeader($currentOrdNum);
            $showTotalForm = '0';
        }
        // echo '<pre>'; print_r($getOrderHeader); die;
        // this is to check the current step for the hyper links in the breadcrums
        // $currentUnsavedOrder = $plinkUserTable->callProcedureGetCurrentOrder ( trim ( $identity ['PLU_CUSTNO'] ) );
        
        if ($request->isPost()) {
            
            $hiddenSearchShipping = $this->getRequest()->getPost('searchshipping');
            if ($hiddenSearchShipping == 'search') {
                $searchFilter = $this->getRequest()->getPost('SEARCHPARAMETER');
                $showTotalForm = '0';
            }
            $formSave = $this->getRequest()->getPost('save');
            if ($formSave == 'submit') {
                
                $customerId = trim($this->getRequest()->getPost('customerNumber'));
                
                // $currentUnsavedOrder = $plinkUserTable->callProcedureGetCurrentOrder ( trim ( $customerId ) );
                // if(empty($currentOrdNum) && !empty($currentUnsavedOrder['orderNum'])){
                // $identity ['PLU_CUSTNO'] = $customerId;
                // $this->getAuthService ()->getStorage ()->write ( $identity );
                // $order_session->offsetSet ( 'order_num', $currentUnsavedOrder ['orderNum'] );
                // $currentOrdNum = $currentUnsavedOrder ['orderNum'];
                // }
                $shipTo = $this->getRequest()->getPost('shipto');
                $shipMethod = $this->getRequest()->getPost('shippingMethod');
                $userId = trim($identity['PLU_USER_ID']);
                
                // code to call the stored procedure for saving the order shipping
                $customerOrderShipping = $plinkUserTable->callProcedureSaveOrderShipping($customerId, $shipTo, $shipMethod, $userId, $currentOrdNum);
                
                if ($customerOrderShipping['orderNum'] > 0) {
                    $order_session->offsetSet('order_num', $customerOrderShipping['orderNum']);
                    $identity['PLU_CUSTNO'] = $customerId;
                    $this->getAuthService()
                        ->getStorage()
                        ->write($identity);
                    
                    // echo $formData->redirect; die;
                    if (! empty($formData->redirect) && isset($formData->redirect)) {
                        
                        $this->redirect()->toRoute('user/reviewOrder', array(
                            'tab' => "Shipping"
                        ));
                    } else {
                        
                        $this->redirect()->toRoute('user/orderHeader', array(
                            'OH_PLINK_ORDER_NO' => $customerOrderShipping['orderNum']
                        ));
                    }
                }
            }
        }
        // code to call the stored procedure for the shipTos
        
        $customerShipTos = $plinkUserTable->callProcedureGetCustomerShipTos(trim($identity['PLU_CUST_GROUP']), $searchFilter);
        // echo '<pre>'; print_r($getOrderHeader); die;
        $plinkAnnouncementsTable = $this->getPlinkAnnouncementsTable();
        $userIdToFetchAnnouncements = 0;
        $shipToFetchAnnouncements = 0;
        
        if (! empty($getOrderHeader['output'])) {
            if (! empty($getOrderHeader['output']['OH_CUSTNO'])) {
                $userIdToFetchAnnouncements = trim($getOrderHeader['output']['OH_CUSTNO']);
            }
            if (! empty($getOrderHeader['output']['OH_SHP2_NUM'])) {
                $shipToFetchAnnouncements = trim($getOrderHeader['output']['OH_SHP2_NUM']);
            }
        }
        $currentAnnouncement = array(
            'output' => ''
        );
        if (! empty($userIdToFetchAnnouncements) && ! empty($shipToFetchAnnouncements)) {
            $currentAnnouncement = $plinkAnnouncementsTable->callProcedureGetCurrentAnnouncements($userIdToFetchAnnouncements, $shipToFetchAnnouncements);
        }
        
        // To check for the defaults for the User as well as the Customer
        $currentUserDetail = array();
        if (trim($identity['PLU_POLAR_CSR']) != 'Y')
            $currentUserDetail = $plinkUserTable->callProcedureGetCsrUsersDetail($identity['PLU_USER_ID']);
        $currentCustomerDetail = $plinkUserTable->callProcedureGetCsrCustomersDetail($identity['PLU_CUST_GROUP']);
        // echo '<pre>'; print_r($currentUserDetail); echo '</pre>';
        $viewModel = new ViewModel();
        
        $viewModel->setVariables(array(
            'identity' => $identity,
            'customerShipTos' => $customerShipTos,
            'customerOrderShipping' => $customerOrderShipping,
            'getOrderHeader' => $getOrderHeader,
            'showTotalForm' => $showTotalForm,
            'form' => $form,
            'formShippingMethod' => $formShippingMethod,
            'currentUnsavedOrder' => $currentOrdNum,
            'currentOrdNum' => $currentOrdNum,
            'currentAnnouncement' => $currentAnnouncement,
            'currentUserDetail' => $currentUserDetail,
            'currentCustomerDetail' => $currentCustomerDetail
        ));
        return $viewModel;
    }

    /*
     * getUserDetail Action - This is the action for the users home page @param void @author rohit
     */
    public function getUserDetailAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
        }
        $plinkUserTable = $this->getPlinkUserTable();
        $loginResult = $plinkUserTable->getUserDetails();
        echo '<pre>';
        print_r($loginResult);
        die();
    }

    /*
     * Order Cancel Action - This is the action for cancelling the order @param void @author rohit
     */
    public function orderCancelAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        }
        
        $currentOrdNum = 0;
        // making the object the model
        $plinkUserTable = $this->getPlinkUserTable();
        $pendingOrders = new PendingOrders($plinkUserTable);
        
        $request = $this->getRequest();
        $formData = $request->getPost()->toArray();
        
        $viewModel = new ViewModel();
        $viewModel->setTerminal($request->isXmlHttpRequest());
        $action = (! empty($formData['action']) ? $formData['action'] : '');
        
        /**
         * jlopez
         *
         * If OH_PLINK_ORDERNO exists as part of formData
         * Use it directly instead of checking into the session.
         */
        if (isset($formData['OH_PLINK_ORDERNO'])) {
            
            $currentOrdNum = $formData['OH_PLINK_ORDERNO'];
        } else {
            
            // this is to check the value of the order id in the session
            $order_session = new Container('order');
            if ($order_session->offsetExists('order_num')) {
                $currentOrdNum = $order_session->offsetGet('order_num');
            }
        }
        
        /**
         * jlopez
         *
         * Whether the response should be json encoded
         */
        $wantsJSON = false;
        
        if (isset($formData['type'])) {
            
            if ($formData['type'] === 'JSON') {
                
                $wantsJSON = true;
            }
        }
        
        // die('kailash here from shipping order page');
        
        /* added by kailash 7/25/2016 */
        $orderAttachmentTable = $this->getOrderAttachmentTable();
        $getOrderAttachementItems = $orderAttachmentTable->callProcedureGetOrderAttachedFiles($currentOrdNum);
        $dataOrderAttachment = array();
        if (! empty($getOrderAttachementItems)) {
            foreach ($getOrderAttachementItems['result'] as $valueOrder) {
                $dataOrderAttachment[] = $valueOrder;
            }
        }
        
        if (! empty($dataOrderAttachment)) {
            $this->config = $this->getServiceLocator()->get('Config');
            $folder_path = $this->config['order_attachment_file_path']['path'];
            // die('kailash here when cancel order');
            foreach ($dataOrderAttachment as $row) {
                if (! empty($row['PLAT_ORDER_NO']) && ! empty($row['PLAT_ATTACH_NO'])) {
                    $removeOrderAttachment = $orderAttachmentTable->callProcedureRemoveOrderAttachment($row['PLAT_ORDER_NO'], $row['PLAT_ATTACH_NO']);
                    
                    $completeFilepath = $folder_path . $row['PLAT_ORDER_NO'] . "/" . $row['PLAT_IFS_FILENAME'];
                    unlink($completeFilepath);
                }
            }
            rmdir($completeFilepath = $folder_path . $currentOrdNum);
        }
        
        if ($currentOrdNum > 0 && ! empty($action)) {
            $customerCancelOrder = $plinkUserTable->callProcedureCancelOrder($currentOrdNum, $action);
            
            if ($customerCancelOrder['result'] == '1') {
                
                /**
                 * @jlopez
                 *
                 * Clear current order
                 *
                 * https://app.asana.com/0/322466378561882/331037971794045
                 */
                $headers = $this->getResponse()->getHeaders();
                
                $headers->addHeader($pendingOrders->clearCurrentOrder());
                
                $this->addFlashMessage('<span class="colorred">Order Cancelled successfully.</span>');
                
                if ($wantsJSON) {
                    
                    exit(print(json_encode([
                        'output' => $customerCancelOrder
                    ])));
                } else {
                    
                    // $order_session->getManager()->destroy();
                    $order_session->offsetUnset ( 'order_num' );
                    echo true;
                    die();
                }
            }
        } else {
            
            $this->addFlashMessage('<span class="colorred">Order Cancelled successfully.</span>');
            echo false;
            die();
        }
        echo false;
        die();
    }

    /*
     * Order Cancel Action - This is the action for cancelling the order @param void @author rohit
     */
    public function setMessageAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        }
        $this->addFlashMessage();
        echo true;
        die();
    }

    /*
     * getAnnouncementsAjax Action - This is the action which will be called from the order shipping page to get the announcements via ajax
     * @param void
     * @author rohit
     */
    public function getAnnouncementsAjaxAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        $viewModel = new ViewModel();
        $request = $this->getRequest();
        $response = $this->getResponse();
        $formData = $request->getPost()->toArray();
        
        // $formData = $request->getPost ();
        $customerId = trim($formData['customerId']);
        $shipTo = trim($formData['shipTo']);
        // disable layout if request by Ajax
        $viewModel->setTerminal($request->isXmlHttpRequest());
        $statusesToUpdate = array();
        // making the object the model
        $plinkAnnouncementsTable = $this->getPlinkAnnouncementsTable();
        $currentAnnouncement = array(
            'output' => ''
        );
        if (! empty($customerId) || ! empty($shipTo)) {
            $currentAnnouncement = $plinkAnnouncementsTable->callProcedureGetCurrentAnnouncements($customerId, $shipTo);
        }
        
        $viewModel->setTerminal(true)
            ->setTemplate('partials/announcements-list.phtml')
            ->setVariables(array(
            'currentAnnouncement' => $currentAnnouncement
        ));
        
        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($viewModel);
        
        $jsonModel = new JsonModel();
        $jsonModel->setVariables(array(
            'html' => $htmlOutput
        ));
        
        return $jsonModel;
    }

    /*
     * Order Header Action - This is the action for the users order header selection page @param void @author rohit
     */
    public function orderHeaderAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        }
        
        $this->_initView();
        $order_session = new Container('order');
        // making the object the model
        $plinkUserTable = $this->getPlinkUserTable();
        
        $request = $this->getRequest();
        $currentOrderNum = '';
        if ($order_session->offsetExists('order_num')) {
            
            $currentOrderNum = $order_session->offsetGet('order_num');
        }
        
        // check if the order header is not set in the session
        if (empty($currentOrderNum)) {
            $this->flashmessenger()->addMessage("There is no current order. Please try again.");
            $this->redirect()->toRoute('user/index');
        }
        $form = new OrderHeaderForm();
        
        if ($request->isPost()) {
            
            $user = $this->getServiceLocator()->get('User/Model/PlinkUser');
            // get and set input filter validation of form user
            // model
            $form->setInputFilter($user->getOrderHeaderInputFilter());
            $formData = $request->getPost();
            $redirectReviewPage = $formData->redirect;
            $form->setData($formData);
            $valid = true;
            $getOrderHeader = $plinkUserTable->callProcedureGetOrderHeader($currentOrderNum);
            
            if ($formData->OH_REQ_DELIV_DATE != "") {
                
                $selectedDate = strtotime($formData->OH_REQ_DELIV_DATE);
                // $selectedDate = strtotime('08/08/2016');
                $currenDate = strtotime(date('m/d/Y'));
                
                if (trim($getOrderHeader['output']['OH_SHIP_METHOD_CODE']) != 'P') {
                    $pickup_method = 'Delivery';
                } else {
                    $pickup_method = 'Pickup';
                }
                if ($selectedDate < $currenDate) {
                    $form->setMessages(array(
                        'OH_REQ_DELIV_DATE' => array(
                            $pickup_method . ' Date cannot be in the past'
                        )
                    ));
                    $valid = false;
                }
                $dateAry = explode('/', $formData->OH_REQ_DELIV_DATE);
                
                $fromDate = $toDate = $dateAry[2] . $dateAry[0] . $dateAry[1];
                $custNum = $getOrderHeader['output']['OH_CUSTNO'];
                $custNum = str_pad($custNum, 8, '0', STR_PAD_LEFT);
                $shipto = $getOrderHeader['output']['OH_SHP2_NUM'];
                $shipto = str_pad($shipto, 4, '0', STR_PAD_LEFT);
                $scheduleDate = $fromDate;
                $puDeliv = $getOrderHeader['output']['OH_SHIP_METHOD_CODE'];
                $getRequestedDate = $plinkUserTable->callProcedureRequestDate($custNum, $shipto, $scheduleDate, $puDeliv);
                $arrValueDate = array();
                foreach ($getRequestedDate['result'] as $valuedate) {
                    $arrValueDate = $valuedate;
                }
                // $arrValueDate['REQDATE'] = '20160216';
                if (strtotime($arrValueDate['REQDATE']) < $currenDate) {
                    
                    $form->setMessages(array(
                        'OH_REQ_DELIV_DATE' => array(
                            'Date entered is not valid because lead time is before today'
                        )
                    ));
                    $valid = false;
                    // $this->redirect()->toRoute ('order-header?redirect=review-order');
                }
                
                $workingDays = $plinkUserTable->callProcedureGetWorkingDays($fromDate, $toDate);
                $arrWorkignDays = array();
                foreach ($workingDays['result'] as $valueOrder) {
                    $arrWorkignDays = $valueOrder;
                }
                
                if ($arrWorkignDays['WORKING_DAY'] == '0') {
                    
                    $form->setMessages(array(
                        'OH_REQ_DELIV_DATE' => array(
                            'Date entered is invalid because it is a Polar Beverage non-working day'
                        )
                    ));
                    $valid = false;
                }
            } else {
                if (trim($getOrderHeader['output']['OH_SHIP_METHOD_CODE']) != 'P') {
                    $pickup_method = 'Delivery';
                } else {
                    $pickup_method = 'Pickup';
                }
                $valid = false;
                $form->setMessages(array(
                    'OH_REQ_DELIV_DATE' => array(
                        'Please select valid ' . $pickup_method . ' date'
                    )
                ));
            }
            
            // end code to check the valid code
            if ($form->isValid() && $valid == true) {
                // code to call the stored procedure
                
                $userId = trim($identity['PLU_USER_ID']);
                $notes = (! empty(trim($formData->OH_NOTES)) ? trim($formData->OH_NOTES) : '');
                $saveOrderNotesResult = $plinkUserTable->callProcedureSaveOrderNotes($currentOrderNum, $notes, $userId);
                
                $delDate = (! empty(trim($formData->OH_REQ_DELIV_DATE)) ? trim($formData->OH_REQ_DELIV_DATE) : '0');
                if (! empty($delDate)) {
                    
                    $delDateArray = explode('/', $delDate);
                    $delDate = $delDateArray['2'] . $delDateArray['0'] . $delDateArray['1'];
                }
                
                $delTime = (! empty(trim($formData->OH_REQ_DELIV_TIME)) ? trim($formData->OH_REQ_DELIV_TIME) : '0');
                if (! empty($delTime)) {
                    $delTimeTotalDigits = strlen($delTime);
                    $delTimeAmPm = substr($delTime, ($delTimeTotalDigits - 2), '2');
                    $delTimeArray = explode(':', $delTime);
                    $delTimeHour = $delTimeArray['0'];
                    if ($delTimeAmPm == 'pm') {
                        $delTimeHour = $delTimeArray['0'] + 12;
                    }
                    $delTimeMin = substr($delTimeArray['1'], '0', '2');
                    if ($delTimeHour < 10) {
                        $delTimeHour = '0' . $delTimeHour;
                    }
                    $delTime = $delTimeHour . $delTimeMin . '00';
                }
                $po1 = (! empty(trim($formData->OH_PO1)) ? trim($formData->OH_PO1) : '');
                $po2 = (! empty(trim($formData->OH_PO2)) ? trim($formData->OH_PO2) : '');
                $po3 = (! empty(trim($formData->OH_PO3)) ? trim($formData->OH_PO3) : '');
                
                $saveOrderHeaderResult = $plinkUserTable->callProcedureSaveOrderHeader($currentOrderNum, $delDate, $delTime, $po1, $po2, $po3, $userId);
                // to check whether we are calling this page using ajax
                if ($request->isXmlHttpRequest()) {
                    if ($saveOrderHeaderResult['result'] == '1') {
                        $this->addFlashMessage();
                        return new JsonModel(array(
                            'success' => true
                        ));
                    } else {
                        return new JsonModel(array(
                            'success' => false
                        ));
                    }
                }
                if (! empty($redirectReviewPage)) {
                    $this->redirect()->toRoute('user/reviewOrder', array(
                        'tab' => "Shipping"
                    ));
                } else {
                    $this->redirect()->toRoute('user/itemSearch');
                }
                
                // $this->flashMessenger()->addMessage($loginResult['message']);
            }
        }
        
        // code to call the stored procedure for the orderHeader
        
        $getOrderHeader = $plinkUserTable->callProcedureGetOrderHeader($currentOrderNum);
        
        // code to call the stored procedure for the OrderNotes
        
        $getOrderNotes = $plinkUserTable->callProcedureGetOrderNotes($currentOrderNum);
        
        $viewModel = new ViewModel();
        
        // this is to check the current step for the hyper links in the breadcrums
        $currentUnsavedOrder = $plinkUserTable->callProcedureGetCurrentOrder(trim($identity['PLU_CUSTNO']));
        
        $viewModel->setVariables(array(
            'identity' => $identity,
            'getOrderHeader' => $getOrderHeader,
            'getOrderNotes' => $getOrderNotes,
            'form' => $form,
            'currentUnsavedOrder' => $currentUnsavedOrder,
            'currentOrderNum' => $currentOrderNum
        ));
        return $viewModel;
    }

    /*
     * Item Search Action - This is the action for the item search selection page @param void @author rohit
     */
    public function itemSearchAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        $currentOrdNum = 0;
        // this is to check the value of the order id in the session
        $order_session = new Container('order');
        
        if ($order_session->offsetExists('order_num')) {
            $currentOrdNum = $order_session->offsetGet('order_num');
        }
        
        $this->_initView();
        
        $request = $this->getRequest();
        
        // making the object the model
        $plinkUserTable = $this->getPlinkUserTable();
        
        // check if the order header is not set in the session
        if (empty($currentOrdNum)) {
            $this->flashmessenger()->addMessage("There is no current order. Please try again.");
            $this->redirect()->toRoute('user/index');
        }
        
        $getOrderHeader = $plinkUserTable->callProcedureGetOrderHeader($currentOrdNum);
        
        /**
         * @jlopez
         *
         * Moving forward to get items:
         * Customer and shipping data should be read from order ($getOrderHeader)
         * $identity should not be used.
         *
         * @link https://app.asana.com/0/322509381293794/332589933393902
         */
        
        $shipNumber = $getOrderHeader['output']['OH_SHP2_NUM'];
        $customerNumber = $getOrderHeader['output']['OH_CUSTNO'];
        
        $getItemsByBrandSize = $plinkUserTable->callProcedureGetItemBrandSizes('BRANDSIZE', $customerNumber, $shipNumber);
        $getItemsByBrand = $plinkUserTable->callProcedureGetItemBrandSizes('BRAND', $customerNumber, $shipNumber);
        $getItemsBySize = $plinkUserTable->callProcedureGetItemBrandSizes('SIZE', $customerNumber, $shipNumber);
        $getOrderTotals = $plinkUserTable->callProcedureGetOrderTotals($currentOrdNum);
        
        $getOrderLineItems = $plinkUserTable->callProcedureGetOrderLineItems($currentOrdNum);
        
        $form = new ItemSearchForm();
        $formData = $request->getPost();
        $form->setData($formData);
        
        $viewModel = new ViewModel();
        
        // this is to check the current step for the hyper links in the breadcrums
        $currentUnsavedOrder = $plinkUserTable->callProcedureGetCurrentOrder($customerNumber);
        
        $viewModel->setVariables(array(
            'form' => $form,
            'identity' => $identity,
            'getItemsByBrandSize' => $getItemsByBrandSize,
            'getItemsByBrand' => $getItemsByBrand,
            'getItemsBySize' => $getItemsBySize,
            'getOrderTotals' => $getOrderTotals,
            'getOrderLineItems' => $getOrderLineItems,
            'currentUnsavedOrder' => $currentUnsavedOrder,
            'getOrderHeader' => $getOrderHeader
        ));
        
        return $viewModel;
    }

    /*
     * Item Search Ajax Action - This is the action for the item search ajax which is returned on the item search @param void @author rohit
     */
    public function itemSearchAjaxAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        $currentOrdNum = 0;
        // this is to check the value of the order id in the session
        $order_session = new Container('order');
        
        if ($order_session->offsetExists('order_num')) {
            $currentOrdNum = $order_session->offsetGet('order_num');
        }
        $brand = '';
        $brandName = '';
        $size = '';
        $sizeName = '';
        $filter = '';
        $viewModel = new ViewModel();
        $request = $this->getRequest();
        $form = new ItemSearchForm();
        $formData = $request->getPost();
        $form->setData($formData);
        
        if (! empty($formData->brand))
            $brand = $formData->brand;
        
        if (! empty($formData->size))
            $size = $formData->size;
        
        if (! empty($formData->brandName))
            $brandName = $formData->brandName;
        
        if (! empty($formData->sizeName))
            $sizeName = $formData->sizeName;
        
        if (! empty($formData->filter))
            $filter = $formData->filter;
        // disable layout if request by Ajax
        $viewModel->setTerminal($request->isXmlHttpRequest());
        
        if (empty($currentOrdNum)) {
            $this->flashmessenger()->addMessage("There is no current order. Please try again.");
            $this->redirect()->toRoute('user/index');
        }
        
        // making the object the model
        $plinkUserTable = $this->getPlinkUserTable();
        
        $getItemsFiltered = $plinkUserTable->callProcedureGetItemSearch($currentOrdNum, $brand, $size, $filter);
        
        $searchFilters = array(
            'brand' => $brand,
            'size' => $size,
            'filter' => $filter,
            'brandName' => $brandName,
            'sizeName' => $sizeName
        );
        
        $viewModel->setVariables(array(
            'form' => $form,
            'identity' => $identity,
            'getItemsFiltered' => $getItemsFiltered,
            'searchFilters' => $searchFilters
        ));
        
        return $viewModel;
    }

    /*
     * Update Order Action - This is the action for the item search ajax which is returned on the item search @param void @author rohit
     */
    public function updateOrderAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        $currentOrdNum = 0;
        // this is to check the value of the order id in the session
        $order_session = new Container('order');
        
        if ($order_session->offsetExists('order_num')) {
            $currentOrdNum = $order_session->offsetGet('order_num');
        }
        $request = $this->getRequest();
        $viewModel = new ViewModel();
        $request = $this->getRequest();
        $response = $this->getResponse();
        $formData = $request->getPost()->toArray();
        
        // $formData = $request->getPost ();
        
        // disable layout if request by Ajax
        $viewModel->setTerminal($request->isXmlHttpRequest());
        $statusesToUpdate = array();
        // making the object the model
        $plinkUserTable = $this->getPlinkUserTable();
        
        $i = 0;
        foreach ($formData['status'] as $key => $statusToUpdate) {
            if ($statusToUpdate) {
                $statusesToUpdate[$i]['quantity'] = (trim($formData['quantity'][$key]) ? $formData['quantity'][$key] : '0');
                $statusesToUpdate[$i]['uom'] = $formData['uom'][$key];
                $statusesToUpdate[$i]['item_number'] = $formData['item_number'][$key];
                $i ++;
            }
        }
        
        $userId = trim($identity['PLU_USER_ID']);
        if (! empty($statusesToUpdate) && ! empty($currentOrdNum)) {
            
            foreach ($statusesToUpdate as $updateArray) {
                $itemNo = $updateArray['item_number'];
                $quantity = $updateArray['quantity'];
                $uom = $updateArray['uom'];
                $getItemsFiltered = $plinkUserTable->callProcedureSaveOrderLineItem($currentOrdNum, $itemNo, $quantity, $uom, $userId);
            }
        }
        
        if (isset($formData['sendOnlyResponse']) && ! empty($formData['sendOnlyResponse'])) {
            $this->addFlashMessage();
            return new JsonModel(array(
                'success' => true
            ));
        }
        
        $getOrderTotals = $plinkUserTable->callProcedureGetOrderTotals($currentOrdNum);
        
        $getOrderHeader = $plinkUserTable->callProcedureGetOrderHeader($currentOrdNum);
        
        /* work for net price calculate */
        
        /*
         * COMMNETED BY KAILASH DATED 21/7/2016 AFTER TEST AND CONFIRAMTION CODE WILL BE REMOVE
         *
         * $custNumItemSearch = $identity['PLU_CUSTNO'];
         * $shipToItemSearch = $identity['PLU_DFT_SHIPTO'];
         * //OH_ENTRY_DATE
         * $reqDateItemSearch = $getOrderHeader['output']['OH_REQ_DELIV_DATE'];
         * $priceBookDateItemSearch = $getOrderHeader['output']['OH_REQ_DELIV_DATE'];
         * //$this->config = $this->getServiceLocator()->get('Config');
         * //$username = $this->config['db']['username'];
         * //$password = $this->config['db']['password'];
         * $getOrderLineItems = $plinkUserTable->callProcedureGetOrderLineItemsWithPrice ($username,$password, $currentOrdNum,$custNumItemSearch,$shipToItemSearch, $reqDateItemSearch, $priceBookDateItemSearch );
         */
        $getOrderLineItems = $plinkUserTable->callProcedureGetOrderLineItems($currentOrdNum);
        
        /* END */
        
        $viewModel->setTerminal(true)
            ->setTemplate('user/user/update-order.phtml')
            ->setVariables(array(
            'getOrderTotals' => $getOrderTotals,
            'getOrderLineItems' => $getOrderLineItems
        ));
        
        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($viewModel);
        
        // if (!$this->getRequest()->isXmlHttpRequest()) {
        // return array();
        // }
        
        $trimPalletQty = trim(number_format($getOrderTotals['palletQty'], '2'));
        if ($trimPalletQty < 1) {
            $palletQty = substr($trimPalletQty, '1', strlen($trimPalletQty));
        } else {
            $palletQty = $trimPalletQty;
        }
        
        $viewModelCurrentOrderTotals = new ViewModel();
        $viewModelCurrentOrderTotals->setTerminal(true)
            ->setTemplate('user/user/alert-msg-good.phtml')
            ->setVariables(array(
            'message' => 'Order was updated with <br />' . number_format($getOrderTotals['caseQty'], '0', '.', ',') . ' Cases, ' . $palletQty . ' Pallets, for total amount of $' . number_format($getOrderTotals['amount'], 2, '.', ',')
        ));
        
        $htmlOutputSuccess = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($viewModelCurrentOrderTotals);
        
        $jsonModel = new JsonModel();
        $jsonModel->setVariables(array(
            'html' => $htmlOutput,
            'successmsg' => $htmlOutputSuccess
        ));
        
        return $jsonModel;
    }

    public function updateOrderInlineAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        $currentOrdNum = 0;
        // this is to check the value of the order id in the session
        $order_session = new Container('order');
        
        if ($order_session->offsetExists('order_num')) {
            $currentOrdNum = $order_session->offsetGet('order_num');
        }
        $request = $this->getRequest();
        $viewModel = new ViewModel();
        $request = $this->getRequest();
        $response = $this->getResponse();
        $formData = $request->getPost()->toArray();
        
        // $formData = $request->getPost ();
        
        // disable layout if request by Ajax
        $viewModel->setTerminal($request->isXmlHttpRequest());
        $statusesToUpdate = array();
        // making the object the model
        $plinkUserTable = $this->getPlinkUserTable();
        
        $userId = trim($identity['PLU_USER_ID']);
        if (! empty($formData) && ! empty($currentOrdNum)) {
            
            $itemNo = $formData['item_number'];
            $quantity = $formData['quantity'];
            $uom = $formData['uom'];
            $getItemsFiltered = $plinkUserTable->callProcedureSaveOrderLineItem($currentOrdNum, $itemNo, $quantity, $uom, $userId);
        }
        $getOrderTotals = $plinkUserTable->callProcedureGetOrderTotals($currentOrdNum);
        
        /* work for net price calculate */
        
        /*
         * COMMNETED BY KAILASH DATED 21/7/2016 AFTER TEST AND CONFIRAMTION CODE WILL BE REMOVE
         * $getOrderHeader = $plinkUserTable->callProcedureGetOrderHeader ( $currentOrdNum );
         * $custNumItemSearch = $identity['PLU_CUSTNO'];
         * $shipToItemSearch = $identity['PLU_DFT_SHIPTO'];
         * //OH_ENTRY_DATE
         * $reqDateItemSearch = $getOrderHeader['output']['OH_REQ_DELIV_DATE'];
         * $priceBookDateItemSearch = $getOrderHeader['output']['OH_REQ_DELIV_DATE'];
         * //$this->config = $this->getServiceLocator()->get('Config');
         * //$username = $this->config['db']['username'];
         * //$password = $this->config['db']['password'];
         * $getOrderLineItems = $plinkUserTable->callProcedureGetOrderLineItemsWithPrice ( $username,$password,$currentOrdNum,$custNumItemSearch,$shipToItemSearch, $reqDateItemSearch, $priceBookDateItemSearch );
         *
         * // end
         * END COMMNETED CODE TO REMOVE
         */
        
        $getOrderLineItems = $plinkUserTable->callProcedureGetOrderLineItems($currentOrdNum);
        $viewModel->setTerminal(true)
            ->setTemplate('user/user/update-order.phtml')
            ->setVariables(array(
            'getOrderTotals' => $getOrderTotals,
            'getOrderLineItems' => $getOrderLineItems
        ));
        
        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($viewModel);
        
        $jsonModel = new JsonModel();
        $jsonModel->setVariables(array(
            'html' => $htmlOutput
        ));
        
        return $jsonModel;
    }

    /*
     * Delete Item Action - This is the action for deleting the item from the current Order
     */
    public function deleteItemAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        $currentOrdNum = 0;
        // this is to check the value of the order id in the session
        $order_session = new Container('order');
        
        if ($order_session->offsetExists('order_num')) {
            $currentOrdNum = $order_session->offsetGet('order_num');
        }
        $viewModel = new ViewModel();
        $request = $this->getRequest();
        $response = $this->getResponse();
        
        $formData = $request->getPost()->toArray();
        
        // making the object the model
        $plinkUserTable = $this->getPlinkUserTable();
        $userId = trim($identity['PLU_USER_ID']);
        $itemNo = $formData['itemId'];
        $quantity = '0';
        $uom = $formData['uom'];
        $getItemsFiltered = $plinkUserTable->callProcedureSaveOrderLineItem($currentOrdNum, $itemNo, $quantity, $uom, $userId);
        $getOrderTotals = $plinkUserTable->callProcedureGetOrderTotals($currentOrdNum);
        
        /* work for net price calculate */
        /*
         * COMMNETED BY KAILASH DATED 21/7/2016 AFTER TEST AND CONFIRAMTION CODE WILL BE REMOVE
         *
         * $getOrderHeader = $plinkUserTable->callProcedureGetOrderHeader ( $currentOrdNum );
         * $custNumItemSearch = $identity['PLU_CUSTNO'];
         * $shipToItemSearch = $identity['PLU_DFT_SHIPTO'];
         * //OH_ENTRY_DATE
         * $reqDateItemSearch = $getOrderHeader['output']['OH_REQ_DELIV_DATE'];
         * $priceBookDateItemSearch = $getOrderHeader['output']['OH_REQ_DELIV_DATE'];
         * //$this->config = $this->getServiceLocator()->get('Config');
         * //$username = $this->config['db']['username'];
         * //$password = $this->config['db']['password'];
         *
         * $getOrderLineItems = $plinkUserTable->callProcedureGetOrderLineItemsWithPrice ($username,$password, $currentOrdNum,$custNumItemSearch,$shipToItemSearch, $reqDateItemSearch, $priceBookDateItemSearch );
         * END CODE COMMENTED
         */
        
        $getOrderLineItems = $plinkUserTable->callProcedureGetOrderLineItems($currentOrdNum);
        
        $viewModel->setTerminal(true)
            ->setTemplate('user/user/update-order.phtml')
            ->setVariables(array(
            'getOrderTotals' => $getOrderTotals,
            'getOrderLineItems' => $getOrderLineItems
        ));
        
        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($viewModel);
        $jsonModel = new JsonModel();
        $jsonModel->setVariables(array(
            'html' => $htmlOutput,
            'output' => trim($getItemsFiltered['result'])
        ));
        
        return $jsonModel;
        
        // disable layout if request by Ajax
    }

    /*
     * Item Substitutes Action - This is the action for the item substitutes page, in the place order process
     */
    public function substitutesAction()
    {
        $configSubstitutes = $this->getServiceLocator()->get('Config');
        
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        $currentOrdNum = 0;
        // this is to check the value of the order id in the session
        $order_session = new Container('order');
        
        if ($order_session->offsetExists('order_num')) {
            $currentOrdNum = $order_session->offsetGet('order_num');
        }
        
        $this->_initView();
        
        $request = $this->getRequest();
        // making the object the model
        $plinkUserTable = $this->getPlinkUserTable();
        
        // check if the order header is not set in the session
        if (empty($currentOrdNum)) {
            $this->flashmessenger()->addMessage("There is no current order. Please try again.");
            $this->redirect()->toRoute('user/index');
        }
        
        $getOrderHeader = $plinkUserTable->callProcedureGetOrderHeader($currentOrdNum);
        
        /**
         * @jlopez
         *
         * Moving forward to get items:
         * Customer and shipping data should be read from order ($getOrderHeader)
         * $identity should not be used.
         *
         * @link https://app.asana.com/0/322509381293794/334541018122951
         */
        
        $shipNumber = $getOrderHeader['output']['OH_SHP2_NUM'];
        $customerNumber = $getOrderHeader['output']['OH_CUSTNO'];
        
        $getItemsByBrandSize = $plinkUserTable->callProcedureGetItemBrandSizes('BRANDSIZE', $customerNumber, $shipNumber);
        $getItemsByBrand = $plinkUserTable->callProcedureGetItemBrandSizes('BRAND', $customerNumber, $shipNumber);
        $getItemsBySize = $plinkUserTable->callProcedureGetItemBrandSizes('SIZE', $customerNumber, $shipNumber);
        
        $getListSubstitutes = $plinkUserTable->callProcedureGetOrderSubstituteItems($currentOrdNum);
        
        $form = new ItemSubstituteForm();
        $formData = $request->getPost();
        $form->setData($formData);
        
        $viewModel = new ViewModel();
        
        // this is to check the current step for the hyper links in the breadcrums
        $currentUnsavedOrder = $plinkUserTable->callProcedureGetCurrentOrder($customerNumber);
        
        $viewModel->setVariables(array(
            'form' => $form,
            'identity' => $identity,
            'getItemsByBrandSize' => $getItemsByBrandSize,
            'getItemsByBrand' => $getItemsByBrand,
            'getItemsBySize' => $getItemsBySize,
            'currentUnsavedOrder' => $currentUnsavedOrder,
            'getOrderHeader' => $getOrderHeader,
            'getListSubstitutes' => $getListSubstitutes,
            'configSubstitutes' => $configSubstitutes
        ));
        
        return $viewModel;
    }

    /*
     * Item Substitute Ajax Action - This is the action for the item search ajax which is returned on the item search @param void @author rohit
     */
    public function itemSubstituteAjaxAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        $currentOrdNum = 0;
        // this is to check the value of the order id in the session
        $order_session = new Container('order');
        
        if ($order_session->offsetExists('order_num')) {
            $currentOrdNum = $order_session->offsetGet('order_num');
        }
        $brand = '';
        $brandName = '';
        $size = '';
        $sizeName = '';
        $filter = '';
        $viewModel = new ViewModel();
        $request = $this->getRequest();
        $form = new ItemSubstituteForm();
        $formData = $request->getPost();
        $form->setData($formData);
        
        if (! empty($formData->brand))
            $brand = $formData->brand;
        
        if (! empty($formData->size))
            $size = $formData->size;
        
        if (! empty($formData->brandName))
            $brandName = $formData->brandName;
        
        if (! empty($formData->sizeName))
            $sizeName = $formData->sizeName;
        
        if (! empty($formData->filter))
            $filter = $formData->filter;
        // disable layout if request by Ajax
        $viewModel->setTerminal($request->isXmlHttpRequest());
        
        if (empty($currentOrdNum)) {
            $this->flashmessenger()->addMessage("There is no current order. Please try again.");
            $this->redirect()->toRoute('user/index');
        }
        
        // making the object the model
        $plinkUserTable = $this->getPlinkUserTable();
        
        $getItemsFiltered = $plinkUserTable->callProcedureGetItemSearch($currentOrdNum, $brand, $size, $filter);
        
        $searchFilters = array(
            'brand' => $brand,
            'size' => $size,
            'filter' => $filter,
            'brandName' => $brandName,
            'sizeName' => $sizeName
        );
        
        $viewModel->setVariables(array(
            'form' => $form,
            'identity' => $identity,
            'getItemsFiltered' => $getItemsFiltered,
            'searchFilters' => $searchFilters
        ));
        
        return $viewModel;
    }

    /*
     * update Substitutes Action - This is the action for updating the substitute item in the current order
     */
    public function updateSubstitutesAction()
    {
        $configSubstitutes = $this->getServiceLocator()->get('Config');
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        $currentOrdNum = 0;
        // this is to check the value of the order id in the session
        $order_session = new Container('order');
        
        if ($order_session->offsetExists('order_num')) {
            $currentOrdNum = $order_session->offsetGet('order_num');
        }
        $itemNumber = '';
        $action = 'ADD';
        $viewModel = new ViewModel();
        $request = $this->getRequest();
        $form = new ItemSubstituteForm();
        $formData = $request->getPost();
        $form->setData($formData);
        
        if (! empty($formData->itemId))
            $itemNumber = $formData->itemId;
        
        if (! empty($formData->action))
            $action = $formData->action;
        
        $userId = trim($identity['PLU_USER_ID']);
        
        if (empty($currentOrdNum)) {
            $this->flashmessenger()->addMessage("There is no current order. Please try again.");
            $this->redirect()->toRoute('user/index');
        }
        
        // making the object the model
        $plinkUserTable = $this->getPlinkUserTable();
        
        $saveSubstitute = $plinkUserTable->callProcedureSaveOrderSubstituteItem($currentOrdNum, $itemNumber, $action, $userId);
        $getListSubstitutes = $plinkUserTable->callProcedureGetOrderSubstituteItems($currentOrdNum);
        
        $viewModel->setTerminal(true)
            ->setTemplate('user/user/update-substitutes.phtml')
            ->setVariables(array(
            'getListSubstitutes' => $getListSubstitutes,
            'configSubstitutes' => $configSubstitutes
        ));
        
        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($viewModel);
        $jsonModel = new JsonModel();
        $jsonModel->setVariables(array(
            'html' => $htmlOutput,
            'subsCount' => $saveSubstitute['subsCount'],
            'result' => $saveSubstitute['result'],
            'message' => $saveSubstitute['message']
        ));
        
        return $jsonModel;
    }

    /*
     * review Order Action - action to review the order based on the steps done so far, in the place order process
     */
    public function reviewOrderAction()
    {
        $configSubstitutes = $this->getServiceLocator()->get('Config');
        
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        $this->config = $this->getServiceLocator()->get('Config');
        
        $serviceDepartmentsEmail = $this->config['service_departments_emails']['0'];
        $currentOrdNum = 0;
        // this is to check the value of the order id in the session
        $order_session = new Container('order');
        
        if ($order_session->offsetExists('order_num')) {
            $currentOrdNum = $order_session->offsetGet('order_num');
        }
        
        $this->_initView();
        $request = $this->getRequest();
        
        // making the object the model
        $plinkUserTable = $this->getPlinkUserTable();
        $user = $this->getServiceLocator()->get('User/Model/PlinkUser');
        // check if the order header is not set in the session
        if (empty($currentOrdNum)) {
            $this->flashmessenger()->addMessage("There is no current order. Please try again.");
            $this->redirect()->toRoute('user/index');
        }
        
        $userId = trim($identity['PLU_USER_ID']);
        if ($identity['PLU_POLAR_CSR'] == 'Y') {
            $formOrderSubmit = new CsrOrderSubmitForm();
        } else {
            $formOrderSubmit = new OrderSubmitForm();
        }
        $formData = $request->getPost();
        $formOrderSubmit->setData($formData);
        $flashMessages = array();
        
        /* added by kailash 7/7/2016 */
        $folder_path = $this->config['order_attachment_file_path']['path'];
        $orderAttachmentTable = $this->getOrderAttachmentTable();
        $getOrderAttachementItems = $orderAttachmentTable->callProcedureGetOrderAttachedFiles($currentOrdNum);
        $dataOrderAttachment = array();
        
        foreach ($getOrderAttachementItems['result'] as $valueOrder) {
            $dataOrderAttachment[] = $valueOrder;
        }
        
        // echo '<pre>'; print_r($dataOrderAttachment); die;
        /* END */
        
        $getOrderTotals = $plinkUserTable->callProcedureGetOrderTotals($currentOrdNum);
        $getOrderHeader = $plinkUserTable->callProcedureGetOrderHeader($currentOrdNum);
        $getOrderLineItems = $plinkUserTable->callProcedureGetOrderLineItems($currentOrdNum);
        $getListSubstitutes = $plinkUserTable->callProcedureGetOrderSubstituteItems($currentOrdNum);
        $getOrderNotes = $plinkUserTable->callProcedureGetOrderNotes($currentOrdNum);
        
        $custGroup = trim($identity['PLU_CUST_GROUP']);
        $custNum = $getOrderHeader['output']['OH_CUSTNO'];
        $shipto = $getOrderHeader['output']['OH_SHP2_NUM'];
        
        // first of all we check whether we have email ids in the plc customer end
        $customerEmails = $plinkUserTable->callProcedureGetCsrCustomersDetail(trim($identity['PLU_CUST_GROUP']));
        
        /**
         * Store customer email ship to email address (if exists)
         * Store customer emails in `order` container
         */
        
        /**
         * Bugfix
         * @jlopez
         * it should be updated everytime a call to the sp produces a valid result set
         */
        
        if($order_session->offsetUnset('mailto')) {
        
            $order_session->offsetUnset('mailto');
        }
        
        $iteratorEmails = $this->getPlinkCustomerTable()->getCustomerNumberShipToEmails($custGroup, $custNum, $shipto, '');
        $copyEmailOrderTo = $iteratorEmails->current();
        
        // Debug::dump($custGroup);
        // Debug::dump($custNum);
        // Debug::dump($shipto);
        // Debug::dump($copyEmailOrderTo);
        
        if ($copyEmailOrderTo) :
            
            $order_session->offsetSet('mailto', $copyEmailOrderTo['PLST_EMAILS']);
	    
	    endif;
        
            // Debug::dump('After Query:');
            // Debug::dump($order_session->offsetGet('mailto'));
            
        // exit;
            
        /* check delivery/pickup date valid or not start */
            // $dateAry = array();
            // $dateAry = explode('/',$formData->OH_REQ_DELIV_DATE);
            // $fromDate = $toDate = $dateAry[2].$dateAry[0].$dateAry[1];
        $custNum = $getOrderHeader['output']['OH_CUSTNO'];
        $custNum = str_pad($custNum, 8, '0', STR_PAD_LEFT);
        $shipto = $getOrderHeader['output']['OH_SHP2_NUM'];
        $shipto = str_pad($shipto, 4, '0', STR_PAD_LEFT);
        $scheduleDate = $getOrderHeader['output']['OH_REQ_DELIV_DATE'];
        $puDeliv = $getOrderHeader['output']['OH_SHIP_METHOD_CODE'];
        // echo '<pre>'; print_r($getOrderHeader);
        $getRequestedDate = $plinkUserTable->callProcedureRequestDate($custNum, $shipto, $scheduleDate, $puDeliv);
        $arrValueDate = array();
        foreach ($getRequestedDate['result'] as $valuedate) {
            $arrValueDate = $valuedate;
        }
        $currenDate = strtotime(date('m/d/Y'));
        $reqLeadDate = $arrValueDate['REQDATE'];
        // $reqLeadDate = $arrValueDate['REQDATE'] = '20160816';
        if (strtotime($arrValueDate['REQDATE']) < $currenDate) {
            // $form->setMessages(array('save'=>array('Date entered is not valid because lead time is before today')));
            // $valid = false;
            $showMessage = '1';
        } else {
            $showMessage = '0';
        }
        /* End check valid date END */
        
        if ($request->isPost()) {
            
            /**
             * Whether or not display pricing information
             * True after negation SUPPRESS_PRICING
             *
             * @var boolean $includePrice
             */
            
            $includePrice = !($getOrderHeader['output']['SUPPRESS_PRICING'] === 'Y');
            //Debug::dump($getOrderHeader['output']);
            //Debug::dump($includePrice);die();
            if ($identity['PLU_POLAR_CSR'] != 'Y') {
                if ($formData['PLU_EMAIL_SAVE'] == '1') {
                    $formOrderSubmit->setInputFilter($user->getOrderReviewEmailRequiredInputFilter());
                } else {
                    $formOrderSubmit->setInputFilter($user->getOrderReviewEmailInputFilter());
                }
            } else {
                
                $formOrderSubmit->setInputFilter($user->getOrderReviewCSREmailInputFilter());
            }
            // check for the validations
            $emailUser = '';
            $updateSessionEmail = 0;
            if ($formOrderSubmit->isValid()) {
                
                if ($formData['PLU_EMAIL_SAVE'] == '1' && ! empty($formData['PLU_EMAIL'])) {
                    $emailUser = trim($formData['PLU_EMAIL']);
                    $updateSessionEmail = 1;
                }
                
                $saveOrder = $plinkUserTable->callProcedureSubmitOrder($currentOrdNum, $userId, $emailUser);
                
                $trimmedOrderNumber = ltrim(trim($saveOrder['outOrderNumber']), '0');
                // if the order is saved and we get a new order as a result
                // then we unset the order number from the session and redirect the user to the home page with a success message
                if (trim($saveOrder['result']) == '1' && ! empty($trimmedOrderNumber)) {
                    
                    /* save current session value to for the order confirmation page */
                    $session_current_submited = new Container('previous_order');
                    $session_current_submited->offsetSet('current_order_number', $currentOrdNum);
                    /* END */
                    $order_session->offsetUnset('order_num');
                    

                    if ($updateSessionEmail) {
                        $identity['PLU_EMAIL_ADDRESS'] = $emailUser;
                        $this->getAuthService()
                            ->getStorage()
                            ->write($identity);
                    }
                    
                    $this->flashmessenger()->addMessage("<span class='colorblue f18px'>Your order has been submitted for processing. Your Polar Beverage order number is <strong><span class='colorred'>" . $trimmedOrderNumber . '</span></strong>.</span>');
                    // $flashMessages = array("<span class='colorblue f18px'>Your order has been submitted for processing. Your Polar Beverage order number is <strong><span class='colorred'>". $trimmedOrderNumber.'</span></strong>.</span>');
                    // condition to check whether we need to send the mail or not
                    
                    if (! empty($customerEmails['output']['PLC_EMAILS']) || ! empty($formData['PLU_EMAIL']) || ! empty($formData['csr_email_address']) || (! empty(trim($getOrderNotes['notes'])) && ! empty($configSubstitutes['service_departments_emails']))) {
                        // code for the email
                        $smtpHost = '172.25.0.2';
                        $smtpUser = '';
                        $smtpPass = '';
                        $emailTo = $serviceDepartmentsEmail;
                        $emailFrom = trim($configSubstitutes['emailAddresses']['from']);
                        
                        $body = '
<table width="650" border="0" cellpadding="0" cellspacing="0" align="center" style="font-family:Arial, Helvetica, sans-serif;">';
                        if (isset($configSubstitutes['settings'])) {
                            
                            if ($this->layout()->env && ! (strcasecmp($this->layout()->env, 'PROD') === 0)) { // $env == 'TEST' || $env == 'PILOT'){
                                
                                $body .= '<tr>
						<td align="left" style="color: red; font-size: 22px; padding-bottom: 10px;">
							*** NOTE: This email generated from the ' . $this->layout()->env . ' environment.  ***
						</td>
						</tr>';
                            }
                        }
                        
                        $body .= '<tr bgcolor="#f4f9fb">
  <td>
<table  style="border-bottom:1px solid #000; padding:10px 0px 5px 10px; font-family:arial;" width="100%">
  <tr>
		<td style=" padding:0px 0px 0px 10px;"><img src="http://www.polarbev.com/polar_logo-sml.png" alt="Polar Link Ordering System"></td>
		<td style="font-size:35px; color:#103986;">Polar Link<br> Ordering System</td>
		<td style=" border-left:1px solid #cccccc; color:#103986; font-size:18px; font-weight:bold; padding:0px 0px 0px 10px;">
			<span style="font-size:28px; display:block; padding-bottom:15px;">Order#: ' . $trimmedOrderNumber . '</span><br>
			Date Created:<br> ' . date('M d, Y', strtotime($getOrderHeader['output']['OH_ENTRY_DATE'])) . '
		</td>
	</tr>
    </table>
  </td>
	</tr>
	<tr>
		<td colspan="3" style="font-size:28px; color:#103986; padding:20px 0px;">Customer: <span style="font-weight:bold; text-transform:uppercase;">' . trim($identity['CUST_NAME']) . '</span></td>
	</tr>
	<tr>
		<td colspan="3" style="font-size:28px; font-weight:bold; color:#a26a00; padding:0px 0px 20px;">Order Totals:</td>
	</tr>
	<tr>
		<td colspan="3">
			<table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #ccc; font-family:arial; font-size:24px;">
				<thead>
					<tr bgcolor="#e6e6ff">';
                        
                        // excluding price
                        if ($includePrice) :
                            
                            $body .= '<td style="color:#000; border-bottom:1px solid #ccc; padding:10px 15px;" align="center">Amount</td>';
	
	                    endif;
                        
                        $body .= '<td style="color:#000; border-bottom:1px solid #ccc; border-left:1px solid #ccc; padding:10px 15px;" colspan="2" align="center">Quantities</td>
						<td style="color:#000; border-bottom:1px solid #ccc; border-left:1px solid #ccc; padding:10px 15px;" colspan="3" align="center">Weights</td>
					</tr>
					<tr align="center">';
                        
                        if ($includePrice) :
                            
                            $body .= '<td style="font-size:15px; color:#103981; border-bottom:1px solid #ccc; padding:10px 15px;">(excluding deposits)</td>';
	
	                    endif;
                        
                        $body .= '<td style="font-size:21px; color:#103981; border-bottom:1px solid #ccc; border-left:1px solid #ccc; padding:10px 15px;">Cases</td>
						<td style="font-size:21px; color:#103981; border-bottom:1px solid #ccc; border-left:1px solid #ccc; padding:10px 15px;">Pallets</td>
						<td style="font-size:21px; color:#103981; border-bottom:1px solid #ccc; border-left:1px solid #ccc; padding:10px 15px;">Product</td>
						<td style="font-size:21px; color:#103981; border-bottom:1px solid #ccc; border-left:1px solid #ccc; padding:10px 15px;">Pallets</td>
						<td style="font-size:21px; color:#103981; border-bottom:1px solid #ccc; border-left:1px solid #ccc; padding:10px 15px;">Total</td>
					</tr>
					<tr align="center">';
                        
                        if ($includePrice) :
                            
                            $body .= '<td style="font-size:18px; color:#a26a00; padding:10px 15px;"> $' . number_format(trim($getOrderTotals['amount']), '2', '.', ',') . '</td>';
	                    endif;
                        
                        $body .= '<td style="font-size:18px; color:#a26a00; border-left:1px solid #ccc; padding:10px 15px;">' . number_format(trim($getOrderTotals['caseQty']), '0', '.', ',') . '</td>
						<td style="font-size:18px; color:#a26a00; border-left:1px solid #ccc; padding:10px 15px;">' . number_format(trim($getOrderTotals['palletQty']), '2', '.', ',') . '</td>
						<td style="font-size:18px; color:#a26a00; border-left:1px solid #ccc; padding:10px 15px;">' . number_format(trim($getOrderTotals['prodWeight']), '0', '.', ',') . ' lbs' . '</td>
						<td style="font-size:18px; color:#a26a00; border-left:1px solid #ccc; padding:10px 15px;">' . number_format(trim($getOrderTotals['palletWeight']), '0', '.', ',') . ' lbs' . '</td>
						<td style="font-size:18px; color:#a26a00; border-left:1px solid #ccc; padding:10px 15px;">' . number_format(trim($getOrderTotals['totalWeight']), '0', '.', ',') . ' lbs' . '</td>
					</tr>
				</thead>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="3" style="font-size:28px; font-weight:bold; color:#a26a00; padding:20px 0px 20px;">Shipping Information:</td>
	</tr>
	<tr>
		<td colspan="3">
			<table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #ccc; font-family:arial; font-size:18px; color:#001b51;">
				<tr>
					<td align="right" width="40%" style="border-bottom:1px solid #ccc; padding:10px 15px;">Cust# / Ship To:</td>
					<td style="border-bottom:1px solid #ccc; border-left:1px solid #ccc; padding:10px 15px;"><span style="color:#000; font-size:15px;">' . $getOrderHeader['output']['OH_CUSTNO'] . ' / ' . $getOrderHeader['output']['OH_SHP2_NUM'] . '</span></td>
				</tr>
				<tr>
					<td align="right" valign="top" style="border-bottom:1px solid #ccc; padding:10px 15px;">Shipping Address:</td>
					<td style="border-bottom:1px solid #ccc; border-left:1px solid #ccc; padding:10px 15px;">
					   <span style="color:#000; font-size:15px; text-transform:uppercase;">' . trim($getOrderHeader['output']['OH_SHP2_NAME']) . '<br />' . trim($getOrderHeader['output']['OH_SHP2_ADDR1']) . '<br />' . trim($getOrderHeader['output']['OH_SHP2_ADDR2']) . '<br />' . trim($getOrderHeader['output']['OH_SHP2_ADDR3']) . ', ' . trim($getOrderHeader['output']['OH_SHP2_STATE']) . ' ' . trim($getOrderHeader['output']['OH_SHP2_ZIP']) . '</span>
    			     </td>
				</tr>
				<tr>
					<td align="right" style="padding:10px 15px;">Shipping Method:</td>
					<td style="border-left:1px solid #ccc; padding:10px 15px;"><span style="color:#000; font-size:15px;">' . $getOrderHeader['output']['OH_SHIP_METHOD_TEXT'] . '</span></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="3" style="font-size:28px; font-weight:bold; color:#a26a00; padding:20px 0px 20px;">Additional Order Information:</td>
	</tr>
	<tr>
		<td colspan="3">
			<table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #ccc; font-family:arial; font-size:18px; color:#001b51;">
				<tr>
					<td align="right" width="40%" style="border-bottom:1px solid #ccc; padding:10px 15px;">Primary PO#:</td>
					<td style="border-bottom:1px solid #ccc; border-left:1px solid #ccc; padding:10px 15px;"><span style="color:#000; font-size:15px;">' . $getOrderHeader['output']['OH_PO1'] . '</span></td>
				</tr>
				<tr>
					<td align="right" style="border-bottom:1px solid #ccc; padding:10px 15px;">Alternate PO# 1:</td>
					<td style="border-bottom:1px solid #ccc; border-left:1px solid #ccc; padding:10px 15px;"><span style="color:#000; font-size:15px;">' . $getOrderHeader['output']['OH_PO2'] . '</span></td>
				</tr>
				<tr>
					<td align="right" style="border-bottom:1px solid #ccc; padding:10px 15px;">Alternate PO# 2:</td>
					<td style="border-bottom:1px solid #ccc; border-left:1px solid #ccc; padding:10px 15px;"><span style="color:#000; font-size:15px;">' . $getOrderHeader['output']['OH_PO3'] . '</span></td>
				</tr>
				<tr>
					<td align="right" style="border-bottom:1px solid #ccc; padding:10px 15px;">';
                        if (trim($getOrderHeader['output']['OH_SHIP_METHOD_CODE']) != 'P') {
                            $body .= 'Delivery';
                        } else {
                            $body .= 'Pickup';
                        }
                        $body .= ' Date:</td>
					<td style="border-bottom:1px solid #ccc; border-left:1px solid #ccc; padding:10px 15px;"><span style="color:#000; font-size:15px;">';
                        $delivDateVal = (! empty(trim($getOrderHeader['output']['OH_REQ_DELIV_DATE'])) ? trim($getOrderHeader['output']['OH_REQ_DELIV_DATE']) : '');
                        $delivDateTotalDigits = strlen($delivDateVal);
                        if ($delivDateTotalDigits == '8') {
                            $delivDateYear = substr($delivDateVal, '0', '4');
                            $delivDateMonth = substr($delivDateVal, '4', '2');
                            $delivDateDay = substr($delivDateVal, '6', '2');
                            $delivDateVal = $delivDateMonth . '/' . $delivDateDay . '/' . $delivDateYear;
                        } else {
                            $delivDateVal = '';
                        }
                        
                        $delivTimeVal = (! empty(trim($getOrderHeader['output']['OH_REQ_DELIV_TIME'])) ? trim($getOrderHeader['output']['OH_REQ_DELIV_TIME']) : '');
                        if (! empty($delivTimeVal)) {
                            $delivTimeTotalDigits = strlen($delivTimeVal);
                            if ($delivTimeTotalDigits == '5') {
                                $delivTimeHour = substr($delivTimeVal, '0', '1');
                                $delivTimeMinutes = substr($delivTimeVal, '1', '2');
                                $delivTimeAmPm = '';
                                if ($delivTimeHour > 12) {
                                    $delivTimeHour = $delivTimeHour - 12;
                                    $delivTimeAmPm = 'pm';
                                } else {
                                    if ($delivTimeHour < 10) {}
                                    $delivTimeAmPm = 'am';
                                }
                                $delivTimeVal = $delivTimeHour . ':' . $delivTimeMinutes . $delivTimeAmPm;
                            } else if ($delivTimeTotalDigits == '6') {
                                $delivTimeHour = substr($delivTimeVal, '0', '2');
                                $delivTimeMinutes = substr($delivTimeVal, '2', '2');
                                $delivTimeAmPm = '';
                                if ($delivTimeHour > 12) {
                                    $delivTimeHour = $delivTimeHour - 12;
                                    $delivTimeAmPm = 'pm';
                                } else {
                                    if ($delivTimeHour < 10) {}
                                    $delivTimeAmPm = 'am';
                                }
                                $delivTimeVal = $delivTimeHour . ':' . $delivTimeMinutes . $delivTimeAmPm;
                            } else {
                                $delivTimeVal = '';
                            }
                        }
                        $body .= $delivDateVal . '</span></td>
				</tr>';
                        if (trim($getOrderHeader['output']['OH_SHIP_METHOD_CODE']) != 'P') {
                            $body .= '<tr>
					<td align="right" style="border-bottom:1px solid #ccc; padding:10px 15px;">Delivery Time:</td>
					<td style="border-bottom:1px solid #ccc; border-left:1px solid #ccc; padding:10px 15px;"><span style="color:#000; font-size:15px;">' . $delivTimeVal . '</span></td>
				</tr>';
                        }
                        $body .= '<tr>
					<td align="right" style="padding:10px 15px;">Notes/Comments:</td>
					<td style="border-left:1px solid #ccc; padding:10px 15px;"><span style="color:#000; font-size:15px;">' . $getOrderNotes['notes'] . '</span></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="3" style="font-size:28px; font-weight:bold; color:#a26a00; padding:20px 0px 20px;">Items on Order:</td>
	</tr>
	<tr>
		<td colspan="3">
	
			<table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #ccc; font-family:arial; font-size:18px; color:#103981;">
				<tr bgcolor="#e6e6e6" align="center">
					<td align="right" style="border-bottom:1px solid #ccc; padding:10px 15px;"><strong>Product</strong></td>
					<td style="border-bottom:1px solid #ccc; border-left:1px solid #ccc; padding:10px 15px;"><strong>Description</strong></td>
					<td style="border-bottom:1px solid #ccc; border-left:1px solid #ccc; padding:10px 15px;"><strong>Qty</strong></td>
					<td style="border-bottom:1px solid #ccc; border-left:1px solid #ccc; padding:10px 15px;"><strong>UOM</strong></td>';
                        
                        if ($includePrice) :
                            $body .= '<td style="border-bottom:1px solid #ccc; border-left:1px solid #ccc; padding:10px 15px;"><strong>Price</strong></td>';
                            $body .= '<td style="border-bottom:1px solid #ccc; border-left:1px solid #ccc; padding:10px 15px;"><strong>Ext. Price</strong></td>';
						                        endif;
                        
                        $body .= '</tr>';
                        
                        if (! empty($getOrderLineItems['output'])) {
                            foreach ($getOrderLineItems['output'] as $orderLine) {
                                $body .= '<tr valign="top">
    					<td align="right" style="border-bottom:1px solid #ccc; padding:10px 15px;"><span style="color:#000; font-size:15px;">' . trim($orderLine['OL_ITEM_NUM']) . '</span></td>
    					<td style="border-bottom:1px solid #ccc; border-left:1px solid #ccc; padding:10px 15px;"><span style="color:#000; font-size:15px;">' . $orderLine['OL_ITEM_DESC'] . '</span></td>
    					<td style="border-bottom:1px solid #ccc; border-left:1px solid #ccc; padding:10px 15px;"><span style="color:#000; font-size:15px;">' . number_format($orderLine['OL_QTY_ORD'], '0') . '</span></td>
    					<td style="border-bottom:1px solid #ccc; border-left:1px solid #ccc; padding:10px 15px;"><span style="color:#000; font-size:15px;">' . trim($orderLine['OL_SELL_UOM']) . '</span></td>';
                                
                                if ($includePrice) :
                                    $body .= '<td style="border-bottom:1px solid #ccc; border-left:1px solid #ccc; padding:10px 15px;"><span style="color:#000; font-size:15px;">' . number_format($orderLine['OL_NET_PRICE'], 2, '.', ',') . '</span></td>';
                                    $body .= '<td style="border-bottom:1px solid #ccc; border-left:1px solid #ccc; padding:10px 15px;"><span style="color:#000; font-size:15px;">' . number_format($orderLine['OL_EXT_PRICE'], 2, '.', ',') . '</span></td>';
						                                endif;
                                
                                $body .= '</tr>';
                            }
                        } else {
                            $body .= '<tr valign="top">
							<td colspan="6" align="center" style="border-bottom:1px solid #ccc; padding:10px 15px;">No Item Exists</td>
							</tr>';
                        }
                        $body .= '</table>
		</td>
	</tr>';
                        if (! empty($getOrderHeader['output']) && trim($getOrderHeader['output']['SUBS_REQUIRED']) == 'Y') {
                            $showSubstitutesTab = true;
                        } else {
                            $showSubstitutesTab = false;
                        }
                        if ($showSubstitutesTab) {
                            $body .= '<tr>
		<td colspan="3" style="font-size:28px; font-weight:bold; color:#a26a00; padding:20px 0px 20px;">Substitute Items on this Order:</td>
	</tr>
	<tr>
		<td colspan="3">
			<table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #ccc; font-size:18px; font-family:arial; color:#103981;">
				<tr bgcolor="#e6e6e6">
					<td style="border-bottom:1px solid #ccc; padding:10px 15px;"><strong>Sub Product</strong></td>
					<td style="border-bottom:1px solid #ccc; border-left:1px solid #ccc; padding:10px 15px;"><strong>Sub Description</strong></td>
				</tr>';
                            
                            if (! empty($getListSubstitutes['output'])) {
                                foreach ($getListSubstitutes['output'] as $substitute) {
                                    $body .= '<tr valign="top">
					<td width="40%" style="border-bottom:1px solid #ccc; padding:10px 15px;"><span style="color:#000; font-size:15px;">' . trim($substitute['PLS_ITEM_NO']) . '</span></td>
					<td style="border-bottom:1px solid #ccc; border-left:1px solid #ccc; padding:10px 15px;"><span style="color:#000; font-size:15px;">' . trim($substitute['ITEM_DESC']) . '</span></td>
				</tr>';
                                }
                            } else {
                                $body .= '<tr valign="top">
					<td colspan="2" style="border-bottom:1px solid #ccc; padding:10px 15px;"> No substitute exists for this order</td>
				</tr>';
                            }
                            $body .= '</table>
		</td>
	</tr>';
                        }
                        $body .= '</table>';
                        
                        $subject = '';
                        
                        // if(isset($configSubstitutes['settings'])){
                        // $env = '';
                        // if($configSubstitutes['settings']['environment'] == "TST"){
                        // $env = '** TEST ** ';
                        // } else if($configSubstitutes['settings']['environment'] == "PLT"){
                        // $env = '** PILOT ** ';
                        // }
                        // $subject .= $env;
                        // }
                        
                        if ($this->layout()->env) {
                            $env = '**' . $this->layout()->env . '**';
                        }
                        
                        /**
                         * Get customer name from session
                         * https://bugtracker.zoho.com/portal/division1systemsllc#buginfo/1014934000000014225/1014934000000032001
                         */
                        $customerName = array_key_exists('CUST_NAME', $identity) ? 
                        trim($identity['CUST_NAME']) : '';
                        
                        $subject .= sprintf("%s - PolarLink Order# %d", 
                        html_entity_decode($customerName), 
                        (int) $trimmedOrderNumber);
                        
                        $email = $emailTo;
                        $from = $emailFrom;
                        
                        // create a new Zend\Mail\Message object
                        $message = new Message();
                        
                        // create a MimeMessage object that will hold the mail body and any attachments
                        $bodyPart = new Mime\Message();
                        
                        // create the mime part for the message body
                        // you can add one for text and one for html if needed
                        $bodyMessage = new Mime\Part($body);
                        $bodyMessage->type = 'text/html';
                        
                        $bodyPart->addPart($bodyMessage);
                        
                        if ($dataOrderAttachment) {
                            foreach ($dataOrderAttachment as $row) {
                                $pathToAttachment = $folder_path . $currentOrdNum . "/" . $row['PLAT_IFS_FILENAME'];
                                $attachment = new Mime\Part(fopen($pathToAttachment, 'r'));
                                $attachment->type = 'application/octet-stream';
                                $attachment->filename = $row['PLAT_UPL_FILENAME'];
                                $attachment->disposition = Mime\Mime::DISPOSITION_ATTACHMENT;
                                $attachment->encoding = Mime\Mime::ENCODING_BASE64;
                                $bodyPart->addPart($attachment);
                            }
                        }
                        
                        $sendMail = false;
                        
                        // changing the value of PLU_EMAIL_ADDRESS Dynamically
                        $cleanEmailAddress = trim($formData['PLU_EMAIL']);
                        
                        if (filter_var($cleanEmailAddress, FILTER_VALIDATE_EMAIL)) :
                            $sendMail = true;
                            $message->addTo($cleanEmailAddress);
						                        endif;
                        
                        if (! empty($customerEmails['output']['PLC_EMAILS'])) {
                            
                            $emailsPlcs = explode(',', $customerEmails['output']['PLC_EMAILS']);
                            
                            foreach ($emailsPlcs as $emailPlc) {
                                
                                $cleanEmailAddress = trim($emailPlc);
                                
                                if (filter_var($cleanEmailAddress, FILTER_VALIDATE_EMAIL)) :
                                    $sendMail = true;
                                    $message->addTo($cleanEmailAddress);
                                                        endif;
                                
                            }
                        }
                        
                        // sending the emails only in case the CSR has manually input them
                        if (! empty($formData['csr_email_address'])) {
                            $emailsPlcs = explode(',', trim($formData['csr_email_address']));
                            foreach ($emailsPlcs as $emailPlc) {
                                
                                $cleanEmailAddress = trim($emailPlc);
                                
                                if (filter_var($cleanEmailAddress, FILTER_VALIDATE_EMAIL)) :
                                    $sendMail = true;
                                    $message->addTo($cleanEmailAddress);
						                                endif;
                                
                            }
                        }
                        
                        $message->setEncoding('utf-8')
                            ->
                        // ->addTo($email)
                        // ->setReplyTo($replyTo)
                        addFrom($from)
                            ->setSubject($subject)
                            ->setBody($bodyPart); // set the body of the Mail to the MimeMessage with the mail content and attachment
                                                  
                        // Setup SMTP transport using LOGIN authentication
                        $transport = new SmtpTransport();
                        $options = new SmtpOptions(array(
                            'name' => 'mail.polarbev.com',
                            'host' => $smtpHost,
                            'port' => 25
                        ));
                        
                        $transport->setOptions($options);
                        
                        if ($sendMail) {
                            
                            try {
                                
                                // =============================================
                                // jlopez
                                // #1 - Email Notification
                                //
                                // send primary email to:
                                // - user who posting the order
                                // - csr email address (if any)
                                // - additional email addresses entered by the user
                                $transport->send($message);
                                
                                // =============================================
                                // jlopez
                                // #2 - Separated Emails
                                //
                                // every customer ship to email addresses should receive a copy
                                //
                                // Refer to:
                                // /user/csr-customer-edit/$custGroup
                                //
                                // Tab ship-to email notifications
                                // https://app.asana.com/0/322466378561882/364097956035321/f
                                //
                                // ==========================================================
                                
                                if ($order_session->offsetExists('mailto')) :
                                    
                                    if (! empty($order_session->offsetGet('mailto'))) :
                                        
                                        $mailto = explode(',', trim($order_session->offsetGet('mailto')));
                                        
                                        foreach ($mailto as $sendCopyTo) :
                                            
                                            $message->setTo(trim($sendCopyTo));
                                            
                                            $transport->send($message);
                                        endforeach
                                        ;
						                                
						                                  endif;
						                                
						                                endif;
                                    
                                
                            } catch (\Exception $e) {
                                echo "Error sending mail: <br>" . $e->getMessage() . '<br> in file ' . $e->getFile() . ' at line ' . $e->getLine() . '<br>Stack trace: <br> ' . $e->getTraceAsString();
                                die('mail could not be sent');
                            }
                        } else {
                            try {
                                /**
                                 * Ivan
                                 *
                                 * Fix send ship-to emails when no additional emails are added to the order
                                 */
                                if ($order_session->offsetExists('mailto')) :

                                    if (! empty($order_session->offsetGet('mailto'))) :

                                        $mailto = explode(',',trim($order_session->offsetGet('mailto')));

                                        foreach($mailto as $sendCopyTo):

                                            $message->setTo(trim($sendCopyTo));

                                            $transport->send($message);

                                        endforeach;
                                    endif;
                                endif;
                            } catch (\Exception $e) {
                                echo "Error sending mail: <br>" . $e->getMessage() . '<br> in file ' . $e->getFile() . ' at line ' . $e->getLine() . '<br>Stack trace: <br> ' . $e->getTraceAsString();
                                die('mail could not be sent');
                            }
                        }
                        
                        // code to send the email to the service department email addresses
                        if (! empty(trim($getOrderNotes['notes'])) || ! empty($dataOrderAttachment['0']['PLAT_ATTACH_NO']) && ! empty($configSubstitutes['service_departments_emails'])) {
                            $messageServiceDepartment = new Message();
                            
                            foreach ($configSubstitutes['service_departments_emails'] as $emailServiceDepartment) {
                                $messageServiceDepartment->addTo(trim($emailServiceDepartment));
                            }
                            
                            $messageServiceDepartment->setEncoding('utf-8')
                                ->
                            // ->addTo($email)
                            // ->setReplyTo($replyTo)
                            addFrom($from)
                                ->setSubject($subject)
                                ->setBody($bodyPart);
                            
                            try {
                                $transport->send($messageServiceDepartment);
                            } catch (Exception $e) {
                                echo "Error sending mail: <br>" . $e->getMessage() . '<br> in file ' . $e->getFile() . ' at line ' . $e->getLine() . '<br>Stack trace: <br> ' . $e->getTraceAsString();
                                die('mail could not be sent');
                            }
                        }
                        // code to send the email to the service department email addresses ends here
                        
                        // code for the email ends here
                    } // condition where we see that the if there is any email address then we send the mail
                } else {
                    $this->flashmessenger()->addMessage("<span class='colorred'>Your order could not be saved. Please try again.</span>");
                    // $flashMessages = array("<span class='colorred'>Your order could not be saved. Please try again.</span>");
                }
                $this->redirect()->toRoute('user/orderConfirmation');
                //clear session container
                $order_session->getManager()->getStorage()->clear('order');
            }
        }
        
        $form = new OrderHeaderForm();
        
        $formData = $request->getPost();
        $form->setData($formData);
        
        $viewModel = new ViewModel();
        
        // this is to check the current step for the hyper links in the breadcrums
        $currentUnsavedOrder = $plinkUserTable->callProcedureGetCurrentOrder(trim($identity['PLU_CUSTNO']));
        $viewModel->setVariables(array(
            'form' => $form,
            'showMessage' => $showMessage,
            'reqLeadDate' => $reqLeadDate,
            'dataOrderAttachment' => $dataOrderAttachment,
            'identity' => $identity,
            'getOrderTotals' => $getOrderTotals,
            'getOrderLineItems' => $getOrderLineItems,
            'currentUnsavedOrder' => $currentUnsavedOrder,
            'getOrderHeader' => $getOrderHeader,
            'getListSubstitutes' => $getListSubstitutes,
            'getOrderNotes' => $getOrderNotes,
            'configSubstitutes' => $configSubstitutes,
            'currentOrdNum' => $currentOrdNum,
            'customerEmails' => $customerEmails,
            'formOrderSubmit' => $formOrderSubmit,
            'request' => $request
        ));
        
        return $viewModel;
    }

    public function exportOrderHistoryAction()
    {
        try {
            
            /**
             * Session Sanity Check...
             */
            $storage = $this->getAuthService()->getIdentity();
            
            if ($this->getAuthService()->hasIdentity()) {
                // Identity exists; get it
                $identity = $this->getAuthService()->getIdentity();
            } else {
                $this->redirect()->toRoute('user');
            }
            
            /**
             * Initial setup for export order history
             */
            $now = date("Ymd_His", time());
            
            $filename = sprintf('PolarLink_OrderHistory_%s.csv', $now);
            
            header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
            
            header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
            
            header("Last-Modified: " . gmdate('D, d M Y H:i:s \G\M\T', time()) . " GMT");
            
            header("Content-Disposition: attachment;filename={$filename}");
            
            header("Content-Transfer-Encoding: binary");
            
            header("Content-Type: application/octet-stream");
            
            $content = fopen('php://output', 'w');
            
            /**
             * Here begins the actual logic to pulling down order history data
             */
            $rows = array();
            $row = array();
            
            $request = $this->getRequest();
            
            // $fromDate = $toDate = '0';
            $user = $this->getServiceLocator()->get('User/Model/PlinkUser');
            
            $fromDate = urldecode($request->getQuery()->get('FROM_DATE'));
            $toDate = urldecode($request->getQuery()->get('TO_DATE'));
            $filterUserId = urldecode($request->getQuery()->get('FILTER_USER_ID'));
            
            // set value to <select/>
            $found = false;
            
            $selectedFilterUserId = '';
            
            if ($filterUserId === 'ALL') {
                
                $selectedFilterUserId = '';
                $found = true;
            }
            
            if ($filterUserId === '') {
                
                $selectedFilterUserId = $identity['PLU_USER_ID'];
                $found = true;
            }
            
            if (! $found) {
                
                $selectedFilterUserId = $filterUserId;
            }
            
            $fromDate = str_replace('/', '', $fromDate);
            $toDate = str_replace('/', '', $toDate);
            
            $fromDate = substr($fromDate, - 4, 4) . substr($fromDate, 0, 2) . substr($fromDate, 2, 2);
            $toDate = substr($toDate, - 4, 4) . substr($toDate, 0, 2) . substr($toDate, 2, 2);
            
            $customerGroup = $identity['PLU_CUST_GROUP'];
            
            $plinkUserTable = $this->getPlinkUserTable();
            
            /**
             * Call stored procedure per @jvalance
             *
             * sproc name: sp_Get_OrderHistory_Download
             */
            $exportOrderHistory = $plinkUserTable->callProcedureExportGetOrderHistory(
            $storage['PLU_USER_ID'], 
            $customerGroup, 
            $selectedFilterUserId, 
            (! empty($fromDate)) ? $fromDate : '0', 
            (! empty($toDate)) ? $toDate : '0')
            ;
            
            /**
             * Parse sproc results
             */
            if (array_key_exists('output', $exportOrderHistory)) {
                
                /**
                 * Get headings column
                 */
                if (! empty($exportOrderHistory['output'])) {
                    
                    $headings = array_keys($exportOrderHistory['output'][0]);
                    
                    array_push($rows, $headings);
                }
                
                /**
                 * Get data columns
                 */
                for ($i = 1; $i < count($exportOrderHistory['output']); $i ++) {
                    
                    $row = array();
                    
                    foreach ($headings as $key => $value) {
                        
                        array_push($row, $exportOrderHistory['output'][$i][$value]);
                    }
                    
                    array_push($rows, $row);
                }
            }
            
            /**
             * Data not found verification
             */
            if (empty($rows)) {
                
                array_push($rows, array(
                    'No data available'
                ));
            }
            
            /**
             * Write all collected data to CSV
             */
            array_map(function ($item) use ($content) {
                
                fputcsv($content, $item);
            }, $rows);
            
            /**
             * Close stream resource
             */
            fclose($content);
        } catch (\Exception $e) {
            
            error_log($e->getMessage());
        }
        
        /**
         * End controller action
         * Skip render view
         */
        
        exit();
    }

    public function orderHistoryAction()
    {
        try {
            
            $storage = $this->getAuthService()->getIdentity();
            
            if ($this->getAuthService()->hasIdentity()) {
                // Identity exists; get it
                $identity = $this->getAuthService()->getIdentity();
            } else {
                $this->redirect()->toRoute('user');
            }
            $fromDate = $toDate = '0';
            $user = $this->getServiceLocator()->get('User/Model/PlinkUser');
            
            $form = new OrderHistorySearchForm();
            $request = $this->getRequest();
            
            /**
             * Display only orders for this user
             * Empty for all
             */
            $filterUserId = '';
            
            if ($request->isPost()) {
                
                $formData = $request->getPost()->toArray();
                
                $form->setData($formData);
                
                if (! empty($formData['FROM_DATE'])) {
                    $fromDateArray = explode('/', $formData['FROM_DATE']);
                    $fromDate = $fromDateArray['2'] . $fromDateArray['0'] . $fromDateArray['1'];
                }
                if (! empty($formData['TO_DATE'])) {
                    $toDateArray = explode('/', $formData['TO_DATE']);
                    $toDate = $toDateArray['2'] . $toDateArray['0'] . $toDateArray['1'];
                }
                
                if (! empty($formData['FILTER_USER_ID'])) {
                    
                    $filterUserId = $formData['FILTER_USER_ID'];
                }
            }
            
            /**
             * Select (Customer group users)
             */
            
            // obtain a Customer adapter
            $customer = $this->getServiceLocator()->get(CustomerController::ADAPTER);
            
            // setup parameter container
            $customer->setParameterContainer(new ParameterContainer());
            
            // get groups
            $users = $customer->users(
            $identity['PLU_CUST_GROUP'], 
            $identity['PLU_USER_ID']);
            
            // parse results for <select/>
            
            $userOptions['ALL'] = 'All Users';
            
            foreach ($users as $key => $user) {
                
                $optionValue = $user['PLU_USER_ID'];
                
                $optionText = sprintf('(%s) %s %s', 
                strtoupper($user['PLU_USER_ID']), 
                strtoupper($user['PLU_FIRST_NAME']), 
                strtoupper($user['PLU_LAST_NAME']));
                
                $userOptions[$optionValue] = $optionText;
            }
            
            // add <select/> form
            
            $form->add(
            array(
                
                'type' => Select::class,
                
                'name' => 'FILTER_USER_ID',
                
                'options' => array(
                    
                    'value_options' => $userOptions
                )
            ));
            
            // set value to <select/>
            $found = false;
            
            $selectedFilterUserId = '';
            
            if ($filterUserId === 'ALL') {
                
                $selectedFilterUserId = '';
                $found = true;
            }
            
            if ($filterUserId === '') {
                
                $selectedFilterUserId = $identity['PLU_USER_ID'];
                $found = true;
            }
            
            if (! $found) {
                
                $selectedFilterUserId = $filterUserId;
            }
            
            $form->get('FILTER_USER_ID')->setValue($selectedFilterUserId);
            
            $this->_initView();
            
            $viewModel = new ViewModel();
            $customerGroup = $identity['PLU_CUST_GROUP'];
            // making the object the model
            $plinkUserTable = $this->getPlinkUserTable();
            $getOrderHistorySearch = $plinkUserTable->callProcedureGetOrderHistorySearch(
            $storage['PLU_USER_ID'], 
            $customerGroup, 
            $selectedFilterUserId, 
            $fromDate, 
            $toDate);
            
            $viewModel->setVariables(array(
                'identity' => $identity,
                
                'form' => $form,
                
                'getOrderHistorySearch' => $getOrderHistorySearch
            ));
            
            return $viewModel;
        } catch (\Exception $e) {
            
            throw new \Exception($e->getMessage);
        }
    }

    public function orderHistoryViewAction()
    {
        // echo '<pre>'; print_r($this->params()->fromQuery('paramname')); die;
        $currentOrdNum = trim($this->getEvent()
            ->getRouteMatch()
            ->getParam('orderNum'));
        $orderNumToDisplay = trim($this->getEvent()
            ->getRouteMatch()
            ->getParam('orderNumToDisplay'));
        $configSubstitutes = $this->getServiceLocator()->get('Config');
        
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        $this->_initView();
        
        $request = $this->getRequest();
        
        // making the object the model
        $plinkUserTable = $this->getPlinkUserTable();
        
        // check if the order header is not set in the session
        if (empty($currentOrdNum)) {
            $this->flashmessenger()->addMessage("There is no current order. Please try again.");
            $this->redirect()->toRoute('user/index');
        }
        
        $userId = trim($identity['PLU_USER_ID']);
        // if ($request->isPost ()) {
        // $saveOrder = $plinkUserTable->callProcedureSubmitOrder ( $currentOrdNum , $userId );
        // $trimmedOrderNumber = ltrim(trim($saveOrder['outOrderNumber']), '0');
        // // if the order is saved and we get a new order as a result
        // // then we unset the order number from the session and redirect the user to the home page with a success message
        // if(trim($saveOrder['result']) == '1' && !empty($trimmedOrderNumber)){
        // $order_session->offsetUnset ( 'order_num' );
        // $this->flashmessenger ()->addMessage ( "<span class='colorblue'>Your order has been submitted for processing. Your Polar Beverage order number is ". $trimmedOrderNumber.'.</span>');
        // } else {
        // $this->flashmessenger ()->addMessage ( "<span class='colorred'>Your order could not be saved. Please try again.</span>");
        // }
        // $this->redirect ()->toRoute ( 'user/index' );
        // }
        
        /* added by kailash 7/12/2016 */
        
        // $order_session = new Container ( 'order' );
        // $currentOrdNum1 = $order_session->offsetGet ( 'order_num' );
        
        $dataOrderAttachment = array();
        if (! empty($currentOrdNum)) {
            $orderAttachmentTable = $this->getOrderAttachmentTable();
            $getOrderAttachementItems = $orderAttachmentTable->callProcedureGetOrderAttachedFiles($currentOrdNum);
            
            foreach ($getOrderAttachementItems['result'] as $valueOrder) {
                $dataOrderAttachment[] = $valueOrder;
            }
        }
        // echo '<pre>'; print_r($dataOrderAttachment); die;
        
        $getOrderTotals = $plinkUserTable->callProcedureGetOrderTotals($currentOrdNum);
        $getOrderHeader = $plinkUserTable->callProcedureGetOrderHeader($currentOrdNum);
        $getOrderLineItems = $plinkUserTable->callProcedureGetOrderLineItems($currentOrdNum);
        $getListSubstitutes = $plinkUserTable->callProcedureGetOrderSubstituteItems($currentOrdNum);
        $getOrderNotes = $plinkUserTable->callProcedureGetOrderNotes($currentOrdNum);
        
        $form = new OrderHeaderForm();
        $formData = $request->getPost();
        $form->setData($formData);
        
        $viewModel = new ViewModel();
        
        /**
         * Parse OH_REQ_DELIV_TIME
         *
         * Same code block exists in order header action
         * 
         * @todo : Encapsulate code below.
         */
        
        if (! empty($getOrderHeader['output']['OH_REQ_DELIV_TIME'])) {
            
            $delivTimeVal = $getOrderHeader['output']['OH_REQ_DELIV_TIME'];
            
            $delivTimeTotalDigits = strlen($delivTimeVal);
            if ($delivTimeTotalDigits == '5') {
                $delivTimeHour = substr($delivTimeVal, '0', '1');
                $delivTimeMinutes = substr($delivTimeVal, '1', '2');
                $delivTimeAmPm = '';
                if ($delivTimeHour > 12) {
                    $delivTimeHour = $delivTimeHour - 12;
                    $delivTimeAmPm = 'pm';
                } else {
                    if ($delivTimeHour < 10) {}
                    $delivTimeAmPm = 'am';
                }
                $delivTimeVal = $delivTimeHour . ':' . $delivTimeMinutes . $delivTimeAmPm;
            } else if ($delivTimeTotalDigits == '6') {
                $delivTimeHour = substr($delivTimeVal, '0', '2');
                $delivTimeMinutes = substr($delivTimeVal, '2', '2');
                $delivTimeAmPm = '';
                if ($delivTimeHour > 12) {
                    $delivTimeHour = $delivTimeHour - 12;
                    $delivTimeAmPm = 'pm';
                } else {
                    if ($delivTimeHour < 10) {}
                    $delivTimeAmPm = 'am';
                }
                $delivTimeVal = $delivTimeHour . ':' . $delivTimeMinutes . $delivTimeAmPm;
            } else {
                $delivTimeVal = '';
            }
            
            $getOrderHeader['output']['OH_REQ_DELIV_TIME'] = $delivTimeVal;
        }
        
        $viewModel->setVariables(array(
            'form' => $form,
            'identity' => $identity,
            'dataOrderAttachment' => $dataOrderAttachment,
            'getOrderTotals' => $getOrderTotals,
            'getOrderLineItems' => $getOrderLineItems,
            'currentOrdNum' => $currentOrdNum,
            'orderNumToDisplay' => $orderNumToDisplay,
            'getOrderHeader' => $getOrderHeader,
            'getListSubstitutes' => $getListSubstitutes,
            'getOrderNotes' => $getOrderNotes,
            'configSubstitutes' => $configSubstitutes
        ));
        
        return $viewModel;
    }

    /**
     * CSR resolve homepage
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function csrIndexAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        $this->_initView();
        //
        
        if ($identity['PLU_POLAR_CSR'] != 'Y') {
            
            $this->redirect()->toRoute('user/index');
        }
        // $startDate = '0001-01-01';
        // $endDate = '0001-01-01';
        $userIdToFetchAnnouncement = 0;
        $shipTo = 0;
        // code to call the stored procedure
        $plinkAnnouncementsTable = $this->getPlinkAnnouncementsTable();
        $plinkUserTable = $this->getPlinkUserTable();
        
        // $currentAnnouncement = $plinkAnnouncementsTable->callProcedureGetCurrentAnnouncements ( trim ( $identity ['PLU_USER_ID'] ), $startDate, $endDate );
        $currentAnnouncement = $plinkAnnouncementsTable->callProcedureGetCurrentAnnouncements($userIdToFetchAnnouncement, $shipTo);
        
        $viewModel = new ViewModel();
        
        //
        // Pending Orders Storage
        //
        $helperPendingOrders = new PendingOrders($plinkUserTable);
        
        $orders = $helperPendingOrders->getOrders(
        $identity['PLU_CUST_GROUP'], $identity['PLU_USER_ID'])
        ;
        
        $pendingOrders = new Container('pendingOrders');
        
        if ($pendingOrders->offsetExists('orders')) {
            
            $pendingOrders->offsetUnset('orders');
        }
        
        $pendingOrders->offsetSet('orders', $orders);
        
        $partialEditCurrentOrder = $this->getRequest()
            ->
        getHeaders()
            ->
        get('Cookie')->
        OH_PLINK_ORDERNO;
        
        /**
         * In order to prepare data for item inquiry
         * a customer must be selected
         * Skip otherwise
         */
        if (! empty(preg_replace('/\s+/', '', $identity['PLU_CUST_GROUP']))) {
            
            $customerHelper = new Customer($plinkUserTable);
            $defaultShipTo = $customerHelper->getCustomerDefaultShipToAddress($identity['PLU_CUST_GROUP']);
            
            $sessInquiry = Inquiry::getInstance();
            $sessInquiry::set('shipNumber', $defaultShipTo['ST_NUM']);
            $sessInquiry::set('customerNumber', $defaultShipTo['ST_CUST']);
            $sessInquiry::set('customerGroup', $identity['PLU_CUST_GROUP']);
            $sessInquiry::set('formattedAddress', Address::format($defaultShipTo));
        }
        
        $viewModel->setVariables(
        array(
            
            'currentAnnouncement' => $currentAnnouncement,
            
            'flashMessages' => $this->flashMessenger()
                ->getMessages(),
            
            'identity' => $identity,
            
            'partialEditCurrentOrder' => $partialEditCurrentOrder,
            
            'pendingOrders' => $pendingOrders->offsetGet('orders')
        ));
        
        return $viewModel;
    }

    public function csrSelectCustomerAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        if ($identity['PLU_POLAR_CSR'] != 'Y') {
            $this->redirect()->toRoute('user/index');
        }
        
        $this->_initView();
        $request = $this->getRequest();
        $searchFilter = '';
        $form = new ShippingSearchForm();
        $formData = $request->getPost();
        $formCsrCustomerForm = new CsrCustomerForm();
        $customerOrderShipping = '';
        // this is to set the data in the search form
        $form->setData($formData);
        // calling the model
        $plinkUserTable = $this->getPlinkUserTable();
        $pendingOrders = new PendingOrders($plinkUserTable);
        
        $getOrderHeader = array();
        
        if ($request->isPost()) {
            $hiddenSearchShipping = $this->getRequest()->getPost('searchshipping');
            if ($hiddenSearchShipping == 'search') {
                $searchFilter = $this->getRequest()->getPost('SEARCHPARAMETER');
            }
            $formSave = $this->getRequest()->getPost('save');
            if ($formSave == 'submit') {
                
                $csr_cust_group = trim($this->getRequest()->getPost('csr_cust_group'));
                
                $csr_user_name = $this->getRequest()->getPost('csr_user_name');
                
                $identity['PLU_CUST_GROUP'] = $csr_cust_group;
                $identity['CUST_NAME'] = $csr_user_name;
                $identity['PLU_CUSTNO'] = '0';
                $this->getAuthService()
                    ->getStorage()
                    ->write($identity);
                
                // this is to check the value of the order id in the session
                $order_session = new Container('order');
                $currentOrdNum = 0;
                if ($order_session->offsetExists('order_num')) {
                    $currentOrdNum = $order_session->offsetGet('order_num');
                    if ($currentOrdNum > 0) {
                        
                        $order_session->offsetUnset('order_num');
                        $order_session->offsetUnset('mailto');
                    }
                }
                
                /**
                 * jlopez
                 *
                 * Reset current order
                 *
                 * @link https://app.asana.com/0/322475715676002/340911515885686
                 */
                $headers = $this->getResponse()->getHeaders();
                $headers->addHeader($pendingOrders->clearCurrentOrder());
                
                $this->redirect()->toRoute('user/csrIndex');
            }
        }
        // code to call the stored procedure for the shipTos
        
        $plinkCustomers = $plinkUserTable->callProcedureGetPLinkCustomers($searchFilter);
        
        $viewModel = new ViewModel();
        
        $viewModel->setVariables(array(
            'identity' => $identity,
            'plinkCustomers' => $plinkCustomers,
            'form' => $form,
            'formCsrCustomerForm' => $formCsrCustomerForm
        ));
        return $viewModel;
    }

    public function csrAnnouncementSearchAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        if ($identity['PLU_POLAR_CSR'] != 'Y') {
            $this->redirect()->toRoute('user/index');
        }
        
        $this->_initView();
        $request = $this->getRequest();
        $searchFilter = '';
        $form = new CsrAnnouncementSearchForm();
        $formData = $request->getPost()->toArray();
        $customerOrderShipping = '';
        // this is to set the data in the search form
        $form->setData($formData);
        // calling the model
        $plinkUserTable = $this->getPlinkUserTable();
        $getOrderHeader = array();
        $facility = '';
        $custType = '';
        $startDate = '0001-01-01';
        $endDate = '0001-01-01';
        $userId = '';
        $announcementText = '';
        
        if ($request->isPost()) {
            
            if (! empty($formData['CUST_TYPE'])) {
                $custType = $formData['CUST_TYPE'];
            }
            if (! empty($formData['FACILITY'])) {
                $facility = $formData['FACILITY'];
            }
            if (! empty($formData['ANNOUNCEMENT_TEXT'])) {
                $announcementText = trim($formData['ANNOUNCEMENT_TEXT']);
            }
            if (! empty($formData['START_DATE'])) {
                $startDateArray = explode('/', $formData['START_DATE']);
                $startDate = $startDateArray['2'] . '-' . $startDateArray['0'] . '-' . $startDateArray['1'];
            }
            if (! empty($formData['END_DATE'])) {
                $endDateArray = explode('/', $formData['END_DATE']);
                $endDate = $endDateArray['2'] . '-' . $endDateArray['0'] . '-' . $endDateArray['1'];
            }
        }
        // code to call the stored procedure for the shipTos
        $plinkAnnouncements = array();
        $plinkAnnouncements = $plinkUserTable->callProcedureGetAnnouncementsSearch($facility, $custType, $startDate, $endDate, $userId, $announcementText);
        $customerTypes = $plinkUserTable->callProcedureGetCustomerTypes();
        $facilities = $plinkUserTable->callProcedureGetFacilities();
        $customerTypesDD = array(
            '' => '-- All --'
        );
        
        if (! empty($customerTypes['output'])) {
            foreach ($customerTypes['output'] as $custType) {
                $customerTypesDD[trim($custType['CUST_TYPE_CODE'])] = $custType['CUST_TYPE_DESC'];
            }
        }
        $facilityDD = array(
            '' => '-- All --'
        );
        if (! empty($facilities['output'])) {
            foreach ($facilities['output'] as $facility) {
                $facilityDD[trim($facility['FACILITY_CODE'])] = $facility['FACILITY_DESC'];
            }
        }
        $viewModel = new ViewModel();
        
        $viewModel->setVariables(array(
            'identity' => $identity,
            'plinkAnnouncements' => $plinkAnnouncements,
            'customerTypes' => $customerTypesDD,
            'facilities' => $facilityDD,
            'form' => $form
        ));
        return $viewModel;
    }

    public function csrAnnouncementDetailAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        if ($identity['PLU_POLAR_CSR'] != 'Y') {
            $this->redirect()->toRoute('user/index');
        }
        $type = trim($this->getEvent()
            ->getRouteMatch()
            ->getParam('type'));
        $announcementId = trim($this->getEvent()
            ->getRouteMatch()
            ->getParam('id'));
        if ($type == 'add') {
            $announcementId = '';
        }
        
        $form = new CsrAnnouncementForm();
        $request = $this->getRequest();
        $response = $this->getResponse();
        $formData = $request->getPost()->toArray();
        
        /**
         * jlopez
         *
         * strip tags
         */
        
        $w3cAvailable = HTMLTag::all(); // all W3C available tags as of May 2017.
        $w3cAttributes = HTMLTag::attributes();
        $announcementExclude = array(
            'script'
        ); // other tags you do not want to include
        $announcementAllowed = array_diff($w3cAvailable, $announcementExclude); // compute the difference all minus exclude tags
        
        $filter = new StripTags([
            'allowTags' => $announcementAllowed,
            'allowAttribs' => $w3cAttributes
        ]);
        
        $keys = array_keys($formData);
        $values = array_values($formData);
        
        $values = array_map(function ($value) use ($filter) {
            
            return $filter->filter($value);
        }, $values);
        
        $formData = array_combine($keys, $values);
        
        $this->_initView();
        // calling the model
        $plinkUserTable = $this->getPlinkUserTable();
        if ($type == 'remove') {
            $plinkAnnouncementDelete = $plinkUserTable->callProcedureDeleteAnnouncement($announcementId);
            if (empty(trim($plinkAnnouncementDelete['message']))) {
                $message = 'success';
            } else {
                $message = trim($plinkAnnouncementDelete['message']);
            }
            $response->setContent(\Zend\Json\Json::encode($message));
            return $response;
        }
        
        $userId = trim($identity['PLU_USER_ID']);
        
        $getOrderHeader = array();
        $facilityFromForm = '';
        $custTypeFromForm = '';
        $startDate = '0001-01-01';
        $endDate = '0001-01-01';
        $announcementText = '';
        
        $customerTypes = $plinkUserTable->callProcedureGetCustomerTypes();
        $facilities = $plinkUserTable->callProcedureGetFacilities();
        $customerTypesDD = array(
            '' => '-- All --'
        );
        
        if (! empty($customerTypes['output'])) {
            foreach ($customerTypes['output'] as $custType) {
                $customerTypesDD[trim($custType['CUST_TYPE_CODE'])] = $custType['CUST_TYPE_DESC'];
            }
        }
        $facilityDD = array(
            '' => '-- All --'
        );
        if (! empty($facilities['output'])) {
            foreach ($facilities['output'] as $facility) {
                $facilityDD[trim($facility['FACILITY_CODE'])] = $facility['FACILITY_DESC'];
            }
        }
        
        $ele = $form->get('FACILITY');
        $ele->setAttribute('options', $facilityDD);
        
        $eleCUST_TYPE = $form->get('CUST_TYPE');
        $eleCUST_TYPE->setAttribute('options', $customerTypesDD);
        if ($request->isPost()) {
            
            $user = $this->getServiceLocator()->get('User/Model/PlinkUser');
            
            // get and set input filter validation of form user
            // model
            $form->setInputFilter($user->getAnnouncementInputFilter());
            
            $form->setData($formData);
            if ($form->isValid()) {
                
                if (! empty($formData['CUST_TYPE'])) {
                    $custTypeFromForm = $formData['CUST_TYPE'];
                }
                
                if (! empty($formData['FACILITY'])) {
                    $facilityFromForm = $formData['FACILITY'];
                }
                
                /**
                 * jlopez
                 *
                 * strip tags
                 */
                if (! empty($formData['MESSAGE'])) {
                    $announcementText = $formData['MESSAGE'];
                }
                if (! empty($formData['START_DATE'])) {
                    $startDateArray = explode('/', $formData['START_DATE']);
                    $startDate = $startDateArray['2'] . '-' . $startDateArray['0'] . '-' . $startDateArray['1'];
                }
                if (! empty($formData['END_DATE'])) {
                    $endDateArray = explode('/', $formData['END_DATE']);
                    $endDate = $endDateArray['2'] . '-' . $endDateArray['0'] . '-' . $endDateArray['1'];
                }
                
                if ($type == 'add') {
                    $plinkAnnouncements = $plinkUserTable->callProcedureSaveAnnouncement($userId, $facilityFromForm, $custTypeFromForm, $startDate, $endDate, $announcementText);
                    if (! empty($plinkAnnouncements['announcementId'])) {
                        // set up success message
                        $this->flashmessenger()->addMessage("Announcement Added Successfully.");
                        $this->redirect()->toRoute('user/csrAnnouncementSearch');
                    }
                }
                
                if ($type == 'edit') {
                    $plinkAnnouncements = $plinkUserTable->callProcedureUpdateAnnouncement($announcementId, $userId, $facilityFromForm, $custTypeFromForm, $startDate, $endDate, $announcementText);
                    if (empty($plinkAnnouncements['message'])) {
                        // set up success message
                        $this->flashmessenger()->addMessage("Announcement Updated Successfully.");
                        $this->redirect()->toRoute('user/csrAnnouncementSearch');
                    }
                }
            }
        }
        $plinkAnnouncementDetail = array();
        if ($type == 'view' || $type == 'edit') {
            $plinkAnnouncementDetail = $plinkUserTable->callProcedureGetAnnouncementsDetail($announcementId);
        }
        
        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'identity' => $identity,
            'type' => $type,
            'announcementId' => $announcementId,
            'facilities' => $facilityDD,
            'customerTypes' => $customerTypesDD,
            'form' => $form,
            'plinkAnnouncementDetail' => $plinkAnnouncementDetail
        ));
        return $viewModel;
    }

    public function csrCustomerListAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        if ($identity['PLU_POLAR_CSR'] != 'Y') {
            $this->redirect()->toRoute('user/index');
        }
        
        $form = new CsrCustomerSearchForm();
        $request = $this->getRequest();
        $response = $this->getResponse();
        $formData = $request->getPost()->toArray();
        $form->setData($formData);
        
        $this->_initView();
        // calling the model
        $plinkUserTable = $this->getPlinkUserTable();
        $searchFilters = '';
        $plcStatus = '';
        $custType = '';
        if ($request->isPost()) {
            $trimmedSearchFilter = trim($formData['SearchFilters']);
            if (! empty($trimmedSearchFilter)) {
                $searchFilters = $trimmedSearchFilter;
            }
            $statusPosted = (! empty($formData['Status']) ? $formData['Status'] : '');
            if (! empty($statusPosted)) {
                $plcStatus = $statusPosted;
            }
        }
        // code to call the stored procedure for the customers
        $plinkCustomers = array();
        $plinkCustomers = $plinkUserTable->callProcedureCSRGetPLinkCustomers($searchFilters, $plcStatus, $custType);
        
        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'identity' => $identity,
            'form' => $form,
            'plinkCustomers' => $plinkCustomers,
            'flashMessages' => $this->flashMessenger()
                ->getMessages()
        ));
        return $viewModel;
    }

    public function csrCustomerDetailAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        $type = trim($this->getEvent()
            ->getRouteMatch()
            ->getParam('type'));
        $customerId = trim($this->getEvent()
            ->getRouteMatch()
            ->getParam('id'));
        if ($type == 'add') {
            $customerId = '';
        }
        if ($type == 'edit') {
            $form = new CsrCustomerEditForm();
        } else {
            $form = new CsrCustomerAddForm();
        }
        
        $customerDefaultsForm = new CsrCustomerDefaultsForm();
        $request = $this->getRequest();
        $response = $this->getResponse();
        $formData = $request->getPost()->toArray();
        $errorMessage = '';
        $setDirtyFlag = false;
        $this->_initView();
        // calling the model
        $plinkUserTable = $this->getPlinkUserTable();
        if ($type == 'remove') {
            $plinkCsrCustomerDelete = $plinkUserTable->callProcedureDeleteCsrCustomer($customerId);
            if (empty(trim($plinkCsrCustomerDelete['message']))) {
                $message = 'success';
            } else {
                $message = trim($plinkCsrCustomerDelete['message']);
            }
            $response->setContent(\Zend\Json\Json::encode($message));
            return $response;
        }
        $shippingSearchForm = new ShippingSearchForm();
        $userId = trim($identity['PLU_USER_ID']);
        $customerShipTos = array();
        $searchFilter = '';
        if ($request->isPost()) {
            
            $hiddenSearchShipping = $this->getRequest()->getPost('searchshipping');
            if ($hiddenSearchShipping == 'search') {
                $searchFilter = $this->getRequest()->getPost('SEARCHPARAMETER');
            }
            $submitPost = $this->getRequest()->getPost('Submit');
            if ($submitPost == 'CustomerDefaults') {
                
                $defaultUom = ''; // $formData['PLC_DFT_UOM'];
                $defaultShipMethod = $formData['PLC_DFT_SHIP_METHOD'];
                $defaultCustomerNumber = $formData['PLC_CUSTNO'];
                $defaultShipTo = $formData['PLC_DFT_SHIPTO'];
                $plinkUserCustDftsUpdate = $plinkUserTable->callProcedureUpdatePlinkCustDfts($userId, $customerId, $defaultUom, $defaultShipMethod, $defaultCustomerNumber, $defaultShipTo);
                if (empty(trim($plinkUserCustDftsUpdate['message']))) {
                    $this->redirect()->toRoute('user/csrCustomerList');
                }
            } else {
                
                $user = $this->getServiceLocator()->get('User/Model/PlinkUser');
                
                // get and set input filter validation of form user
                // model
                // if($type == 'add'){
                $form->setInputFilter($user->getCustomerAddInputFilter());
                
                $form->setData($formData);
                if ($form->isValid()) {
                    // echo '<pre>'; print_r($formData); print_r($identity); die;
                    $CustGroup = $CustName = $plcEmails = $plcStatus = $custType = '';
                    if (! empty($formData['csr_cust_group'])) {
                        $CustGroup = $formData['csr_cust_group'];
                    }
                    
                    if (! empty($formData['csr_user_name'])) {
                        $CustName = $formData['csr_user_name'];
                    }
                    if (! empty($formData['csr_email_address'])) {
                        $plcEmails = $formData['csr_email_address'];
                    }
                    if (! empty($formData['csr_status'])) {
                        $plcStatus = $formData['csr_status'];
                    }
                    $userId = trim($identity['PLU_USER_ID']);
                    /*
                     * echo $userId;
                     * echo '<br />';
                     * echo $CustGroup;
                     * echo '<br />';
                     * echo $CustName;
                     * echo '<br />';
                     * echo $plcEmails;
                     * echo '<br />';
                     * echo $plcStatus;
                     * echo '<br />';
                     * echo $custType;
                     * echo '<br />';
                     *
                     * die;
                     */
                    if ($type == 'add') {
                        $plinkCsrCustomerAdd = $plinkUserTable->callProcedureSaveCsrCustomerAdd($userId, $CustGroup, $CustName, $plcEmails, $plcStatus, $custType);
                        
                        $returnMessage = trim($plinkCsrCustomerAdd['message']);
                        if (! empty($returnMessage)) {
                            $errorMessage = $returnMessage;
                        } else if (! empty(trim($plinkCsrCustomerAdd['csrCustomerId']))) {
                            $this->redirect()->toRoute('user/csrCustomerDetailAction/edit/' . $CustGroup);
                        }
                    } else if ($type == 'edit') {
                        $plinkCsrCustomerEdit = $plinkUserTable->callProcedureSaveCsrCustomerEdit($userId, $CustGroup, $CustName, $plcEmails, $plcStatus, $custType);
                        
                        $returnMessage = trim($plinkCsrCustomerEdit['message']);
                        if (! empty($returnMessage)) {
                            $errorMessage = $returnMessage;
                        } else {
                            $this->addFlashMessage('Customer Details Updated Successfully');
                            $this->redirect()->toRoute('user/csrCustomerList');
                        }
                    }
                } else {
                    $setDirtyFlag = true;
                }
            }
        }
        $plinkCsrCustomerDetail = array();
        if ($type == 'view' || $type == 'edit') {
            
            $customerShipTos = $plinkUserTable->callProcedureGetCustomerShipTos(trim($identity['PLU_CUST_GROUP']), $searchFilter);
            $plinkCsrCustomerDetail = $plinkUserTable->callProcedureGetCsrCustomersDetail($customerId);
        }
        // echo '<pre>'; print_r($plinkCsrCustomerDetail); die;
        
        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'identity' => $identity,
            'type' => $type,
            'customerId' => $customerId,
            'form' => $form,
            'plinkCsrCustomerDetail' => $plinkCsrCustomerDetail,
            'errorMessage' => $errorMessage,
            'request' => $request,
            'customerDefaultsForm' => $customerDefaultsForm,
            'customerShipTos' => $customerShipTos,
            'setDirtyFlag' => $setDirtyFlag,
            'shippingSearchForm' => $shippingSearchForm
        ));
        return $viewModel;
    }

    /*
     * csrCustomerAddAction - This is the function used to add a Csr Customer
     */
    public function csrCustomerAddAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        if ($identity['PLU_POLAR_CSR'] != 'Y') {
            $this->redirect()->toRoute('user/index');
        }
        
        $customerId = '';
        
        $form = new CsrCustomerAddForm();
        $shippingSearchForm = new ShippingSearchForm();
        $request = $this->getRequest();
        $response = $this->getResponse();
        $formData = $request->getPost()->toArray();
        $errorMessage = '';
        // to set the layout
        $this->_initView();
        // calling the model
        $plinkUserTable = $this->getPlinkUserTable();
        $userId = trim($identity['PLU_USER_ID']);
        $flag = true;
        if ($request->isPost()) {
            
            $user = $this->getServiceLocator()->get('User/Model/PlinkUser');
            
            // get and set input filter validation of form user
            $form->setInputFilter($user->getCustomerAddInputFilter());
            $form->setValidationGroup('csr_cust_group', 'csr_user_name', 'csr_status', 'PLC_DFT_SHIPTO', 'PLC_CUSTNO');
            $form->setData($formData);
            $csr_cust_group = $formData['csr_cust_group'];
            /*
             * if(!empty($csr_cust_group)){
             * $custGroup = $plinkUserTable->callProcedureCheckCustgroup ( trim ($csr_cust_group) );
             * }
             *
             * if(!empty($custGroup['Message'])){
             * $form->setMessages(array('csr_cust_group'=>array($custGroup['Message'])));
             * $flag = false;
             * }
             */
            
            if (empty($formData['PLC_DFT_SHIPTO']) || empty($formData['PLC_CUSTNO'])) {
                $msg_default_shipto = 'Default Ship-To is required';
                $form->setMessages(array(
                    'PLC_DFT_SHIPTO' => array(
                        $msg_default_shipto
                    )
                ));
                $flag = false;
            }
            
            if ($form->isValid() && $flag === true) {
                $plcDCustNo = $plcDftShipTo = $plcDftShipMethod = $plcDftUom = $CustGroup = $CustName = $plcEmails = $plcStatus = $custType = '';
                
                if (! empty($formData['csr_cust_group'])) {
                    $CustGroup = $formData['csr_cust_group'];
                }
                if (! empty($formData['csr_user_name'])) {
                    $CustName = strtoupper($formData['csr_user_name']);
                }
                if (! empty($formData['csr_email_address'])) {
                    $plcEmails = $formData['csr_email_address'];
                }
                if (! empty($formData['csr_status'])) {
                    $plcStatus = $formData['csr_status'];
                }
                // if(!empty($formData['PLC_DFT_UOM'])){
                // $plcDftUom = $formData['PLC_DFT_UOM'];
                // }
                
                if (! empty($formData['PLC_DFT_SHIP_METHOD'])) {
                    $plcDftShipMethod = $formData['PLC_DFT_SHIP_METHOD'];
                }
                if (! empty($formData['PLC_DFT_SHIPTO'])) {
                    $plcDftShipTo = $formData['PLC_DFT_SHIPTO'];
                }
                if (! empty($formData['PLC_CUSTNO'])) {
                    $plcDCustNo = $formData['PLC_CUSTNO'];
                }
                $userId = trim($identity['PLU_USER_ID']);
                $custType = '';
                // $plinkCsrCustomerAdd = $plinkUserTable->callProcedureSaveCsrCustomerAdd($userId, $CustGroup, $CustName, $plcEmails, $plcStatus, $custType);
                $plinkCsrCustomerAdd = $plinkUserTable->callProcedureSaveCsrCustomerAddWithDefaults($userId, $CustGroup, $CustName, $plcEmails, $plcStatus, $plcDftUom, $plcDftShipMethod, $plcDftShipTo, $plcDCustNo);
                
                $returnMessage = trim($plinkCsrCustomerAdd['message']);
                if (! empty($returnMessage)) {
                    $errorMessage = $returnMessage;
                } else {
                    $this->addFlashMessage('Customer Added Successfully');
                    // $this->redirect ()->toUrl ( '/user/csr-customer-list/'. $CustGroup );
                    $this->redirect()->toUrl('/user/csr-customer-list');
                }
            }
        }
        $plinkCsrCustomerDetail = array();
        
        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'identity' => $identity,
            'form' => $form,
            'errorMessage' => $errorMessage,
            'request' => $request,
            'shippingSearchForm' => $shippingSearchForm
        
        ));
        return $viewModel;
    }

    /*
     * csrCustomerEditAction - This is the function used to add a Csr Customer
     */
    public function csrCustomerEditAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        if ($identity['PLU_POLAR_CSR'] != 'Y') {
            $this->redirect()->toRoute('user/index');
        }
        
        $customerId = trim($this->getEvent()
            ->getRouteMatch()
            ->getParam('id'));
        
        $form = new CsrCustomerEditForm();
        $customerDefaultsForm = new CsrCustomerDefaultsForm();
        $request = $this->getRequest();
        $response = $this->getResponse();
        $formData = $request->getPost()->toArray();
        $errorMessage = '';
        $setDirtyFlag = false;
        $this->_initView();
        // calling the model
        $plinkUserTable = $this->getPlinkUserTable();
        
        $shippingSearchForm = new ShippingSearchForm();
        $userId = trim($identity['PLU_USER_ID']);
        $customerShipTos = array();
        $searchFilter = '';
        if ($request->isPost()) {
            $user = $this->getServiceLocator()->get('User/Model/PlinkUser');
            $form->setInputFilter($user->getCustomerAddInputFilter());
            $form->setValidationGroup('csr_user_name', 'csr_status', 'PLC_DFT_SHIPTO', 'PLC_CUSTNO');
            $form->setData($formData);
            $flag = true;
            $csr_cust_group = $formData['csr_cust_group'];
            
            // die('kailash');
            
            /*
             * if(!empty($csr_cust_group)){
             * $custGroup = $plinkUserTable->callProcedureCheckCustgroup ( trim ($csr_cust_group) );
             * }
             *
             * if(!empty($custGroup['Message'])){
             * $form->setMessages(array('csr_cust_group'=>array($custGroup['Message'])));
             * $flag = false;
             * }
             */
            if (empty($formData['PLC_DFT_SHIPTO']) || $formData['PLC_DFT_SHIPTO'] == '0' || empty($formData['PLC_CUSTNO']) || $formData['PLC_CUSTNO'] == '0') {
                $msg_default_shipto = 'Default Ship-To is required';
                $form->setMessages(array(
                    'PLC_DFT_SHIPTO' => array(
                        $msg_default_shipto
                    )
                ));
                $flag = false;
            }
            if ($form->isValid() && $flag == true) {
                $plcDCustNo = $plcDftShipTo = $plcDftShipMethod = $plcDftUom = $CustGroup = $CustName = $plcEmails = $plcStatus = $custType = '';
                
                if (! empty($formData['csr_cust_group'])) {
                    $CustGroup = $formData['csr_cust_group'];
                }
                if (! empty($formData['csr_user_name'])) {
                    $CustName = strtoupper($formData['csr_user_name']);
                }
                if (! empty($formData['csr_email_address'])) {
                    $plcEmails = $formData['csr_email_address'];
                }
                if (! empty($formData['csr_status'])) {
                    $plcStatus = $formData['csr_status'];
                }
                // if(!empty($formData['PLC_DFT_UOM'])){
                // $plcDftUom = $formData['PLC_DFT_UOM'];
                // }
                
                if (! empty($formData['PLC_DFT_SHIP_METHOD'])) {
                    $plcDftShipMethod = $formData['PLC_DFT_SHIP_METHOD'];
                }
                if (! empty($formData['PLC_DFT_SHIPTO'])) {
                    $plcDftShipTo = $formData['PLC_DFT_SHIPTO'];
                }
                if (! empty($formData['PLC_CUSTNO'])) {
                    $plcDCustNo = $formData['PLC_CUSTNO'];
                }
                $userId = trim($identity['PLU_USER_ID']);
                
                // $plinkCsrCustomerAdd = $plinkUserTable->callProcedureSaveCsrCustomerAdd($userId, $CustGroup, $CustName, $plcEmails, $plcStatus, $custType);
                $plinkCsrCustomerAdd = $plinkUserTable->callProcedureSaveCsrCustomerAddWithDefaults($userId, $CustGroup, $CustName, $plcEmails, $plcStatus, $plcDftUom, $plcDftShipMethod, $plcDftShipTo, $plcDCustNo);
                // $plinkCsrCustomerEdit = $plinkUserTable->callProcedureSaveCsrCustomerEdit($userId, $CustGroup, $CustName, $plcEmails, $plcStatus, $custType);
                $returnMessage = trim($plinkCsrCustomerEdit['message']);
                if (! empty($returnMessage)) {
                    $errorMessage = $returnMessage;
                } else {
                    $this->addFlashMessage('Customer Details Updated Successfully');
                    $this->redirect()->toRoute('user/csrCustomerList');
                }
            } else {
                $setDirtyFlag = true;
            }
        }
        
        /**
         * Technical Debt
         *
         * Refactor:
         * $cust_group = $this->getEvent()->getRouteMatch()->getParam('id');
         * $customerShipTos = $plinkUserTable->callProcedureGetCustomerShipTos ($cust_group, $searchFilter );
         * $plinkCsrCustomerDetail = $plinkUserTable->callProcedureGetCsrCustomersDetail($customerId);
         *
         * @var Ambiguous $cust_group
         */
        
        $cust_group = $this->getEvent()
            ->getRouteMatch()
            ->getParam('id');
        $customerShipTos = $plinkUserTable->callProcedureGetCustomerShipTos($cust_group, $searchFilter);
        $plinkCsrCustomerDetail = $plinkUserTable->callProcedureGetCsrCustomersDetail($customerId);
        
        $customerIdentity = $plinkCsrCustomerDetail['output'];
        
        $shipToAddressList = [];
        $customerGroup = $customerIdentity['PLC_CUST_GRP'];
        $customerNumber = $customerIdentity['PLC_CUSTNO'];
        $customerShipTo = $customerIdentity['PLC_DFT_SHIPTO'];
        
        $customerHelper = new Customer($plinkUserTable);
        $availableCustomerShipToAddress = current($customerHelper->getShipToAddressList($customerGroup));
        
        // add email list to each available customer in customer group
        $emailListCustomerNumber = [];
        $customerGroupList = $this->getPlinkCustomerTable()->
        getCustomerShiptoEmails($customerIdentity['PLC_CUST_GRP']);
        
        foreach ($customerGroupList as $eachList) :
            
            $needleCustNo = $eachList['PLST_CUSTNO'];
            $needleShipTo = $eachList['PLST_SHIPTO'];
            
            $emailListCustomerNumber[$needleCustNo][$needleShipTo] = 
            trim($eachList['PLST_EMAILS']);
        endforeach
        ;
        
        foreach ($availableCustomerShipToAddress as &$availableCustomerShipTo) :
            
            $needleCustNo = $availableCustomerShipTo['ST_CUST'];
            $needleShipTo = $availableCustomerShipTo['ST_NUM'];
            
            $availableCustomerShipTo['EMAILS'] = 
            array_key_exists($needleCustNo, $emailListCustomerNumber) ? 
            $emailListCustomerNumber[$needleCustNo][$needleShipTo] : 
            null;
        endforeach
        ;
        
        $formattedDefaultAddress = null;
        
        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'identity' => $identity,
            'customerId' => $customerId,
            'form' => $form,
            'plinkCsrCustomerDetail' => $plinkCsrCustomerDetail,
            'errorMessage' => $errorMessage,
            'request' => $request,
            'customerDefaultsForm' => $customerDefaultsForm,
            'customerShipTos' => $customerShipTos,
            'availableCustomerShipToAddress' => $availableCustomerShipToAddress,
            'setDirtyFlag' => $setDirtyFlag,
            'shippingSearchForm' => $shippingSearchForm,
            
            /**
             * @jlopez
             *
             * Required POST data to save customer ship-to email list
             */
            'postShipToEmail' => [
                
                'customerGroup' => preg_replace('/\s+/', '', $customerGroup),
                
                'customerNumber' => $customerNumber
            ],
            
            'formattedDefaultAddress' => $formattedDefaultAddress
        ));
        return $viewModel;
    }

    /*
     * csrCustomerDeleteAction - Action to delete the csr customer action
     */
    public function csrCustomerDeleteAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        if ($identity['PLU_POLAR_CSR'] != 'Y') {
            $this->redirect()->toRoute('user/index');
        }
        $customerId = trim($this->getEvent()
            ->getRouteMatch()
            ->getParam('id'));
        $response = $this->getResponse();
        // calling the model
        $plinkUserTable = $this->getPlinkUserTable();
        $plinkCsrCustomerDelete = $plinkUserTable->callProcedureDeleteCsrCustomer($customerId);
        if (empty(trim($plinkCsrCustomerDelete['message']))) {
            $message = 'success';
        } else {
            $message = trim($plinkCsrCustomerDelete['message']);
        }
        $response->setContent(\Zend\Json\Json::encode($message));
        return $response;
    }

    /*
     * csrCustomerLoadDataAction - This is the function to load the tab data in the edit form
     */
    public function csrCustomerLoadDataAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        $form = new CsrCustomerEditForm();
        $customerDefaultsForm = new CsrCustomerDefaultsForm();
        $request = $this->getRequest();
        $response = $this->getResponse();
        $formDataAjax = $request->getPost()->toArray();
        $formData = array();
        $customerId = $formDataAjax['customerId'];
        $tabSelected = $formDataAjax['activeTab'];
        if (! empty($formDataAjax['formData']))
            parse_str($formDataAjax['formData'], $formData);
        $errorMessage = '';
        $flashMessages = array();
        $setDirtyFlag = false;
        $this->_initView();
        // calling the model
        $plinkUserTable = $this->getPlinkUserTable();
        
        $shippingSearchForm = new ShippingSearchForm();
        $userId = trim($identity['PLU_USER_ID']);
        $customerShipTos = array();
        $searchFilter = '';
        
        if (! empty($formData)) {
            
            $submitPost = ((isset($formData['csr_customer_defaults'])) ? $formData['csr_customer_defaults'] : '');
            $csrCustomerEditForm = ((isset($formData['csr_customer_field'])) ? $formData['csr_customer_field'] : '');
            if ($submitPost == '1') {
                
                $customerDefaultsForm->setData($formData);
                $defaultUom = ''; // $formData['PLC_DFT_UOM'];
                $defaultShipMethod = $formData['PLC_DFT_SHIP_METHOD'];
                $defaultCustomerNumber = $formData['PLC_CUSTNO'];
                $defaultShipTo = $formData['PLC_DFT_SHIPTO'];
                $plinkUserCustDftsUpdate = $plinkUserTable->callProcedureUpdatePlinkCustDfts($userId, $customerId, $defaultUom, $defaultShipMethod, $defaultCustomerNumber, $defaultShipTo);
                if (empty(trim($plinkUserCustDftsUpdate['message']))) {
                    $flashMessages = array(
                        '<span class="colorblue f18px">Customer Defaults Updated Successfully</span>'
                    );
                    // $this->addFlashMessage('Customer Defaults Updated Successfully');
                    // $this->redirect ()->toRoute ( 'user/csrCustomerList' );
                } else {
                    $errorMessage = trim($plinkUserCustDftsUpdate['message']);
                }
            } else if (isset($csrCustomerEditForm) && ($csrCustomerEditForm == '1')) {
                
                $user = $this->getServiceLocator()->get('User/Model/PlinkUser');
                
                // get and set input filter validation of form user
                // model
                // if($type == 'add'){
                $form->setInputFilter($user->getCustomerAddInputFilter());
                
                $form->setData($formData);
                if ($form->isValid()) {
                    // echo '<pre>'; print_r($formData); print_r($identity); die;
                    $CustGroup = $CustName = $plcEmails = $plcStatus = $custType = '';
                    if (! empty($formData['csr_cust_group'])) {
                        $CustGroup = $formData['csr_cust_group'];
                    }
                    
                    if (! empty($formData['csr_user_name'])) {
                        $CustName = $formData['csr_user_name'];
                    }
                    if (! empty($formData['csr_email_address'])) {
                        $plcEmails = $formData['csr_email_address'];
                    }
                    if (! empty($formData['csr_status'])) {
                        $plcStatus = $formData['csr_status'];
                    }
                    $userId = trim($identity['PLU_USER_ID']);
                    
                    /*
                     * echo $userId;
                     * echo '<br />';
                     * echo $CustGroup;
                     * echo '<br />';
                     * echo $CustName;
                     * echo '<br />';
                     * echo $plcEmails;
                     * echo '<br />';
                     * echo $plcStatus;
                     * echo '<br />';
                     * echo $custType;
                     * echo '<br />';
                     *
                     * die;
                     */
                    
                    $plinkCsrCustomerEdit = $plinkUserTable->callProcedureSaveCsrCustomerEdit($userId, $CustGroup, $CustName, $plcEmails, $plcStatus, $custType);
                    
                    $returnMessage = trim($plinkCsrCustomerEdit['message']);
                    if (! empty($returnMessage)) {
                        $errorMessage = $returnMessage;
                    } else {
                        $flashMessages = array(
                            '<span class="colorblue f18px">Customer Details Updated Successfully</span>'
                        );
                        // $this->addFlashMessage('Customer Details Updated Successfully');
                        // $this->redirect ()->toRoute ( 'user/csrCustomerList');
                    }
                } else {
                    $setDirtyFlag = true;
                }
            }
        }
        
        $customerShipTos = $plinkUserTable->callProcedureGetCustomerShipTos(trim($customerId), $searchFilter);
        // $customerShipTos = array();
        // print_r($customerShipTos); die;
        $plinkCsrCustomerDetail = $plinkUserTable->callProcedureGetCsrCustomersDetail($customerId);
        
        $viewModel = new ViewModel();
        $viewModel->setTerminal('1');
        $viewModel->setVariables(array(
            'identity' => $identity,
            'customerId' => $customerId,
            'form' => $form,
            'formData' => $formData,
            'plinkCsrCustomerDetail' => $plinkCsrCustomerDetail,
            'errorMessage' => $errorMessage,
            'request' => $request,
            'customerDefaultsForm' => $customerDefaultsForm,
            'customerShipTos' => $customerShipTos,
            'setDirtyFlag' => $setDirtyFlag,
            'shippingSearchForm' => $shippingSearchForm,
            'tabSelected' => $tabSelected,
            'flashMessages' => $flashMessages
        ));
        return $viewModel;
    }

    /*
     * csrCustomerViewAction - This is the function used to add a Csr Customer
     */
    public function csrCustomerViewAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        if ($identity['PLU_POLAR_CSR'] != 'Y') {
            $this->redirect()->toRoute('user/index');
        }
        
        $customerId = trim($this->getEvent()
            ->getRouteMatch()
            ->getParam('id'));
        
        $this->_initView();
        // calling the model
        $plinkUserTable = $this->getPlinkUserTable();
        
        $userId = trim($identity['PLU_USER_ID']);
        
        $plinkCsrCustomerDetail = $plinkUserTable->callProcedureGetCsrCustomersDetail($customerId);
        $searchFilter = '';
        $status = '';
        // $usersList = $plinkUserTable->callProcedureGetUserSearch($customerId, $searchFilters, $status);
        $customerShipTos = $plinkUserTable->callProcedureGetCustomerShipTos(trim($customerId), $searchFilter);
        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'identity' => $identity,
            'customerId' => $customerId,
            'plinkCsrCustomerDetail' => $plinkCsrCustomerDetail,
            'customerShipTos' => $customerShipTos
        ));
        return $viewModel;
    }

    /*
     * Item Search Ajax Action - This is the action for the item search ajax which is returned on the item search @param void @author rohit
     */
    public function ajaxSearchShipToAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        $searchFilter = '';
        $customerId = '';
        $viewModel = new ViewModel();
        $request = $this->getRequest();
        $formData = $request->getPost()->toArray();
        
        // print_r($formData); die;
        
        if (! empty(trim($formData['search']))) {
            $searchFilter = trim($formData['search']);
        }
        
        if (isset($formData['customerId']) && ! empty(trim($formData['customerId']))) {
            $customerId = trim($formData['customerId']);
        }
        
        if (empty($customerId)) {
            $customerId = trim($identity['PLU_CUST_GROUP']);
        }
        // disable layout if request by Ajax
        $viewModel->setTerminal($request->isXmlHttpRequest());
        
        // making the object the model
        $plinkUserTable = $this->getPlinkUserTable();
        
        $customerShipTos = $plinkUserTable->callProcedureGetCustomerShipTos($customerId, $searchFilter);
        
        $viewModel->setVariables(array(
            'customerShipTos' => $customerShipTos,
            'searchFilter' => $searchFilter
        ));
        
        return $viewModel;
    }

    /*
     * csrUserListAction - This is the function used to list the Csr Users
     */
    public function csrUserListAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        // echo '<pre>'; print_r($identity); die;
        if ($identity['PLU_POLAR_CSR'] == 'N' && $identity['PLU_PLINK_ADMIN'] == 'N') {
            $this->redirect()->toRoute('user/index');
        }
        
        $form = new CsrUserSearchForm();
        $request = $this->getRequest();
        $response = $this->getResponse();
        $formData = $request->getPost()->toArray();
        $form->setData($formData);
        $this->_initView();
        // calling the model
        $plinkUserTable = $this->getPlinkUserTable();
        $customerId = '';
        $searchFilters = '';
        $status = '';
        if ($request->isPost()) {
            // echo '<pre>'; print_r($formData); die;
            $trimmedCustGroup = trim($formData['CustGroup']);
            if (! empty($trimmedCustGroup)) {
                $customerId = $trimmedCustGroup;
            }
            $trimmedSearchFilter = trim($formData['SearchFilters']);
            if (! empty($trimmedSearchFilter)) {
                $searchFilters = $trimmedSearchFilter;
            }
            $statusPosted = (! empty($formData['Status']) ? $formData['Status'] : '');
            if (! empty($statusPosted)) {
                $status = $statusPosted;
            }
        }
        
        if ($identity['PLU_PLINK_ADMIN'] == 'Y' && $identity['PLU_POLAR_CSR'] == 'N')
            $customerId = trim($identity['PLU_CUST_GROUP']);
        // code to call the stored procedure for the customers
        $plinkUsers = array();
        $plinkUsers = $plinkUserTable->callProcedureGetUserSearch($customerId, $searchFilters, $status);
        
        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'identity' => $identity,
            'form' => $form,
            'plinkUsers' => $plinkUsers,
            'flashMessages' => $this->flashMessenger()
                ->getMessages()
        ));
        return $viewModel;
    }

    /*
     * checkBasicFunctionAuth - This function is written to check whether the particular logged in person is allowed for this action or not
     * input - $identity - Array - this is the user Identity
     * $funcName - String - Here we will write the particular function name that is calling this function
     *
     * Return - True or False - True in case the user is allowed and false if he/she is not allowed
     *
     */
    private function __checkBasicFunctionAuth($identity, $funcName)
    {
        $returnArray = array(
            'UserActionAllowed' => false
        );
        $userAllowedAction = array(
            
            'Normal' => array(
                'csrUserEditAction',
                'csrUserViewAction',
                'adminCustomerViewAction'
            
            ),
            'Admin' => array(
                'csrUserAddAction',
                'csrUserEditAction',
                'csrUserViewAction',
                'csrUserDeleteAction',
                'adminCustomerEditAction',
                'adminCustomerViewAction'
            
            )
        
        );
        
        if ($identity['PLU_POLAR_CSR'] == 'Y') {
            $currentUserRole = 'CSR';
        } else if ($identity['PLU_PLINK_ADMIN'] == 'Y') {
            $currentUserRole = 'Admin';
        } else {
            $currentUserRole = 'Normal';
        }
        $returnArray['UserRole'] = $currentUserRole;
        
        if ($currentUserRole == 'CSR') {
            $returnArray['UserActionAllowed'] = true;
        } 
        else if (in_array($funcName, $userAllowedAction[$currentUserRole])) {
            $returnArray['UserActionAllowed'] = true;
        }
        return $returnArray;
    }

    /*
     * csrUserAddAction - This is the function used to add a Csr User
     */
    public function csrUserAddAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        // code by rohit on 17th January 17 to check for the normal user
        $isUserAllowed = $this->__checkBasicFunctionAuth($identity, 'csrUserAddAction');
        
        // if($identity['PLU_POLAR_CSR'] != 'Y' && $identity['PLU_PLINK_ADMIN'] != 'Y'){
        if (empty($isUserAllowed) && $isUserAllowed['UserActionAllowed'] == false) {
            $this->redirect()->toRoute('user/index');
        }
        
        $customerId = '';
        
        $form = new CsrUserAddForm();
        $request = $this->getRequest();
        $response = $this->getResponse();
        $formData = $request->getPost()->toArray();
        $errorMessage = '';
        // to set the layout
        $this->_initView();
        // calling the model
        $plinkUserTable = $this->getPlinkUserTable();
        
        if ($request->isPost()) {
            $user = $this->getServiceLocator()->get('User/Model/PlinkUser');
            
            // get and set input filter validation of form user
            $form->setInputFilter($user->getUserAddInputFilter());
            $form->setData($formData);
            if ($form->isValid()) {
                $userId = $CustGroup = $fname = $lname = $password = $default_uom = $defaultShipMethod = $pluStatus = $pluEmail = $pluCrtUser = '';
                $CustNo = $defaultShipTo = '0';
                $pluPolarCsr = $pluPlinkAdmin = 'N';
                if (! empty($formData['PLU_CUST_GROUP'])) {
                    $CustGroup = trim($formData['PLU_CUST_GROUP']);
                }
                
                if (! empty($formData['PLU_USER_ID'])) {
                    $userId = trim($formData['PLU_USER_ID']);
                }
                
                if (! empty($formData['PLU_FIRST_NAME'])) {
                    $fname = trim($formData['PLU_FIRST_NAME']);
                }
                
                if (! empty($formData['PLU_LAST_NAME'])) {
                    $lname = trim($formData['PLU_LAST_NAME']);
                }
                
                if (! empty($formData['PLU_PASSWORD'])) {
                    $password = md5(trim($formData['PLU_PASSWORD']));
                }
                
                if (! empty($formData['PLU_STATUS'])) {
                    $pluStatus = $formData['PLU_STATUS'];
                }
                
                if (! empty($formData['PLU_EMAIL_ADDRESS'])) {
                    $pluEmail = trim($formData['PLU_EMAIL_ADDRESS']);
                }
                
                // if(!empty($formData['PLU_POLAR_CSR'])){
                // $pluPolarCsr = 'Y';
                // }
                
                // if(!empty($formData['PLU_PLINK_ADMIN'])){
                // $pluPlinkAdmin = 'Y';
                // }
                if (! empty($formData['PLU_USER_TYPE'])) {
                    if ($formData['PLU_USER_TYPE'] == 'csr') {
                        $pluPolarCsr = 'Y';
                    } else if ($formData['PLU_USER_TYPE'] == 'admin') {
                        $pluPlinkAdmin = 'Y';
                    }
                }
                $default_uom = ''; // CS';
                
                $plinkCsrUserAdd = $plinkUserTable->callProcedureSaveCsrUserAdd($userId, $CustGroup, $fname, $lname, $password, $CustNo, $default_uom, $defaultShipTo, $defaultShipMethod, $pluPolarCsr, $pluPlinkAdmin, $pluStatus, $pluEmail, $pluCrtUser);
                
                $returnMessage = trim($plinkCsrUserAdd['message']);
                if (! empty($returnMessage)) {
                    $errorMessage = $returnMessage;
                } else if (! empty(trim($plinkCsrUserAdd['csrUserId']))) {
                    
                    $this->redirect()->toUrl('/user/csr-user-edit/' . $userId);
                }
            }
        }
        $plinkCsrCustomerDetail = array();
        
        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'identity' => $identity,
            'form' => $form,
            'errorMessage' => $errorMessage,
            'request' => $request
        
        ));
        return $viewModel;
    }

    /*
     * checkCustomerUserAction - This is the action to check the customer group and the user group
     */
    public function checkCustomerUserAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        $viewModel = new ViewModel();
        $request = $this->getRequest();
        $response = $this->getResponse();
        $formData = $request->getPost()->toArray();
        // calling the model
        $plinkUserTable = $this->getPlinkUserTable();
        $customerError = $userError = '';
        // $formData = $request->getPost ();
        $customerId = trim($formData['customerId']);
        $userId = trim($formData['userId']);
        // disable layout if request by Ajax
        $viewModel->setTerminal($request->isXmlHttpRequest());
        $statusesToUpdate = array();
        // making the object the model
        $plinkCsrCustomerDetail = $plinkUserTable->callProcedureGetCsrCustomersDetail($customerId);
        
        $currentAnnouncement = array(
            'output' => ''
        );
        if (empty($plinkCsrCustomerDetail['output'])) {
            $customerError = 'Customer Group does not exist in PolarLink Customer table.';
        }
        
        $plinkCsrUserDetail = $plinkUserTable->callProcedureGetCsrUsersDetail($userId);
        
        if (! empty($plinkCsrUserDetail['output'])) {
            $userError = 'User already exists';
        }
        
        $jsonModel = new JsonModel();
        $jsonModel->setVariables(array(
            'customerError' => $customerError,
            'userError' => $userError
        ));
        
        return $jsonModel;
    }

    /*
     * csrUserViewAction - This is the function used to add a Csr User
     */
    public function csrUserViewAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        $userId = trim($this->getEvent()
            ->getRouteMatch()
            ->getParam('id'));
        
        // if($identity['PLU_POLAR_CSR'] != 'Y'){
        // if($userId != trim($identity['PLU_USER_ID']) ){
        // $this->redirect ()->toRoute ( 'user/index' );
        // }
        // }
        
        $this->_initView();
        // calling the model
        $plinkUserTable = $this->getPlinkUserTable();
        
        $plinkCsrUserDetail = $plinkUserTable->callProcedureGetCsrUsersDetail($userId);
        // echo '<pre>'; print_r($plinkCsrUserDetail); die;
        // $usersList = $plinkUserTable->callProcedureGetUserSearch($customerId, $searchFilters, $status);
        $isUserAllowed = $this->__checkBasicFunctionAuth($identity, 'csrUserViewAction');
        
        // checking whether user is allowed and based on his role checking the conditions
        if (empty($isUserAllowed) && $isUserAllowed['UserActionAllowed'] == false) {
            $this->redirect()->toRoute('user/index');
        } else if (($isUserAllowed['UserRole'] == 'Normal') && ($userId != trim($identity['PLU_USER_ID']))) {
            $this->redirect()->toRoute('user/index');
        } else if (($isUserAllowed['UserRole'] == 'Admin') && (trim($identity['PLU_CUST_GROUP']) != trim($plinkCsrUserDetail['output']['PLU_CUST_GROUP']))) {
            $this->redirect()->toRoute('user/index');
        }
        
        $customerShipTos = $plinkUserTable->callProcedureGetCustomerShipTos(trim($plinkCsrUserDetail['output']['PLU_CUST_GROUP']), '');
        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'identity' => $identity,
            'userId' => $userId,
            'plinkCsrUserDetail' => $plinkCsrUserDetail,
            'customerShipTos' => $customerShipTos
        ));
        return $viewModel;
    }

    /*
     * csrUserEditAction - This is the function used to add a Csr User
     */
    public function csrUserEditAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        $userId = trim($this->getEvent()
            ->getRouteMatch()
            ->getParam('id'));
        
        // if($identity['PLU_POLAR_CSR'] != 'Y'){
        // if($userId != trim($identity['PLU_USER_ID']) ){
        // $this->redirect ()->toRoute ( 'user/index' );
        // }
        // }
        
        $form = new CsrUserEditForm();
        $request = $this->getRequest();
        $response = $this->getResponse();
        $formData = $request->getPost()->toArray();
        $errorMessage = '';
        $setDirtyFlag = false;
        $this->_initView();
        // calling the model
        $plinkUserTable = $this->getPlinkUserTable();
        
        $shippingSearchForm = new ShippingSearchForm();
        
        $customerShipTos = array();
        $plinkCsrUserDetail = $plinkUserTable->callProcedureGetCsrUsersDetail($userId);
        
        $isUserAllowed = $this->__checkBasicFunctionAuth($identity, 'csrUserEditAction');
        
        // checking whether user is allowed and based on his role checking the conditions
        if (empty($isUserAllowed) && $isUserAllowed['UserActionAllowed'] == false) {
            $this->redirect()->toRoute('user/index');
        } else if (($isUserAllowed['UserRole'] == 'Normal') && ($userId != trim($identity['PLU_USER_ID']))) {
            $this->redirect()->toRoute('user/index');
        } else if (($isUserAllowed['UserRole'] == 'Admin') && (trim($identity['PLU_CUST_GROUP']) != trim($plinkCsrUserDetail['output']['PLU_CUST_GROUP']))) {
            $this->redirect()->toRoute('user/index');
        }
        
        $customerShipTos = $plinkUserTable->callProcedureGetCustomerShipTos(trim($plinkCsrUserDetail['output']['PLU_CUST_GROUP']), '');
        
        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'identity' => $identity,
            'userId' => $userId,
            'form' => $form,
            'setDirtyFlag' => $setDirtyFlag,
            'plinkCsrUserDetail' => $plinkCsrUserDetail,
            'request' => $request,
            'customerShipTos' => $customerShipTos,
            'shippingSearchForm' => $shippingSearchForm
        ));
        return $viewModel;
    }

    /*
     * csrUserDeleteAction - Action to delete the csr customer action
     */
    public function csrUserDeleteAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        $userId = trim($this->getEvent()
            ->getRouteMatch()
            ->getParam('id'));
        $response = $this->getResponse();
        // calling the model
        $plinkUserTable = $this->getPlinkUserTable();
        
        $plinkCsrUserDetail = $plinkUserTable->callProcedureGetCsrUsersDetail($userId);
        
        $isUserAllowed = $this->__checkBasicFunctionAuth($identity, 'csrUserDeleteAction');
        
        // checking whether user is allowed and based on his role checking the conditions
        if (empty($isUserAllowed) && $isUserAllowed['UserActionAllowed'] == false) {
            $this->redirect()->toRoute('user/index');
        } else if (($isUserAllowed['UserRole'] == 'Admin') && (trim($identity['PLU_CUST_GROUP']) != trim($plinkCsrUserDetail['output']['PLU_CUST_GROUP']))) {
            $this->redirect()->toRoute('user/index');
        }
        
        $plinkCsrUserDelete = $plinkUserTable->callProcedureDeleteCsrUser($userId);
        if (empty(trim($plinkCsrUserDelete['message']))) {
            $message = 'success';
        } else {
            $message = trim($plinkCsrUserDelete['message']);
        }
        $response->setContent(\Zend\Json\Json::encode($message));
        return $response;
    }

    /*
     * csrUserLoadDataAction - This is the function to load the tab data in the edit user form
     */
    public function csrUserLoadDataAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        $form = new CsrUserEditForm();
        $customerDefaultsForm = new CsrCustomerDefaultsForm();
        $request = $this->getRequest();
        $response = $this->getResponse();
        $formDataAjax = $request->getPost()->toArray();
        $formData = array();
        $userId = $formDataAjax['userId'];
        $tabSelected = $formDataAjax['activeTab'];
        if (! empty($formDataAjax['formData']))
            parse_str($formDataAjax['formData'], $formData);
        $errorMessage = '';
        $flashMessages = array();
        $setDirtyFlag = false;
        $this->_initView();
        // calling the model
        $plinkUserTable = $this->getPlinkUserTable();
        $form_entered_pwd = '';
        $shippingSearchForm = new ShippingSearchForm();
        $searchFilter = '';
        $plinkCsrUserDetail = $plinkUserTable->callProcedureGetCsrUsersDetail($userId);
        $takeValuesAgain = false;
        if (! empty($formData)) {
            
            $submitPost = ((isset($formData['csr_user_defaults'])) ? $formData['csr_user_defaults'] : '');
            $csrUserEditForm = ((isset($formData['csr_user_field'])) ? $formData['csr_user_field'] : '');
            // this is the case to update the defaults tab
            if ($submitPost == '1') {
                $userId = $plinkCsrUserDetail['output']['PLU_USER_ID'];
                $fname = $plinkCsrUserDetail['output']['PLU_FIRST_NAME'];
                $lname = $plinkCsrUserDetail['output']['PLU_LAST_NAME'];
                $password = $plinkCsrUserDetail['output']['PLU_PASSWORD'];
                $pluPolarCsr = $plinkCsrUserDetail['output']['PLU_POLAR_CSR'];
                $pluPlinkAdmin = $plinkCsrUserDetail['output']['PLU_PLINK_ADMIN'];
                $pluStatus = $plinkCsrUserDetail['output']['PLU_STATUS'];
                $pluEmail = $plinkCsrUserDetail['output']['PLU_EMAIL_ADDRESS'];
                $form->setData($formData);
                $default_uom = ''; // (!empty(trim($formData['PLU_DFT_UOM']))?trim($formData['PLU_DFT_UOM']):'');
                $defaultShipMethod = (! empty(trim($formData['PLU_DFT_SHIP_METHOD'])) ? trim($formData['PLU_DFT_SHIP_METHOD']) : '');
                $CustNo = (! empty(trim($formData['PLU_CUSTNO'])) ? trim($formData['PLU_CUSTNO']) : '0');
                $defaultShipTo = (! empty(trim($formData['PLU_DFT_SHIPTO'])) ? trim($formData['PLU_DFT_SHIPTO']) : '0');
                $takeValuesAgain = true;
                $pluChgUser = $identity['PLU_USER_ID'];
                $plinkCsrUserEdit = $plinkUserTable->callProcedureSaveCsrUserEdit($userId, $fname, $lname, $password, $CustNo, $default_uom, $defaultShipTo, $defaultShipMethod, $pluPolarCsr, $pluPlinkAdmin, $pluStatus, $pluEmail, $pluChgUser);
                $returnMessage = trim($plinkCsrUserEdit['message']);
                if (! empty($returnMessage)) {
                    $errorMessage = $returnMessage;
                } else {
                    $flashMessages = array(
                        '<span class="colorblue f18px">User Defaults Updated Successfully</span>'
                    );
                }
            }            // this is the case to update the users details tab
            else if (isset($csrUserEditForm) && ($csrUserEditForm == '1')) {
                
                $user = $this->getServiceLocator()->get('User/Model/PlinkUser');
                
                // get and set input filter validation of form user
                // model
                // if($type == 'add'){
                // get and set input filter validation of form user
                if ($formData['csr_change_pwd'] == '1') {
                    // $form_entered_pwd = $formData['PLU_OLD_PASSWORD'];
                    // $formData['PLU_OLD_PASSWORD'] = md5(trim($formData['PLU_OLD_PASSWORD']));
                    $form->setInputFilter($user->getUserEditInputPasswordFilter());
                } else {
                    $form->setInputFilter($user->getUserEditInputFilter());
                }
                
                $form->setData($formData);
                
                if ($form->isValid()) {
                    
                    // $pluCrtUser = trim($identity['PLU_USER_ID']);
                    $fname = $lname = $default_uom = $defaultShipMethod = $pluStatus = $pluEmail = $pluChgUser = '';
                    $password = $plinkCsrUserDetail['output']['PLU_PASSWORD'];
                    
                    $CustNo = $defaultShipTo = '0';
                    $pluPolarCsr = $pluPlinkAdmin = 'N';
                    
                    if (! empty($formData['PLU_FIRST_NAME'])) {
                        $fname = trim($formData['PLU_FIRST_NAME']);
                    }
                    
                    if (! empty($formData['PLU_LAST_NAME'])) {
                        $lname = trim($formData['PLU_LAST_NAME']);
                    }
                    
                    if (! empty($formData['PLU_NEW_PASSWORD'])) {
                        $password = md5(trim($formData['PLU_NEW_PASSWORD']));
                    }
                    
                    if (! empty($formData['PLU_STATUS'])) {
                        $pluStatus = $formData['PLU_STATUS'];
                    }
                    
                    if (! empty($formData['PLU_EMAIL_ADDRESS'])) {
                        $pluEmail = trim($formData['PLU_EMAIL_ADDRESS']);
                    }
                    
                    // if(!empty($formData['PLU_POLAR_CSR'])){
                    // $pluPolarCsr = 'Y';
                    // }
                    
                    // if(!empty($formData['PLU_PLINK_ADMIN'])){
                    // $pluPlinkAdmin = 'Y';
                    // }
                    
                    if (! empty($formData['PLU_USER_TYPE'])) {
                        if ($formData['PLU_USER_TYPE'] == 'csr') {
                            $pluPolarCsr = 'Y';
                        } else if ($formData['PLU_USER_TYPE'] == 'admin') {
                            $pluPlinkAdmin = 'Y';
                        }
                    }
                    
                    $pluChgUser = $identity['PLU_USER_ID'];
                    $plinkCsrUserEdit = $plinkUserTable->callProcedureSaveCsrUserEdit($userId, $fname, $lname, $password, $CustNo, $default_uom, $defaultShipTo, $defaultShipMethod, $pluPolarCsr, $pluPlinkAdmin, $pluStatus, $pluEmail, $pluChgUser);
                    $returnMessage = trim($plinkCsrUserEdit['message']);
                    if (! empty($returnMessage)) {
                        $errorMessage = $returnMessage;
                    } else {
                        
                        if ($formData['csr_change_pwd'] == '1') {
                            $formData['csr_change_pwd'] = '0';
                        }
                        
                        $flashMessages = array(
                            '<span class="colorblue f18px">User Details Updated Successfully</span>'
                        );
                    }
                } else {
                    $setDirtyFlag = true;
                    // check whether the password was entered or not
                    if (! empty($form_entered_pwd))
                        $formData['PLU_NEW_PASSWORD'] = $form_entered_pwd;
                }
            }
        }
        // this we are using because in case of the user defaults update, we need to take this again
        if ($takeValuesAgain)
            $plinkCsrUserDetail = $plinkUserTable->callProcedureGetCsrUsersDetail($userId);
        // echo '<pre>'; print_r($plinkCsrUserDetail); die;
        
        $customerShipTos = $plinkUserTable->callProcedureGetCustomerShipTos(trim($plinkCsrUserDetail['output']['PLU_CUST_GROUP']), '');
        
        $viewModel = new ViewModel();
        $viewModel->setTerminal('1');
        $viewModel->setVariables(array(
            'identity' => $identity,
            'userId' => $userId,
            'form' => $form,
            'formData' => $formData,
            'setDirtyFlag' => $setDirtyFlag,
            'plinkCsrUserDetail' => $plinkCsrUserDetail,
            'request' => $request,
            'errorMessage' => $errorMessage,
            'customerShipTos' => $customerShipTos,
            'shippingSearchForm' => $shippingSearchForm,
            'tabSelected' => $tabSelected,
            'flashMessages' => $flashMessages
        ));
        return $viewModel;
    }

    /*
     * csrUserDetailAction - This is the function to view the users details
     */
    
    /* THIS FUNCTION NOT IN USED JOHN PLEASE REVIEW SO WE WILL HAVE TO ARCHIVE THESE FUNCTIONS */
    
    /*
     * public function csrUserDetailAction(){
     * if ($this->getAuthService ()->hasIdentity ()) {
     * // Identity exists; get it
     * $identity = $this->getAuthService ()->getIdentity ();
     * } else {
     * $this->redirect ()->toRoute ( 'user' );
     * }
     *
     *
     * $this->_initView ();
     * $viewModel = new ViewModel ();
     * $viewModel->setVariables ( array (
     * 'identity' => $identity
     * ) );
     * return $viewModel;
     * }
     */
    /*
     * adminCustomerViewAction - This is the function used to view the customer setting for the user role admin
     */
    public function adminCustomerViewAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        // echo '<pre>'; print_r($identity); die;
        
        /*
         * if($identity['PLU_PLINK_ADMIN'] != 'Y'){
         * $this->redirect ()->toRoute ( 'user/index' );
         * }
         */
        $customerId = trim($this->getEvent()
            ->getRouteMatch()
            ->getParam('id'));
        
        $isUserAllowed = $this->__checkBasicFunctionAuth($identity, 'adminCustomerEditAction');
        // checking whether user is allowed and based on his role checking the conditions
        if (empty($isUserAllowed) && $isUserAllowed['UserActionAllowed'] == false) {
            $this->redirect()->toRoute('user/index');
        } else if ($isUserAllowed['UserRole'] == 'CSR') {
            $this->redirect()->toRoute('user/index');
        } else if ((($isUserAllowed['UserRole'] == 'Normal') || ($isUserAllowed['UserRole'] == 'Admin')) && (trim($identity['PLU_CUST_GROUP']) != $customerId)) {
            $this->redirect()->toRoute('user/index');
        }
        
        $this->_initView();
        // calling the model
        $plinkUserTable = $this->getPlinkUserTable();
        
        $userId = trim($identity['PLU_USER_ID']);
        
        $plinkCsrCustomerDetail = $plinkUserTable->callProcedureGetCsrCustomersDetail($customerId);
        $searchFilter = '';
        $status = '';
        // $usersList = $plinkUserTable->callProcedureGetUserSearch($customerId, $searchFilters, $status);
        $customerShipTos = $plinkUserTable->callProcedureGetCustomerShipTos(trim($customerId), $searchFilter);
        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'identity' => $identity,
            'customerId' => $customerId,
            'plinkCsrCustomerDetail' => $plinkCsrCustomerDetail,
            'customerShipTos' => $customerShipTos
        ));
        return $viewModel;
    }

    /*
     * adminCustomerEditAction - This is the function used to edit the customer defaults using the user role admin
     */
    public function adminCustomerEditAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        // if($identity['PLU_PLINK_ADMIN'] != 'Y'){
        // $this->redirect ()->toRoute ( 'user/index' );
        // }
        // //echo '<pre>'; print_r($identity); die;
        
        $customerId = trim($this->getEvent()
            ->getRouteMatch()
            ->getParam('id'));
        
        $isUserAllowed = $this->__checkBasicFunctionAuth($identity, 'adminCustomerEditAction');
        // checking whether user is allowed and based on his role checking the conditions
        if (empty($isUserAllowed) && $isUserAllowed['UserActionAllowed'] == false) {
            $this->redirect()->toRoute('user/index');
        } else if (($isUserAllowed['UserRole'] == 'Normal') || ($isUserAllowed['UserRole'] == 'CSR')) {
            $this->redirect()->toRoute('user/index');
        } else if (($isUserAllowed['UserRole'] == 'Admin') && (trim($identity['PLU_CUST_GROUP']) != $customerId)) {
            $this->redirect()->toRoute('user/index');
        }
        
        $form = new CsrCustomerEditForm();
        $customerDefaultsForm = new CsrCustomerDefaultsForm();
        $request = $this->getRequest();
        $response = $this->getResponse();
        $formData = $request->getPost()->toArray();
        $errorMessage = '';
        $setDirtyFlag = false;
        $this->_initView();
        // calling the model
        $plinkUserTable = $this->getPlinkUserTable();
        
        $shippingSearchForm = new ShippingSearchForm();
        $userId = trim($identity['PLU_USER_ID']);
        $customerShipTos = array();
        $searchFilter = '';
        
        if ($request->isPost()) {
            $user = $this->getServiceLocator()->get('User/Model/PlinkUser');
            $form->setInputFilter($user->getCustomerAddInputFilter());
            $form->setValidationGroup('csr_user_name', 'csr_status', 'PLC_DFT_SHIPTO', 'PLC_CUSTNO');
            $form->setData($formData);
            $flag = true;
            $csr_cust_group = $formData['csr_cust_group'];
            
            /*
             * if(!empty($csr_cust_group)){
             * $custGroup = $plinkUserTable->callProcedureCheckCustgroup ( trim ($csr_cust_group) );
             * }
             *
             * if(!empty($custGroup['Message'])){
             * $form->setMessages(array('csr_cust_group'=>array($custGroup['Message'])));
             * $flag = false;
             * }
             */
            if (empty($formData['PLC_DFT_SHIPTO']) || $formData['PLC_DFT_SHIPTO'] == '0' || empty($formData['PLC_CUSTNO']) || $formData['PLC_CUSTNO'] == '0') {
                $msg_default_shipto = 'Default Ship-To is required';
                $form->setMessages(array(
                    'PLC_DFT_SHIPTO' => array(
                        $msg_default_shipto
                    )
                ));
                $flag = false;
            }
            if ($form->isValid() && $flag == true) {
                $plcDCustNo = $plcDftShipTo = $plcDftShipMethod = $plcDftUom = $CustGroup = $CustName = $plcEmails = $plcStatus = $custType = '';
                
                if (! empty($formData['csr_cust_group'])) {
                    $CustGroup = $formData['csr_cust_group'];
                }
                if (! empty($formData['csr_user_name'])) {
                    $CustName = strtoupper($formData['csr_user_name']);
                }
                if (! empty($formData['csr_email_address'])) {
                    $plcEmails = $formData['csr_email_address'];
                }
                if (! empty($formData['csr_status'])) {
                    $plcStatus = $formData['csr_status'];
                }
                // if(!empty($formData['PLC_DFT_UOM'])){
                // $plcDftUom = $formData['PLC_DFT_UOM'];
                // }
                
                if (! empty($formData['PLC_DFT_SHIP_METHOD'])) {
                    $plcDftShipMethod = $formData['PLC_DFT_SHIP_METHOD'];
                }
                if (! empty($formData['PLC_DFT_SHIPTO'])) {
                    $plcDftShipTo = $formData['PLC_DFT_SHIPTO'];
                }
                if (! empty($formData['PLC_CUSTNO'])) {
                    $plcDCustNo = $formData['PLC_CUSTNO'];
                }
                $userId = trim($identity['PLU_USER_ID']);
                
                // $plinkCsrCustomerAdd = $plinkUserTable->callProcedureSaveCsrCustomerAdd($userId, $CustGroup, $CustName, $plcEmails, $plcStatus, $custType);
                $plinkCsrCustomerAdd = $plinkUserTable->callProcedureSaveCsrCustomerAddWithDefaults($userId, $CustGroup, $CustName, $plcEmails, $plcStatus, $plcDftUom, $plcDftShipMethod, $plcDftShipTo, $plcDCustNo);
                // $plinkCsrCustomerEdit = $plinkUserTable->callProcedureSaveCsrCustomerEdit($userId, $CustGroup, $CustName, $plcEmails, $plcStatus, $custType);
                $returnMessage = trim($plinkCsrCustomerEdit['message']);
                if (! empty($returnMessage)) {
                    $errorMessage = $returnMessage;
                } else {
                    $this->addFlashMessage('Customer Details Updated Successfully');
                    $this->redirect()->toRoute('user/csrCustomerList');
                }
            } else {
                $setDirtyFlag = true;
            }
        }
        
        $customerShipTos = $plinkUserTable->callProcedureGetCustomerShipTos(trim($identity['PLU_CUST_GROUP']), $searchFilter);
        $plinkCsrCustomerDetail = $plinkUserTable->callProcedureGetCsrCustomersDetail($customerId);
        
        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'identity' => $identity,
            'customerId' => $customerId,
            'form' => $form,
            'plinkCsrCustomerDetail' => $plinkCsrCustomerDetail,
            'errorMessage' => $errorMessage,
            'request' => $request,
            'customerDefaultsForm' => $customerDefaultsForm,
            'customerShipTos' => $customerShipTos,
            'setDirtyFlag' => $setDirtyFlag,
            'shippingSearchForm' => $shippingSearchForm
        ));
        return $viewModel;
    }

    /*
     * adminCustomerLoadDataAction - This is the function to load the tab data in the edit form for the admin edit customer screen
     */
    public function adminCustomerLoadDataAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        $form = new CsrCustomerEditForm();
        $customerDefaultsForm = new CsrCustomerDefaultsForm();
        $request = $this->getRequest();
        $response = $this->getResponse();
        $formDataAjax = $request->getPost()->toArray();
        $formData = array();
        $customerId = $formDataAjax['customerId'];
        $tabSelected = $formDataAjax['activeTab'];
        if (! empty($formDataAjax['formData']))
            parse_str($formDataAjax['formData'], $formData);
        $errorMessage = '';
        $flashMessages = array();
        $setDirtyFlag = false;
        $this->_initView();
        // calling the model
        $plinkUserTable = $this->getPlinkUserTable();
        
        $shippingSearchForm = new ShippingSearchForm();
        $userId = trim($identity['PLU_USER_ID']);
        $customerShipTos = array();
        $searchFilter = '';
        
        if (! empty($formData)) {
            
            $submitPost = ((isset($formData['csr_customer_defaults'])) ? $formData['csr_customer_defaults'] : '');
            $csrCustomerEditForm = ((isset($formData['csr_customer_field'])) ? $formData['csr_customer_field'] : '');
            if ($submitPost == '1') {
                
                $customerDefaultsForm->setData($formData);
                $defaultUom = ''; // $formData['PLC_DFT_UOM'];
                $defaultShipMethod = $formData['PLC_DFT_SHIP_METHOD'];
                $defaultCustomerNumber = $formData['PLC_CUSTNO'];
                $defaultShipTo = $formData['PLC_DFT_SHIPTO'];
                echo '<pre>';
                print_r($formData);
                die();
                $plinkUserCustDftsUpdate = $plinkUserTable->callProcedureUpdatePlinkCustDfts($userId, $customerId, $defaultUom, $defaultShipMethod, $defaultCustomerNumber, $defaultShipTo);
                if (empty(trim($plinkUserCustDftsUpdate['message']))) {
                    $flashMessages = array(
                        '<span class="colorblue f18px">Customer Defaults Updated Successfully</span>'
                    );
                    // $this->addFlashMessage('Customer Defaults Updated Successfully');
                    // $this->redirect ()->toRoute ( 'user/csrCustomerList' );
                } else {
                    $errorMessage = trim($plinkUserCustDftsUpdate['message']);
                }
            } else if (isset($csrCustomerEditForm) && ($csrCustomerEditForm == '1')) {
                
                $user = $this->getServiceLocator()->get('User/Model/PlinkUser');
                
                // get and set input filter validation of form user
                // model
                // if($type == 'add'){
                $form->setInputFilter($user->getCustomerAddInputFilter());
                
                $form->setData($formData);
                if ($form->isValid()) {
                    // echo '<pre>'; print_r($formData); print_r($identity); die;
                    $CustGroup = $CustName = $plcEmails = $plcStatus = $custType = '';
                    if (! empty($formData['csr_cust_group'])) {
                        $CustGroup = $formData['csr_cust_group'];
                    }
                    
                    if (! empty($formData['csr_user_name'])) {
                        $CustName = $formData['csr_user_name'];
                    }
                    if (! empty($formData['csr_email_address'])) {
                        $plcEmails = $formData['csr_email_address'];
                    }
                    if (! empty($formData['csr_status'])) {
                        $plcStatus = $formData['csr_status'];
                    }
                    $userId = trim($identity['PLU_USER_ID']);
                    
                    /*
                     * echo $userId;
                     * echo '<br />';
                     * echo $CustGroup;
                     * echo '<br />';
                     * echo $CustName;
                     * echo '<br />';
                     * echo $plcEmails;
                     * echo '<br />';
                     * echo $plcStatus;
                     * echo '<br />';
                     * echo $custType;
                     * echo '<br />';
                     *
                     * die;
                     */
                    
                    $plinkCsrCustomerEdit = $plinkUserTable->callProcedureSaveCsrCustomerEdit($userId, $CustGroup, $CustName, $plcEmails, $plcStatus, $custType);
                    
                    $returnMessage = trim($plinkCsrCustomerEdit['message']);
                    if (! empty($returnMessage)) {
                        $errorMessage = $returnMessage;
                    } else {
                        $flashMessages = array(
                            '<span class="colorblue f18px">Customer Details Updated Successfully</span>'
                        );
                        // $this->addFlashMessage('Customer Details Updated Successfully');
                        // $this->redirect ()->toRoute ( 'user/csrCustomerList');
                    }
                } else {
                    $setDirtyFlag = true;
                }
            }
        }
        
        $customerShipTos = $plinkUserTable->callProcedureGetCustomerShipTos(trim($customerId), $searchFilter);
        $plinkCsrCustomerDetail = $plinkUserTable->callProcedureGetCsrCustomersDetail($customerId);
        
        $viewModel = new ViewModel();
        $viewModel->setTerminal('1');
        $viewModel->setVariables(array(
            'identity' => $identity,
            'customerId' => $customerId,
            'form' => $form,
            'formData' => $formData,
            'plinkCsrCustomerDetail' => $plinkCsrCustomerDetail,
            'errorMessage' => $errorMessage,
            'request' => $request,
            'customerDefaultsForm' => $customerDefaultsForm,
            'customerShipTos' => $customerShipTos,
            'setDirtyFlag' => $setDirtyFlag,
            'shippingSearchForm' => $shippingSearchForm,
            'tabSelected' => $tabSelected,
            'flashMessages' => $flashMessages
        ));
        return $viewModel;
    }

    public function printPdfAction()
    {
        $orderNum = intval(trim($this->getEvent()
            ->getRouteMatch()
            ->getParam('id')));
        $plinkUserTable = $this->getPlinkUserTable();
        $sp = $plinkUserTable->callProcedurePrintPdf($orderNum);
        
        $ord_totals = $plinkUserTable->callProcedureGetOrderTotals($orderNum);
        
        $ord_totals['caseQty'] = floor($ord_totals['caseQty']);
        
        $ord_totals['productWgt'] = $ord_totals['prodWeight'] . ' lbs';
        unset($ord_totals['prodWeight']);
        $ord_totals['palletWgt'] = $ord_totals['palletWeight'] . ' lbs';
        unset($ord_totals['palletWeight']);
        $ord_totals['totalWgt'] = $ord_totals['totalWeight'] . ' lbs';
        unset($ord_totals['totalWeight']);
        
        unset($ord_totals['output']);
        unset($ord_totals['result']);
        unset($ord_totals['message']);
        
        $ord_header_sp = $plinkUserTable->callProcedureGetOrderHeader($orderNum);
        $ord_header = $ord_header_sp['output'];
        $ord_header['OH_ENTRY_DATE'] = date('M d, Y', strtotime($ord_header['OH_ENTRY_DATE']));
        $ord_header['OH_REQ_DELIV_DATE'] = date('M d, Y', strtotime($ord_header['OH_REQ_DELIV_DATE']));
        
        if ($ord_header['OH_REQ_DELIV_TIME'] == '0') {
            $ord_header['OH_REQ_DELIV_TIME'] = '';
        }
        
        $delivTimeVal = $ord_header['OH_REQ_DELIV_TIME'];
        
        /**
         * Same code block exists in order header action
         * 
         * @todo : Encapsulate code below.
         */
        if (! empty($delivTimeVal)) {
            $delivTimeTotalDigits = strlen($delivTimeVal);
            if ($delivTimeTotalDigits == '5') {
                $delivTimeHour = substr($delivTimeVal, '0', '1');
                $delivTimeMinutes = substr($delivTimeVal, '1', '2');
                $delivTimeAmPm = '';
                if ($delivTimeHour > 12) {
                    $delivTimeHour = $delivTimeHour - 12;
                    $delivTimeAmPm = 'pm';
                } else {
                    if ($delivTimeHour < 10) {}
                    $delivTimeAmPm = 'am';
                }
                $delivTimeVal = $delivTimeHour . ':' . $delivTimeMinutes . $delivTimeAmPm;
            } else if ($delivTimeTotalDigits == '6') {
                $delivTimeHour = substr($delivTimeVal, '0', '2');
                $delivTimeMinutes = substr($delivTimeVal, '2', '2');
                $delivTimeAmPm = '';
                if ($delivTimeHour > 12) {
                    $delivTimeHour = $delivTimeHour - 12;
                    $delivTimeAmPm = 'pm';
                } else {
                    if ($delivTimeHour < 10) {}
                    $delivTimeAmPm = 'am';
                }
                $delivTimeVal = $delivTimeHour . ':' . $delivTimeMinutes . $delivTimeAmPm;
            } else {
                $delivTimeVal = '';
            }
            
            $ord_header['OH_REQ_DELIV_TIME'] = $delivTimeVal;
        }
        
        $ord_header_notes_sp = $plinkUserTable->callProcedureGetOrderNotes($orderNum);
        $ord_header['notes'] = $ord_header_notes_sp['output'];
        
        $ord_items_sp = $plinkUserTable->callProcedureGetOrderLineItems($orderNum);
        $ord_items = $ord_items_sp['output'];
        $ord_subs_sp = $plinkUserTable->callProcedureGetOrderSubstituteItems($orderNum);
        $ord_subs = $ord_subs_sp['output'];
        
        // Merge w/o data into document template fields
        $jod = new \Lib\Service1\Custom();
        
        $xml_totals = $jod->CreateXMLTagArray('totals', $ord_totals);
        $xml_header = $jod->CreateXMLTagArray('header', $ord_header);
        
        $xml_items = '';
        foreach ($ord_items as $line_item) {
            $line_item['OL_QTY_ORD'] = floor($line_item['OL_QTY_ORD']);
            $line_item['ITM_CASE_QTY_ORD'] = floor($line_item['ITM_CASE_QTY_ORD']);
            $line_item['ITM_UNIT_QTY_ORD'] = floor($line_item['ITM_UNIT_QTY_ORD']);
            $line_item['OL_NET_PRICE'] = number_format($line_item['OL_NET_PRICE'], 2);
            $line_item['OL_LIST_PRICE'] = number_format($line_item['OL_LIST_PRICE'], 2);
            $line_item['OL_EXT_PRICE'] = number_format($line_item['OL_EXT_PRICE'], 2);
            
            $xml_items .= $jod->CreateXMLTagArray('item', $line_item);
        }
        $xml_items = "<items>$xml_items</items>";
        
        $xml_subs = '';
        foreach ($ord_subs as $sub_item) {
            $xml_subs .= $jod->CreateXMLTagArray('sub', $sub_item);
        }
        $xml_subs = "<subs>$xml_subs</subs>";
        
        $xml = "<order>
		$xml_totals
		$xml_header
		$xml_items
		$xml_subs
		</order>";
        
        // header("content-type: text/xml");
        // echo $xml;
        // exit;
        
        $printTemplate = 'OrderPrint-Data.pdf';
        // WEB-INF\templates
        // Set the document template
        // error_reporting(E_ALL);
        // ini_set("display_errors", 1);
        
        // $request = "http://172.25.10.30:8080/jodreports-webapp-2.4.0/WEB-INF/templates/$printTemplate";
        $request = "http://172.25.10.30:8080/jodreports-webapp-2.4.0/xml/$printTemplate";
        $fileName = "PLink_Order_{$orderNum}.pdf";
        
        // Create the PDF
        $pdf = $jod->retrievePDF($xml, $request);
        if (substr($pdf, 0, 4) != '%PDF') {
            echo "<h1>Error occured - PDF not generated. See below. </h1>" . $pdf;
            exit();
        }
        // echo $pdf;
        // exit;
        
        // file_put_contents ( '/www/zendsvr6/htdocs/order_print.pdf', $pdf);
        // echo "<h2>PDF file saved. See /www/zendsvr6/htdocs/order_print.pdf</h2>";
        // exit;
        
        $jod->SendPDF2Browser($pdf, $fileName);
        die();
    }

    /*
     * orderAttachment Action - This is the action which will be called to attach the order file for the current order
     * @param void
     * @author kailash
     */
    public function orderAttachmentAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        $this->config = $this->getServiceLocator()->get('Config');
        $userId = trim($identity['PLU_USER_ID']);
        $order_session = new Container('order');
        $currentOrdNum = $order_session->offsetGet('order_num');
        $orderAttachmentTable = $this->getOrderAttachmentTable();
        $plinkUserTable = $this->getPlinkUserTable();
        $getOrderHeader = $plinkUserTable->callProcedureGetOrderHeader($currentOrdNum);
        
        $customerId = '';
        $allowed_order_attachmets_max_size = '0';
        $this->config = $this->getServiceLocator()->get('Config');
        $allowed_file_attachments = $this->config['allowed_file_attachments_white_list'];
        $allowed_order_attachmets_max_size = $this->config['allowed_order_attachmets_max_size']['size'];
        $form = new OrderAttachmentForm();
        $request = $this->getRequest();
        $response = $this->getResponse();
        $formData = $request->getPost()->toArray();
        $errorMessage = '';
        // to set the layout
        $this->_initView();
        // calling the model
        if ($request->isPost()) {
            $user = $this->getServiceLocator()->get('User/Model/OrderAttachment');
            // get and set input filter validation of attachment form user
            $form->setInputFilter($user->getAttachmentInputFilter());
            $formData = array_merge_recursive($request->getPost()->toArray(), $request->getFiles()->toArray());
            
            $form->setData($formData);
            
            $fileSizeMB = number_format($formData['PLAT_UPL_FILENAME']['size'] / 1048576, 2);
            
            /* file extension and size check validation */
            $this->config = $this->getServiceLocator()->get('Config');
            $allowed_file_attachments = $this->config['allowed_file_attachments_white_list'];
            
            // die;
            $valid_file = true;
            $ext = pathinfo($formData['PLAT_UPL_FILENAME']['name'], PATHINFO_EXTENSION);
            
            if (empty($formData['PLAT_UPL_FILENAME']['name'])) {
                $valid_file = false;
                $form->setMessages(array(
                    'PLAT_UPL_FILENAME' => array(
                        'Please select a file for upload'
                    )
                ));
            } else if (! in_array(strtolower($ext), $allowed_file_attachments)) {
                $valid_file = false;
                $form->setMessages(array(
                    'PLAT_UPL_FILENAME' => array(
                        'The file "' . $formData['PLAT_UPL_FILENAME']['name'] . '"  has a file type that is not allowed for upload'
                    )
                ));
            } else if ($fileSizeMB > $allowed_order_attachmets_max_size) {
                
                // die('kailash');
                $valid_file = false;
                $form->setMessages(array(
                    'PLAT_UPL_FILENAME' => array(
                        'Size of file "' . $formData['PLAT_UPL_FILENAME']['name'] . '"  exceeds ' . $allowed_order_attachmets_max_size . 'MB. Maximum allowed size of attachment file is ' . $allowed_order_attachmets_max_size . 'MB'
                    )
                ));
            }
            /* END */
            if ($form->isValid() && $valid_file === true) {
                $order_session = new Container('order');
                $currentOrdNum = $order_session->offsetGet('order_num');
                
                if (isset($this->config['order_attachment_file_path']['path']) && ! empty($this->config['order_attachment_file_path']['path'])) {
                    
                    // $folder_path = "./../plink_attachments/pilot/";
                    $folder_path = $this->config['order_attachment_file_path']['path'];
                }
                
                if (! is_dir($folder_path . $currentOrdNum)) {
                    mkdir($folder_path . $currentOrdNum, 0777, true);
                }
                
                $filter = new \Zend\Filter\File\RenameUpload(array(
                    'target' => $folder_path . $currentOrdNum . "/",
                    'randomize' => true,
                    "use_upload_extension" => true,
                    "use_upload_name" => false
                
                ));
                
                $UplodFileData = $filter->filter($formData['PLAT_UPL_FILENAME']);
                /* data to be send to stored procedure */
                $current_user = $identity['PLU_CRT_USER'];
                $current_order = $currentOrdNum;
                $file_original_name = str_replace(" ", "_", $formData['PLAT_UPL_FILENAME']['name']);
                
                $file_ext = $ext;
                $file_size = $formData['PLAT_UPL_FILENAME']['size'];
                $file_description = $formData['PLAT_DESCRIPTION'];
                $file_server_name = end(explode("/", $UplodFileData['tmp_name']));
                
                /* end */
                
                $orderAttachmentTable = $this->getOrderAttachmentTable();
                $plinkOrderAttachmentAdd = $orderAttachmentTable->callProcedureSaveAttachment($current_user, $current_order, $file_original_name, $file_ext, $file_size, $file_description, $file_server_name);
                $returnMessage = trim($plinkOrderAttachmentAdd['Message']);
                
                if (! empty($returnMessage)) {
                    $errorMessage = $returnMessage;
                } else if (! empty(trim($plinkOrderAttachmentAdd['plat_attach_number']))) {
                    
                    $this->redirect()->toUrl('/user/review-order/Attachments');
                    // $this->redirect ()->toUrl ( '/user/review-order/'. $userId );
                }
            }
        }
        // $plinkCsrCustomerDetail = array();
        
        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'identity' => $identity,
            'getOrderHeader' => $getOrderHeader,
            'allowed_file_attachments' => $allowed_file_attachments,
            'allowed_order_attachmets_max_size' => $allowed_order_attachmets_max_size,
            'form' => $form,
            'errorMessage' => $errorMessage,
            'request' => $request
        
        ));
        return $viewModel;
    }

    /*
     * deleteOrderAttachmentFile Action - This is the action which will be called to delete the attached order file via ajax
     * @param void
     * @author kailash
     */
    public function deleteOrderAttachmentFileAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        $this->config = $this->getServiceLocator()->get('Config');
        $viewModel = new ViewModel();
        $request = $this->getRequest();
        $response = $this->getResponse();
        $formData = $request->getPost()->toArray();
        
        $orderAttachmentTable = $this->getOrderAttachmentTable();
        // $formData = $request->getPost ();
        $platAttachNo = trim($formData['PLAT_ATTACH_NO']);
        $platOrderNo = trim($formData['PLAT_ORDER_NO']);
        
        $removeOrderAttachmentFileDetails = $orderAttachmentTable->callProcedureGetOrderAttachmentDetail($platOrderNo, $platAttachNo);
        $fileDetailsComplete = array();
        foreach ($removeOrderAttachmentFileDetails['result'] as $fileDetails) {
            $fileDetailsComplete = $fileDetails;
        }
        // $folder_path = "./../plink_attachments/pilot/";
        
        if (isset($this->config['order_attachment_file_path']['path']) && ! empty($this->config['order_attachment_file_path']['path'])) {
            
            // $folder_path = "./../plink_attachments/pilot/";
            $folder_path = $this->config['order_attachment_file_path']['path'];
        }
        
        $completeFilepath = $folder_path . $fileDetailsComplete['PLAT_ORDER_NO'] . "/" . $fileDetailsComplete['PLAT_IFS_FILENAME'];
        // disable layout if request by Ajax
        $viewModel->setTerminal($request->isXmlHttpRequest());
        $statusesToUpdate = array();
        // making the object the model
        $removeOrderAttachment = array();
        if (! empty($fileDetailsComplete['PLAT_ORDER_NO']) && ! empty($fileDetailsComplete['PLAT_ATTACH_NO'])) {
            $removeOrderAttachment = $orderAttachmentTable->callProcedureRemoveOrderAttachment($fileDetailsComplete['PLAT_ORDER_NO'], $fileDetailsComplete['PLAT_ATTACH_NO']);
        }
        
        if (empty($removeOrderAttachment['message'])) {
            unlink($completeFilepath);
            $response = 'success';
        } else {
            $response = 'fail';
        }
        
        $jsonModel = new JsonModel();
        $jsonModel->setVariables(array(
            'html' => $response
        ));
        
        return $jsonModel;
    }

    /*
     * updateOrderAttachmentDescription Action - This is the action which will be called to update the attached file description
     * @param void
     * @author kailash
     */
    public function updateOrderAttachmentDescriptionAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        $viewModel = new ViewModel();
        $request = $this->getRequest();
        $response = $this->getResponse();
        $formData = $request->getPost()->toArray();
        
        $platAttachNo = trim($formData['PLAT_ATTACH_NO']);
        $platOrderNo = trim($formData['PLAT_ORDER_NO']);
        $platDescription = trim($formData['PLAT_DESCRIPTION']);
        $userId = trim($identity['PLU_USER_ID']);
        
        $orderAttachmentTable = $this->getOrderAttachmentTable();
        // disable layout if request by Ajax
        $viewModel->setTerminal($request->isXmlHttpRequest());
        $statusesToUpdate = array();
        // making the object the model
        $updateOrderAttachmentDescription = array();
        if (! empty($platAttachNo) && ! empty($platOrderNo)) {
            $updateOrderAttachmentDescription = $orderAttachmentTable->callProcedureUpdateOrderAttachmentDescription($platAttachNo, $platOrderNo, $platDescription, $userId);
        }
        
        if (empty($updateOrderAttachmentDescription['message'])) {
            
            $response = 'success';
        } else {
            $response = 'fail';
        }
        
        $jsonModel = new JsonModel();
        $jsonModel->setVariables(array(
            'html' => $response
        ));
        
        return $jsonModel;
    }

    /*
     * orderAttachmentFileDownloadAction Action - This is the action which will be called to download the attached order file
     * @param void
     * @author kailash
     */
    public function orderAttachmentFileDownloadAction($file_name = null)
    {
        $this->config = $this->getServiceLocator()->get('Config');
        $file_name = $this->getRequest()->getQuery('fileName');
        $file_name_ori = $this->getRequest()->getQuery('fileNameOri');
        
        $currentOrdNum = $this->getRequest()->getQuery('orderNumber');
        // $folder_path = "./../plink_attachments/pilot/";
        if (isset($this->config['order_attachment_file_path']['path']) && ! empty($this->config['order_attachment_file_path']['path'])) {
            $folder_path = $this->config['order_attachment_file_path']['path'];
        }
        
        $target = $folder_path . $currentOrdNum . "/";
        $filename = $file_name;
        $file = $target . $filename;
        $len = filesize($file); // Calculate File Size
        ob_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Type:application/pdf"); // Send type of file
        header("Content-type: application/octet-stream"); // Send type of file
        $header = "Content-Disposition: attachment; filename=$file_name_ori"; // Send File Name
        header($header);
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . $len); // Send File Size
        @readfile($file);
        exit();
    }

    /*
     * orderConfirmation Action - This is the action which will be called to show the current submitted order details confirmation
     * @param void
     * @author kailash
     */
    public function orderConfirmationAction()
    {
        
        /* get current saved order value to for the order confirmation page */
        $session_current_submited = new Container('previous_order');
        $currentOrdNum = $session_current_submited->offsetGet('current_order_number');
        /* END */
        $orderNumToDisplay = trim($this->getEvent()
            ->getRouteMatch()
            ->getParam('orderNumToDisplay'));
        $configSubstitutes = $this->getServiceLocator()->get('Config');
        
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        $this->_initView();
        
        $request = $this->getRequest();
        
        // making the object the model
        $plinkUserTable = $this->getPlinkUserTable();
        
        // check if the order header is not set in the session
        if (empty($currentOrdNum)) {
            $this->flashmessenger()->addMessage("There is no current order. Please try again.");
            $this->redirect()->toRoute('user/index');
        }
        
        $userId = trim($identity['PLU_USER_ID']);
        $getOrderTotals = $plinkUserTable->callProcedureGetOrderTotals($currentOrdNum);
        $getOrderHeader = $plinkUserTable->callProcedureGetOrderHeader($currentOrdNum);
        
        /**
         * jlopez
         * Reset order container session storage
         */
        $storage = new Container('order');
        
        if ($storage->offsetExists('order_num')) {
            
            $storage->offsetUnset('order_num');
        }
        
        if ($storage->offsetExists('mailto')) {
            
            $storage->offsetUnset('mailto');
        }
        
        /**
         * @jlopez
         *
         * Clear current order
         *
         * https://app.asana.com/0/322466378561882/331037971794045
         */
        $headers = $this->getResponse()->getHeaders();
        
        $pLinkOrderNoCookie = new SetCookie('OH_PLINK_ORDERNO', null);
        
        $pLinkOrderNoCookie->setPath('/');
        
        $pLinkOrderNoCookie->setExpires(time() + 365 * 60 * 60 + 24);
        
        $headers->addHeader($pLinkOrderNoCookie);
        
        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'identity' => $identity,
            'getOrderTotals' => $getOrderTotals,
            'currentOrdNum' => $currentOrdNum,
            'getOrderHeader' => $getOrderHeader
        ));
        
        return $viewModel;
    }

    public function dummyPricingAction()
    {
        
        /*
         * $adapter = $this->getServiceLocator()->get('adapter');
         * $xxx = $adapter->getDriver ()->getConnection ()->getResource ();
         */
        $plinkUserTable = $this->getPlinkUserTable();
        $dbConn = $plinkUserTable->testFunction();
        
        /*
         * var_dump($xxx);
         * echo "<br/>";
         * var_dump($dbConn);
         * die;
         */
        
        $currDate = date('Ymd');
        $sql = "select fn_Get_Item_Price('1004253', 10, 11120, 1, 4608379, 1, $currDate, $currDate, $currDate) as Price from sysibm.sysdummy1";
        // $sql = "select fn_Get_Item_Price('1740020', '00000958', '0002',$currDate, $currDate) as Price from sysibm.sysdummy1";
        $stmt = db2_prepare($dbConn, $sql) or die("<br>Prepare failed! " . db2_stmt_errormsg());
        db2_execute($stmt);
        $row = db2_fetch_assoc($stmt);
        // print_r($row);
        
        die('success');
        // echo'<pre>'; print_r($row); die;
    }

    public function checkCustGroupValidationAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        $viewModel = new ViewModel();
        $request = $this->getRequest();
        $response = $this->getResponse();
        $formData = $request->getPost()->toArray();
        
        // $formData = $request->getPost ();
        $csr_cust_group = trim(strtoupper($formData['csr_cust_group']));
        // $shipTo = trim($formData['shipTo']);
        // disable layout if request by Ajax
        $viewModel->setTerminal($request->isXmlHttpRequest());
        // $statusesToUpdate = array ();
        // making the object the model
        $plinkUserTable = $this->getPlinkUserTable();
        $custGroup = array();
        $searchFilter = '';
        $customerShipTos = array();
        if (! empty($csr_cust_group)) {
            $custGroup = $plinkUserTable->callProcedureCheckCustgroup(trim($csr_cust_group));
        }
        // $customerShipTos = $plinkUserTable->callProcedureGetCustomerShipTos ( trim ( $csr_cust_group ), $searchFilter );
        if (! empty($custGroup['Message'])) {
            
            $msg = $custGroup['Message'];
        } else {
            $msg = $custGroup['Message'] = '';
        }
        
        $jsonModel = new JsonModel();
        $jsonModel->setVariables(array(
            // 'html' => $customerShipTos,
			'Message' => trim($msg)
        ));
        
        return $jsonModel;
    }

    public function getDefaultShipToAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        
        $viewModel = new ViewModel();
        $request = $this->getRequest();
        $response = $this->getResponse();
        $formData = $request->getPost()->toArray();
        
        // $formData = $request->getPost ();
        $csr_cust_group = trim(strtoupper($formData['csr_cust_group']));
        // $shipTo = trim($formData['shipTo']);
        // disable layout if request by Ajax
        $viewModel->setTerminal($request->isXmlHttpRequest());
        // $statusesToUpdate = array ();
        // making the object the model
        $plinkUserTable = $this->getPlinkUserTable();
        $custGroup = array();
        $searchFilter = '';
        $customerShipTos = array();
        if (! empty($csr_cust_group)) {
            $customerShipTos = $plinkUserTable->callProcedureGetCustomerShipTos(trim($csr_cust_group), $searchFilter);
        }
        
        // print_r($customerShipTos); die;
        
        if (! empty($customerShipTos['Message'])) {
            
            $msg = $custGroup['Message'];
        } else {
            $msg = $custGroup['Message'] = '';
        }
        
        $jsonModel = new JsonModel();
        $jsonModel->setVariables(array(
            'html' => $customerShipTos,
            'Message' => $msg
        ));
        
        return $jsonModel;
    }

    public function checkAjaxCsrReviewOrderAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            // Identity exists; get it
            $identity = $this->getAuthService()->getIdentity();
        } else {
            $this->redirect()->toRoute('user');
        }
        $msg = '';
        $viewModel = new ViewModel();
        $request = $this->getRequest();
        $response = $this->getResponse();
        $formData = $request->getPost()->toArray();
        $user = $this->getServiceLocator()->get('User/Model/PlinkUser');
        $formOrderSubmit = new CsrOrderSubmitForm();
        
        $formOrderSubmit->setData($formData);
        $formOrderSubmit->setInputFilter($user->getOrderReviewCSREmailInputFilter());
        $viewModel->setTerminal($request->isXmlHttpRequest());
        
        if ($formOrderSubmit->isValid()) {
            
            $validStatus = true;
        } else {
            $validStatus = false;
            $msg = 'Invalid email address.<br />Either correct the email addresses or blank it out.';
        }
        
        $jsonModel = new JsonModel();
        $jsonModel->setVariables(array(
            'valid' => $validStatus,
            'Message' => $msg
        ));
        
        return $jsonModel;
    }
}
