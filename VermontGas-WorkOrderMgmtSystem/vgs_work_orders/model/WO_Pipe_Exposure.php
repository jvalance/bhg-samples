<?php
require_once '../model/VGS_DB_Table.php';
require_once '../model/Code_Values_Master.php';

class WO_Pipe_Exposure extends VGS_DB_Table
{
    public function __construct($conn) {
    	parent::__construct($conn);
//    	$this->checkPermissionByCategory('WO', 'INQUIRY');
    	
    	$this->tableName = 'WO_PIPE_EXPOSURE';
		$this->tablePrefix = 'WPE_';
	    $this->keyFields = array('WPE_WO_NUM', 'WPE_SEQNO');
    	$this->hasAuditFields = true;
    }

    /**
	 * Insert a record in the WO_PIPE_EXPOSURE table
	 * @param array $rec
	 */
    public function create( $rec ) {
    	$this->checkPermissionByCategory('WO', 'CREATE');
    	$rec['WPE_SEQNO'] = $this->getNextSeqNum($rec['WPE_WO_NUM']);
    	return $this->autoCreateRecord($rec);
    }

    public function getNextSeqNum($woNum) {
    	$select = new VGS_DB_Select();
    	$select->from = $this->tableName;
    	$select->columns = 'ifnull(max(WPE_SEQNO),0) as LAST_SEQNO';
    	$select->andWhere("WPE_WO_NUM = ?", $woNum);
    	$maxAry = $this->fetchRow($select->toString(), $select->parms);
    	return (int)$maxAry['LAST_SEQNO'] + 1;
    }
    
    /**
     * Update a record in the WO_PIPE_EXPOSURE table
     * @param array $rec
     */
    public function update( $rec ) {
    	$auth = $this->checkPermissionByCategory('WO', 'UPDATE');
    	return $this->autoUpdateRecord($rec);
    }

    public function retrieve( $data ) {
		$select = new VGS_DB_Select();
		$select->from = $this->tableName;
		$select->joins = 'left join WORKORDER_MASTER on WPE_WO_NUM = WO_NUM';
		$select->andWhere("WPE_WO_NUM = ?", trim($data['WPE_WO_NUM']) );
		$select->andWhere("WPE_SEQNO = ?", trim($data['WPE_SEQNO']) );
		
		$this->execListQuery($select->toString(), $select->parms);
		$row = db2_fetch_assoc( $this->stmt );
		if (is_array($row)) {
			$row['WPE_CHANGE_TIME'] = VGS_Form::getTimeStampOutputFormat($row['WPE_CHANGE_TIME']);
			$row['WPE_CREATE_TIME'] = VGS_Form::getTimeStampOutputFormat($row['WPE_CREATE_TIME']);
		}
		return $row;
    }
    
    public function getPipeExposuresForWO( $woNum ) {
		$select = new VGS_DB_Select();
		$select->from = $this->tableName;
		$select->andWhere("WPE_WO_NUM = ?", trim($woNum) );

		$this->execListQuery($select->toString(), $select->parms);
		$wpeWoNums = array();
		for ($exposureCount = 0; $row = db2_fetch_assoc( $this->stmt ); $exposureCount++) {
			$wpeWoNums[] = $row;
		};
		$wpeWorkOrders = array('count' => $exposureCount, 'wonums' => $wpeWoNums); 
		return $wpeWorkOrders;
    }
    
