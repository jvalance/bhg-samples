<?php


namespace User\Model;



//require_once('/usr/local/zendsvr6/share/ToolkitApi/ToolkitService.php');

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Adapter\ParameterContainer;

//use \usr\local\zendsvr6\share\ToolkitAPI\ToolkitService;
//use usr\local\zendsvr6\share\ToolkitApi;



//echo ini_get('include_path');


class PlinkUserTable {
	protected $tableGateway;
	protected $tableName = 'PLINK_USER';
	protected $identityField = 'PLU_USER_ID';
	public function __construct(TableGateway $tableGateway) {
		$this->tableGateway = $tableGateway;
	}

	/*
	 * public function callProcedureLogin($userId = 0, $password = ''){ // 	$pswd = '6c11d9114ee42a70ae988eecf63f296d'; // 	$userId = 'dfsaas'; $result = ''; $message = ''; // Get adapter $dbAdapter = $this->tableGateway->getAdapter(); $stmt = $dbAdapter->createStatement(); $qSelect = $stmt->prepare('CALL SPAUTHENTICATE_PLINK_USER(?, ?, ?, ?)'); $parameters = array($userId, $password, $result, $message); // ->bindParam(1, $userId); // $stmt->setParameterContainer(1, $userId); // // $stmt->getResource()->bindParam(2, $pswd); $result1 = $stmt->execute(array( 'in_User_ID' => $userId , 'in_Password_Encrypted' => $password , 'out_Result' => $result , 'out_Message' => $message)); // echo 'next'; echo '<pre>'; // var_dump($result1); $output = $result1->current(); echo '<pre>'; var_dump($output); die('rr'); // $statement = $result->getResource(); // // Retrieve output parameter // $stmt2 = $dbAdapter->createStatement(); // $stmt2->prepare("SELECT @out1 AS result, @out2 AS message"); // Retrieve output parameter // $stmt2 = $dbAdapter->createStatement(); // $stmt2->prepare("SELECT @result as result, @message as message"); // $result = $stmt2->execute(); // $output = $result->current(); return $output; }
	 */

	/*
	 * callProcedureLogin  - This is the function to be used for the users login - This is used when the input values pass the validations
	 * @params - $userId - This is the user Id that the user has input from the login form
	 * $password - This is the password entered by the user after the md5 algorithm used
	 * @author - Rohit
	*/
	public function callProcedureLogin($userId = 0, $password = '') {

		$result = '';
		$message = '';
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Authenticate_PLink_User(?, ?, ?, ?)' );


		// $cont = new ParameterContainer($parameters);
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'userId', $userId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'password', $password, $cont::TYPE_STRING );
		$cont->offsetSet ( 'result', $result, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );
		$cont->offsetSetReference ( 'result', 'result' );
		$cont->offsetSetReference ( 'message', 'message' );
		$stmt->setParameterContainer ( $cont );
		
		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"userId" => DB2_PARAM_IN,
				"password" => DB2_PARAM_IN,
				"result" => DB2_PARAM_OUT,
				"message" => DB2_PARAM_OUT
		);

		// $result1 = $stmt->execute($parameters, $directs);
		$result1 = $stmt->execute ( $cont, $directs );

		// var_dump($stmt->getParameterContainer());
		// var_dump($cont->getPositionalArray());

		$result = $cont->offsetGet ( 'result' );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array('result' => $result, 'message' => $message, 'output' => '');

		if($result == '1'){
		$output = $result1->current ();

		$return_array['output'] = $output;

		}

		return $return_array;
	}


	public function callProcedureGetItemInquiry($customer = '', $ship = '', $brand = '', $size = '', $filter = ''){
	    
	    $result = '';
	    $message = '';
	    
	    try{
    	    $dbAdapter = $this->tableGateway->getAdapter ();
    	    $stmt = $dbAdapter->createStatement ();
    	    $stmt->prepare ( 'CALL sp_Get_Item_Inquiry (?, ?, ?, ?, ?, ?, ?)' );
    	   
    	    // $cont = new ParameterContainer($parameters);
    	    $cont = new ParameterContainer ();
    	    $cont->offsetSet ( 'in_Cust_Num', $customer, $cont::TYPE_STRING );
    	    $cont->offsetSet ( 'in_ShipTo', $ship, $cont::TYPE_STRING );
    	    $cont->offsetSet ( 'in_Brand', $brand, $cont::TYPE_STRING );
    	    $cont->offsetSet ( 'in_Size', $size, $cont::TYPE_STRING );
    	    $cont->offsetSet ( 'in_Filter', $filter, $cont::TYPE_STRING );
    	    $cont->offsetSet ( 'result', $result, $cont::TYPE_STRING );
    	    $cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );
    	    
    	    
    	    $directs = array (
    	        "in_Cust_Num" => DB2_PARAM_IN,
    	        "in_ShipTo" => DB2_PARAM_IN,
    	        "in_Brand" => DB2_PARAM_IN,
    	        "in_Size" => DB2_PARAM_IN,
    	        "in_Filter" => DB2_PARAM_IN,
    	        "result" => DB2_PARAM_OUT,
    	        "message" => DB2_PARAM_OUT
    	    );
    	    
    	    $stmt->setParameterContainer ( $cont );
    	    

    	    $exec = $stmt->execute ( $cont, $directs );
    	        	    
    	    $result = $cont->offsetGet ( 'result' );
    	    $message = $cont->offsetGet ( 'message' );
    	    $return_array = array('result' => $result, 'message' => $message, 'output' => '');
    	    
            if (! empty ( $exec )) {
                
                $output = $exec->current ();

    			// checking if there is result
    			if (! empty ( $output )) {
    				$i = 0;
    				$outputArray = array ();
    				$pricing = array();
    				// setting up the current result in the array
    				$outputArray [$i] = $output;
    				// iterating for all the next results and if there is result then setting them up in the array
    				while ( $outputNext = $exec->next () ) {
    					$i ++;
    					$outputArray [$i] = $outputNext;
    
    
    				}

    			// setting up the final array to the output variable
    			
    			if (! empty ( $output )) {
    			    
    					$return_array ['output'] = $outputArray;
    				}
    			}
            }
		      
		  return $return_array;

	    }catch(\Exception $e){
	        
	        $err = $e->getMessage();
	         
	        if(!$err){
	             
	            $err = 'DB2 Error.';
	            $err .= ' Unexpected result after execute statement: ';
	            $err .= ' Expected DB2 resource. Received false';
	        }
	         
	        error_log(sprintf('[jlopez] - %s', $err));
	        
	        return false;
	    }
	}
	/*
	 * callProcedureGetCustomerShipTos  - This is the function to be used at the customer order shipping page to get the ship to addresses
	* @params - $customerGroup - This is the customer Group from the session
	* $searchFilter - These are the search filters that are input in the search field
	* @author - Rohit
	*/
	public function callProcedureGetCustomerShipTos($customerGroup = '', $searchFilter = ''){

		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Get_Cust_ShipTos(?, ?)' );

		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'customerGroup', $customerGroup, $cont::TYPE_STRING );
		$cont->offsetSet ( 'searchFilter', $searchFilter, $cont::TYPE_STRING );
		$stmt->setParameterContainer ( $cont );


		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"customerGroup" => DB2_PARAM_IN,
				"searchFilter" => DB2_PARAM_IN,
		);

		$result1 = $stmt->execute ( $cont, $directs );

		$return_array = array (
				'output' => ''
		);

		if (! empty ( $result1 )) {
			$output = $result1->current ();

			// checking if there is result
			if (! empty ( $output )) {
				$i = 0;
				$outputArray = array ();
				// setting up the current result in the array
				$outputArray [$i] = $output;
				// iterating for all the next results and if there is result then setting them up in the array
				while ( $outputNext = $result1->next () ) {
					$i ++;
					$outputArray [$i] = $outputNext;
				}
				// setting up the final array to the output variable
				if (! empty ( $output )) {
					$return_array ['output'] = $outputArray;
				}
			}
		}

		return $return_array;

	}

	/*
	 * callProcedureGetUnsavedOrder  - This is the function to be used at the order shipping and order header page to get the data from the previous saved order
	* @params - $customerId - This is the customer Id from the session
	* @author - Rohit
	*/
public function callProcedureGetUnsavedOrder($customerId = ''){
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Get_CurrentOrder(?, ?, ?, ?)' );
		$orderNum = '';
		$result = '';
		$message = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'customerId', $customerId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'orderNum', $orderNum, $cont::TYPE_STRING );
		$cont->offsetSet ( 'result', $result, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );

		$stmt->setParameterContainer ( $cont );

		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"customerId" => DB2_PARAM_IN,
				"orderNum" => DB2_PARAM_OUT,
				"result" => DB2_PARAM_OUT,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$orderNum = $cont->offsetGet ( 'orderNum' );
		$result = $cont->offsetGet ( 'result' );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'output' => '',
				'orderNum' => $orderNum,
				'result' => $result,
				'message' => $message
		);

// 		if (! empty ( $result1 )) {
// 			$output = $result1->current ();
// 			$return_array['output'] = $output;
// 			// checking if there is result

// 		}

		return $return_array;

	}


/*
	 * callProcedureGetCurrentOrder  - This is the function to be used at the order shipping and order header page to get the data from the previous saved order
	* @params - $customerId - This is the customer Id from the session
	* @author - Rohit
	*/
