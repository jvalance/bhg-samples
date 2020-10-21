<?php
require_once '../model/VGS_DB_Table.php';

class WO_Cleanup extends VGS_DB_Table
{
    public function __construct($conn) {
    	parent::__construct($conn);
//    	$this->checkPermissionByCategory('WO', 'INQUIRY');
    	
    	$this->tableName = 'WO_CLEANUP';
		$this->tablePrefix = 'WC_';
	    $this->keyFields = array('WC_WONUM', 'WC_CLEANUP_NUM');
    	$this->hasAuditFields = true;
    }

    /**
	 * Insert a record in the WO_CLEANUP table
	 * @param array $rec
	 */
    public function create( $rec ) {
    	$this->checkPermissionByCategory('WO', 'CREATE');
    	$next_CU_NUM = $this->getNextCleanupNum();
    	$rec['WC_CLEANUP_NUM'] = $next_CU_NUM;
    	return $this->autoCreateRecord($rec);
    }

    /**
     * Update a record in the WO_CLEANUP table
     * @param array $rec
     */
    public function update( $rec ) {
    	$this->checkPermissionByCategory('WO', 'UPDATE');
    	return $this->autoUpdateRecord($rec);
    }

    public function getNextCleanupNum() {
    	$select = new VGS_DB_Select();
    	$select->from = $this->tableName;
    	$select->columns = 'max(WC_CLEANUP_NUM) as LAST_CU_NUM';
        $maxAry = $this->fetchRow($select->toString());
        return (int)$maxAry['LAST_CU_NUM'] + 1;
    }
    
    public function retrieve( $data ) {
		$select = new VGS_DB_Select();
		$select->from = $this->tableName;
		$select->andWhere("WC_WONUM = ?", trim($data['WC_WONUM']) );
		$select->andWhere("WC_CLEANUP_NUM = ?", trim($data['WC_CLEANUP_NUM']) );

		$this->execListQuery($select->toString(), $select->parms);
		$row = db2_fetch_assoc( $this->stmt );
		if (is_array($row)) {
			$row['WC_CHANGE_TIME'] = VGS_Form::getTimeStampOutputFormat($row['WC_CHANGE_TIME']);
			$row['WC_CREATE_TIME'] = VGS_Form::getTimeStampOutputFormat($row['WC_CREATE_TIME']);
		}
		return $row;
    }
    
    public function getCleanupsForWO( $woNum ) {
		$select = new VGS_DB_Select();
		$select->from = $this->tableName;
		$select->andWhere("WC_WONUM = ?", trim($woNum) );

		$this->execListQuery($select->toString(), $select->parms);
		$wcWoNums = array();
		for ($cleanUpCount = 0; $row = db2_fetch_assoc( $this->stmt ); $cleanUpCount++) {
			$wcWoNums[] = $row;
		};
		$wcWorkOrders = array('count' => $cleanUpCount, 'wonums' => $wcWoNums); 
		return $wcWorkOrders;
    }
    
    public function getPendingCleanups( 
		$cleanupType,
		$startDateBeginRange,
    	$startDateBeginRange,
    	$townFilter,
    	$addressFilter
    ) 
    {
		$filter = new VGS_Search_Filter_Group();
		$filter->addFilter('WC_WONUM', '');
		$filter->addFilter('WO_DESCRIPTION', '', 'LIKE');
		$filter->addFilter('WO_TAX_MUNICIPALITY', '');
		$filter->addFilter('WC_CLEANUP_TYPE', '');
		$filter->addFilter('WC_VENDOR_NUM', '');
		$filter->addFilter('WC_EARLY_START_DATE', 'Begin start date range', '>=');
		$filter->addFilter('WC_EARLY_START_DATE', 'End start date range', '<=');

		$select = new VGS_DB_Select();
		$select->from = $this->tableName . ' wc ';
		$select->columns = 	'wc.*, wo.*, 
			WC_WONUM as "WO#",
			WC_CLEANUP_NUM as "Clean Up#",
			WC_CLEANUP_STATUS as "C/U Status",
			cv1.CV_VALUE as WO_TYPE_DESC, 
			cv2.CV_VALUE as WO_STATUS_DESC, 
			cv3.CV_VALUE as CLEANUP_TYPE_DESC'; 
		
		$select->order = 'WO_TAX_MUNICIPALITY, WO_DESCRIPTION, WC_CLEANUP_TYPE';
		$select->joins = "LEFT JOIN WORKORDER_MASTER wo on WC_WONUM = WO_NUM
			LEFT JOIN CODE_VALUES_MASTER as cv1 ON cv1.CV_GROUP = 'WO_TYPE' and cv1.CV_CODE = WO_TYPE  
			LEFT JOIN CODE_VALUES_MASTER as cv2 ON cv2.CV_GROUP = 'WO_STATUS' and cv2.CV_CODE = WO_STATUS   
			LEFT JOIN CODE_VALUES_MASTER as cv3 ON cv3.CV_GROUP = 'WC_CLEANUP_TYPES' and cv3.CV_CODE = WC_CLEANUP_TYPE   
			";
		
		$filter->renderWhere($screenData, $select);

		
		$this->execListQuery($select->toString(), $select->parms);
		$wcWoNums = array();
		for ($cleanUpCount = 0; $row = db2_fetch_assoc( $this->stmt ); $cleanUpCount++) {
			$wcWoNums[] = $row;
		};
		$wcWorkOrders = array('count' => $cleanUpCount, 'wonums' => $wcWoNums); 
		return $wcWorkOrders;
    }
    
