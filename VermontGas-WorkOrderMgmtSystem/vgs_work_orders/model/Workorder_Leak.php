<?php 
require_once '../model/VGS_DB_Table.php';
require_once '../common/vgs_utilities.php';
require_once '../forms/VGS_Search_Filter.php';

class Workorder_Leak extends VGS_DB_Table
{
	/**
	 * Array of fieldNames/fieldValues for one workorder record retrieved from database.
	 * @var array
	 */
	public $record; 

	public function __construct($conn) {
		parent::__construct($conn);
//    	$this->checkPermissionByCategory('WO', 'INQUIRY');
		
    	$this->tableName = 'WORKORDER_LEAK';
    	$this->tablePrefix = 'LK_';
	    $this->keyFields = array('LK_WO_NUM');
    	$this->hasAuditFields = false;
//    	pre_dump($this);
	}
	
    public function get_Workorder_Leak ($WO_NUM)
    {
    	$WO_NUM = (int) $WO_NUM;
    	$select = new VGS_DB_Select();
    	$this->setDefaultWOLeakSelect($select);
    	$select->andWhere('LK_WO_NUM = ?', $WO_NUM);
        $rowAry = $this->fetchRow($select->toString(), $select->parms);
        $rowAry = array_map('trim', $rowAry);
        $this->record = $rowAry;
        return $rowAry;
    }

    /**
	 * Insert a record in the WORKORDER_LEAK table
	 * @param array $rec
	 */
    public function createWorkOrder_Leak( $rec ) {
    	$this->checkPermissionByCategory('WO', 'CREATE');
    	return $this->autoCreateRecord($rec);
    }

    /**
     * Update a record in the WORKORDER_LEAK table
     * @param array $rec
     */
    public function updateWorkOrder_Leak( $rec ) {
    	$this->checkPermissionByCategory('WO', 'UPDATE');
    	return $this->autoUpdateRecord($rec);
    }
    
    /**
     * This will set up the default columns and joins in a VGS_DB_Select object for 
     * Work Order master record selection (including joins to code files etc)
     *   
     * @param VGS_DB_Select $select
     */
    
    public function setDefaultWOLeakSelect( VGS_DB_Select $select ) {
    	$select->from = $this->tableName . ' as lk ';
		$select->columns = 'lk.*';

//		$select->joins = <<<WO_JOINS_1
//			LEFT JOIN CODE_VALUES_MASTER as cv1 ON cv1.CV_GROUP = 'WO_TYPE' and cv1.CV_CODE = WO_TYPE 
    }

    
}