public function callProcedureGetCurrentOrder($customerId = ''){

		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Get_CurrentOrder(?, ?, ?, ?, ?)' );
		$orderingStep = '';
		$orderNum = '';
		$result = '';
		$message = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'customerId', $customerId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'orderNum', $orderNum, $cont::TYPE_STRING );
		$cont->offsetSet ( 'orderingStep', $orderingStep, $cont::TYPE_STRING );
		$cont->offsetSet ( 'result', $result, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );

		$stmt->setParameterContainer ( $cont );

		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"customerId" => DB2_PARAM_IN,
				"orderNum" => DB2_PARAM_OUT,
				"orderingStep" => DB2_PARAM_OUT,
				"result" => DB2_PARAM_OUT,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$orderNum = $cont->offsetGet ( 'orderNum' );
		$orderingStep = $cont->offsetGet ( 'orderingStep' );
		$result = $cont->offsetGet ( 'result' );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'output' => '',
				'orderNum' => $orderNum,
				'orderingStep' => $orderingStep,
				'result' => $result,
				'message' => $message
		);

		return $return_array;

	}


	/*
	 * callProcedureSaveOrderShipping  - This is the function to save the current order from the order shipping page
	* @params - $customerId - This is the customer Id from the session
	*  $shipTo - The id of the ship to address selected by the user for this order
	*  $shipMethod - This is the shipping method selected for this order
	*  $userId - This is the user Id from the session
	*  $currentOrdNum - This is the order Id that is there in the session, if there is none then it is 0(for new order)
	* @author - Rohit
	*/

	public function callProcedureSaveOrderShipping($customerId = '', $shipTo = '', $shipMethod = '', $userId = '', $currentOrdNum = ''){
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Save_Order_Shipping(?, ?, ?, ?, ?, ?)' );
		$message = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'customerId', $customerId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'shipTo', $shipTo, $cont::TYPE_STRING );
		$cont->offsetSet ( 'shipMethod', $shipMethod, $cont::TYPE_STRING );
		$cont->offsetSet ( 'userId', $userId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'currentOrdNum', $currentOrdNum, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );

		$stmt->setParameterContainer ( $cont );

		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"customerId" => DB2_PARAM_IN,
				"shipTo" => DB2_PARAM_IN,
				"shipMethod" => DB2_PARAM_IN,
				"userId" => DB2_PARAM_IN,
				"currentOrdNum" => DB2_PARAM_INOUT,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$currentOrdNum = $cont->offsetGet ( 'currentOrdNum' );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'output' => '',
				'orderNum' => $currentOrdNum,
				'message' => $message
		);

		return $return_array;

	}

	/*
	 * callProcedureCancelOrder  - This is the function to cancel the current order or save it for later use
	* @params - $orderId - This is the order Id that is there in the session
	*  $action - This accept 2 values either save or cncl - for saving or cancelling the current order respectively
	* @author - Rohit
	*/

public function callProcedureCancelOrder($orderId = '', $action = "CNCL"){
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Cancel_PLink_Order(?, ?, ?, ?)' );
		$result = '';
		$message = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'orderId', $orderId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'action', $action, $cont::TYPE_STRING );
		$cont->offsetSet ( 'result', $result, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );

		$stmt->setParameterContainer ( $cont );

		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"orderId" => DB2_PARAM_IN,
				"action" => DB2_PARAM_IN,
				"result" => DB2_PARAM_OUT,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$result = $cont->offsetGet ( 'result' );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'output' => '',
				'result' => $result,
				'message' => $message
		);

		return $return_array;

	}

	/**
	 * Return all pending orders (unconfirmed orders)
	 * @param string $customerId
	 * @param string $userId
	 * @param string $message
	 * @return resource|NULL
	 */
	public function callProcedureGetPendingOrders($customerId = '', $userId = '', $message = '') {
	    
	    $data = [];
	    
	    try{
	        
    	    $stmt = $this->tableGateway->getAdapter()->createStatement();
    	    $stmt->prepare('CALL sp_Get_PendingOrders(?, ?, ?)');
    	    
    	    $containerParameters = new ParameterContainer();
    	    $containerParameters->offsetSet('customerId', $customerId, ParameterContainer::TYPE_STRING);
    	    $containerParameters->offsetSet('userId', $userId, ParameterContainer::TYPE_STRING);
    	    $containerParameters->offsetSet('message', $message, ParameterContainer::TYPE_STRING);
    	    $stmt->setParameterContainer($containerParameters);
    	    
    	    $directs = [
    	        
    	        'customerId' => DB2_PARAM_IN,
    	        'userId' => DB2_PARAM_IN,
    	        'message' => DB2_PARAM_OUT
    	    ];
    	    
    	    $result = $stmt->execute($containerParameters, $directs);
    	    
    	    foreach($result as $order){
    	        
    	        $data[] = $order;
    	    }
    	    
    	    return $data;
    	    
	    }catch(\Exception $e){
	        
	        $err = $e->getMessage();
	        
	        if(!$err){
	            
	            $err = 'DB2 Error.'; 
	            $err .= ' Unexpected result after execute statement: ';
	            $err .= ' Expected DB2 resource. Received false';
	        }
	        
	        error_log(sprintf('[jlopez] - %s', $err));
	     
	        return new null;
	    }
	}
	/*
	 * callProcedureGetOrderHeader  - This is the function to get the order details, this mainly returns the values for the order header and the order shipping details
	* @params - $orderId - This is the order Id that is there in the session
	* @author - Rohit
	*/

	public function callProcedureGetOrderHeader($orderId = ''){
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Get_OrderHeader(?, ?, ?)' );
		$result = '';
		$message = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'orderId', $orderId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'result', $result, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );

		$stmt->setParameterContainer ( $cont );

		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"orderId" => DB2_PARAM_IN,
				"result" => DB2_PARAM_OUT,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$result = $cont->offsetGet ( 'result' );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'output' => '',
				'result' => $result,
				'message' => $message
		);

		if (! empty ( $result1 )) {
			$output = $result1->current ();
			$return_array['output'] = $output;
			// checking if there is result

		}

		return $return_array;

	}

	/*
	 * callProcedureGetOrderNotes  - This is the function to get the order notes for the order header page
	* @params - $orderId - This is the order Id that is there in the session
	* @author - Rohit
	*/

public function callProcedureGetOrderNotes($orderId = ''){
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Get_OrderNotes(?, ?, ?, ?)' );
		$notes = '';
		$result = '';
		$message = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'orderId', $orderId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'notes', $notes, $cont::TYPE_STRING );
		$cont->offsetSet ( 'result', $result, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );

		$stmt->setParameterContainer ( $cont );

		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"orderId" => DB2_PARAM_IN,
				'notes' => DB2_PARAM_OUT,
				"result" => DB2_PARAM_OUT,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$notes = $cont->offsetGet ( 'notes' );
		$result = $cont->offsetGet ( 'result' );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'output' => '',
				'notes' => $notes,
				'result' => $result,
				'message' => $message
		);

		return $return_array;

	}

	/*
	 * callProcedureSaveOrderHeader  - This is the function to save the order header form
	* @params - $orderId - This is the order Id that is there in the session
	* $reqDeliveryDate - This is the date input from the calender, default value that needs to be set is 0
	* $reqDeliveryTime - This is the time value input from the timepicker, default value that needs to be set is 0
	* $po1 - This is the value of the primary po# field input by the user
	* $po2 - This is the value of the alternate po# 1 field input by the user
	* $po2 - This is the value of the alternate po# 2 field input by the user
	* $userId - The value of user Id who is filling these details from the session
	* @author - Rohit
	*/