    // Get oldest exposure of main (distribution) pipe
    // useful for getting main-pipe data associated with a given work order
    public function getOldestMainExposureForWO( $woNum ) {
    	$select = new VGS_DB_Select();
    	$select->from = $this->tableName;
    	$select->andWhere("WPE_WO_NUM = ?", $woNum);
    	$select->andWhere("WPE_DESIGNATION = ?", 'D');
    	$select->order = 'WPE_EXPOSURE_DATE';
    
    	$this->execListQuery($select->toString(), $select->parms);
    	$wpe = db2_fetch_assoc( $this->stmt );

    	return $wpe;
    }
    
    
    public function getWPESearchSelect(
    	array &$screenData, 
    	VGS_DB_Select $select,
    	VGS_Search_Filter_Group $filter
	   	) 
	{
		$cvm = new Code_Values_Master($conn);

		// Add a drop down to allow and/or logical matching on filter values.
		$filter->addFilter('and_or', 'Match');
		$filter->setSpecialWhere('and_or');
		$filter->setDropDownList('and_or', array('and'=>'All', 'or'=>'Any'));
		$filter->setDefaultValue('and_or', 'or');
		
		$filter->addFilter('WPE_WO_NUM', 'W/O#');
		$filter->addFilter('PT_DESCRIPTION', 'Pipe Type', 'LIKE');
		$filter->addFilter('WO_TAX_MUNICIPALITY', 'Town');
		$filter->addFilter('WO_DESCRIPTION', 'Location', 'LIKE');
		
		$filter->addFilter('WPE_COATING_CONDITION', '<br />Coating cond.');
		$filter->addFilter('WPE_PIPE_CONDITION', '&nbsp;&nbsp;&nbsp;&nbsp;Pipe cond.');
		$filter->addFilter('WPE_INTERNAL_CONDITION', '<br />Int. cond.');

// 		$filter->addFilter('CP20_OPERATOR', '&nbsp;&nbsp;&nbsp;&nbsp;<b>CP20 is: </b>');
// 		$filter->setSpecialWhere('CP20_OPERATOR');
		
		$filter->addFilter('CP20_READING_low', '&nbsp;&nbsp;&nbsp;&nbsp;<b>CP20 >= </b>', '>=');
		$filter->setSpecialWhere('CP20_READING_low');
		$filter->setInputSize('CP20_READING_low', 3);
		
		$filter->addFilter('CP20_READING_high', '<b>CP20 <= </b>', '<=');
		$filter->setSpecialWhere('CP20_READING_high');
		$filter->setInputSize('CP20_READING_high', 3);

		$cvList = $cvm->getCodeValuesList('WPE_COATCOND');
		$filter->setDropDownList('WPE_COATING_CONDITION', $cvList, 'multi-select');

		$cvList = $cvm->getCodeValuesList('PIPE_CONDITION');
		$filter->setDropDownList('WPE_PIPE_CONDITION', $cvList, 'multi-select');

		$cvList = $cvm->getCodeValuesList('WPE_INTCOND');
		$filter->setDropDownList('WPE_INTERNAL_CONDITION', $cvList, 'multi-select');

// 		$cvList = $cvm->getCodeValuesList('COMP_OPERS', ' ');
// 		$filter->setDropDownList('CP20_OPERATOR', $cvList);

		$cvList = $cvm->getCodeValuesList('TOWN', '-- All --');
		$filter->setDropDownList('WO_TAX_MUNICIPALITY', $cvList);
		
		
    	$filter->saveRestoreFilters($screenData);
    	
    	
		$select->from = $this->tableName . ' wpe ';
		$select->columns = 	"wpe.*, wo.*, 
			cv1.CV_VALUE as WO_TYPE_DESC, 
			cv2.CV_VALUE as WO_STATUS_DESC, 
			PT_DESCRIPTION as PIPE_TYPE_DESC";
		
		$select->order = 'WPE_WO_NUM DESC, WPE_SEQNO DESC';
		$select->joins = "LEFT JOIN WORKORDER_MASTER wo on WPE_WO_NUM = WO_NUM 
			LEFT JOIN CODE_VALUES_MASTER as cv1 ON cv1.CV_GROUP = 'WO_TYPE' and cv1.CV_CODE = WO_TYPE  
			LEFT JOIN CODE_VALUES_MASTER as cv2 ON cv2.CV_GROUP = 'WO_STATUS' and cv2.CV_CODE = WO_STATUS   
			LEFT JOIN PIPE_TYPE_MASTER as pt ON PT_PIPE_TYPE = WO_PIPE_TYPE 
			";

		if (isset($screenData['filter_and_or'])) {
			$filter->setLogicalOperator($screenData['filter_and_or']);
		}
		$filter->renderWhere($screenData, $select);
		
		// CP20_READING filters
		$cp20_low = $screenData['filter_CP20_READING_low'];
		$cp20_high = $screenData['filter_CP20_READING_high'];
		
		if (isset($cp20_low) && trim($cp20_low) != '' 
		&& isset($cp20_high) && trim($cp20_high) != '')
		{
			$select->where(
						"WPE_CP20_READING BETWEEN ? AND  ?", 
						$filter->getLogicalOperator(), 
						array($cp20_low, 	$cp20_high)
					);
		} else {
			if (isset($cp20_low) && trim($cp20_low) != '') {
				$select->where("WPE_CP20_READING >= ?", $filter->getLogicalOperator(), $cp20_low);
			}
			if (isset($cp20_high) && trim($cp20_high) != '') {
				$select->where("WPE_CP20_READING <= ?", $filter->getLogicalOperator(), $cp20_high);
			}
		}
		
// 		pre_dump($select->toString());
// 		pre_dump($select->parms);
		
    }	
    
}