    public function buildFilteredSelect(
    	array &$screenData, 
    	VGS_DB_Select $select,
    	VGS_Search_Filter_Group $filter
	) 
	{
		$cvm = new Code_Values_Master($this->db_conn_obj);
		$filter->addFilter('WC_WONUM', 'W/O#');
		$filter->addFilter('WO_DESCRIPTION', 'Street Addr.', 'LIKE');
		$filter->addFilter('WO_TAX_MUNICIPALITY', 'Town');
		$filter->addFilter('WC_CLEANUP_TYPE', 'C/U Type');
		$filter->addFilter('WC_VENDOR_NUM', '<br>Vendor');
		$filter->addFilter('WC_CLEANUP_STATUS', 'Status');

		$filter->addFilter('WC_START_DATE_FROM', 'From Date');
		$filter->setSpecialWhere('WC_START_DATE_FROM');
		$filter->setDateField('WC_START_DATE_FROM');

		$filter->addFilter('WC_START_DATE_TO', 'To Date');
		$filter->setSpecialWhere('WC_START_DATE_TO');
		$filter->setDateField('WC_START_DATE_TO');
		
		$cvList = $cvm->getCodeValuesList('WC_CLEANUP_TYPES', '-- All --');
		$filter->setDropDownList('WC_CLEANUP_TYPE', $cvList);
		
		$cvList = $cvm->getCodeValuesList('TOWN', '-- All --');
		//ary_dump($cvList);
		$filter->setDropDownList('WO_TAX_MUNICIPALITY', $cvList);
		
		$statusValues = array(''=>'- All -', 'Open'=>'Open', 'Complete'=>'Complete');
		$filter->setDropDownList('WC_CLEANUP_STATUS', $statusValues);

		$filter->saveRestoreFilters($screenData);

		$select->from = $this->tableName . ' wc ';
		$select->columns = ' 
			WC_WONUM,
			WC_CLEANUP_NUM,
			WC_CLEANUP_STATUS,
			WC_ADDR_STREET,
			WC_ADDR_CITY,
			WO_TAX_MUNICIPALITY,
			WO_DESCRIPTION,
			WC_VENDOR_NUM,
			WC_EARLY_START_DATE,
			cv1.CV_VALUE as WO_TYPE_DESC, 
			cv2.CV_VALUE as WO_STATUS_DESC, 
			cv3.CV_VALUE as CLEANUP_TYPE_DESC, 
			cv4.CV_VALUE as TOWN_DESC'; 
		 
		$select->order = 'WC_WONUM DESC, WC_CLEANUP_NUM';
		$select->joins = "LEFT JOIN WORKORDER_MASTER wo on WC_WONUM = WO_NUM
			LEFT JOIN CODE_VALUES_MASTER as cv1 ON cv1.CV_GROUP = 'WO_TYPE' and cv1.CV_CODE = WO_TYPE  
			LEFT JOIN CODE_VALUES_MASTER as cv2 ON cv2.CV_GROUP = 'WO_STATUS' and cv2.CV_CODE = WO_STATUS   
			LEFT JOIN CODE_VALUES_MASTER as cv3 ON cv3.CV_GROUP = 'WC_CLEANUP_TYPES' and cv3.CV_CODE = WC_CLEANUP_TYPE
			LEFT JOIN CODE_VALUES_MASTER as cv4 ON cv4.CV_GROUP = 'TOWN' and cv4.CV_CODE = WO_TAX_MUNICIPALITY 
			"; 
 		
		$filter->renderWhere($screenData, $select);
		
		// WC_EARLY_START_DATE special filters
		if (isset($screenData['filter_WC_START_DATE_FROM']) 
			&& trim($screenData['filter_WC_START_DATE_FROM']) != '') 
		{
			$from_date = trim($screenData['filter_WC_START_DATE_FROM']);
	    	$select->andWhere("WC_EARLY_START_DATE >= ?", $from_date);
		}
		
		if (isset($screenData['filter_WC_START_DATE_TO']) 
			&& trim($screenData['filter_WC_START_DATE_TO']) != '') 
		{
			$to_date = trim($screenData['filter_WC_START_DATE_TO']);
	    	$select->andWhere("WC_EARLY_START_DATE <= ?", $to_date);
		}
		
    }	
    
}