public function callProcedureSaveOrderHeader($orderId = '', $reqDeliveryDate = '', $reqDeliveryTime = '', $po1 = '', $po2 = '', $po3 = '', $userId = ''){
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Save_OrderHeader(?, ?, ?, ?, ?, ?, ?, ?, ?)' );
		$result = '';
		$message = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'orderId', $orderId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'reqDeliveryDate', $reqDeliveryDate, $cont::TYPE_STRING );
		$cont->offsetSet ( 'reqDeliveryTime', $reqDeliveryTime, $cont::TYPE_STRING );
		$cont->offsetSet ( 'po1', $po1, $cont::TYPE_STRING );
		$cont->offsetSet ( 'po2', $po2, $cont::TYPE_STRING );
		$cont->offsetSet ( 'po3', $po3, $cont::TYPE_STRING );
		$cont->offsetSet ( 'userId', $userId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'result', $result, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );

		$stmt->setParameterContainer ( $cont );

		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"orderId" => DB2_PARAM_IN,
				"reqDeliveryDate" => DB2_PARAM_IN,
				"reqDeliveryTime" => DB2_PARAM_IN,
				"po1" => DB2_PARAM_IN,
				"po2" => DB2_PARAM_IN,
				"po3" => DB2_PARAM_IN,
				"userId" => DB2_PARAM_IN,
				"result" => DB2_PARAM_OUT,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$result = $cont->offsetGet ( 'result' );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'output' => '',
				'result' => $result,
				'message' => $message
		);

		return $return_array;

	}
 
	/*
	 * callProcedureSaveOrderNotes  - This is the function to save the order notes on the order header form
	* @params - $orderId - This is the order Id that is there in the session
	* $notes - These are the notes input by the user
	* $userId - The value of user Id who is filling these details from the session
	* @author - Rohit
	*/

	public function callProcedureSaveOrderNotes($orderId = '', $notes = '', $userId = ''){
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Save_OrderNotes (?, ?, ?, ?, ?)' );
		$result = '';
		$message = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'orderId', $orderId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'notes', $notes, $cont::TYPE_STRING );
		$cont->offsetSet ( 'userId', $userId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'result', $result, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );

		$stmt->setParameterContainer ( $cont );

		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"orderId" => DB2_PARAM_IN,
				"notes" => DB2_PARAM_IN,
				"userId" => DB2_PARAM_IN,
				"result" => DB2_PARAM_OUT,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$result = $cont->offsetGet ( 'result' );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'output' => '',
				'result' => $result,
				'message' => $message
		);

		return $return_array;

	}

	/*
	* callProcedureGetItemBrandSizes  - This is the function to get the items by brand, size and brandsize
	* @params - $type - This is the type by which we want the items listed
	* @author - Rohit
	*/

	public function callProcedureGetItemBrandSizes($type = '', $customerNum, $shipNumber){
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Get_ItemBrandsSizes (?, ?, ?, ?, ?)' );
		$result = '';
		$message = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'type', $type, $cont::TYPE_STRING );
		$cont->offsetSet ( 'customerNum', $customerNum, $cont::TYPE_STRING );
		$cont->offsetSet ( 'shipNumber', $shipNumber, $cont::TYPE_STRING );
		$cont->offsetSet ( 'result', $result, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );

		$stmt->setParameterContainer ( $cont );

		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"type" => DB2_PARAM_IN,
				"customerNum" => DB2_PARAM_IN,
				"shipNumber" =>  DB2_PARAM_IN,
				"result" => DB2_PARAM_OUT,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$result = $cont->offsetGet ( 'result' );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'output' => '',
				'result' => $result,
				'message' => $message
		);

	if (! empty ( $result1 )) {
			$output = $result1->current ();

			// checking if there is result
			if (! empty ( $output )) {
				$i = 0;
				$outputArray = array ();
				// setting up the current result in the array
				$outputArray [$i] = $output;
				// iterating for all the next results and if there is result then setting them up in the array
				while ( $outputNext = $result1->next () ) {
					$i ++;
					$outputArray [$i] = $outputNext;
				}
				// setting up the final array to the output variable
				if (! empty ( $output )) {
					$return_array ['output'] = $outputArray;
				}
			}
		}

		return $return_array;

	}



	//-----------------------------------------------------------
	function getPrice($tk, $itemNum, $custNum, $shipTo = 0, $reqDate, $priceBookDate) {


    $entryDate = date('Ymd');
   // $reqDate = 20160315;    // THIS SHOULD BE VARIABLE PARAMETER BASED ON VALUE FROM ORDER HEADER
   // $priceBookDate = 20160315; // FOR NOW USE SAME DATE AS ABOVE -- THIS WILL CHANGE LATER
    $netPrice = 0.0;
    $listPrice = 0.0;

   // require_once 'ToolkitService.php';
   // $tk = $serviceManager->get('tk');

    $param = array();
    $param[] = $tk->AddParameterChar('both', 35, 'ItemNo', 'itemNum', $itemNum);
    $param[] = $tk->AddParameterPackDec('both', 8, 0, 'CustNo', 'custNum', $custNum);
    $param[] = $tk->AddParameterPackDec('both', 4, 0, 'ShipTo', 'shipTo', $shipTo);
    $param[] = $tk->AddParameterPackDec('both', 8, 0, 'EntryDate', 'entryDate', $entryDate);
    $param[] = $tk->AddParameterPackDec('both', 8, 0, 'RequestDate', 'reqDate', $reqDate);
    $param[] = $tk->AddParameterPackDec('both', 8, 0, 'PriceBookDate', 'priceBookDate', $priceBookDate);
    $param[] = $tk->AddParameterPackDec('both', 7, 2, 'netPrice', 'netPrice', $netPrice);
    $param[] = $tk->AddParameterPackDec('both', 7, 2, 'listPrice', 'listPrice', $listPrice);

    try {

        $result = $tk->PgmCall('PROG36', "*LIBL", $param, null, null);
//          var_dump($result);
    } catch (Exception $e) {
        echo "Holy Library List Batman!<br>";
        var_dump($e);
    }

    return $result;
	}

	function toolkitSetup( $tk ) {
	    $libl = "PLINKTST LXTSTEC LXTSTUSRF LXTSTF LXTSTUSR LXPRDUSR " .
	    "LXPTF LXOBJ LXSYS WEBTOPTST SSAGTLIC83 LXPARPTF ".
	    "LXPARF LXPARO  RVILIB  ".
	    "LXSRC  QGPL QTEMP";

	    $cmd = "chglibl ($libl)";

	    try {
	        $tk->CLCommand($cmd);
	    } catch (Exception $e) {
	        echo $e->getMessage(), "<br>\n";
	        exit;
	    }

	    // Set up *LDA (local data area) for RPG programs
	    $cmd = "CALL pgm(SYS664) parm('SYS664')";
	    $tk->CLCommand($cmd);

	    // Change job so no logging takes place
	    $cmd = "CHGJOB RUNPTY(10) LOG(0 99 *NOLIST) LOGCLPGM(*NO)";
	    $tk->CLCommand($cmd);

	}

	//-----------------------------------------------------------

	/*
	 * callProcedureGetItemSearch2  - This is the function to get the items by the search parameters on the item search page by brand, size and filter
	* @params - $orderId - This is the current order id that is being processed
	* $brand - This is the brand that is selected from the tab either brand tab or the brand/ size tab
	* $size - This is the size that is selected from the tab either size tab or the brand/ size tab
	* $filter - This is the value that is input in the item/description tab
	* @author - Rohit
	*/

	public function callProcedureGetItemSearch2($orderId = '', $brand = '', $size = '', $filter = '',$custNum = '', $shipTo = ''){
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Get_ItemSearch (?, ?, ?, ?, ?, ?)' );
		$result = '';
		$message = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'orderId', $orderId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'brand', $brand, $cont::TYPE_STRING );
		$cont->offsetSet ( 'size', $size, $cont::TYPE_STRING );
		$cont->offsetSet ( 'filter', $filter, $cont::TYPE_STRING );
		$cont->offsetSet ( 'result', $result, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );


		$stmt->setParameterContainer ( $cont );

		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"orderId" => DB2_PARAM_IN,
				"brand" => DB2_PARAM_IN,
				"size" => DB2_PARAM_IN,
				"filter" => DB2_PARAM_IN,
				"result" => DB2_PARAM_OUT,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$result = $cont->offsetGet ( 'result' );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'output' => '',
				'result' => $result,
				'message' => $message
		);


		if (! empty ( $result1 )) {
			$output = $result1->current ();

			// checking if there is result
			if (! empty ( $output )) {
				$i = 0;
				$outputArray = array ();
				$pricing = array();
				// setting up the current result in the array
				$outputArray [$i] = $output;
				// iterating for all the next results and if there is result then setting them up in the array
				while ( $outputNext = $result1->next () ) {
					$i ++;
					$outputArray [$i] = $outputNext;


				}
			// setting up the final array to the output variable
				if (! empty ( $output )) {
					$return_array ['output'] = $outputArray;
				}
			}
		}
		return $return_array;

	}

	/*
	 * callProcedureGetItemSearch  - This is the function to get the items by the search parameters on the item search page by brand, size and filter
	* @params - $orderId - This is the current order id that is being processed
	* $brand - This is the brand that is selected from the tab either brand tab or the brand/ size tab
	* $size - This is the size that is selected from the tab either size tab or the brand/ size tab
	* $filter - This is the value that is input in the item/description tab
	* @author - Rohit
	*/

	public function callProcedureGetItemSearch($orderId = '', $brand = '', $size = '', $filter = ''){
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Get_ItemSearch (?, ?, ?, ?, ?, ?)' );
		$result = '';
		$message = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'orderId', $orderId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'brand', $brand, $cont::TYPE_STRING );
		$cont->offsetSet ( 'size', $size, $cont::TYPE_STRING );
		$cont->offsetSet ( 'filter', $filter, $cont::TYPE_STRING );
		$cont->offsetSet ( 'result', $result, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );

		$stmt->setParameterContainer ( $cont );

		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"orderId" => DB2_PARAM_IN,
				"brand" => DB2_PARAM_IN,
				"size" => DB2_PARAM_IN,
				"filter" => DB2_PARAM_IN,
				"result" => DB2_PARAM_OUT,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$result = $cont->offsetGet ( 'result' );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'output' => '',
				'result' => $result,
				'message' => $message
		);


	if (! empty ( $result1 )) {
			$output = $result1->current ();

			// checking if there is result
			if (! empty ( $output )) {
				$i = 0;
				$outputArray = array ();
				// setting up the current result in the array
				$outputArray [$i] = $output;
				// iterating for all the next results and if there is result then setting them up in the array
				while ( $outputNext = $result1->next () ) {
					$i ++;
					$outputArray [$i] = $outputNext;
				}
				// setting up the final array to the output variable
				if (! empty ( $output )) {
					$return_array ['output'] = $outputArray;
				}
			}
		}
	// echo '<pre>'; print_r($return_array); die;
		return $return_array;

	}

/*
	 * callProcedureGetOrderTotals  - This is the function to get the order totals for the item search page
	* @params - $orderId - This is the order Id for the current order that is there in the session
	* @author - Rohit
	*/

