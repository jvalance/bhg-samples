<?php
namespace User\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Adapter\ParameterContainer;



class OrderAttachmentTable {
	
	protected $tableGateway;
	protected $tableName = 'PLINK_ATTACHMENT';
	
	
	public function __construct(TableGateway $tableGateway) {
		$this->tableGateway = $tableGateway;
	}
	
	
	
	
	public function callProcedureSaveAttachment($current_user, $current_order, $file_original_name,$file_ext,$file_size, $file_description,$file_server_name){
	
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Save_PLink_Attachment (?, ?, ?, ?, ?, ?, ?, ?, ?)' );
		$plat_attach_no = time();
		$message = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'PLAT_ORDER_NO', $current_order, $cont::TYPE_STRING );
		$cont->offsetSet ( 'PLAT_UPL_FILENAME', $file_original_name, $cont::TYPE_STRING );
		$cont->offsetSet ( 'PLAT_IFS_FILENAME', $file_server_name, $cont::TYPE_STRING );
		$cont->offsetSet ( 'PLAT_FILE_EXT', $file_ext, $cont::TYPE_STRING );
		$cont->offsetSet ( 'PLAT_FILE_SIZE', $file_size, $cont::TYPE_STRING );
		$cont->offsetSet ( 'PLAT_DESCRIPTION', $file_description, $cont::TYPE_STRING );
		$cont->offsetSet ( 'USER', $current_user, $cont::TYPE_STRING );
		$cont->offsetSet ( 'PLAT_ATTACH_NO', $plat_attach_no, $cont::TYPE_STRING );
		$cont->offsetSet ( 'Message', $message, $cont::TYPE_STRING );
	
		$stmt->setParameterContainer ( $cont );
	
		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"PLAT_ORDER_NO" => DB2_PARAM_IN,
				"PLAT_UPL_FILENAME" => DB2_PARAM_IN,
				"PLAT_IFS_FILENAME" => DB2_PARAM_IN,
				"PLAT_FILE_EXT" => DB2_PARAM_IN,
				"PLAT_FILE_SIZE" => DB2_PARAM_IN,
				"PLAT_DESCRIPTION" => DB2_PARAM_IN,
				"USER" => DB2_PARAM_IN,
				"PLAT_ATTACH_NO" => DB2_PARAM_OUT,
				"Message" => DB2_PARAM_OUT
		);
	
		$result1 = $stmt->execute ( $cont, $directs );
		$plat_attach_number = $cont->offsetGet ( 'PLAT_ATTACH_NO' );
		$message = $cont->offsetGet ( 'Message' );
		$return_array = array (
				'output' => '',
				'plat_attach_number' => $plat_attach_number,
				'Message' => $message
		);
	
		return $return_array;
	
	}
	
	
	
	/*
	 * callProcedureGetOrderAttachedFiles  - This is the function to get the order totals for the item search page
	* @params - $currentOrdNum - This is the order Id for the current order that is there in the session
	* @author - Kailash
	*/
	
	public function callProcedureGetOrderAttachedFiles ($currentOrdNum = ''){
		
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		
		
		$stmt->prepare ( 'CALL sp_Get_PLink_Attachment_Search(?, ?)' );
	
		$message = '';
		$result1 = '';
		 $cont = new ParameterContainer ();
		 $cont->offsetSet ( 'PLAT_ORDER_NO', $currentOrdNum, $cont::TYPE_STRING );
		 $cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );
		 $stmt->setParameterContainer ( $cont );
		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"PLAT_ORDER_NO" => DB2_PARAM_IN,
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
	 * callProcedureRemoveOrderAttachment  - This is the function to be used at the announcement delete
	* @params - $platOrderNo
	* @author - Rohit
	*/
	public function callProcedureRemoveOrderAttachment($platOrderNo = '' , $platAttachNo = '' ){
		
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Delete_PLink_Attachment(?, ?, ?)' );
		$message = '';
		$result1 = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'PLAT_ORDER_NO', $platOrderNo, $cont::TYPE_STRING );
		$cont->offsetSet ( 'PLAT_ATTACH_NO', $platAttachNo, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );
		$stmt->setParameterContainer ( $cont );
		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"PLAT_ORDER_NO" => DB2_PARAM_IN,
				"PLAT_ATTACH_NO" => DB2_PARAM_IN,
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
	* @params - $platOrderNo - This is the order Id for the current order that is there in the session
	* @author - Kailash
	*/
	
	public function callProcedureGetOrderAttachmentDetail ($platOrderNo = '', $platAttachNo= ''){
		
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		$stmt->prepare ( 'CALL sp_Get_PLink_Attachment_Detail(?, ?,?)' );
		$message = '';
		$result1 = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'PLAT_ORDER_NO', $platOrderNo, $cont::TYPE_STRING );
		$cont->offsetSet ( 'PLAT_ATTACH_NO', $platAttachNo, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );
		$stmt->setParameterContainer ( $cont );
		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"PLAT_ORDER_NO" => DB2_PARAM_IN,
				"PLAT_ATTACH_NO" => DB2_PARAM_IN,
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
	 * callProcedureUpdateOrderAttachmentDescription  - This is the function to get the order totals for the item search page
	* @params - $orderId - This is the order Id for the current order that is there in the session
	* @author - Kailash
	*/
	
	public function callProcedureUpdateOrderAttachmentDescription ($platAttachNo = '', $platOrderNo = '',$platDescription = '',$userId = ''){
	
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
	
	
		$stmt->prepare ( 'CALL sp_Update_PLink_Attachment(?, ?, ?, ?, ?)' );
	
		$message = '';
		$result1 = '';
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'PLAT_ORDER_NO', $platOrderNo, $cont::TYPE_STRING );
		$cont->offsetSet ( 'PLAT_ATTACH_NO', $platAttachNo, $cont::TYPE_STRING );
		$cont->offsetSet ( 'PLAT_DESCRIPTION', $platDescription, $cont::TYPE_STRING );
		$cont->offsetSet ( 'USER', $userId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );
		$stmt->setParameterContainer ( $cont );
		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"PLAT_ORDER_NO" => DB2_PARAM_IN,
				"PLAT_ATTACH_NO" => DB2_PARAM_IN,
				"PLAT_DESCRIPTION" => DB2_PARAM_IN,
				"USER" => DB2_PARAM_IN,
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
	
	
	
	

}