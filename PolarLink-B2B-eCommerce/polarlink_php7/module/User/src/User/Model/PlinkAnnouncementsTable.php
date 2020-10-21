<?php

namespace User\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Adapter\ParameterContainer;

class PlinkAnnouncementsTable {
	protected $tableGateway;
	protected $tableName = 'PLINK_ANNOUNCEMENTS';
	public function __construct(TableGateway $tableGateway) {
		$this->tableGateway = $tableGateway;
	}
	
	
	// public function callProcedureGetCurrentAnnouncements($userId = 0, $startDate = '0001-01-01', $endDate = '0001-01-01') {
	public function callProcedureGetCurrentAnnouncements($userId = 0, $shipTo = 0) {
		
		$message = '';
		// Get adapter
		$dbAdapter = $this->tableGateway->getAdapter ();
		$stmt = $dbAdapter->createStatement ();
		// $stmt->prepare ( 'CALL sp_Get_Current_Announcements(?, ?, ?, ?)' );
		$stmt->prepare ( 'CALL sp_Get_Current_Announcements(?, ?, ?)' );
		$cont = new ParameterContainer ();
		$cont->offsetSet ( 'userId', $userId, $cont::TYPE_STRING );
		$cont->offsetSet ( 'shipTo', $shipTo, $cont::TYPE_STRING );
		/* $cont->offsetSet ( 'startDate', $startDate, $cont::TYPE_STRING );
		$cont->offsetSet ( 'endDate', $endDate, $cont::TYPE_STRING );
		*/
		$cont->offsetSet ( 'message', $message, $cont::TYPE_STRING );
		$cont->offsetSetReference ( 'message', 'message' );
		$stmt->setParameterContainer ( $cont );
		
		
		// Set array with parameter directions (IN, OUT, INOUT)
		$directs = array (
				"userId" => DB2_PARAM_IN,
				"shipTo" => DB2_PARAM_IN,
// 				"startDate" => DB2_PARAM_IN,
// 				"endDate" => DB2_PARAM_IN,
				"message" => DB2_PARAM_OUT 
		);
		
		$result1 = $stmt->execute ( $cont, $directs );
		
		$message = $cont->offsetGet ( 'message' );
		
		$return_array = array (
				'message' => $message,
				'output' => '' 
		);
		
		if (! empty ( $result1 ) && empty(trim($message))) {
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
}