public function callProcedureGetOrderTotals($orderId = ''){
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Get_OrderTotals(?, ?, ?, ?, ?, ?, ?, ?, ?)' );
		$amount = '';
		$caseQty = '';
		$palletQty = '';
		$prodWeight = '';
		$palletWeight = '';
		$totalWeight = '';
		$result = '';
		$message = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'orderId', $orderId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'amount', $amount, $cont::TYPE_STRING );
		$cont->offsetSet ( 'caseQty', $caseQty, $cont::TYPE_STRING );
		$cont->offsetSet ( 'palletQty', $palletQty, $cont::TYPE_STRING );
		$cont->offsetSet ( 'prodWeight', $prodWeight, $cont::TYPE_STRING );
		$cont->offsetSet ( 'palletWeight', $palletWeight, $cont::TYPE_STRING );
		$cont->offsetSet ( 'totalWeight', $totalWeight, $cont::TYPE_STRING );
		$cont->offsetSet ( 'result', $result, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );

		$stmt->setParameterContainer ( $cont );

		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"orderId" => DB2_PARAM_IN,
				"amount" => DB2_PARAM_OUT,
				"caseQty"  => DB2_PARAM_OUT,
				"palletQty"  => DB2_PARAM_OUT,
				"prodWeight"  => DB2_PARAM_OUT,
				"palletWeight"  => DB2_PARAM_OUT,
				"totalWeight"  => DB2_PARAM_OUT,
				"result" => DB2_PARAM_OUT,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$amount = $cont->offsetGet ( 'amount' );
		$caseQty = $cont->offsetGet ( 'caseQty' );
		$palletQty = $cont->offsetGet ( 'palletQty' );
		$prodWeight = $cont->offsetGet ( 'prodWeight' );
		$palletWeight = $cont->offsetGet ( 'palletWeight' );
		$totalWeight = $cont->offsetGet ( 'totalWeight' );
		$result = $cont->offsetGet ( 'result' );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'output' => '',
				'amount' => $amount,
				'caseQty'  => $caseQty,
				'palletQty'  => $palletQty,
				'prodWeight'  => $prodWeight,
				'palletWeight'  => $palletWeight,
				'totalWeight'  => $totalWeight,
				'result' => $result,
				'message' => $message
		);

		return $return_array;

	}

	/*
	 * callProcedureGetOrderLineItemsWithPrice  - This is the function to get the items list which are currently there in the current order
	* @params - $orderId - This is the current order id that is being processed
	* @author - Kailash
	*/
	
	
	
	
	// commneted action by kailash 21/7/2016 will REMOVE after confirmation
	
	
	/* public function callProcedureGetOrderLineItemsWithPrice($username='',$password='', $orderId = '', $custNumItemSearch='',$shipToItemSearch='', $reqDateItemSearch, $priceBookDateItemSearch){
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Get_OrderLineItems (?, ?, ?)' );
		$result = '';
		$message = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'orderId', $orderId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'result', $result, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );

		$stmt->setParameterContainer ( $cont );


		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"orderId" => DB2_PARAM_IN,
				"result" => DB2_PARAM_OUT,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$result = $cont->offsetGet ( 'result' );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'output' => '',
				'result' => $result,
				'message' => $message
		);


		if (! empty ( $result1 )) {
			$output = $result1->current ();

			$tk = \ToolkitService::getInstance("*LOCAL", $username,$password);
			$this->toolkitSetup($tk);


			// checking if there is result
			if (! empty ( $output )) {
				$i = 0;
				$outputArray = array ();
				//pricing data fetch start
				$pricingArray = array();
				$itemNum = $output['OL_ITEM_NUM'];
				$reqDate = $reqDateItemSearch; //20160315
				$priceBookDate = $priceBookDateItemSearch; //20160315

//				$pricingArray[$i] = $this->getPrice($tk, $itemNum, $custNumItemSearch, $shipToItemSearch, $reqDate, $priceBookDate);
// 				echo '<BR>IN LOOP getPrice() result = ' . $pricingArray[$i]['io_param'];

//				$output['OL_NET_PRICE'] =$pricingArray[$i]['io_param']['netPrice'];
				

				// setting up the current result in the array
				$outputArray [$i] = $output;
				// iterating for all the next results and if there is result then setting them up in the array


				while ( $outputNext = $result1->next () ) {
					$i ++;
					$itemNum = $outputNext['OL_ITEM_NUM'];
					$reqDate = $reqDateItemSearch; //20160315
					$priceBookDate = $priceBookDateItemSearch; //20160315
//					$pricingArray[$i] = $this->getPrice($tk, $itemNum, $custNumItemSearch, $shipToItemSearch, $reqDate, $priceBookDate);
//					$outputNext['OL_NET_PRICE'] =$pricingArray[$i]['io_param']['netPrice'];
					$outputArray [$i] = $outputNext;
				}

				// setting up the final array to the output variable
				if (! empty ( $output )) {
					$return_array ['output'] = $outputArray;
				}
			}
		}

		return $return_array;

	} */

	/*
	 * callProcedureGetOrderLineItems  - This is the function to get the items list which are currently there in the current order
	* @params - $orderId - This is the current order id that is being processed
	* @author - Rohit
	*/

	public function callProcedureGetOrderLineItems($orderId = ''){
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Get_OrderLineItems (?, ?, ?)' );
		$result = '';
		$message = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'orderId', $orderId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'result', $result, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );

		$stmt->setParameterContainer ( $cont );


		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"orderId" => DB2_PARAM_IN,
				"result" => DB2_PARAM_OUT,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$result = $cont->offsetGet ( 'result' );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'output' => '',
				'result' => $result,
				'message' => $message
		);


		if (! empty ( $result1 )) {
			$output = $result1->current ();

			// checking if there is result
			if (! empty ( $output )) {
				$i = 0;
				$outputArray = array ();
				// setting up the current result in the array
				$outputArray [$i] = $output;
				// iterating for all the next results and if there is result then setting them up in the array
				while ( $outputNext = $result1->next () ) {
					$i ++;
					$outputArray [$i] = $outputNext;

				}


				// setting up the final array to the output variable
				if (! empty ( $output )) {
					$return_array ['output'] = $outputArray;
				}
			}
		}

		return $return_array;

	}

/*
	 * callProcedureGetOrderLineItems  - This is the function to get the items list which are currently there in the current order
	* @params - $currentOrdNum - This is the current order id that is being processed
	* $itemNo - The item no of the row which is being updated
	* $quantity - This is the field value being input by the user
	* $uom - THis is the UOM value of the row being updated
	* $userId - The user id of the logged in user from the session
	* @author - Rohit
	*/

	public function callProcedureSaveOrderLineItem  ($currentOrdNum, $itemNo , $quantity, $uom, $userId){
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Save_OrderLineItem (?, ?, ?, ?, ?, ?, ?)' );
		$result = '';
		$message = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'currentOrdNum', $currentOrdNum, $cont::TYPE_STRING );
		$cont->offsetSet ( 'itemNo', $itemNo, $cont::TYPE_STRING );
		$cont->offsetSet ( 'quantity', $quantity, $cont::TYPE_STRING );
		$cont->offsetSet ( 'uom', $uom, $cont::TYPE_STRING );
		$cont->offsetSet ( 'userId', $userId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'result', $result, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );

		$stmt->setParameterContainer ( $cont );

		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"currentOrdNum" => DB2_PARAM_IN,
				"itemNo" => DB2_PARAM_IN,
				"quantity" => DB2_PARAM_IN,
				"uom" => DB2_PARAM_IN,
				"userId" => DB2_PARAM_IN,
				"result" => DB2_PARAM_OUT,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$result = $cont->offsetGet ( 'result' );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'output' => '',
				'result' => $result,
				'message' => $message
		);

		return $return_array;

	}

