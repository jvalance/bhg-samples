<?php
require_once '../model/VGS_DB_Table.php';

class Account extends VGS_DB_Table
{
    public function __construct($conn) {
    	parent::__construct($conn);
    	/**
    	 * TODO: Setup security category for CUST or ECIS
    	 */
//		$this->checkPermissionByCategory('WO', 'INQUIRY');

		$this->tableName = 'UACT';
    	// Following not needed for UACT - since no maintenance allowed
		$this->tablePrefix = 'UM';
	    $this->keyFields = array('UMACT');
    	$this->hasAuditFields = false;
    }

    public function retrieveByPremiseNo( $premiseNo ) {
		$select = new VGS_DB_Select();
		$select->from = $this->tableName . ' as ac1 ';
		
		// Match on premise#
		$select->andWhere("trim(ac1.umprm) = ?", trim($premiseNo) );
		// Select account with most recent connect date
		$select->andWhere("ac1.UMPCN = (select max(ac2.UMPCN) from uact ac2 where trim(ac2.umprm) = ?)", trim($premiseNo) );
		
		$this->execListQuery($select->toString(), $select->parms);
		$row = db2_fetch_assoc( $this->stmt );

		return $row;
    }
    
    public static function formatPhoneNo( $phoneNo ) {
    	$phoneNo = trim($phoneNo);
    	$phnLength = strlen($phoneNo);
    	$fmtdPhoneNo = $phoneNo;
    	
    	switch ($phnLength) {
    		case 7:
    			$fmtdPhoneNo = 
    				substr($phoneNo, 0, 3) .
    				'-' .
    				substr($phoneNo, 3, 4);
    			break;
    		case 10:
    			$fmtdPhoneNo = 
    				substr($phoneNo, 0, 3) .
    				'-' .
    				substr($phoneNo, 3, 3) .
    				'-' .
    				substr($phoneNo, 6, 4);
    			break;
    			
       	}
       	return $fmtdPhoneNo;
    }
}