/*
	 * callProcedureSaveOrderSubstituteItem  - This is the function to get the items substitute list which are currently there in the current order
	* @params - $currentOrdNum - This is the current order id that is being processed
	* $itemNo - The item no of the row which is being updated
	* $action - This is the action that needs to be done for this item - 'ADD', 'RMV', 'UPD'
	* $userId - The user id of the logged in user from the session
	* @author - Rohit
	*/

	public function callProcedureSaveOrderSubstituteItem  ($currentOrdNum, $itemNo , $action, $userId){
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Save_OrderSubstituteItem (?, ?, ?, ?, ?, ?, ?)' );
		$result = '';
		$subsCount = '';
		$message = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'currentOrdNum', $currentOrdNum, $cont::TYPE_STRING );
		$cont->offsetSet ( 'itemNo', $itemNo, $cont::TYPE_STRING );
		$cont->offsetSet ( 'action', $action, $cont::TYPE_STRING );
		$cont->offsetSet ( 'userId', $userId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'subsCount', $subsCount, $cont::TYPE_STRING );
		$cont->offsetSet ( 'result', $result, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );

		$stmt->setParameterContainer ( $cont );

		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"currentOrdNum" => DB2_PARAM_IN,
				"itemNo" => DB2_PARAM_IN,
				"action" => DB2_PARAM_IN,
				"userId" => DB2_PARAM_IN,
				"subsCount" => DB2_PARAM_OUT,
				"result" => DB2_PARAM_OUT,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$subsCount = $cont->offsetGet ( 'subsCount' );
		$result = $cont->offsetGet ( 'result' );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'subsCount' => $subsCount,
				'result' => $result,
				'message' => $message
		);

		return $return_array;

	}


	public function callProcedureGetOrderSubstituteItems($currentOrdNum){
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Get_OrderSubstituteItems (?, ?, ?, ?)' );
		$subsCount = '';
		$result = '';
		$message = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'currentOrdNum', $currentOrdNum, $cont::TYPE_STRING );
		$cont->offsetSet ( 'subsCount', $subsCount, $cont::TYPE_STRING );
		$cont->offsetSet ( 'result', $result, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );

		$stmt->setParameterContainer ( $cont );

		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"currentOrdNum" => DB2_PARAM_IN,
				"subsCount" => DB2_PARAM_OUT,
				"result" => DB2_PARAM_OUT,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$subsCount = $cont->offsetGet ( 'subsCount' );
		$result = $cont->offsetGet ( 'result' );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'output' => '',
				'subsCount' => $subsCount,
				'result' => $result,
				'message' => $message
		);


		if (! empty ( $result1 )) {
			$output = $result1->current ();

			// checking if there is result
			if (! empty ( $output )) {
				$i = 0;
				$outputArray = array ();
				// setting up the current result in the array
				$outputArray [$i] = $output;
				// iterating for all the next results and if there is result then setting them up in the array
				while ( $outputNext = $result1->next () ) {
					$i ++;
					$outputArray [$i] = $outputNext;
				}
				// setting up the final array to the output variable
				if (! empty ( $output )) {
					$return_array ['output'] = $outputArray;
				}
			}
		}

		return $return_array;
	}

	/*
	 * callProcedureSubmitOrder  - This is the function to save the order details when user finally clicks the submit button
	* @params - $orderNumber - This is the current Order number from the session that we want to save
	*  $userId - This is the user Id from the session
	* @author - Rohit
	*/

	public function callProcedureSubmitOrder($orderNumber = '',  $userId = '',  $emailAddress = ''){
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_SubmitOrder(?, ?, ?, ?, ?, ?)' );
		$outOrderNumber = '';
		$result = '';
		$message = '';

		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'orderNumber', $orderNumber, $cont::TYPE_STRING );
		$cont->offsetSet ( 'userId', $userId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'emailAddress', $emailAddress, $cont::TYPE_STRING );
		$cont->offsetSet ( 'outOrderNumber', $outOrderNumber, $cont::TYPE_STRING );
		$cont->offsetSet ( 'result', $result, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );

		$stmt->setParameterContainer ( $cont );

		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"orderNumber" => DB2_PARAM_IN,
				"userId" => DB2_PARAM_IN,
				"emailAddress" => DB2_PARAM_IN,
				"outOrderNumber" => DB2_PARAM_OUT,
				"result" => DB2_PARAM_OUT,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$outOrderNumber = $cont->offsetGet ( 'outOrderNumber' );
		$result = $cont->offsetGet ( 'result' );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'output' => '',
				'outOrderNumber' => $outOrderNumber,
				'result' => $result,
				'message' => $message
		);
		return $return_array;

	}
	/*
	 *
	 */

	public function callProcedureGetOrderHistorySearch($userId = '', $customerGroup, $filterUserId = '', $fromDate = '0', $toDate = '0'){
	    
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Get_OrderHistorySearch (?, ?, ?, ?, ?, ?, ?)' );
		$subsCount = '';
		$result = '';
		$message = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'userId', $userId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'customerGroup', $customerGroup, $cont::TYPE_STRING );
		$cont->offsetSet ( 'filterUserId', $filterUserId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'fromDate', $fromDate, $cont::TYPE_STRING );
		$cont->offsetSet ( 'toDate', $toDate, $cont::TYPE_STRING );
		$cont->offsetSet ( 'result', $result, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );

		$stmt->setParameterContainer ( $cont );

		
		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
            "userId" => DB2_PARAM_IN,
			"customerGroup" => DB2_PARAM_IN,
		    "filterUserId" => DB2_PARAM_IN,
			"fromDate" => DB2_PARAM_IN,
			"toDate" => DB2_PARAM_IN,
			"result" => DB2_PARAM_OUT,
			"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$result = $cont->offsetGet ( 'result' );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'output' => '',
				'result' => $result,
				'message' => $message
		);


		if (! empty ( $result1 )) {
			$output = $result1->current ();

			// checking if there is result
			if (! empty ( $output )) {
				$i = 0;
				$outputArray = array ();
				// setting up the current result in the array
				$outputArray [$i] = $output;
				// iterating for all the next results and if there is result then setting them up in the array
				while ( $outputNext = $result1->next () ) {
					$i ++;
					$outputArray [$i] = $outputNext;
				}
				// setting up the final array to the output variable
				if (! empty ( $output )) {
					$return_array ['output'] = $outputArray;
				}
			}
		}

		return $return_array;
	}

	public function callProcedureExportGetOrderHistory($userId = '', $customerGroup, $filterUserId = '', $fromDate = '0', $toDate = '0'){
	     
	    // Get adapter
	    $dbAdapter = $this->tableGateway->getAdapter ();
	    $stmt = $dbAdapter->createStatement ();
	    $stmt->prepare ( 'CALL sp_Get_OrderHistory_Download (?, ?, ?, ?, ?, ?, ?)' );
	    $subsCount = '';
	    $result = '';
	    $message = '';
	    $cont = new ParameterContainer ();
	    $cont->offsetSet ( 'userId', $userId, $cont::TYPE_STRING );
	    $cont->offsetSet ( 'customerGroup', $customerGroup, $cont::TYPE_STRING );
	    $cont->offsetSet ( 'filterUserId', $filterUserId, $cont::TYPE_STRING );
	    $cont->offsetSet ( 'fromDate', $fromDate, $cont::TYPE_STRING );
	    $cont->offsetSet ( 'toDate', $toDate, $cont::TYPE_STRING );
	    $cont->offsetSet ( 'result', $result, $cont::TYPE_STRING );
	    $cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );
	
	    $stmt->setParameterContainer ( $cont );
	
	
	    // Set array with parameter directions (IN, OUT, INOUT)
	    $directs = array (
	        "userId" => DB2_PARAM_IN,
	        "customerGroup" => DB2_PARAM_IN,
	        "filterUserId" => DB2_PARAM_IN,
	        "fromDate" => DB2_PARAM_IN,
	        "toDate" => DB2_PARAM_IN,
	        "result" => DB2_PARAM_OUT,
	        "message" => DB2_PARAM_OUT
	    );
	
	    $result1 = $stmt->execute ( $cont, $directs );
	    $result = $cont->offsetGet ( 'result' );
	    $message = $cont->offsetGet ( 'message' );
	    $return_array = array (
	        'output' => '',
	        'result' => $result,
	        'message' => $message
	    );
	
	
	    if (! empty ( $result1 )) {
	        $output = $result1->current ();
	
	        // checking if there is result
	        if (! empty ( $output )) {
	            $i = 0;
	            $outputArray = array ();
	            // setting up the current result in the array
	            $outputArray [$i] = $output;
	            // iterating for all the next results and if there is result then setting them up in the array
	            while ( $outputNext = $result1->next () ) {
	                $i ++;
	                $outputArray [$i] = $outputNext;
	            }
	            // setting up the final array to the output variable
	            if (! empty ( $output )) {
	                $return_array ['output'] = $outputArray;
	            }
	        }
	    }
	
	    return $return_array;
	}
	
	/*
	 * callProcedureGetPLinkCustomers  - This is the function to be used at the customer order shipping page to get the ship to addresses
	* @params $searchFilter - These are the search filters that are input in the search field
	* @author - Rohit
	*/
	public function callProcedureGetPLinkCustomers($searchFilter = ''){

		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Get_PLink_Customers(?)' );

		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'searchFilter', $searchFilter, $cont::TYPE_STRING );
		$stmt->setParameterContainer ( $cont );


		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"searchFilter" => DB2_PARAM_IN,
		);

		//$stmt->order(array('PLC_CUST_NAME ASC'));
		$result1 = $stmt->execute ( $cont, $directs );

		$return_array = array (
				'output' => ''
		);

		if (! empty ( $result1 )) {
			$output = $result1->current ();

			// checking if there is result
			if (! empty ( $output )) {
				$i = 0;
				$outputArray = array ();
				// setting up the current result in the array
				$outputArray [$i] = $output;
				// iterating for all the next results and if there is result then setting them up in the array
				while ( $outputNext = $result1->next () ) {
					$i ++;
					$outputArray [$i] = $outputNext;
				}
				// setting up the final array to the output variable
				if (! empty ( $output )) {
					$return_array ['output'] = $outputArray;
				}
			}
		}

		return $return_array;

	}


	/*
	 * callProcedureGetAnnouncementsSearch  - This is the function to be used at the customer order shipping page to get the ship to addresses
	* @params
	* @author - Rohit
	*/
	public function callProcedureGetAnnouncementsSearch($facility = '', $custType = '', $startDate = '', $endDate = '', $userId = '', $announcementText = ''){

		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$message = '';

		$stmt->prepare ( 'CALL sp_Get_Announcements_Search(?, ?, ?, ?, ?, ?, ?)' );

		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'facility', $facility, $cont::TYPE_STRING );
		$cont->offsetSet ( 'custType', $custType, $cont::TYPE_STRING );
		$cont->offsetSet ( 'startDate', $startDate, $cont::TYPE_STRING );
		$cont->offsetSet ( 'endDate', $endDate, $cont::TYPE_STRING );
		$cont->offsetSet ( 'userId', $userId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'announcementText', $announcementText, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );
		$stmt->setParameterContainer ( $cont );


		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"facility" => DB2_PARAM_IN,
				"custType" => DB2_PARAM_IN,
				"startDate" => DB2_PARAM_IN,
				"endDate" => DB2_PARAM_IN,
				"userId" => DB2_PARAM_IN,
				"announcementText" => DB2_PARAM_IN,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'message' => $message,
				'output' => ''
		);

		if (! empty ( $result1 )) {
			$output = $result1->current ();

			// checking if there is result
			if (! empty ( $output )) {
				$i = 0;
				$outputArray = array ();
				// setting up the current result in the array
				$outputArray [$i] = $output;
				// iterating for all the next results and if there is result then setting them up in the array
				while ( $outputNext = $result1->next () ) {
					$i ++;
					$outputArray [$i] = $outputNext;
				}
				// setting up the final array to the output variable
				if (! empty ( $output )) {
					$return_array ['output'] = $outputArray;
				}
			}
		}

		return $return_array;

	}

	/*
	 * callProcedureGetCustomerTypes  - This is the function to fetch the customer types from the database
	* @params
	* @author - Rohit
	*/
	public function callProcedureGetCustomerTypes(){

		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$message = '';

		$stmt->prepare ( 'CALL sp_Get_Customer_Types(?)' );

		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );
		$stmt->setParameterContainer ( $cont );


		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'message' => $message,
				'output' => ''
		);

		if (! empty ( $result1 )) {
			$output = $result1->current ();

			// checking if there is result
			if (! empty ( $output )) {
				$i = 0;
				$outputArray = array ();
				// setting up the current result in the array
				$outputArray [$i] = $output;
				// iterating for all the next results and if there is result then setting them up in the array
				while ( $outputNext = $result1->next () ) {
					$i ++;
					$outputArray [$i] = $outputNext;
				}
				// setting up the final array to the output variable
				if (! empty ( $output )) {
					$return_array ['output'] = $outputArray;
				}
			}
		}

		return $return_array;

	}

/*
	 * callProcedureGetFacilities  - This is the function to fetch the facility types from the database
	* @params
	* @author - Rohit
	*/
	public function callProcedureGetFacilities(){

		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$message = '';

		$stmt->prepare ( 'CALL sp_Get_Facilities(?)' );

		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );
		$stmt->setParameterContainer ( $cont );
		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'message' => $message,
				'output' => ''
		);

		if (! empty ( $result1 )) {
			$output = $result1->current ();

			// checking if there is result
			if (! empty ( $output )) {
				$i = 0;
				$outputArray = array ();
				// setting up the current result in the array
				$outputArray [$i] = $output;
				// iterating for all the next results and if there is result then setting them up in the array
				while ( $outputNext = $result1->next () ) {
					$i ++;
					$outputArray [$i] = $outputNext;
				}
				// setting up the final array to the output variable
				if (! empty ( $output )) {
					$return_array ['output'] = $outputArray;
				}
			}
		}

		return $return_array;

	}



	/*
	 * callProcedureGetAnnouncementsSearch  - This is the function to be used at the customer order shipping page to get the ship to addresses
	* @params
	* @author - Rohit
	*/
	public function callProcedureSaveAnnouncement($userId = '', $facility = '', $custType = '', $startDate = '', $endDate = '', $announcementText = ''){

		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$announcementId = '';
		$message = '';

		$stmt->prepare ( 'CALL sp_Save_Announcement(?, ?, ?, ?, ?, ?, ?, ?)' );

		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'userId', $userId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'facility', $facility, $cont::TYPE_STRING );
		$cont->offsetSet ( 'custType', $custType, $cont::TYPE_STRING );
		$cont->offsetSet ( 'startDate', $startDate, $cont::TYPE_STRING );
		$cont->offsetSet ( 'endDate', $endDate, $cont::TYPE_STRING );
		$cont->offsetSet ( 'announcementText', $announcementText, $cont::TYPE_STRING );
		$cont->offsetSet ( 'announcementId', $announcementId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );
		$stmt->setParameterContainer ( $cont );


		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"userId" => DB2_PARAM_IN,
				"facility" => DB2_PARAM_IN,
				"custType" => DB2_PARAM_IN,
				"startDate" => DB2_PARAM_IN,
				"endDate" => DB2_PARAM_IN,
				"announcementText" => DB2_PARAM_IN,
				"announcementId" => DB2_PARAM_OUT,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$announcementId = $cont->offsetGet ( 'announcementId' );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'announcementId' => $announcementId,
				'message' => $message,
				'output' => ''
		);

		return $return_array;

	}

	/*
	 * callProcedureGetAnnouncementsDetail  - This is the function to be used at the announcement view or edit page
	* @params - $announcementId
	* @author - Rohit
	*/
	public function callProcedureGetAnnouncementsDetail($announcementId = ''){

		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$message = '';

		$stmt->prepare ( 'CALL sp_Get_Announcements_Detail(?, ?)' );

		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'announcementId', $announcementId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );
		$stmt->setParameterContainer ( $cont );


		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"announcementId" => DB2_PARAM_IN,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'message' => $message,
				'output' => ''
		);

		if (! empty ( $result1 )) {
			$output = $result1->current ();

			// checking if there is result
			if (! empty ( $output )) {
					$return_array ['output'] = $output;
				}
			}

		return $return_array;

	}

	/*
	 * callProcedureUpdateAnnouncement  - This is the function to be used at the announcement view or edit page
	* @params - $announcementId
	* @author - Rohit
	*/
public function callProcedureUpdateAnnouncement($announcementId = '', $userId = '', $facility = '', $custType = '', $startDate = '', $endDate = '', $announcementText = ''){

		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$message = '';

		$stmt->prepare ( 'CALL sp_Update_Announcement(?, ?, ?, ?, ?, ?, ?, ?)' );

		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'announcementId', $announcementId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'userId', $userId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'facility', $facility, $cont::TYPE_STRING );
		$cont->offsetSet ( 'custType', $custType, $cont::TYPE_STRING );
		$cont->offsetSet ( 'startDate', $startDate, $cont::TYPE_STRING );
		$cont->offsetSet ( 'endDate', $endDate, $cont::TYPE_STRING );
		$cont->offsetSet ( 'announcementText', $announcementText, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );
		$stmt->setParameterContainer ( $cont );
		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"announcementId" => DB2_PARAM_IN,
				"userId" => DB2_PARAM_IN,
				"facility" => DB2_PARAM_IN,
				"custType" => DB2_PARAM_IN,
				"startDate" => DB2_PARAM_IN,
				"endDate" => DB2_PARAM_IN,
				"announcementText" => DB2_PARAM_IN,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'message' => $message,
				'output' => ''
		);

		return $return_array;

	}

	/*
	 * callProcedureDeleteAnnouncement  - This is the function to be used at the announcement delete
	* @params - $announcementId
	* @author - Rohit
	*/
	public function callProcedureDeleteAnnouncement($announcementId = ''){

		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$message = '';

		$stmt->prepare ( 'CALL sp_Delete_Announcement(?, ?)' );

		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'announcementId', $announcementId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );
		$stmt->setParameterContainer ( $cont );


		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"announcementId" => DB2_PARAM_IN,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'message' => $message
		);

		return $return_array;

	}

	/*
	 * callProcedureCSRGetPLinkCustomers  - This is the function to be used at the customer list page for a CSR
	* @params - $searchFilters - This is the input value of the filter
	* $plcStatus - The status field
	* $custType - The Cust type field
	* @author - Rohit
	*/
	public function callProcedureCSRGetPLinkCustomers($searchFilters = '', $plcStatus = '', $custType = ''){

		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Get_PLinkCust_Search(?, ?, ?, ?)' );
		$message = '';

		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'searchFilters', $searchFilters, $cont::TYPE_STRING );
		$cont->offsetSet ( 'plcStatus', $plcStatus, $cont::TYPE_STRING );
		$cont->offsetSet ( 'custType', $custType, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );
		$stmt->setParameterContainer ( $cont );


		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"searchFilters" => DB2_PARAM_IN,
				"plcStatus" => DB2_PARAM_IN,
				"custType" => DB2_PARAM_IN,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'message' => $message,
				'output' => ''
		);


		if (! empty ( $result1 )) {
			$output = $result1->current ();

			// checking if there is result
			if (! empty ( $output )) {
				$i = 0;
				$outputArray = array ();
				// setting up the current result in the array
				$outputArray [$i] = $output;
				// iterating for all the next results and if there is result then setting them up in the array
				while ( $outputNext = $result1->next () ) {
					$i ++;
					$outputArray [$i] = $outputNext;
				}
				// setting up the final array to the output variable
				if (! empty ( $output )) {
					$return_array ['output'] = $outputArray;
				}
			}
		}

		return $return_array;

	}

	/*
	 * callProcedureGetCsrCustomersDetail  - This is the function to be used at the announcement view or edit page
	* @params - $custGroup
	* @author - Rohit
	*/
	public function callProcedureGetCsrCustomersDetail($custGroup = ''){

		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$message = '';

		$stmt->prepare ( 'CALL sp_Get_PLink_Customer_Detail(?, ?)' );

		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'custGroup', $custGroup, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );
		$stmt->setParameterContainer ( $cont );


		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"custGroup" => DB2_PARAM_IN,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'message' => $message,
				'output' => ''
		);

		if (! empty ( $result1 )) {
			$output = $result1->current ();

			// checking if there is result
			if (! empty ( $output )) {
				$return_array ['output'] = $output;
			}
		}

		return $return_array;

	}


	/*
	 * callProcedureSaveCsrCustomerEdit  - This is the function to save the csr customer
	* @params - $userId - This is the user Id that is there in the session
	* $CustGroup - This is the customer group input from the form
	* $CustName - This is the name for polarlink input from the form
	* $plcEmails - This is the email address input from the form
	* $plcStatus - This is the status input from the form
	* $custType - This is the cust type input from the form
	* @author - Rohit
	*/

	public function callProcedureSaveCsrCustomerEdit($userId = '', $CustGroup = '', $CustName = '', $plcEmails = '', $plcStatus = '', $custType = ''){
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Update_PlinkCust(?, ?, ?, ?, ?, ?, ?)' );
		$message = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'userId', $userId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'CustGroup', $CustGroup, $cont::TYPE_STRING );
		$cont->offsetSet ( 'CustName', $CustName, $cont::TYPE_STRING );
		$cont->offsetSet ( 'plcEmails', $plcEmails, $cont::TYPE_STRING );
		$cont->offsetSet ( 'plcStatus', $plcStatus, $cont::TYPE_STRING );
		$cont->offsetSet ( 'custType', $custType, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );

		$stmt->setParameterContainer ( $cont );

		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"userId" => DB2_PARAM_IN,
				"CustGroup" => DB2_PARAM_IN,
				"CustName" => DB2_PARAM_IN,
				"plcEmails" => DB2_PARAM_IN,
				"plcStatus" => DB2_PARAM_IN,
				"custType" => DB2_PARAM_IN,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'output' => '',
				'message' => $message
		);

		return $return_array;

	}


	/*
	 * callProcedureSaveCsrCustomerEdit  - This is the function to save the csr customer
	* @params - $userId - This is the user Id that is there in the session
	* $CustGroup - This is the customer group input from the form
	* $CustName - This is the name for polarlink input from the form
	* $plcEmails - This is the email address input from the form
	* $plcStatus - This is the status input from the form
	* $custType - This is the cust type input from the form
	* @author - Rohit
	*/

	public function callProcedureUpdatePlinkCustDfts($userId = '', $CustGroup = '', $defaultUom = '', $defaultShipMethod = '', $defaultCustomerNumber = '0', $defaultShipTo = '0'){
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Update_PlinkCustDfts(?, ?, ?, ?, ?, ?, ?)' );
		$message = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'userId', $userId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'CustGroup', $CustGroup, $cont::TYPE_STRING );
		$cont->offsetSet ( 'defaultUom', $defaultUom, $cont::TYPE_STRING );
		$cont->offsetSet ( 'defaultShipMethod', $defaultShipMethod, $cont::TYPE_STRING );
		$cont->offsetSet ( 'defaultCustomerNumber', $defaultCustomerNumber, $cont::TYPE_STRING );
		$cont->offsetSet ( 'defaultShipTo', $defaultShipTo, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );

		$stmt->setParameterContainer ( $cont );

		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"userId" => DB2_PARAM_IN,
				"CustGroup" => DB2_PARAM_IN,
				"defaultUom" => DB2_PARAM_IN,
				"defaultShipMethod" => DB2_PARAM_IN,
				"defaultCustomerNumber" => DB2_PARAM_IN,
				"defaultShipTo" => DB2_PARAM_IN,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );

		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'output' => '',
				'message' => $message
		);

		return $return_array;

	}


	/*
	 * callProcedureSaveCsrCustomerAdd  - This is the function to save the csr customer
	* @params - $userId - This is the user Id that is there in the session
	* $CustGroup - This is the customer group input from the form
	* $CustName - This is the name for polarlink input from the form
	* $plcEmails - This is the email address input from the form
	* $plcStatus - This is the status input from the form
	* $custType - This is the cust type input from the form
	* @author - Rohit
	*/

	public function callProcedureSaveCsrCustomerAdd($userId = '', $CustGroup = '', $CustName = '', $plcEmails = '', $plcStatus = '', $custType = ''){
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Save_PlinkCust(?, ?, ?, ?, ?, ?, ?, ?)' );
		$csrCustomerId = '';
		$message = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'userId', $userId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'CustGroup', $CustGroup, $cont::TYPE_STRING );
		$cont->offsetSet ( 'CustName', $CustName, $cont::TYPE_STRING );
		$cont->offsetSet ( 'plcEmails', $plcEmails, $cont::TYPE_STRING );
		$cont->offsetSet ( 'plcStatus', $plcStatus, $cont::TYPE_STRING );
		$cont->offsetSet ( 'custType', $custType, $cont::TYPE_STRING );
		$cont->offsetSet ( 'csrCustomerId', $csrCustomerId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );

		$stmt->setParameterContainer ( $cont );

		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"userId" => DB2_PARAM_IN,
				"CustGroup" => DB2_PARAM_IN,
				"CustName" => DB2_PARAM_IN,
				"plcEmails" => DB2_PARAM_IN,
				"plcStatus" => DB2_PARAM_IN,
				"custType" => DB2_PARAM_IN,
				"csrCustomerId" => DB2_PARAM_OUT,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$csrCustomerId = $cont->offsetGet ( 'csrCustomerId' );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'output' => '',
				'csrCustomerId' => $csrCustomerId,
				'message' => $message
		);
		
		return $return_array;

	}


	/*
	 * callProcedureDeleteCsrCustomer  - This is the function to be used at the csr customer delete
	* @params - $announcementId
	* @author - Rohit
	*/
	public function callProcedureDeleteCsrCustomer($custGroup = ''){

		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$message = '';

		$stmt->prepare ( 'CALL sp_Delete_PlinkCust(?, ?)' );

		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'custGroup', $custGroup, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );
		$stmt->setParameterContainer ( $cont );


		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"custGroup" => DB2_PARAM_IN,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'message' => $message
		);

		return $return_array;

	}

	/*
	 * callProcedureSaveCsrUserAdd - Function to save the user input values
	 */
	public function callProcedureSaveCsrUserAdd($userId = '', $CustGroup = '', $fname = '', $lname = '', $password = '', $CustNo = '', $default_uom = '', $defaultShipTo = '', $defaultShipMethod = ' ', $pluPolarCsr = '', $pluPlinkAdmin = '', $pluStatus = '', $pluEmail = '', $pluCrtUser = ''){
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Save_Plink_User(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)' );
		$csrUserId = '';
		$message = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'userId', trim($userId), $cont::TYPE_STRING );
		$cont->offsetSet ( 'CustGroup', trim($CustGroup), $cont::TYPE_STRING );
		$cont->offsetSet ( 'fname', trim($fname), $cont::TYPE_STRING );
		$cont->offsetSet ( 'lname', trim($lname), $cont::TYPE_STRING );
		$cont->offsetSet ( 'password', trim($password), $cont::TYPE_STRING );
		$cont->offsetSet ( 'CustNo', trim($CustNo), $cont::TYPE_STRING );
		$cont->offsetSet ( 'default_uom', $default_uom, $cont::TYPE_STRING );
		$cont->offsetSet ( 'defaultShipTo', trim($defaultShipTo), $cont::TYPE_STRING );
		$cont->offsetSet ( 'defaultShipMethod', $defaultShipMethod, $cont::TYPE_STRING );
		$cont->offsetSet ( 'pluPolarCsr', trim($pluPolarCsr), $cont::TYPE_STRING );
		$cont->offsetSet ( 'pluPlinkAdmin', trim($pluPlinkAdmin), $cont::TYPE_STRING );
		$cont->offsetSet ( 'pluStatus', trim($pluStatus), $cont::TYPE_STRING );
		$cont->offsetSet ( 'pluEmail', trim($pluEmail), $cont::TYPE_STRING );
		$cont->offsetSet ( 'pluCrtUser', trim($pluCrtUser), $cont::TYPE_STRING );
		$cont->offsetSet ( 'csrUserId', $csrUserId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );

		$stmt->setParameterContainer ( $cont );

		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"userId" => DB2_PARAM_IN,
				"CustGroup" => DB2_PARAM_IN,
				"fname" => DB2_PARAM_IN,
				"lname" => DB2_PARAM_IN,
				"password" => DB2_PARAM_IN,
				"CustNo" => DB2_PARAM_IN,
				"default_uom" => DB2_PARAM_IN,
				"defaultShipTo" => DB2_PARAM_IN,
				"defaultShipMethod" => DB2_PARAM_IN,
				"pluPolarCsr" => DB2_PARAM_IN,
				"pluPlinkAdmin" => DB2_PARAM_IN,
				"pluStatus" => DB2_PARAM_IN,
				"pluEmail" => DB2_PARAM_IN,
				"pluCrtUser" => DB2_PARAM_IN,
				"csrUserId" => DB2_PARAM_OUT,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$csrUserId = $cont->offsetGet ( 'csrUserId' );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'output' => '',
				'csrUserId' => $csrUserId,
				'message' => $message
		);

		return $return_array;

	}

	/*
	 * callProcedureGetCsrUsersDetail  - This is the function to be used at the user view or edit page
	* @params - $userId
	* @author - Rohit
	*/
	public function callProcedureGetCsrUsersDetail($userId = ''){

		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$message = '';

		$stmt->prepare ( 'CALL sp_Get_PLink_User_Detail(?, ?)' );

		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'userId', $userId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );
		$stmt->setParameterContainer ( $cont );


		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"userId" => DB2_PARAM_IN,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'message' => $message,
				'output' => ''
		);

		if (! empty ( $result1 )) {
			$output = $result1->current ();

			// checking if there is result
			if (! empty ( $output )) {
				$return_array ['output'] = $output;
			}
		}

		return $return_array;
	}

	/*
	 * callProcedureSaveCsrUserAdd - Function to save the user input values
	*/
	public function callProcedureSaveCsrUserEdit($userId = '', $fname = '', $lname = '', $password = '', $CustNo = '', $default_uom = '', $defaultShipTo = '', $defaultShipMethod = ' ', $pluPolarCsr = '', $pluPlinkAdmin = '', $pluStatus = '', $pluEmail = '', $pluChgUser = ''){
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Update_Plink_User(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)' );
		$message = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'userId', trim($userId), $cont::TYPE_STRING );
		$cont->offsetSet ( 'fname', trim($fname), $cont::TYPE_STRING );
		$cont->offsetSet ( 'lname', trim($lname), $cont::TYPE_STRING );
		$cont->offsetSet ( 'password', trim($password), $cont::TYPE_STRING );
		$cont->offsetSet ( 'CustNo', trim($CustNo), $cont::TYPE_STRING );
		$cont->offsetSet ( 'default_uom', $default_uom, $cont::TYPE_STRING );
		$cont->offsetSet ( 'defaultShipTo', trim($defaultShipTo), $cont::TYPE_STRING );
		$cont->offsetSet ( 'defaultShipMethod', $defaultShipMethod, $cont::TYPE_STRING );
		$cont->offsetSet ( 'pluPolarCsr', trim($pluPolarCsr), $cont::TYPE_STRING );
		$cont->offsetSet ( 'pluPlinkAdmin', trim($pluPlinkAdmin), $cont::TYPE_STRING );
		$cont->offsetSet ( 'pluStatus', trim($pluStatus), $cont::TYPE_STRING );
		$cont->offsetSet ( 'pluEmail', trim($pluEmail), $cont::TYPE_STRING );
		$cont->offsetSet ( 'pluChgUser', trim($pluChgUser), $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );

		$stmt->setParameterContainer ( $cont );

		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"userId" => DB2_PARAM_IN,
				"fname" => DB2_PARAM_IN,
				"lname" => DB2_PARAM_IN,
				"password" => DB2_PARAM_IN,
				"CustNo" => DB2_PARAM_IN,
				"default_uom" => DB2_PARAM_IN,
				"defaultShipTo" => DB2_PARAM_IN,
				"defaultShipMethod" => DB2_PARAM_IN,
				"pluPolarCsr" => DB2_PARAM_IN,
				"pluPlinkAdmin" => DB2_PARAM_IN,
				"pluStatus" => DB2_PARAM_IN,
				"pluEmail" => DB2_PARAM_IN,
				"pluChgUser" => DB2_PARAM_IN,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'output' => '',
				'message' => $message
		);

		return $return_array;

	}

	/*
	 * callProcedureDeleteCsrUser  - This is the function to be used at the csr customer delete
	* @params - $userId
	* @author - Rohit
	*/
	public function callProcedureDeleteCsrUser($userId = ''){

		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$message = '';

		$stmt->prepare ( 'CALL sp_Delete_Plink_User(?, ?)' );

		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'userId', $userId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );
		$stmt->setParameterContainer ( $cont );


		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"userId" => DB2_PARAM_IN,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'message' => $message
		);

		return $return_array;

	}


	public function callProcedurePrintPdf($orderNum){
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Get_OrderTotals(?, ?, ?, ?, ?, ?, ?, ?, ?)' );
		$result = '';
		$message = '';
		$amount = 0.0;
		$caseQty = 0.0;
		$palletQty = 0.0;
		$productWgt = 0.0;
		$palletWgt = 0.0;
		$totalWgt = 0.0;
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'orderNum', $orderNum, $cont::TYPE_STRING );
		$cont->offsetSet ( 'amount', $amount, $cont::TYPE_STRING );
		$cont->offsetSet ( 'caseQty', $caseQty, $cont::TYPE_STRING );
		$cont->offsetSet ( 'palletQty', $palletQty, $cont::TYPE_STRING );
		$cont->offsetSet ( 'productWgt', $productWgt, $cont::TYPE_STRING );
		$cont->offsetSet ( 'palletWgt', $palletWgt, $cont::TYPE_STRING );
		$cont->offsetSet ( 'totalWgt', $totalWgt, $cont::TYPE_STRING );
		$cont->offsetSet ( 'result', $result, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );

		$stmt->setParameterContainer ( $cont );

		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"orderNum" => DB2_PARAM_IN,
				"amount" => DB2_PARAM_OUT,
				"caseQty" => DB2_PARAM_OUT,
				"palletQty" => DB2_PARAM_OUT,
				"productWgt" => DB2_PARAM_OUT,
				"palletWgt" => DB2_PARAM_OUT,
				"totalWgt" => DB2_PARAM_OUT,
				"result" => DB2_PARAM_OUT,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$amount = $cont->offsetGet ( 'amount' );
		$caseQty = $cont->offsetGet ( 'caseQty' );
		$palletQty = $cont->offsetGet ( 'palletQty' );
		$productWgt = $cont->offsetGet ( 'productWgt' );
		$palletWgt = $cont->offsetGet ( 'palletWgt' );
		$totalWgt = $cont->offsetGet ( 'totalWgt' );
		$result = $cont->offsetGet ( 'result' );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'output' => '',
				'amount' => $amount,
				'caseQty' => $caseQty,
				'palletQty' => $palletQty,
				'productWgt' => $productWgt,
				'palletWgt' => $palletWgt,
				'totalWgt' => $totalWgt,
				'result' => $result,
				'message' => $message
		);
		
		return $return_array;

	}


	/*
	 * callProcedureGetUserSearch  - This is the function to fetch the facility types from the database
	* @params  -
	* 				$customerId - This is the customer group from the page we are viewing
	*	 			$searchFilters - This is the input value from the search filter
	*	 			$status - This is the input value from the status filter
	* @author - Rohit
	*/
	public function callProcedureGetUserSearch($customerId = '', $searchFilters = '', $status = ''){

		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$message = '';

		$stmt->prepare ( 'CALL sp_Get_PLink_User_Search(?, ?, ?, ?)' );

		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'customerId', $customerId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'searchFilters', $searchFilters, $cont::TYPE_STRING );
		$cont->offsetSet ( 'status', $status, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );
		$stmt->setParameterContainer ( $cont );


		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"customerId" => DB2_PARAM_IN,
				"searchFilters" => DB2_PARAM_IN,
				"status" => DB2_PARAM_IN,
				"message" => DB2_PARAM_OUT
		);

		$result1 = $stmt->execute ( $cont, $directs );
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'message' => $message,
				'output' => ''
		);

		if (! empty ( $result1 )) {
			$output = $result1->current ();

			// checking if there is result
			if (! empty ( $output )) {
				$i = 0;
				$outputArray = array ();
				// setting up the current result in the array
				$outputArray [$i] = $output;
				// iterating for all the next results and if there is result then setting them up in the array
				while ( $outputNext = $result1->next () ) {
					$i ++;
					$outputArray [$i] = $outputNext;
				}
				// setting up the final array to the output variable
				if (! empty ( $output )) {
					$return_array ['output'] = $outputArray;
				}
			}
		}

		return $return_array;

	}

	public function getUserDetails($identity = 'JVALANCE', $fields = null, $param = null, Select $select = null) {
		$this->conditions [$this->identityField] = $identity;
		if ($param != null) {
			if (is_array ( $param )) {
				foreach ( $param as $field => $value ) {
					$this->conditions [$field] = $value;
				}
			}
		}

		if (null === $select)
			$select = new Select ();

		$select->from ( $this->tableName );

		if ($fields !== null && is_array ( $fields ))
			$select->columns ( $fields );

		$select->where ( array (
				$this->identityField => $identity
		) );

		$resultSet = $this->tableGateway->selectWith ( $select );
		$row = $resultSet->current ();
		return $row;
	}



	public function db2_sp($userId = 0, $password = '') {
		$dbConn = $this->tableGateway->getAdapter ()->getDriver ()->getConnection ()->getResource ();
		// $dbConn = $this->getDbConnection();
		$sql = "call SPAUTHENTICATE_PLINK_USER(?, ?, ?, ?)";
		$pswd = '6c11d9114ee42a70ae988eecf63f296d';

		$stmt = db2_prepare ( $dbConn, $sql ) or die ( "<br>Prepare failed! " . db2_stmt_errormsg () );

		$result = '';
		$message = '';

		db2_bind_param ( $stmt, 1, "userId", DB2_PARAM_IN );
		db2_bind_param ( $stmt, 2, "pswd", DB2_PARAM_IN );
		db2_bind_param ( $stmt, 3, "result", DB2_PARAM_OUT );
		db2_bind_param ( $stmt, 4, "message", DB2_PARAM_OUT );

		print "Values of bound parameters before CALL:<br>";
		print "user_id: <b>{$userId}</b> <br> pswd: <b>{$password}</b> <br>";
		print "result: <b>{$result}</b> <br> message: <b>{$message}</b> <br>";

		if (db2_execute ( $stmt )) {
			print "<HR>Values of bound parameters after CALL:<br>";
			print "user_id: <b>{$userId}</b> <br> pswd: <b>{$password}</b> <br>";
			print "result: <b>{$result}</b> <br> message: <b>{$message}</b> <br>";
			$row = db2_fetch_assoc ( $stmt );
			var_dump ( $row );
		} else {
			print ("<br>Call failed! " . db2_stmt_errormsg ()) ;
		}
		die ( 'rrr' );
	}
	
	
	
	/*
	 * callProcedureGetOrderAttachmentDetail  - This is the function to get the order totals for the item search page
	* @params - $orderId - This is the order Id for the current order that is there in the session
	* @author - Rohit
	*/
	
	public function callProcedureGetWorkingDays ($fromDate = '', $toDate= ''){
	
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
	
	
		$stmt->prepare ( 'CALL sp_Get_WorkingDays(?, ?,?)' );
	
		$message = '';
		$result1 = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'FromDate', $fromDate, $cont::TYPE_STRING );
		$cont->offsetSet ( 'ToDate', $toDate, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );
		$stmt->setParameterContainer ( $cont );
		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"FromDate" => DB2_PARAM_IN,
				"ToDate" => DB2_PARAM_IN,
				"message" => DB2_PARAM_OUT
		);
		$result1 = $stmt->execute ( $cont, $directs );
	
	
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'output' => '',
				'message' => $message,
				'result' => $result1,
		);
		
		return $return_array;
	
	}
	
	
	/*
	 * callProcedureGetOrderAttachmentDetail  - This is the function to get the order totals for the item search page
	* @params - $orderId - This is the order Id for the current order that is there in the session
	* @author - Rohit
	*/
	
	public function callProcedureRequestDate ($custNum='',$shipto='',$scheduleDate='',$puDeliv=''){
	
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
	
	
		$stmt->prepare ( 'CALL sp_Get_RequestDate (?, ?,?,?)' );
	
		$message = '';
		$result1 = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'CustNum', $custNum, $cont::TYPE_STRING );
		$cont->offsetSet ( 'ShipTo', $shipto, $cont::TYPE_STRING );
		$cont->offsetSet ( 'ScheduleDate', $scheduleDate, $cont::TYPE_STRING );
		$cont->offsetSet ( 'PU_Deliv', $puDeliv, $cont::TYPE_STRING );
		$stmt->setParameterContainer ( $cont );
		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"CustNum" => DB2_PARAM_IN,
				"ShipTo" => DB2_PARAM_IN,
				"ScheduleDate" => DB2_PARAM_IN,
				"PU_Deliv" => DB2_PARAM_IN,
		);
		$result1 = $stmt->execute ( $cont, $directs );
	
	
		$message = $cont->offsetGet ( 'message' );
		$return_array = array (
				'output' => '',
				'message' => $message,
				'result' => $result1,
		);
	
		return $return_array;
	
	}
	
	public function testFunction(){
		return $this->tableGateway->getAdapter ()->getDriver ()->getConnection ()->getResource ();
	}
	
	/*to check the validCustomer Group addede 10/13/2016*/
	
	public function callProcedureCheckCustgroup($csr_cust_group){
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Validate_CustGroup(?, ?)' );		
		$message = '';
		$result1 = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'PLC_CUST_GRP', $csr_cust_group, $cont::TYPE_STRING );
		$cont->offsetSet ( 'Message', $message, $cont::TYPE_STRING );
		$stmt->setParameterContainer ( $cont );
		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"PLC_CUST_GRP" => DB2_PARAM_IN,
				"Message" => DB2_PARAM_OUT
		);
		$result1 = $stmt->execute ( $cont, $directs );
		$message = $cont->offsetGet ( 'Message' );
		$return_array = array (
				'output' => '',
				'Message' => $message,
				'result' => $result1,
		);
		
		return $return_array;
	}
	
	
	
	
	/*
	 * callProcedureSaveCsrCustomerAddWithDefaults  - This is the function to save the csr customer with defaults
	* @params - $userId - This is the user Id that is there in the session
	* $CustGroup - This is the customer group input from the form
	* $CustName - This is the name for polarlink input from the form
	* $plcEmails - This is the email address input from the form
	* $plcStatus - This is the status input from the form
	* $custType - This is the cust type input from the form
	* @author - kailash
	*/
	
	public function callProcedureSaveCsrCustomerAddWithDefaults($userId = '', $CustGroup = '', $CustName = '', $plcEmails = '', $plcStatus = '',$plcDftUom='',$plcDftShipMethod='',$plcDftShipTo='',$plcDCustNo=''){
		
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Save_PlinkCust (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)' );
		$csrCustomerId = '';
		$message = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'USER_ID', $userId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'PLC_CUST_GRP', $CustGroup, $cont::TYPE_STRING );
		$cont->offsetSet ( 'PLC_CUST_NAME', $CustName, $cont::TYPE_STRING );
		$cont->offsetSet ( 'PLC_EMAILS', $plcEmails, $cont::TYPE_STRING );
		$cont->offsetSet ( 'PLC_STATUS', $plcStatus, $cont::TYPE_STRING );
		//$cont->offsetSet ( 'CUST_TYPE', $custType, $cont::TYPE_STRING );
		$cont->offsetSet ( 'PLC_DFT_UOM',$plcDftUom, $cont::TYPE_STRING );
		$cont->offsetSet ( 'PLC_DFT_SHIP_METHOD', $plcDftShipMethod, $cont::TYPE_STRING );
		$cont->offsetSet ( 'PLC_CUSTNO', $plcDCustNo, $cont::TYPE_STRING );
		$cont->offsetSet ( 'PLC_DFT_SHIPTO', $plcDftShipTo, $cont::TYPE_STRING );
		
		$cont->offsetSet ( 'id', $csrCustomerId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'Message', $message, $cont::TYPE_STRING );
		$stmt->setParameterContainer ( $cont );
	
		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"USER_ID" => DB2_PARAM_IN,
				"PLC_CUST_GRP" => DB2_PARAM_IN,
				"PLC_CUST_NAME" => DB2_PARAM_IN,
				"PLC_EMAILS" => DB2_PARAM_IN,
				"PLC_STATUS" => DB2_PARAM_IN,
			//	"CUST_TYPE" => DB2_PARAM_IN,
				"PLC_DFT_UOM" => DB2_PARAM_IN,
				"PLC_DFT_SHIP_METHOD" => DB2_PARAM_IN,
				"PLC_CUSTNO" => DB2_PARAM_IN,
				"PLC_DFT_SHIPTO" => DB2_PARAM_IN,
				"id" => DB2_PARAM_OUT,
				"Message" => DB2_PARAM_OUT
		);
	
		$result1 = $stmt->execute ( $cont, $directs );
		$csrCustomerId = $cont->offsetGet ( 'id' );
		$message = $cont->offsetGet ( 'Message' );
		$return_array = array (
				'output' => '',
				'csrCustomerId' => $csrCustomerId,
				'message' => $message
		);
	
		return $return_array;
	
	}
	
}